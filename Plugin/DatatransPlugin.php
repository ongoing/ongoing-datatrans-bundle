<?php

namespace Ongoing\DatatransBundle\Plugin;

use JMS\Payment\CoreBundle\Model\ExtendedDataInterface;
use JMS\Payment\CoreBundle\Model\FinancialTransactionInterface;
use JMS\Payment\CoreBundle\Plugin\AbstractPlugin;
use JMS\Payment\CoreBundle\Plugin\Exception\Action\VisitUrl;
use JMS\Payment\CoreBundle\Plugin\Exception\ActionRequiredException;
use JMS\Payment\CoreBundle\Plugin\Exception\FinancialException;
use JMS\Payment\CoreBundle\Plugin\PluginInterface;
use Omnipay\Common\CreditCard;
use Ongoing\DatatransBundle\Client\Client;
use Ongoing\DatatransBundle\Model\Parameter;
use Ongoing\DatatransBundle\Model\Request\Request;
use Ongoing\DatatransBundle\Model\Response\AuthorizationResponse;
use Symfony\Component\HttpFoundation\RequestStack;

class DatatransPlugin extends AbstractPlugin
{
    const PAYMENT_SYSTEM_NAME = 'ongoing_datatrans';

    /**
     * @var Client
     */
    protected $client;
    /**
     * @var RequestStack
     */
    private $requestStack;

    /**
     * @var array
     */
    private $returnUrls;

    /**
     * DatatransPlugin constructor.
     *
     * @param Client $client
     * @param RequestStack $requestStack
     * @param array $returnUrls
     */
    public function __construct(Client $client, RequestStack $requestStack, array $returnUrls = [])
    {
        $this->client = $client;
        $this->requestStack = $requestStack;
        $this->returnUrls = $returnUrls;
    }

    /**
     * @param FinancialTransactionInterface $transaction
     * @param $retry
     */
    public function approveAndDeposit(FinancialTransactionInterface $transaction, $retry)
    {
        $this->approve($transaction, $retry);
        $this->deposit($transaction, $retry);
    }

    /**
     * @param FinancialTransactionInterface $transaction
     * @param $retry
     * @throws ActionRequiredException
     */
    public function approve(FinancialTransactionInterface $transaction, $retry)
    {
        $authRequest = $this->buildAuthorizationRequest($transaction);

        //check if an alias is set, then authorize payment directly without redirecting user
        if ($authRequest->has(Parameter::PARAM_ALIAS_CC)) {
            //do authorization
            $authRespone = $this->client->authorizePayment($authRequest, true);
            $this->setConfirmationData($transaction, $authRespone);

            return;
        }

        //confirm request
        if ($this->getRequest()->request->has('responseCode')) {
            try {
                $authResponse = $this->getAuthorizationResponse();
                $this->throwUnlessValidPayConfirm($authResponse, $authRequest);
                $this->setConfirmationData($transaction, $authResponse);
                $transaction->setResponseCode(PluginInterface::RESPONSE_CODE_SUCCESS);
                $transaction->setReasonCode(PluginInterface::REASON_CODE_SUCCESS);
            } catch (\Exception $e) {
                $this->throwFinancialTransaction($transaction, $e->getMessage());
            }
        } else {
            //create redirect url
            $redirectUrl = $this->client->getAuthorizationUrl($authRequest);

            $actionException = new ActionRequiredException('Not yet authorized');
            $actionException->setFinancialTransaction($transaction);
            $actionException->setAction(new VisitUrl($redirectUrl));

            throw $actionException;
        }
    }

    /**
     * @param FinancialTransactionInterface $transaction
     * @param bool $retry
     */
    public function deposit(FinancialTransactionInterface $transaction, $retry)
    {
        //do deposit
        $settlementParameter = $this->buildSettlementRequest($transaction);
        $settlementResponse = $this->client->completePayment($settlementParameter);

        if ($settlementResponse->hasError()) {
            $this->throwFinancialTransaction(
                $transaction,
                new \Exception()
            );
        }

        $transaction->setReferenceNumber($settlementResponse->getReferenceNumber());
        $transaction->setProcessedAmount($settlementResponse->getAmount());
        $transaction->setResponseCode(PluginInterface::RESPONSE_CODE_SUCCESS);
        $transaction->setReasonCode(PluginInterface::REASON_CODE_SUCCESS);
    }

    /**
     *
     * @param string $paymentSystemName
     *
     * @return bool
     */
    public function processes($paymentSystemName)
    {
        return $paymentSystemName === self::PAYMENT_SYSTEM_NAME;
    }

    /**
     * @param FinancialTransactionInterface $transaction
     * @return Request
     */
    private function buildAuthorizationRequest(FinancialTransactionInterface $transaction)
    {
        $data = $transaction->getExtendedData();
        $payment = $transaction->getPayment();
        $paymentInstruction = $payment->getPaymentInstruction();

        $params = [
            Parameter::PARAM_AMOUNT         => $transaction->getRequestedAmount(),
            Parameter::PARAM_CURRENCY       => $paymentInstruction->getCurrency(),
            Parameter::PARAM_RETURN_URL     => $this->getReturnUrl(Parameter::PARAM_RETURN_URL, $data),
            Parameter::PARAM_ERROR_URL      => $this->getReturnUrl(Parameter::PARAM_ERROR_URL, $data),
            Parameter::PARAM_CANCEL_URL     => $this->getReturnUrl(Parameter::PARAM_CANCEL_URL, $data),
            Parameter::PARAM_RETURNMASKEDCC => 'yes',
            Parameter::PARAM_USEALIAS       => 'yes',
            Parameter::PARAM_TRANSACTIONID  => $data->has(Parameter::PARAM_TRANSACTIONID) ? $data->get(
                Parameter::PARAM_TRANSACTIONID
            ) : $payment->getId(),
        ];

        if ($data->has(Parameter::PARAM_ALIAS_CC)) {
            $params[Parameter::PARAM_ALIAS_CC] = $data->get(Parameter::PARAM_ALIAS_CC);
            $card = new CreditCard(
                [
                    'number'       => $data->get(Parameter::PARAM_ALIAS_CC),
                    'expiry_month' => '12',
                    'expiry_year'  => '18',
                ]
            );
            $params['card'] = $card;
        }

        return new Request($params);
    }

    /**
     * @param FinancialTransactionInterface $transaction
     * @return Request
     */
    private function buildSettlementRequest(FinancialTransactionInterface $transaction)
    {
        $trackingId = $transaction->getPayment()->getApproveTransaction()->getTrackingId();
        $extendedData = $transaction->getExtendedData();
        $payment = $transaction->getPayment();
        $paymentInstruction = $payment->getPaymentInstruction();

        $params = [
            Parameter::PARAM_UPPTRANSACTIONID => $trackingId,
            Parameter::PARAM_AMOUNT           => $transaction->getRequestedAmount(),
            Parameter::PARAM_CURRENCY         => $paymentInstruction->getCurrency(),
            Parameter::PARAM_TRANSACTIONID    => $extendedData->has('reference_number') ? $extendedData->get(
                'reference_number'
            ) : $payment->getId(),
        ];

        return new Request($params);
    }

    /**
     * @return null|\Symfony\Component\HttpFoundation\Request
     */
    protected function getRequest()
    {
        return $this->requestStack->getCurrentRequest();
    }

    /**
     * Throw financial transaction
     *
     * @param FinancialTransactionInterface $transaction
     * @param $e
     * @throws \JMS\Payment\CoreBundle\Plugin\Exception\FinancialException
     */
    protected function throwFinancialTransaction(FinancialTransactionInterface $transaction, $e)
    {
        $ex = new FinancialException('PaymentStatus is not completed: '.$e);
        $ex->setFinancialTransaction($transaction);
        $transaction->setResponseCode('Failed');
        $transaction->setReasonCode($e);

        throw $ex;
    }

    /**
     * @return AuthorizationResponse
     */
    protected function getAuthorizationResponse()
    {
        $params = $this->getRequest()->request->all();
        $authResponse = new AuthorizationResponse($params);

        return $authResponse;
    }

    /**
     * @param AuthorizationResponse $authResponse
     * @param Request $authRequest
     * @throws \Exception
     */
    protected function throwUnlessValidPayConfirm(AuthorizationResponse $authResponse, Request $authRequest)
    {
        $valid = $authResponse->getAmount() == $authRequest->getAmount() && $authResponse->getCurrency(
        ) == $authRequest->getCurrency();

        if (!$valid) {
            throw new \Exception(
                sprintf(
                    'Invalid response! Requested: [%s %s] | Confirmed: [%s %s]',
                    $authRequest->getAmount(),
                    $authRequest->getCurrency(),
                    $authResponse->getAmount(),
                    $authResponse->getCurrency()
                )
            );
        }
    }

    private function setConfirmationData(
        FinancialTransactionInterface $transaction,
        AuthorizationResponse $authResponse
    ) {
        //set base data
        $transaction->setReferenceNumber($authResponse->getReferenceNumber());
        $transaction->setTrackingId($authResponse->getUppTransactionId());
        $transaction->setProcessedAmount($authResponse->getAmount());

        //set creditcard data if exists
        $extendedData = $transaction->getExtendedData();

        $ccData = array_filter(
            [
                Parameter::PARAM_MASKED_CC => $authResponse->getMaskedCC(),
                Parameter::PARAM_EXPM      => $authResponse->getExpirationMonth(),
                Parameter::PARAM_EXPY      => $authResponse->getExpirationYear(),
                Parameter::PARAM_ALIAS_CC  => $authResponse->getAliasCC(),
                Parameter::PARAM_PMETHOD   => $authResponse->getPMethod()
            ]
        );

        foreach ($ccData as $key => $value) {
            $extendedData->set($key, $value);
        }
    }

    /**
     * @param $type - [ returnUrl, errorUrl, cancelUrl ]
     * @param ExtendedDataInterface $data
     * @return mixed
     */
    private function getReturnUrl($type, ExtendedDataInterface $data)
    {
        if ($data->has($type)) {
            return $data->get($type);
        }

        if (isset($this->returnUrls[$type])) {
            return $this->returnUrls[$type];
        }

        throw new \RuntimeException('You must configure a return url.');
    }
}

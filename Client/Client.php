<?php

namespace Ongoing\DatatransBundle\Client;

use Omnipay\Datatrans\Message\CompletePurchaseResponse;
use Omnipay\Datatrans\Message\PurchaseResponse;
use Omnipay\Datatrans\Message\XmlAuthorizationResponse;
use Ongoing\DatatransBundle\Model\Request\Request;
use Ongoing\DatatransBundle\Model\Response\AbstractResponse;
use Ongoing\DatatransBundle\Model\Response\AuthorizationResponse;
use Ongoing\DatatransBundle\Model\Response\SettlementResponse;
use Ongoing\DatatransBundle\Model\Response\StatusResponse;

class Client
{

    /**
     * @var GatewayFactory
     */
    private $gatewayFactory;

    /**
     * @var \Omnipay\Datatrans\Gateway
     */
    private $gateway;

    /**
     * @var \Omnipay\Datatrans\XmlGateway
     */
    private $xmlGateway;

    /**
     * Client constructor.
     *
     * @param GatewayFactory $gatewayFactory
     */
    public function __construct(GatewayFactory $gatewayFactory)
    {
        $this->gatewayFactory = $gatewayFactory;
        $this->gateway = $gatewayFactory->getGateway();
        $this->xmlGateway = $gatewayFactory->getXmlGateway();
    }

    /**
     * @param Request $authRequest
     * @return string
     */
    public function getAuthorizationUrl(Request $authRequest)
    {
        // Send purchase request
        $request = $this->gateway->purchase($authRequest->getData());

        /** @var PurchaseResponse $response */
        $response = $request->send();
        $url = $response->getRedirectUrl();
        $data = $response->getRedirectData();

        $url = sprintf("%s?%s", $url, http_build_query($data));

        return $url;
    }

    /**
     * @param Request $settlementRequest
     * @return AbstractResponse
     */
    public function completePayment(Request $settlementRequest)
    {
        //check if settlement is needed
        $apiStatusResponse = $this->xmlGateway->status($settlementRequest->getData())->send();
        $statusResponse = new StatusResponse($apiStatusResponse->getData());

        if ($statusResponse->isWaitingForSettlement()) {
            /** @var CompletePurchaseResponse $response */
            $response = $this->xmlGateway->settlementDebit($settlementRequest->getData())->send();

            return new SettlementResponse(array_merge_recursive($response->getData(), $apiStatusResponse->getData()));
        }

        return $statusResponse;
    }

    /**
     * @param Request $request
     * @param bool $aliasPayment
     * @return AuthorizationResponse
     */
    public function authorizePayment(Request $request, $aliasPayment = true)
    {
        //for aliaspayment, merchant id and sign from mail/phone order has to be used
        // it is set for the duration of one request
        if ($aliasPayment) {
            $this->xmlGateway = $this->gatewayFactory->getAliasXmlGateway();
        }

        /** @var XMLAuthorizationResponse $response */
        $response = $this->xmlGateway->authorize($request->getData())->send();

        return new AuthorizationResponse($response->getData());
    }
}
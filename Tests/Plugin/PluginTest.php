<?php

namespace Ongoing\DatatransBundle\Tests\Plugin;

use JMS\Payment\CoreBundle\Entity\ExtendedData;
use JMS\Payment\CoreBundle\Entity\FinancialTransaction;
use JMS\Payment\CoreBundle\Entity\Payment;
use JMS\Payment\CoreBundle\Entity\PaymentInstruction;
use JMS\Payment\CoreBundle\Model\FinancialTransactionInterface;
use JMS\Payment\CoreBundle\Plugin\PluginInterface;
use Ongoing\DatatransBundle\Client\Client;
use Ongoing\DatatransBundle\Model\Parameter;
use Ongoing\DatatransBundle\Model\Response\AuthorizationResponse;
use Ongoing\DatatransBundle\Model\Response\SettlementResponse;
use Ongoing\DatatransBundle\Plugin\DatatransPlugin;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class PluginTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @expectedException \JMS\Payment\CoreBundle\Plugin\Exception\ActionRequiredException
     */
    public function testApproveThrowsActionRequiredException()
    {
        $transaction = $this->buildTestTransaction();
        $client = $this->getMockBuilder(Client::class)->disableOriginalConstructor()->getMock();
        $plugin = new DatatransPlugin($client, $this->getRequestStack());
        $plugin->approve($transaction, false);
    }

    /**
     * @throws \JMS\Payment\CoreBundle\Plugin\Exception\ActionRequiredException
     */
    public function testApproveWithAlias()
    {
        $transaction = $this->buildTestTransaction();
        $transaction->getExtendedData()->set(Parameter::PARAM_ALIAS_CC, 'thisisanalias');
        $transaction->getExtendedData()->set(Parameter::PARAM_EXPY, '18');
        $transaction->getExtendedData()->set(Parameter::PARAM_EXPM, '01');
        $plugin = new DatatransPlugin($this->getClient(), $this->getRequestStack());
        $plugin->approve($transaction, false);

        $this->assertEquals(150, $transaction->getProcessedAmount());
        $this->assertEquals('transactionid', $transaction->getTrackingId());
        $this->assertEquals('refno', $transaction->getReferenceNumber());
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage PaymentStatus is not completed: Invalid response! Requested: [15 CHF] | Confirmed: [0.15 CHF]
     */
    public function testApproveConfirmationThrowsException()
    {
        $transaction = $this->buildTestTransaction();
        $transaction->getExtendedData()->set(Parameter::PARAM_ALIAS_CC, 'thisisanalias');
        $plugin = new DatatransPlugin(
            $this->getClient(),
            $this->getRequestStack(['responseCode' => 1, 'amount' => 15, 'currency' => 'CHF'])
        );
        $plugin->approve($transaction = $this->buildTestTransaction(), false);
    }

    /**
     * @throws \JMS\Payment\CoreBundle\Plugin\Exception\ActionRequiredException
     */
    public function testSuccessfulApproval()
    {
        $transaction = $this->buildTestTransaction();
        $transaction->getExtendedData()->set(Parameter::PARAM_ALIAS_CC, 'thisisanalias');
        $plugin = new DatatransPlugin(
            $this->getClient(),
            $this->getRequestStack(['responseCode' => 1, 'amount' => 1500, 'currency' => 'CHF'])
        );
        $plugin->approve($transaction = $this->buildTestTransaction(), false);

        $this->assertEquals(PluginInterface::RESPONSE_CODE_SUCCESS, $transaction->getResponseCode());
        $this->assertEquals(PluginInterface::REASON_CODE_SUCCESS, $transaction->getReasonCode());
    }

    /**
     * test simple deposit
     */
    public function testDeposit()
    {
        $transaction = $this->buildTestTransaction();
        $plugin = new DatatransPlugin($this->getClient(), $this->getRequestStack());

        $transaction->setTransactionType(FinancialTransactionInterface::TRANSACTION_TYPE_APPROVE_AND_DEPOSIT);

        $plugin->deposit($transaction, false);

        $this->assertEquals(PluginInterface::RESPONSE_CODE_SUCCESS, $transaction->getResponseCode());
        $this->assertEquals(PluginInterface::REASON_CODE_SUCCESS, $transaction->getReasonCode());
    }

    /**
     * building a test transaction
     */
    protected function buildTestTransaction()
    {
        $extendedData = new ExtendedData();
        $extendedData->set(Parameter::PARAM_RETURN_URL, 'http://example.com/return');
        $extendedData->set(Parameter::PARAM_ERROR_URL, 'http://example.com/error');
        $extendedData->set(Parameter::PARAM_CANCEL_URL, 'http://example.com/cancel');
        $extendedData->set(Parameter::PARAM_TRANSACTIONID, '1234');

        $paymentInstrucation = new PaymentInstruction(15.00, 'CHF', 'ongoing_datatrans', $extendedData);

        $payment = new Payment($paymentInstrucation, $paymentInstrucation->getAmount());

        $transaction = new FinancialTransaction();
        $transaction->setRequestedAmount(15.00);
        $payment->addTransaction($transaction);

        return $transaction;
    }

    /**
     * @param array $postData
     * @return \PHPUnit_Framework_MockObject_MockObject|RequestStack
     */
    protected function getRequestStack($postData = [])
    {
        $request = new Request([], $postData);

        $requestStackMock = $this->getMockBuilder(RequestStack::class)->getMock();
        $requestStackMock->method('getCurrentRequest')->willReturn($request);

        return $requestStackMock;
    }

    /**
     * @return Client|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getClient()
    {
        $clientMock = $this->getMockBuilder(Client::class)
            ->disableOriginalConstructor()
            ->getMock();

        $clientMock->method('authorizePayment')->willReturn(new AuthorizationResponse([
            'response' => [
                Parameter::PARAM_AMOUNT => 15000,
                Parameter::PARAM_CURRENCY => 'CHF',
                Parameter::PARAM_REFNO => 'refno',
                Parameter::PARAM_UPPTRANSACTIONID => 'transactionid',
            ],
        ]));

        $clientMock->method('completePayment')->willReturn(new SettlementResponse([
            'response' => [
                Parameter::PARAM_AMOUNT => 15000,
                Parameter::PARAM_CURRENCY => 'CHF',
                Parameter::PARAM_REFNO => 'refno',
                Parameter::PARAM_UPPTRANSACTIONID => 'transactionid',
            ],
        ]));

        return $clientMock;
    }
}

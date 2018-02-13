<?php

namespace Ongoing\DatatransBundle\Tests\Plugin;

use JMS\Payment\CoreBundle\Entity\ExtendedData;
use JMS\Payment\CoreBundle\Entity\FinancialTransaction;
use JMS\Payment\CoreBundle\Entity\Payment;
use JMS\Payment\CoreBundle\Entity\PaymentInstruction;
use Ongoing\DatatransBundle\Client\Client;
use Ongoing\DatatransBundle\Model\Parameter;
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
        $plugin = new DatatransPlugin($this->createMock(Client::class), $this->getRequestStack());
        $plugin->approve($transaction, false);
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
        $transaction->setPayment($payment);

        return $transaction;
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|RequestStack
     */
    protected function getRequestStack()
    {
        $request = new Request();

        $requestStackMock = $this->createMock(RequestStack::class);
        $requestStackMock->method('getCurrentRequest')->willReturn($request);

        return $requestStackMock;
    }
}

<?php

namespace Ongoing\DatatransBundle\Tests\Model\Response;

use Ongoing\DatatransBundle\Model\Parameter;
use Ongoing\DatatransBundle\Model\Response\AuthorizationResponse;
use Ongoing\DatatransBundle\Model\Response\SettlementResponse;
use Ongoing\DatatransBundle\Model\Response\StatusResponse;
use phpDocumentor\Reflection\DocBlock\Tags\Param;

class ResponseTest extends \PHPUnit_Framework_TestCase
{
    /**
     * test valid response on authorization
     */
    public function testAuthorizationResponse()
    {
        $validResponse = $this->getValidResponse();
        $authResponse = new AuthorizationResponse($validResponse);

        $this->assertEquals(150, $authResponse->getAmount());
        $this->assertEquals('CHF', $authResponse->getCurrency());
        $this->assertEquals('987654321', $authResponse->getUppTransactionId());
        $this->assertEquals('orderid', $authResponse->getReferenceNumber());
        $this->assertEquals('42424 **** **** 4567', $authResponse->getMaskedCC());
        $this->assertEquals('12', $authResponse->getExpirationMonth());
        $this->assertEquals('18', $authResponse->getExpirationYear());
        $this->assertEquals('VIS', $authResponse->getPMethod());
        $this->assertEquals('thisisanalias', $authResponse->getAliasCC());
        $this->assertFalse($authResponse->hasError());
    }

    /**
     * test statusresponse
     */
    public function testStatusResponse()
    {
        $statusResponse = new StatusResponse(['response' => [Parameter::PARAM_RESPONSECODE => 1]]);
        $this->assertTrue($statusResponse->isWaitingForSettlement());

        $statusResponse->set(Parameter::PARAM_RESPONSECODE, 2);
        $this->assertFalse($statusResponse->isWaitingForSettlement());
    }

    /**
     * test for error in response
     */
    public function testErrorOnResponse()
    {
        $response = new SettlementResponse(['error' => ['errorMessage' => 'Permission denied!']]);
        $this->assertTrue($response->hasError());
        $this->assertEquals($response->getErrorMessage(), 'Permission denied!');
    }

    /**
     * @return array
     */
    public function getValidResponse()
    {
        return [
            'response' => [
                Parameter::PARAM_AMOUNT => '15000',
                Parameter::PARAM_CURRENCY => 'CHF',
                Parameter::PARAM_UPPTRANSACTIONID => '987654321',
                Parameter::PARAM_REFNO => 'orderid',
                Parameter::PARAM_MASKED_CC => "42424 **** **** 4567",
                Parameter::PARAM_EXPM => '12',
                Parameter::PARAM_EXPY => '18',
                Parameter::PARAM_PMETHOD => 'VIS',
                Parameter::PARAM_ALIAS_CC => 'thisisanalias',
            ],
        ];
    }
}

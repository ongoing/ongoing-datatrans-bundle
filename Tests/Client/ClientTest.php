<?php

namespace Ongoing\DatatransBundle\Tests\Client;

use Omnipay\Common\Message\ResponseInterface;
use Omnipay\Datatrans\Message\XmlStatusRequest;
use Omnipay\Datatrans\XmlGateway;
use Omnipay\Omnipay;
use Ongoing\DatatransBundle\Client\Client;
use Ongoing\DatatransBundle\Client\GatewayFactory;
use Ongoing\DatatransBundle\Model\DatatransParameter;
use Ongoing\DatatransBundle\Model\Parameter;
use Ongoing\DatatransBundle\Model\Request\Request;
use Ongoing\DatatransBundle\Model\Response\SettlementResponse;
use Ongoing\DatatransBundle\Model\Response\StatusResponse;
use phpDocumentor\Reflection\DocBlock\Tags\Param;

class ClientTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var GatewayFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $gatewayFactory;

    /**
     * setup deps
     */
    protected function setUp()
    {
        $this->gatewayFactory = $this->getMockBuilder(GatewayFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * test getting authorization url
     */
    public function testGetAuthorizationUrl()
    {
        $gateway = Omnipay::create(GatewayFactory::DATATRANS_GATEWAY);
        $this->gatewayFactory->method('getGateway')
            ->willReturn($gateway);

        $client = new Client($this->gatewayFactory);

        $authRequest = $this->buildAuthRequest();
        $authorizationUrl = $client->getAuthorizationUrl($authRequest);

        //check if correct url is used - testurl
        $this->assertStringStartsWith('https://pay.sandbox.datatrans.com/upp/jsp/upStart.jsp', $authorizationUrl);

        //get parameter from url - should be the same as initialized
        parse_str(parse_url($authorizationUrl, PHP_URL_QUERY), $params);

        //check for mandatory params
        $this->assertArrayHasKey(Parameter::PARAM_MERCHANT_ID, $params);
        $this->assertArrayHasKey(Parameter::PARAM_AMOUNT, $params);
        $this->assertArrayHasKey(Parameter::PARAM_REFNO, $params);
        $this->assertArrayHasKey(Parameter::PARAM_CURRENCY, $params);
    }

    /**
     * should be a statusresponse on completing
     */
    public function testCompletePayment()
    {
        $xmlGateway = $this->buildXmlGatewayMock();
        $this->gatewayFactory->method('getXmlGateway')
            ->willReturn($xmlGateway);

        $client = new Client($this->gatewayFactory);
        $response = $client->completePayment(new Request());

        $this->assertInstanceOf(StatusResponse::class, $response);
    }

    /**
     * there should be another response when awaiting settlement
     */
    public function testCompletePaymentWithAwaitingSettlement()
    {
        //test with different response code
        $xmlGateway = $this->buildXmlGatewayMock([Parameter::PARAM_RESPONSECODE => 1]);

        $this->gatewayFactory->method('getXmlGateway')
            ->willReturn($xmlGateway);

        $client = new Client($this->gatewayFactory);
        $response = $client->completePayment(new Request());

        $this->assertInstanceOf(SettlementResponse::class, $response);
    }

    /**
     * @return Request
     */
    private function buildAuthRequest()
    {
        $params = [
            Parameter::PARAM_MERCHANT_ID => '111222333',
            Parameter::PARAM_SIGN => '111222333',
            Parameter::PARAM_AMOUNT => 55.95,
            Parameter::PARAM_CURRENCY => 'CHF',
            Parameter::PARAM_RETURNMASKEDCC => 'yes',
            Parameter::PARAM_USEALIAS => 'yes',
            Parameter::PARAM_TRANSACTIONID => 'some_order_number',
            Parameter::PARAM_RETURN_URL => 'https://www.example.com/return',
            Parameter::PARAM_ERROR_URL => 'https://www.example.com/error',
            Parameter::PARAM_CANCEL_URL => 'https://www.example.com/cancel'
        ];

        return new Request($params);
    }

    /**
     * @param array $returnValue - what response returns
     * @return \PHPUnit_Framework_MockObject_MockObject
*/
    protected function buildXmlGatewayMock($returnValue = [])
    {
        $xmlGateway = $this->getMockBuilder(XmlGateway::class)->disableOriginalConstructor()->getMock();

        $response = $this->createMock(ResponseInterface::class);
        $response->method('getData')->willReturn($returnValue);

        $request = $this->getMockBuilder(XmlStatusRequest::class)->disableOriginalConstructor()->getMock();
        $request->method('send')->willReturn($response);

        $xmlGateway->method('status')->willReturn($request);
        $xmlGateway->method('settlementDebit')->willReturn($request);

        return $xmlGateway;
    }
}
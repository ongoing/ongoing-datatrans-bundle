<?php

namespace Ongoing\DatatransBundle\Tests\Client;

use Ongoing\DatatransBundle\Client\GatewayFactory;
use Omnipay\Datatrans\Gateway;
use Omnipay\Datatrans\XmlGateway;

class GatewayFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * test generating gateway factories
     */
    public function testGatewayFactory()
    {
        $credentials = $this->getTestCredentials();
        $gatewayFactory = new GatewayFactory($credentials, false);

        $gateway = $gatewayFactory->getGateway();
        $xmlGateWay = $gatewayFactory->getXmlGateway();
        $aliasXmlGateway = $gatewayFactory->getAliasXmlGateway();

        $this->assertInstanceOf(Gateway::class, $gateway);
        $this->assertInstanceOf(XmlGateway::class, $xmlGateWay);
        $this->assertInstanceOf(XmlGateway::class, $aliasXmlGateway);

        //should use same credentials
        $this->assertSame($gateway->getMerchantId(), $xmlGateWay->getMerchantId());
        $this->assertSame($gateway->getSign(), $xmlGateWay->getSign());

        //alias should use other credentials
        $this->assertNotContains(
            $aliasXmlGateway->getMerchantId(),
            [$gateway->getMerchantId(), $xmlGateWay->getMerchantId()]
        );
    }

    /**
     * @expectedException RuntimeException
     */
    public function testAliasgatewayNotCreated()
    {
        $testCredentials = $this->getTestCredentials(false);
        $gatewayFactory = new GatewayFactory($testCredentials, false);

        $gatewayFactory->getAliasXmlGateway();
    }


    /**
     * @param bool $includeXmlCredentials
     * @return array
     */
    private function getTestCredentials($includeXmlCredentials = true)
    {
        $credentials = [
            'merchant_id' => "123",
            'sign' => "sign",
        ];

        if ($includeXmlCredentials) {
            $credentials['xml_merchant_id'] = 'xml123';
            $credentials['xml_sign'] = 'xmlsign';
        }

        return $credentials;
    }
}

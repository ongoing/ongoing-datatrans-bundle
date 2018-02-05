<?php

namespace Ongoing\DatatransBundle\Client;

use Omnipay\Datatrans\Gateway;
use Omnipay\Datatrans\XmlGateway;
use Omnipay\Omnipay;
use Symfony\Component\HttpFoundation\ParameterBag;

class GatewayFactory
{
    const DATATRANS_GATEWAY = 'Datatrans';
    const DATATRANS_XML_GATEWAY = 'Datatrans_Xml';

    /**
     * @var ParameterBag
     */
    protected $credentials;

    /**
     * @var Gateway
     */
    protected $gateway;

    /**
     * @var XmlGateway
     */
    protected $xmlGateway;

    /**
     * @var XmlGateway
     */
    protected $aliasXmlGateway;

    /**
     * GatewayFactory constructor.
     *
     * @param array $credentials
     * @param array $credentials
     * @param bool $testmode
     */
    public function __construct(array $credentials, $testmode)
    {
        $this->credentials = new ParameterBag(array_merge($credentials, ['testmode' => $testmode]));
        $this->createGateways();
    }

    /**
     * @return Gateway
     */
    public function getGateway()
    {
        return $this->gateway;
    }

    /**
     * @return XmlGateway
     */
    public function getXmlGateway()
    {
        return $this->xmlGateway;
    }

    /**
     * @return XmlGateway
     */
    public function getAliasXmlGateway()
    {
        if (!$this->aliasXmlGateway) {
            throw new \RuntimeException('Mail/Phone merchant id and sign is needed for executing alias payments.');
        }

        return $this->aliasXmlGateway;
    }

    /**
     * build gateways from parameter
     */
    protected function createGateways()
    {
        $this->gateway = Omnipay::create(static::DATATRANS_GATEWAY);
        $this->gateway->setMerchantId($this->credentials->get('merchant_id'));
        $this->gateway->setSign($this->credentials->get('sign'));
        $this->gateway->setTestMode($this->credentials->get('testmode'));

        $this->xmlGateway = Omnipay::create(static::DATATRANS_XML_GATEWAY);
        $this->xmlGateway->setMerchantId($this->credentials->get('merchant_id'));
        $this->xmlGateway->setSign($this->credentials->get('sign'));
        $this->xmlGateway->setTestMode($this->credentials->get('testmode'));

        //aliasxml gateway to implement one click checkout - a different merchant id is needed from datatrans
        if ($this->credentials->has('xml_merchant_id') && $this->credentials->get('xml_merchant_id') != null) {
            $this->aliasXmlGateway = Omnipay::create(static::DATATRANS_XML_GATEWAY);
            $this->aliasXmlGateway->setMerchantId($this->credentials->get('xml_merchant_id'));
            $this->aliasXmlGateway->setSign($this->credentials->get('xml_sign'));
            $this->aliasXmlGateway->setTestMode($this->credentials->get('testmode'));
        }
    }
}

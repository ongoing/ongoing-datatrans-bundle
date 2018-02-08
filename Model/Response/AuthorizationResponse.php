<?php

namespace Ongoing\DatatransBundle\Model\Response;

use Ongoing\DatatransBundle\Model\Parameter;

class AuthorizationResponse extends AbstractResponse
{
    /**
     * @return string
     */
    public function getCurrency()
    {
        return $this->get(Parameter::PARAM_CURRENCY);
    }

    /**
     * @return string
     */
    public function getUppTransactionId()
    {
        return $this->get(Parameter::PARAM_UPPTRANSACTIONID);
    }

    /**
     * @return string
     */
    public function getMaskedCC()
    {
        return $this->get(Parameter::PARAM_MASKED_CC);
    }

    /**
     * @return string
     */
    public function getExpirationMonth()
    {
        return $this->get(Parameter::PARAM_EXPM);
    }

    /**
     * @return string
     */
    public function getExpirationYear()
    {
        return $this->get(Parameter::PARAM_EXPY);
    }

    /**
     * @return string
     */
    public function getAliasCC()
    {
        return $this->get(Parameter::PARAM_ALIAS_CC);
    }

    /**
     * @return string
     */
    public function getPMethod()
    {
        return $this->get(Parameter::PARAM_PMETHOD);
    }
}

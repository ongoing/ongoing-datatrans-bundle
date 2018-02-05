<?php

namespace Ongoing\DatatransBundle\Model\Request;

use Ongoing\DatatransBundle\Model\Parameter;
use Symfony\Component\HttpFoundation\ParameterBag;

class Request extends ParameterBag
{
    /**
     * @return array - all parameters
     */
    public function getData()
    {
        return $this->all();
    }

    /**
     * @return int|float
     */
    public function getAmount()
    {
        return $this->get(Parameter::PARAM_AMOUNT);
    }

    /**
     * @return string
     */
    public function getCurrency()
    {
        return $this->get(Parameter::PARAM_CURRENCY);
    }
}
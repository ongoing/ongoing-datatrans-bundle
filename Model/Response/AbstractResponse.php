<?php

namespace Ongoing\DatatransBundle\Model\Response;

use Ongoing\DatatransBundle\Model\Parameter;
use Symfony\Component\HttpFoundation\ParameterBag;

class AbstractResponse extends ParameterBag
{

    /**
     * Constructor.
     *
     * @param array $parameters An array of parameters
     */
    public function __construct(array $parameters = array())
    {
        if (isset($parameters['response'])) {
            parent::__construct($parameters['response']);
            return;
        }

        if (isset($parameters['error'])) {
            parent::__construct($parameters['error']);
            return;
        }

        parent::__construct($parameters);
    }

    /**
     * @return bool
     */
    public function hasError()
    {
        return isset($this->parameters['errorMessage']);
    }

    /**
     * @return string
     */
    public function getErrorMessage()
    {
        if ($this->hasError()) {
            return $this->get('errorMessage');
        }
    }

    /**
     * @return mixed
     */
    public function getReferenceNumber()
    {
        return $this->get(Parameter::PARAM_REFNO, null, true);
    }

    /**
     * @return mixed
     */
    public function getAmount()
    {
        return $this->get(Parameter::PARAM_AMOUNT, null, true);
    }
}

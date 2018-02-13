<?php

namespace Ongoing\DatatransBundle\Model\Response;

use Ongoing\DatatransBundle\Model\Parameter;

class StatusResponse extends AbstractResponse
{
    /**
     * @return mixed
     */
    public function getResponseCode()
    {
        return $this->get(Parameter::PARAM_RESPONSECODE, null);
    }

    /**
     * @return bool
     */
    public function isWaitingForSettlement()
    {
        $responseCode = $this->getResponseCode();
        return $responseCode == 1;
    }
}

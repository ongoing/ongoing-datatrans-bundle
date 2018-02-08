<?php

namespace Ongoing\DatatransBundle\Model;

final class Parameter
{
    /**
     * mandatory params
     */
    const PARAM_MERCHANT_ID = 'merchantId';
    const PARAM_SIGN = 'sign';
    const PARAM_AMOUNT = 'amount';
    const PARAM_CURRENCY = 'currency';
    const PARAM_REFNO = 'refno';
    const PARAM_RETURN_URL = 'returnUrl';
    const PARAM_ERROR_URL = 'errorUrl';
    const PARAM_CANCEL_URL = 'cancelUrl';

    /**
     * optional params
     */
    const PARAM_LANGUAGE = 'language';
    const PARAM_TRANSACTIONID = 'transactionId'; //will be used as refno in w-vision client
    const PARAM_RESPONSECODE = "responseCode";
    const PARAM_RESPONSEMESSAGE = "responseMessage";
    const PARAM_UPPTRANSACTIONID = "uppTransactionId";
    const PARAM_ERRORCODE = "errorCode";
    const PARAM_ERRORMESSAGE = "errorMessage";
    const PARAM_ERRORDETAIL = "errorDetail";
    const PARAM_REQTYPE = "reqtype";
    const PARAM_RETURNMASKEDCC = "uppReturnMaskedCC";
    const PARAM_USEALIAS = "useAlias";
    const PARAM_ALIAS_CC = 'aliasCC';
    const PARAM_MASKED_CC = 'maskedCC';
    const PARAM_EXPM = 'expm';
    const PARAM_EXPY = 'expy';
    const PARAM_PAYMENTMETHOD = 'paymentmethod';
    const PARAM_HIDDEN_MODE = 'hiddenMode';
    const PARAM_PMETHOD = 'pmethod';
}

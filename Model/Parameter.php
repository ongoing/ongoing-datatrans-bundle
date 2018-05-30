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
    const PARAM_PMETHOD = 'pmethod';
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
    const PARAM_ACCOUNT_ON_FILE = 'account_on_file';

    /**
     * optional customer params
     */
    const PARAM_UPP_CUSTOMER_DETAILS = 'uppCustomerDetails';
    const PARAM_UPP_CUSTOMER_TITLE = 'uppCustomerTitle';
    const PARAM_UPP_CUSTOMER_FIRSTNAME = 'uppCustomerFirstName';
    const PARAM_UPP_CUSTOMER_LASTNAME = 'uppCustomerLastName';
    const PARAM_UPP_CUSTOMER_STREET = 'uppCustomerStreet';
    const PARAM_UPP_CUSTOMER_STREET2 = 'uppCustomerStreet2';
    const PARAM_UPP_CUSTOMER_CITY = 'uppCustomerCity';
    const PARAM_UPP_CUSTOMER_COUNTRY = 'uppCustomerCountry';
    const PARAM_UPP_CUSTOMER_ZIPCODE = 'uppCustomerZipCode';
    const PARAM_UPP_CUSTOMER_STATE = 'uppCustomerState';
    const PARAM_UPP_CUSTOMER_PHONE = 'uppCustomerPhone';
    const PARAM_UPP_CUSTOMER_FAX = 'uppCustomerFax';
    const PARAM_UPP_CUSTOMER_EMAIL = 'uppCustomerEmail';
    const PARAM_UPP_CUSTOMER_GENDER = 'uppCustomerGender';
    const PARAM_UPP_CUSTOMER_BIRTHDATE = 'uppCustomerBirthDate';
    const PARAM_UPP_CUSTOMER_LANGUAGE = 'uppCustomerLanguage';
}

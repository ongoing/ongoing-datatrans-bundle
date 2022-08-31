# Ongoing Datatransbundle

[![Build Status](https://travis-ci.org/ongoing/ongoing-datatrans-bundle.svg?branch=master)](https://travis-ci.org/ongoing/ongoing-datatrans-bundle)

Ongoing Datatransbundle is an extension to the [JMSPaymentCoreBundle](https://github.com/schmittjoh/JMSPaymentCoreBundle) 

## Installation

Require repo

```
$ composer require ongoing/datatrans-bundle "~1.0"
```

Activate Bundle in AppKernel

```php
<?php
// app/AppKernel.php

// ...
class AppKernel extends Kernel
{
    public function registerBundles()
    {
        $bundles = array(
            // ...
            new Ongoing\Payment\DatatransBundle\OngoingDatatransBundle()
        );
    }

    // ...
}
```

## Usage

```yaml

ongoing_datatrans:
    credentials:
        merchant_id: 1100004624
        password: ~
        sign: 30916165706580013
        hmac_key: ~
        xml_merchant_id: 1100004624
        xml_password: ~
        xml_sign: 30916165706580013
        xml_hmac_key: ~
    test_mode: true
    transaction_parameter: ~
```

* Only merchant_id and sign are mandatory
* xml_merchant_id and xml_sign are mandatory if using alias payments with account on file (recurring payments or one click checkout)
* transaction parameter like return urls, uppRememberMe, useAlias e.g.


### Example implementation

```php
<?php

    $form = $this->createForm(ChoosePaymentMethodType::class, null, [
        'amount'   => $order->getAmount(),
        'currency' => 'CHF',
        'predefined_data' => [          
            'ongoing_datatrans' => [
                Parameter::PARAM_RETURN_URL => $this->router->generate('payment_datatrans_confirm', [ 'id' => $order->getId() ], Router::ABSOLUTE_URL),
                Parameter::PARAM_ERROR_URL => $this->router->generate('payment_datatrans_error', [ 'id' => $order->getId() ], Router::ABSOLUTE_URL),
                Parameter::PARAM_CANCEL_URL => $this->router->generate('payment_datatrans_cancel', [ 'id' => $order->getId() ], Router::ABSOLUTE_URL),
                Parameter::PARAM_TRANSACTIONID => $order->getId(),
                Parameter::PARAM_ALIAS_CC => "7011435122433810042",
                Parameter::PARAM_EXPM => '12',
                Parameter::PARAM_EXPY => '18'
            ]
        ],
    ]);
}
```

* Alias is requested when user checks out first, plugin will set the alias along with expiration month, expiration year and masked credit card number to extended data
* By setting PARAM_ALIAS_CC, PARAM_EXPM, PARAM_EXPM datatrans will prefill the checkout form^
* By additionally setting Parameter::PARAM_ACCOUNT_ON_FILE to "yes", the bundle will try to execute authorization and settlement in one request (alias/token payment).
* For more information on alias payment, checkout [datatrans technical reference](https://pilot.datatrans.biz/showcase/doc/XML_Authorisation.pdf)
* Typically alias could be set in a form listener which decides to add it on underlying data, or on form initialization like in the example above
* For retrieving masked cc number, option uppReturnMaskedCC has to be set to yes under transaction_parameters

## License 

Ongoing Datatransbundle is distributed under the MIT license.

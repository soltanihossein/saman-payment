# Saman bank payment package

[![License: GPL v3](https://img.shields.io/badge/License-GPL%20v3-blue.svg)](https://www.gnu.org/licenses/gpl.html)

Easily integrate PHP application with saman bank payment.

# Installation
``` bash
$ composer require soltanihossein/saman-payment
```

# Implementation
Attention: The Saman Bank webservice just available with IP that allowed with Saman Bank.
#### Request payment
```php
<?php 
use SoltaniHossein\Saman\Saman;

try{
    /**
    * @param int $merchantId (required) The Saman gateway merchant id 
    */
    $saman = new Saman($merchantId);
	
    /**
     * @param int $amount (required) The amount that customer must pay
     * @param string $callbackUrl (required) The url that customer redirect to after payment
     * @param int $orderId (optional) The unique order id, generate by package if value passed null
     * @param int $additionalData (optional) addition data
	 *
	 * @method request Return array contain transaction `token` and you can save.
     * $token = $response;
     *     
     */
    $response = $saman->request($amount, $callbackUrl, $invoiceId);
    
    /**
     * Redirect user to payment gateway
     */
     echo $saman->redirect();
   
}catch (\Throwable $exception){
    echo $exception->getMessage();
}
```
#### Verify transaction
Customer redirect to callback url with all transaction data and you must verify transaction.

#### verify:
```php
<?php
use SoltaniHossein\Saman\Saman;

try{
        /**
         * @param int $merchantId (required) The Saman gateway merchant id
         */
        $saman = new Saman($merchantId);
	
        /**
          * 
          * @method $verify return array of  transaction data.
          *
          */
        $response = $saman->verify($amount);
        
        echo "Successful payment ...";
}catch (\Throwable $exception){
    echo $exception->getMessage();
}
```

## License
[![License: GPL v3](https://img.shields.io/badge/License-GPL%20v3-blue.svg)](https://www.gnu.org/licenses/gpl.html)

Copyright (c) 2020

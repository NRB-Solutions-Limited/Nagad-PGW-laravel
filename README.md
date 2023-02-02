# Laravel package for Nagad Payment Gateway
Nagad is a payment gateway service provided by the Bangladesh Post Office. It enables merchants to accept online payments from customers in a secure and convenient manner. Nagad offers a range of features, including the ability to receive payments 24/7, real-time payment tracking, and support for multiple payment methods such as mobile banking . With Nagad, merchants can easily manage their online transactions and grow their business.



## Installation ##

Run the command below to install via Composer

```shell
composer require nrbsolution/nagad_payment_gateway
```

## Getting Started ##

`Nagad Payment Gateway` requires change to your composer.json file 
```php
 "minimum-stability": "stable",
```
to
```php
 "minimum-stability": "dev",
```
## 
`Nagad Payment Gateway` requires add to your env file 
```php
NAGAD_SANDBOX=true // true only sandbox testing purpose or main api work only NAGAD_SANDBOX=false
NAGAD_MERCHANT_ID= //Your NAGAD_MERCHANT_ID
NAGAD_MERCHANT_NUMBER= // Your NAGAD_MERCHANT_NUMBER
NAGAD_PUBLIC_KEY= // Your NAGAD_PUBLIC_KEY
NAGAD_PRIVATE_KEY= // Your NAGAD_PRIVATE_KEY
NAGAD_ORDER_ID_PREFIX= // You can change your order id prefix 
NAGAD_LOGO=// Your logo url

// if you  controls the response type callback 
//You need change to your config/nagad_payment_gateway.php file 
//  Supported: "json", "html",

"response_type"   => "html" 
// You need to publish this package 
php artisan vendor:publish --provider="App\Providers\NagadPaymentGatewaySerivceProvider"

```
### Instructions for usegs  ###
```php
Route: 
http://{Your Host address}/nagad/nagad/{reference_id}/{amount}
http://127.0.0.1:8000/nagad/pid_234234234/3000
```

## License ##

Released under the MIT License attached with this code.

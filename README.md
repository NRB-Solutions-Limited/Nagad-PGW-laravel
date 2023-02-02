# Laravel package for Firebase Notification
Laravel Package to send push notifications to Android and IOS devices



## Installation ##

Run the command below to install via Composer

```shell
composer require nrbsolution/push_notification
```

## Getting Started ##

`push_notification` requires change to your composer.json file 
```php
 "minimum-stability": "stable",
```
to
```php
 "minimum-stability": "dev",
```
### Instructions for usegs  ###
```php
Postman body request

title:demo title
body:demo body
device_key:send notification device key,


Route: 
http://{Your Host address}/api/push_notification
```
<img src="https://i.ibb.co/tbz67GZ/Screenshot-2023-01-31-174005.png"/>

## License ##

Released under the MIT License attached with this code.

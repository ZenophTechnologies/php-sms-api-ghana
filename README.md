# php-sms-api-ghana
A PHP class library to send bulk SMS to mobile destinations in Ghana through http://smsonlinegh.com/



## Installation
To use the PHP SMS API library in your application, extract the contents of the archive in your application directory. Set the include path to 'ZenophSMSGH.php' file. For example,


```php
include_once (__DIR__.'/ZenophSMSGH/lib/ZenophSMSGH.php');
```

Once the library has been imported, you are ready to send bulk SMS to Ghana from your PHP applications.


## Sending Non-personalised Messages
To send a message, you must declare an object of type ZenophSMSGH. This is irrespective of sending personalised or non-personalised SMS. Message parameters can then be set.

```php
$zs = new ZenophSMSGH();
$zs->setUser('account_login');
$zs->setPassword('account_password');

// set other parameters.
$zs->setMessageType(ZenophSMSGH_MESSAGETYPE::TEXT);
$zs->setSenderId('PHP SMS API');
$zs->SetMessage('Hello there!');

// add destinations.
$zs->addDestination('0246310000');
$zs->addDestination('0207000000');

// send the message.
$response = $zs->sendMessage();
```

## Sending Personalised Messages


## Scheduling Messages

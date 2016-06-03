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
Our PHP SMS API makes it easy to send personalised messages to destinations. Applications only need to set the message once while defining variables in the parts of the message where values will be different for each destination.

Consider the following example:

```php
// set the message
$message = 'Hello {$name}, your balance is ${$balance}';
$zs->setMessage($message);
```

The message above has variables defined in it. The variables are not meant to be parsed by the PHP interpreter so we define the
message in single quotes. The presence of the variables in the message inform the SMS server that values will be substituted in parts of the message where the variables have been defined before submitting the message to the destinations.

The values for each destination are specified when adding the destinations. Variables must be unique in the message. If a variable name is defined more than once, Exception will be thrown when setMessage() is called. The values should be passed as an array and as the third argument to the addDestination() function. As an example, consider the following:

```php
// here is an arbitrary contacts data
$contacts[] = array('phonenum'=>'0246300001', 'name'=>'Daniel', 'balance'=>546.89);
$contacts[] = array('phonenum'=>'0207000001', 'name'=>'Oppong', 'balance'=>324.56);

// add the destinations and their values for personalising message.
foreach ($contacts as $contact)
    $zs->addDestination($contact['phonenum'], true, array($contact['name'], $contact['balance']));
    
```

The second argument to addDestination() specifies whether Exception should be thrown if a phone number is invalid and or when any of the values is invalid. The default is true but it must be explicitly set when adding destinations with personalised values. In an iteration, this can be set to false to prevent the iteration from being terminated. In our case, we have set it to true and the iteration will terminate if data validation fails.

As seen from the above code, the values to be substituted in the message for each destination are passed as an array and as the third argument to addDestinations() function. Notice that the values are ordered such that they match the order in which the variables are defined in the message for substitution. When done, the message can be submitted:

```php
$zs->sendMessage();
````

## Scheduling Messages
Messages can be scheduled for delivery at a later date and time. To schedule a message, a DateTime object must be defined with the date, time, and timezone properties set and then passing it to the schedule() function:

```php
// define the date, time, and timezone for message scheduling
$datetime = new DateTime();
$datetime->setDate(2016, 5, 28);
$datetime->setTime(13, 45, 0);
$datetime->setTimezone(new DateTimeZone('Africa/Accra'));

// set the schedule date and time
$zs->schedule($datetime);

// send the message to the server for scheduling
$zs->sendMessage();
```



## Conclusion
As the ultimate bulk SMS provider in Ghana, <a alt='Cheap bulk SMS provider in Ghana' href='http://smsonlinegh.com/'>SMSONLINEGH</a> releases a simple PHP SMS API that can be used to send bulk SMS to mobile destinations in Ghana. The API can be used to send both personalised and non-personalised messages as well as schedule messages. 

Developers need not worry about sending bulk SMS to Ghana from their PHP applications anymore. Use our PHP SMS API for Ghana by importing the library in your PHP applications. Set your message, add destinations, and then send.

To get started, download the API and extract it. You will find the API library as well as documentation to get you easily going.

## Other SMS APIs and Tools
- <a alt='Java SMS API for Ghana Destinations' target='_blank' href='http://smsonlinegh.com/resources.php?page=java-api'>Java SMS API for Ghana Destinations</a>
- <a alt='C#, VB.NET, C++/CLI SMS API for Ghana Destinations' target='_blank' href='http://smsonlinegh.com/resources.php?page=dotnet-api'>C#, VB.NET, C++/CLI Bulk SMS API for Ghana Destinations</a>
- <a alt='Send Bulk SMS in Ghana from Excel spreadsheet' target='_blank' href='http://smsonlinegh.com/resources.php?page=excel-sms-plugin'>Excel Bulk SMS plugin for Ghana Destinations</a>

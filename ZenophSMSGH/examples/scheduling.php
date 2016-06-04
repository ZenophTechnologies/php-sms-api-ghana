<?php
    /*
    * This is an example PHP script to schedule a message
    * using the PHP SMS library through http://smsonlinegh.com/
    */

    include_once (__DIR__.'/../lib/ZenophSMSGH.php');
    
    try{
        // Initialise the object for sending the message and set parameters.
        $zs = new ZenophSMSGH();
        $zs->setUser('account_login');
        $zs->setPassword('account_password');
        
        // set message parameters
        $zs->setSenderId('API EXAMPLE');
        $zs->setMessageType(ZenophSMSGH_MESSAGETYPE::TEXT); // default is TEXT if you do not set it yourself.
        $zs->setMessage('This message was scheduled.');
        
        // add destinations.
        $zs->addDestination('0246300001');
        $zs->addDestination('0246300002', false);   // don't throw Exception if destination is invalid.
        
        /*
         * for local number format, the leading zero is optional. This is an advantage
         * to read phone numbers from Excel when the leading zero are absent.
         */
        $zs->addDestination('246300004');
        
        /*
         * phone numbers can also be in international number format. 
         * the leading '+' is also optional.
         */
        $zs->addDestination('233246300005');
        $zs->addDestination('+233246300006'); 
        
        /*
         * We will need to specify the date and time at which the message should be
         * submitted. If not satisified with the default timezone, it can be 
         * explicitly set as well.
         */
        $datetime = new DateTime();
        $datetime->setDate(2016, 6, 4);
        $datetime->setTime(14, 0, 0);
        $datetime->setTimezone(new DateTimeZone('Africa/Accra'));   // if there is the need to specify timezone
        
        // scheduling information must be set.
        $zs->schedule($datetime);
        
        /*
         * The message can now been sent. The SMS server will return a token
         * which identifies the message.
         */
        $response = $zs->sendMessage();
        
        // this is not required but if there is the need.
        $messagetoken = $response->getResponseValue();
    } 
    
    // when sending requests to the server, ZenophSMSGH_Exception may be
    // thrown if error occurs or the server rejects the request.
    catch (ZenophSMSGH_Exception $ex){
        $errmessage = $ex->getMessage();
        $responsecode = $ex->getResponseCode();
        
        // the response code indicates the specific cause of the error
        // you will need to compare with the elements in ZenophSMSGH_RESPONSECODE class.
        // for example,
        switch ($response){
            case ZenophSMSGH_RESPONSECODE::ERR_AUTH:
                // authentication failed.
                break;
            
            case ZenophSMSGH_RESPONSECODE::ERR_INSUFF_CREDIT:
                // balance is insufficient to send message to all destinations.
                break;
            
            // you can check for the other causes.
        }
    }
    
    // Exceptions caught here are mostly not the cause of 
    // sending request to the SMS server.
    catch (Exception $ex) {
        $errmessage = $ex->getMessage();
        
        // if the error needs to be echoed.
        echo $errmessage;
    }
?>
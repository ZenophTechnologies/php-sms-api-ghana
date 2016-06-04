<?php

/*
 * This is an example PHP script to send non-personalised message
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
        $zs->setMessage('This is a test message from PHP SMS API.');
        $zs->setMessageType(ZenophSMSGH_MESSAGETYPE::TEXT); // default is TEXT if you do not set it yourself.
        
        // add destinations.
        $zs->addDestination('0246300001');          // if number is invalid, exception will be thrown.
        $zs->addDestination('0246300002', true);    // if number is invalid, exception will be thrown.
        $zs->addDestination('0246300003', false);   // if number is invalid, exception will not be thrown.
        
        // for local number format, the leading zero is optional. This is an advantage
        // to read phone numbers from Excel when the leading zero are absent.
        $zs->addDestination('246300004');
        
        // phone numbers can also be in international number format. 
        // the leading '+' is also optional.
        $zs->addDestination('233246300005');
        $zs->addDestination('+233246300006');  
        
        // after adding phone numbers, the message can be submitted.
        $response = $zs->sendMessage();
        
        // the value returned depends on whether the server returned a token
        // or the submit status of the destinations. You may need to read the
        // documentation for information on handling the value returned from sendMessage();
        if ($response->isTokenResponse() == false)  {
            // we have the destinations and their submit status.
            $destinations = $response->getResponseValue();
            
            // iterate and show destination and status.
            foreach ($destinations as $destination) {
                $phonenumber   = $destination['number'];        // the destination phone number         
                $statusCode    = $destination['statusCode'];    // whether message was submitted to destination or not
                $destinationId = $destination['destinationId']; // assigned to the destination
            }
        }
        
        else {  // a token was rather returned.
            /*
             * a token will always be returned if the message was personalised, 
             * scheduled or when the number of destinations is greater than 400.
             */ 
            $messagetoken = $response->getResponseValue();
        }
    } 
    
    // when sending requests to the server, ZenophSMSGH_Exception may be
    // thrown if error occurs or the server rejects the request.
    catch (ZenophSMSGH_Exception $ex){
        $errmessage   = $ex->getMessage();
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
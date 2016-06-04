<?php
    /*
     * This is an example PHP script to send personalised message
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
        
        /*
         * to send personalised message, you will need to set the message once. However,
         * the message should contain variables in parts of the message where values will
         * be different for each destination.
         * 
         * To prevent the PHP interpreter from parsing the variables defined in the message,
         * the message can be enclosed in single quotes.
         */
        $message = 'Hello {$name}, your balance is GHC{$balance}.';
        $zs->setMessage($message);
         
        /*
         * destinations can now be added. Let's construct some contact data.
         * This is only for demonstration.
         */
        $contacts[] = array('phonenum'=>'0246300001', 'name'=>'Daniel', 'balance'=>450.34);
        $contacts[] = array('phonenum'=>'0246300002', 'name'=>'Oppong', 'balance'=>342.89);
         
        /*
         * now add the destinations.
         * 
         * The second argument to the method indicates whether the method should throw Exception
         * if validation fails for the destination. In this case which is set to true, if validation
         * fails for a destination, the iteration will be terminated and Exception will be thrown.
         * 
         * If we do not want Exception to be thrown but only reject the destination that validation
         * failed, the second parameter will have to be set to false so that the iteration will
         * not be terminated.
         */
        foreach ($contacts as $contact)
            $zs->addDestination ($contact['phonenum'], true, array($contact['name'], $contact['balance']));
         
        /*
         * the message can now be submitted.
         * for personalised messaging, a token is always returned by the SMS server.
         */
        $response = $zs->sendMessage();
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
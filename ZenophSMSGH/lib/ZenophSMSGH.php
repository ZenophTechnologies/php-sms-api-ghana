<?php

    include_once (__DIR__.'/MessageTypes.php');
    include_once (__DIR__.'/ZenophSMSGHException.php');
    include_once (__DIR__.'/SMSResponse.php');
    
 //   use Zenoph\ZenophSMSGH\Enums;

    class ZenophSMSGH {
        private $user;
        private $password;
        private $message;
        private $msgtype;
        private $senderid;
        private $destinations;
        private $scheduledatetime;
        private $scheduletimeoffset;

        private static $PSND_VALSEP = '__@';
        private static $PSND_RECPTSEP = '__#';
        private static $PSND_VARPATTERN = "/\{\\$[a-zA-Z][a-zA-Z0-9]+\}/";
        private static $BASEURL = "api.smsonlinegh.com";
        private static $RESPONSE_SEP = '@';
        private static $DESTINATION_SEP = ',';
        
        /**
         * The maximum number of destinations for which the submit status of the destinations will be
         * immediately returned.
         */
        const BATCH_SUBMIT_MAX = 400;
        
        /**
         * The maximum number of characters for message sender identifier when it 
         * contains only numeric characters, eg., phone number.
         */
        const NUMERIC_SENDER_ID_MAX_LEN = 15;
        
        /**
         * The maximum number of characters for message sender identifier when it
         * contains alphanumeric characters.
         */
        const ALPHANUMERIC_SENDER_ID_MAX_LEN = 11;

        /**
         * Constructor to set properties to default values.
         */
        public function __construct() {
            $this->user = null;
            $this->password = null;
            $this->message = null;
            $this->senderid = null;
            $this->msgtype  = ZenophSMSGH_MESSAGETYPE::TEXT;
            $this->destinations = null;
            $this->scheduledatetime = null;
            $this->scheduletimeoffset = null;
        }
        /**
         * <p>Sets the user account login for authentication</p><br />
         * @param string $user
         * The account login for authentication
         * @throws Exception Thrown if <i>$user</i> is <i>null</i> or empty.
         * <p></p><br />
         * @example
         * <p>The following sets the accout login:</p>
         * <code>
         * $zs = new ZenophSMSGH();
         * $zs->setUser('account_login');
         * </code>
         */
        public function setUser($user){
            if (!isset($user) || empty($user))
                self::throwException('Invalid value for setting account login.');
            
            // set user login.
            $this->user = $user;
        }
        
        /**
         * <p>Sets the user account password for authentication.</p><br />
         * @param string $password The account password for authentication
         * @throws Exception Thrown if <i>$password</i> is <i>null</i> or empty.
         * <p></p><br />
         * @example
         * <p>The following sets the account password: </p>
         * <code>
         * $zs = new ZenophSMSGH();
         * $zs->setPassword('account_password');
         * </code>
         */
        public function setPassword($password){
            if (!isset($password) || empty($password))
                self::throwException('Invalid value for setting account password.');
            
            // set the account password.
            $this->password = $password;
        }
        
        /**
         * <p>Sets the message that will be submitted to added mobile destinations.</p><br />
         * @param string $message The message to be sent to added mobile destinations.
         * @throws Exception Thrown if <i>$message</i> is <i>null</i> or empty.
         * <p></p><br />
         * @example
         * <p>The following non-personalised message will be submitted to destinations as it is composed:</p>
         * <code>
         * $message = 'Bulk SMS to Ghana destinations from PHP SMS API library.';
         * 
         * $zs = new ZenophSMSGH();
         * $zs->setMessage($message);
         * </code><br />
         * <p>In personalised messaging, variables must be defined in the message. The variables indicate
         * the parts of the message where values will be different for each destination. The variables should be enclosed
         * in single quotes otherwise the PHP interpreter will try to parse the variables defined 
         * in them which may generate an error.
         * </p><br />
         * <p>For example, the message can be defined as follows:</p>
         * <code>
         * $message = 'Hello {$name}, your balance is ${$balance}';
         * 
         * $zs = new ZenophSMSGH();
         * $zs->setMessage($message);
         * </code><br />
         * <p>
         * The above example message shows that the message contains two variables. Each destination will have
         * <i>name</i> and <i>balance</i> substituted in the message before being delivered to that destination.
         * The values to be substituted will need to be provided when adding destinations.
         * </p><br />
         * <p>
         * See the discussion on {@link addDestination()} for how to specify values for each destination 
         * when sending personalised messages.
         * </p><br />
         */
        public function setMessage($message){
            if (!isset($message) || empty($message))
                self::throwException('Invalid value for setting message.');
            
            // if the same message has already been set, we should return.
            if ($this->message == $message)
                return;
            
            if (self::getMessageVariablesCount($this->message) > 0 || self::getMessageVariablesCount($message) > 0){
                $this->clearDestinations();
                
                // if the message contains variables, we should ensure the variables are unique.
                $variables = self::getVariables($message);
                
                foreach ($variables as $testvar){
                    $count = 0;
                    
                    foreach ($variables as $variable){
                        if ($variable == $testvar && (++$count > 1)){
                            $trimmed = $this->trimVariable($testvar);
                            throw new Exception("Variable '{$trimmed}' must be unique in the message.");
                        }
                    }
                }
            }
            
            // set the message.
            $this->message = $message;
        }

        /**
         * Sets the type of message to be sent.
         * @param type $msgtype
         * <table class='summary'>
         * <caption>Message Types and 1 SMS count limit</caption>
         * <tr align='left'><th style='text-align: center;'>Message Type</th><th>Single</th><th>Concatenated</th></tr>
         * <tr><td style='text-align: left;'>{@link ZenophSMSGH_MESSAGETYPE::TEXT}</td><td>160</td><td>153</td></tr>
         * <tr><td style='text-align: left;'>{@link ZenophSMSGH_MESSAGETYPE::FLASH_TEXT}</td><td>160</td><td>153</td></tr>
         * <tr><td style='text-align: left;'>{@link ZenophSMSGH_MESSAGETYPE::UNICODE}</td><td>280</td><td>268</td></tr>
         * <tr><td style='text-align: left;'>{@link ZenophSMSGH_MESSAGETYPE::FLASH_UNICODE}</td><td>280</td><td>268</td></tr>
         * </table>
         * <p>
         * For any message type, the message will be concatenated if its length exceeds the maximum number of 
         * characters for which it is treated as a single message. In that case, the corresponding limit for
         * concatenated message will be used to count the number of SMS.
         * </p>
         */
        public function setMessageType($msgtype){
            if (!$this->isValidMessageType($msgtype))
                self::throwException('Invalid message type identifier.');
            
            // set the type of message to be sent.
            $this->msgtype = $msgtype;
        }
        
        private function isValidMessageType($msgtype){
            if (!isset($msgtype))
                return false;
            
            switch ($msgtype){
                case ZenophSMSGH_MESSAGETYPE::TEXT:
                case ZenophSMSGH_MESSAGETYPE::FLASH_TEXT:
                case ZenophSMSGH_MESSAGETYPE::UNICODE:
                case ZenophSMSGH_MESSAGETYPE::FLASH_UNICODE:
                    return true;
                    
                // any other ones are not valid, as per this API version.
                default:
                    return false;
            }
        }
        
        /**
         * <p>Sets the message sender identifier.</p><br /> 
         * @param string $senderid The message sender identifier.
         * @throws Exception Thrown if any of the following is true:
         * <ul>
         *  <li><i>$senderid</i> is <i>null</i> or empty.</li>
         *  <li><i>$senderid</i> is numeric only sender identifier (phone number) and its length exceeds 15 characters.</li>
         *  <li><i>$senderid</i> is alphanumeric sender identifier and its length exceeds 11 characters.</li>
         * </ul>
         * <p></p><br />
         * <p>The message sender identifier is what recipients of the message will see as the sender of the
         * message. The maximum number of characters for alphanumeric sender identifiers is 11 characters
         * while the maximum number of characters for numeric sender identifiers is 15 characters.
         * </p>
         * @see NUMERIC_SENDER_ID_MAX_LEN
         * @see ALPHANUMERIC_SENDER_ID_MAX_LEN
         */
        public function setSenderId($senderid){
            // validate the sender id before setting it.
            $this->validateSenderId($senderid);
            $this->senderid = $senderid;
        }
        
        private function validateSenderId($senderid){
            if (!isset($senderid) || empty($senderid))
                self::throwException('Invalid value for setting message sender.');
            
            // check for numberic only sender id
            if (self::isValidPhoneNumber($senderid)){    // phone number as sender id
                if (strlen($senderid) > self::NUMERIC_SENDER_ID_MAX_LEN)
                    self::throwException('Numeric Sender ID must not exceed '.self::NUMERIC_SENDER_ID_MAX_LEN.' characters.');
            }
            
            else {   // alphanumeric sender id
                if (strlen($senderid) > self::ALPHANUMERIC_SENDER_ID_MAX_LEN)
                    self::throwException('Alphanumeric Sender ID must not exceed '.self::ALPHANUMERIC_SENDER_ID_MAX_LEN.' characters.');
            }
        }
        
        /**
         * <p>Checks to see if <i>$number</i> is a valid phone number or not.</p><br />
         * @param string $number <p>The value to check whether it is a valid phone number or not.</p><br />
         * @return boolean <i>true</i> if <i>$number</i> is a valid phone number otherwise it returns <i>false</i>.
         */
        public static function isValidPhoneNumber($number) {
            return preg_match("/^\+?[0-9]{8,15}$/", $number) > 0 ? true : false;
        }
        
        private function addRecipient($phonenum, $values=null){
            $destination['number'] = $phonenum;
            
            if (isset($values) && !empty($values))
                $destination['values'] = $values;
            
            // add the destination.
            $this->destinations[] = $destination;
        }
        
        private function addPersonalisedDestination($phonenum, $throwex, $psndvalues){
            if (!isset($psndvalues) || !is_array($psndvalues) || !count($psndvalues)){
                if ($throwex)
                    self::throwException('Personalised values must be provided for destination.');

                // return control without adding destination
                return;
            }

            // ensure all values are valid.
            for ($i = 1; $i <= count($psndvalues); ++$i){
                $value = $psndvalues[$i-1];

                if (!isset($value) || empty($value)){
                    if ($throwex)
                        self::throwException("Value at position {$i} is invalid for destination '{$phonenum}'.");
                }
            }

            // validation ok
            $this->addRecipient($phonenum, $psndvalues);
        }
        
        /**
         * <p>Adds a phone number the destinations list.</p><br />
         * @param string $phonenum  <p>The phone number to be added to the destinations list. It can be in either local
         * or international number format.</p><br />
         * @param boolean $throwex <p>Indicates whether or not Exception should be thrown if <i>$phonenum</i>
         * is invalid or not allowed on user routes. Default is <i>true</i>.</p><br />
         * @param array $psndvalues  <p>An array of personalised values for the destination in a personalised message.</p><br />
         * @throws Exception Thrown if <i>$phonenum</i> is invalid or, for personalised messaging, when
         * <i>$psndvalues</i> is invalid.
         * <p></p><br />
         * <p>
         * <i>$phonenum</i> can be specified in either international or local number format:
         * <code>
         * // initialise SMS object and set parameters.
         * $zs = new ZenophSMSGH();
         * $zs->setUser('account_login');
         * $zs->setPassword('account_password');
         * 
         * // set message parameters.
         * 
         * // add destinations.
         * $zs->addDestination('264004000');     // local number format, no leading zero (0)
         * $zs->addDestination('0207700001');    // local number format with leading zero (0).
         * $zs->addDestination('233246000001');  // international number format, no leading '+'.
         * $zs->addDestination('+233208001100'); // international number format with leading '+'
         * </code>
         * </p><br />
         * <p>
         * By default, Exception will be thrown if the <i>$phonenum</i> is invalid. To prevent the
         * throwing of Exception when <i>$phonenum</i> is invalid, the second argument to the method must
         * be set to <i>false</i>.
         * <code>
         * // do not throw Exception if value specified as phone number is invalid.
         * $zs->addDestination('0207700001', false);
         * 
         * // throw Exception if value specified as phone number is invalid.
         * $zs->addDestination('026400400');
         * $zs->addDestination('0246000001', true);
         * </code>
         * </p><br />
         * <p>
         * Setting <i>$throwex</i> to <i>false</i> may be handy when adding a lot of destinations in
         * an iteration. In such cases, when a phone number is invalid, no Exception will be thrown and thus
         * the iteration will not be terminated.
         * <code>
         * // suppose we have the destinations in an array.
         * $destinations = array('0264004000', '0207700001', '246000001');
         * 
         * // we can add the destinations in an iteration. To prevent the iteration
         * // from being terminated when a phone number is invalid, we will need to
         * // set $throwex to false.
         * foreach ($destinations as $destination)
         *     $zs->addDestination($destination, false);
         * </code>
         * </p><br />
         * <p>
         * In personalised messaging, the values for each destination must also be passed  as an array to the
         * method as the third argument. In such cases, the second argument must be explicitly set.
         * </p><br />
         * <p>
         * Suppose we want to send personalised message to clients on their balance:
         * <code>
         * // initialise SMS object and set parameters.
         * $zs = new ZenophSMSGH();
         * $zs->setUser('account_login');
         * $zs->setPassword('account_password');
         * 
         * // set other parameters.
         * 
         * // as an example, let's set the message as follows.
         * $zs->setMessage('Hello {$name} your current balance is ${$balance}');
         * </code>
         * </p><br />
         * <p>
         * As can be seen, the message contains two variables (<i>name</i>, <i>balance</i>). When adding destinations in
         * personalised messaging, values must be supplied for each destination as an array with elements
         * each for the variables in the same order as the variables are defined in the message.
         * </p><br />
         * <p>
         * Let's setup client for demonstration.
         * <code>
         * // client's data.
         * $clients[] = array('name'=>'Daniel', 'phone'=>'0264004000', 'balance'=>545.56);
         * $clients[] = array('name'=>'Mavis', 'phone'=>'0207700001', 'balance'=>345.90);
         * 
         * // we can add destinations and specify values as follows.
         * foreach ($clients as $client)
         *     $zs->addDestination($client['phone'], false, array($client['name'], $client['balance']));
         * </code>
         * </p><br />
         * <p>
         * Observe how the values are provided in the method call as the third argument. There two
         * variables defined in the message and hence two values are provided for each destination.
         * Again, the order in which the values are set should match the order in which the variables
         * are defined in the message for substitution.
         * </p><br />
         */
        public function addDestination($phonenum, $throwex=true, $psndvalues=null){
            // first, ensure that the phone number is valid.
            if (!self::isValidPhoneNumber($phonenum)){
                
                if ($throwex)
                    self::throwException("'{$phonenum}' is not a valid phone number.");
                
                // return control without adding the phone number.
                return;
            }
            
            // if personalised values are provided, the message must already be set.
            if ((!is_null($psndvalues) && is_array($psndvalues) && count($psndvalues) > 0) && is_null($this->message) || empty($this->message))
                self::throwException('No message has been set for adding personalised destination.');
            
            // get number of variables in the message and ensure it corresponds with
            // the number of variables being passed to be added.
            $varscount = self::getMessageVariablesCount($this->message);
            
            if (!$varscount){
                $this->addRecipient($phonenum);
                return;
            }
            
            else {  // message contains variables.
                $this->addPersonalisedDestination($phonenum, $throwex, $psndvalues);
            }
        }
        
        private function sendRequest($url, $params, $getresponse){
            $ch  = curl_init($url);
            curl_setopt($ch, CURLOPT_POST, 1);
            
            // see if we have parameters, if so set it.
            if (isset($params) && !empty($params))
                curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
            
            // see if we are to return response, if so set to true
            if ($getresponse)
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            
            // submit the message.
            $response = curl_exec ($ch);
            $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

            // HTTP success code is 200
            if ($code != 200)
                self::throwException('Message submit error.');
            
            if ($getresponse)
                return $response;
        }
        
        /**
         * <p>Sends the message to the added destinations.</p><br />
         * @param boolean $cleardests Indicates whether the destinations list should be cleared
         * after submitting the message. The default is <i>true</i>. Hence, the destinations will be
         * cleared after submitting the message if it is not explicitly set to <i>false</i>.
         * @throws ZenophSMSGH_Exception  Thrown if server request fails.<br />
         * @throws Exception  Thrown if message submit validation fails.
         * <p></p><br />
         * @example 
         * <code>
         * // initialise SMS object and set parameters.
         * $zs = new ZenophSMSGH();
         * $zs->setUser('account_login');
         * $zs->setPassword('account_password');
         * 
         * // message parameters.
         * $zs->setMessageType(ZenophSMSGH_MESSAGETYPE::TEXT);
         * $zs->setSenderId('PHP API TEST');
         * $zs->setMessage('Hello there!');
         * 
         * // add destinations.
         * $zs->addDestination('0207729851');   // in local number format
         * $zs->addDestination('233246314915'); // in international number format
         * 
         * // send the message
         * $response = $zs->sendMessage();
         * </code><br />
         * <p>
         * If you do not want the destinations list to be cleared after submitting the message, you must set
         * <i>$cleardests</i> to <i>false</i>. Then you can later clear the destinations list by calling
         * {@link clearDestinations()}
         * </p><br />
         * <code>
         * // send the message, but do not clear the destinations list.
         * $response = $zs->sendMessage(false);
         * 
         * // the destinations list may be cleared later.
         * $zs->clearDestinations();
         * </code><br />
         * <p>
         * When {@link sendMessage()} is called, it returns an object of type {@link ZenophSMSGH_MessageResponse}. The
         * returned object contains the value returned from the SMS server as well as indicator that tells whether
         * the returned value is a token response or not.
         * </p><br />
         * <p>
         * When a message is submitted and the number of destinations is less than or equal to {@link BATCH_SUBMIT_MAX},
         * the SMS server will submit the message and return the submit status of the destinations. In such case, the return object 
         * will contain an array of the destinations. Each destination will also be an array of three elements containing
         * the phone number, submit status of the destination and an identifier which identifies the destination.
         * </p><br />
         * <p>
         * For example, suppose we submit the message to two destinations:
         * <code>
         * // initialise SMS object and set message parameters
         * 
         * // now add destinations.
         * $zs->addDestination('0246314915');
         * $zs->addDestination('0207729851');
         * 
         * // now suppose total destinations is less than {@link BATCH_SUBMIT_MAX}
         * // and the message is submitted.
         * $response = $zs->sendMessage();
         * 
         * // the response value will not be a token but we can also test for it.
         * if ($response->isTokenResponse() == false){ // we have submit status of destinations.
         *     $destinations = $response->getResponseValue();
         * 
         *     // in the destinations array are arrays of individual destinations 
         *     // with their status and identifier.
         *     foreach ($destinations as $destination){
         *         echo "Destination: {$destination['number']}, ".
         *             "StatusCode: {$destination['statusCode']}, ".
         *             "Destination ID: {$destination['destinationId']}";
         *     }
         * }
         * </code>
         * </p><br />
         * <p>
         * For scheduled messages and messages with destinations greater than {@link BATCH_SUBMIT_MAX}, 
         * the SMS server will immediately return a token before proceeding to submit the message. 
         * This is to prevent timeout when there will be long data processing. In such case, 
         * the response will be a single token that identifies the message submission. If there is the need, 
         * the returned token may be used to query the submit status of the destinations later.
         * </p>
         */
        public function sendMessage($cleardests=true){
            $this->validateMessageSubmission();
            $params = "user=".urlencode($this->user)."&password=".urlencode($this->password)."&sender=".urlencode($this->senderid).
                "&type=".$this->msgtype."&message=".urlencode($this->message)."&".$this->getDestinationsString().
                (isset($this->scheduledatetime) ? "&schedule=".urlencode($this->scheduledatetime->format('Y-m-d H:i:s')).
                "&gmtoffset=".urlencode($this->formatTimezoneOffset($this->scheduledatetime)) : "");

            // submit message and process the response.
            return $this->processSubmitResponse($this->sendRequest(self::$BASEURL.'/sendsms/', $params, true), $cleardests);
        }
        
        private function formatTimezoneOffset($datetime){
            $offset = $datetime->getOffset();
            return ($offset < 0 ? '-' : '+').gmdate('H:i', $offset);
        }
        
        private function processSubmitResponse($resp, $cleardests){
            // find out if the message was accepted.
            $response = explode(self::$RESPONSE_SEP, $resp);
            
            if ($response[0] != ZenophSMSGH_RESPONSECODE::SUCCESS)  // not successful.
                throw new ZenophSMSGH_Exception($response[1], $response[0]);
            
            // see if we are to clear the destinations.
            if ($cleardests)
                $this->clearDestinations();

           // check to see if we have a token response or not.
            if (substr_count($response[1], '|') == 0)
                return new ZenophSMSGH_MessageResponse(true, $response[1]);
            else {
                $response = explode(self::$DESTINATION_SEP, $response[1]);
                $submitresp = array();
                
                foreach ($response as $destinfo){
                    $destarr = explode('|', $destinfo);
                    $submitresp[] = array('number'=>$destarr[1], 'statusCode'=>$destarr[0], 'destinationId'=>$destarr[2]);
                }
                
                return new ZenophSMSGH_MessageResponse(false, $submitresp);
            }
        }
        
        private function validateMessageSubmission(){
            if (!isset($this->user) || empty($this->user))
                self::throwException('Account login has not been set.');
            
            if (!isset($this->password) || empty($this->password))
                self::throwException('Account password has not been set.');
            
            if (!isset($this->destinations) || !count($this->destinations))
                self::throwException('Message destination has not been set.');
            
            if (!isset($this->message) || empty($this->message))
                self::throwException('Message body has not been set.');
            
            if (!isset($this->senderid) || empty($this->senderid))
                self::throwException('Message sender identifier has not been set.');
            
            // validate the sender identifier.
            $this->validateSenderId($this->senderid);
            return true;
        }
        
        private function getDestinationsString(){
            $destr = "";
            $valstr = "";
            
            foreach ($this->destinations as $destination){
                $destr .= (!empty($destr) ? "," : "").$destination['number'];
               
                if (isset($destination['values']) && count($destination['values']) > 0){
                    $destvalstr = "";
                    
                    foreach ($destination['values'] as $value)
                        $destvalstr .= (!empty($destvalstr) ? self::$PSND_VALSEP : "").$value;

                    $valstr .= (!empty($valstr) ? self::$PSND_RECPTSEP : "").$destvalstr;
                }
            }
            
            // return everything.
            return ("destination={$destr}").(isset($valstr) && !empty($valstr) ? "&values=".urlencode($valstr):"");
        }
        
        /**
         * <p>Gets the remaining account balance based on 1 SMS count.</p><br />
         * @throws Exception    Thrown if account login and or password is not set for authentication.<br />
         * @throws ZenophSMSGH_Exception Thrown if request fails.
         * <p></p><br />
         * <p>Account login and password must be set before calling this method otherwise
         * Exception will be thrown.
         * </p><br />
         * @example 
         * <code>
         * // initialise object and set required parameters.
         * $zs = new ZenophSMSGH();
         * $zs->setUser('account_login');
         * $zs->setPassword('account_password');
         * 
         * // now we can request for the credits balance.
         * $balance = $zs->getBalance();
         * </code>
         */
        public function getBalance(){
            if (!isset($this->user) || empty($this->user))
                self::throwException('Account login has not been set.');
            
            if (!isset($this->password) || empty($this->password))
                self::throwException('Account password has not been set.');

            $params = "user=".urlencode($this->user)."&password=".urlencode($this->password);
            $response = explode(self::$RESPONSE_SEP, $this->sendRequest(self::$BASEURL.'/balance/', $params, true));
            return $response[1];
        }
        
        /**
         * <p>Gets the number of SMS that will be charged per each destination.</p><br />
         * <p>
         * The SMS count is determined based on the message type and the length of the
         * message to be submitted. The total credits that will be charged is always the
         * SMS count returned by this method multiplied by the total number of destinations added.
         * </p>
         */
        public function getSMSCount(){
            if (!isset($this->msgtype) || !$this->isValidMessageType($this->msgtype))
                $this->throwException('Invalid message type identifier.');
            
            if (!isset($this->message) || empty($this->message))
                return 0;
            
            $smscount = 1;
            $limits = $this->getMessageTypeTextLimits();
            $singlelen = $limits['single_len'];
            $concatlen = $limits['concat_len'];
            $msglen = $limits['char_len'] * strlen($this->message);
            
            if ($msglen > $singlelen){
                $smscount = $msglen / $concatlen;
                $smscount = floor($smscount);
                $smscount += ($msglen % $singlelen > 0 ? 1 : 0);
            }
            
            // return the SMS count.
            return $smscount;
        }
        
        private function getMessageTypeTextLimits(){
            switch ($this->msgtype){
                case ZenophSMSGH_MESSAGETYPE::TEXT:
                case ZenophSMSGH_MESSAGETYPE::FLASH_TEXT:
                    return array('single_len'=>160, 'concat_len'=>153, 'char_len'=>1);
                    
                case ZenophSMSGH_MESSAGETYPE::UNICODE:
                case ZenophSMSGH_MESSAGETYPE::FLASH_UNICODE:
                    return array('single_len'=>280, 'concat_len'=>268, 'char_len'=>4);
                    
                default:
                    $this->throwException('Invalid message type identifier.');
            }
        }
        
        private static function throwException($message){
            if (!isset($message) || empty($message))
                $message = 'Unknown error.';
            
            throw new Exception($message);
        }
        
        private function trimVariable($variable){
            if (!isset($variable) || empty($variable))
                self::throwException('Invalid value for trimming variable.');
            
            $trimmed = preg_replace("/[\{\$]|[\}]/", "", $variable);
            return $trimmed[0];
        }  
        
        private static function getMessageVariablesCount($message){
            return count(self::getVariables($message));
        }
        
        private static function &getVariables($message){
            $vars = array(); 
            preg_match_all(self::$PSND_VARPATTERN, $message, $vars, PREG_SET_ORDER);           
            return $vars;
        }
        
        /**
         * Clears the list of phone numbers added to the destinations list.
         */
        public function clearDestinations(){
            if ($this->destinations) {
                unset($this->destinations);
                $this->destinations = null;
            }
        }
        
        /**
         * <p>Sets date and time for scheduling message.</p><br />
         * @param DateTime $datetime A <i>DateTime</i> object that contains the date, time, and timezone for 
         * scheduling the message.<br />
         * @throws Exception Thrown if <i>$datetime</i> is <i>null</i> or not an instance of <i>DateTime</i>.
         * <p></p><br />
         * <p>
         * The method sets the date and time for scheduling the message. <i>$datetime</i> must be a 
         * <i>DateTime</i> object with date, time, and the timezone offset from UTC set.
         * </p><br />
         * <p>
         * The time zone for the specified date and time will also affect the time the message will be submitted.
         * </p><br />
         * @example 
         * <code>
         * // initialise sms object and set parameters.
         * $zs = new ZenophSMSGH();
         * 
         * // set other parameters.
         * 
         * // to schedule the message for submission on April 2, 2016 at 3:30 PM
         * $datetime = new DateTime();
         * $datetime->setDate(2016, 4, 2);
         * $datetime->setTime(15, 30, 0);
         * $datetime->setTimezone(new DateTimeZone('Africa/Accra'));
         * 
         * // the method can be called to set the schedule date, time and timezone.
         * // It does not submit the message to the server.
         * $zs->schedule($datetime);
         * 
         * // when ready, sendMessage() can be called.
         * $response = $zs->sendMessage();
         * </code>
         */
        public function schedule($datetime){
            // ensure we have a DateTime object.
            if (isset($datetime) && !$datetime instanceof DateTime)
                $this->throwException('Schedule date and time must be passed as DateTime object');
            
            // set the scheduling datetime object.
            $this->scheduledatetime = $datetime;
        }
    }
?>

<?php

    class ZenophSMSGH_MessageResponse {
        private $istokenresp = false;
        private $responsevalue = null;
        
        public function __construct($istokenresp, $responsevalue) {
            $this->istokenresp  = (bool)$istokenresp;
            $this->responsevalue = $responsevalue;
        }
        
        /**
         * <p>Tells whether the response value after submitting a message is a token response or not.</p><br />
         * @return boolean  Returns <i>true</i> if the response value is a token response otherwise it returns <i>false</i>.
         */
        public function isTokenResponse(){
            return $this->istokenresp;
        }
        
        /**
         * <p>Gets the response value that is returned from the server after submitting a message.</p><br />
         * @return mixed <p>Returns the response value that is returned from the SMS server after submitting a message.</p><br />
         * <p>
         * The response value that is returned after submitting a message is variant depending on factors such as
         * the total number of destinations submitted, whether the message was scheduled, and whether the message
         * was personalised or not.
         * </p><br />
         * <p>
         * Firstly, when a non-personalised message is submitted and the total number of destinations is less than or 
         * equal to 400, the SMS server will submit the message and return the submit status of destinations
         * as well as unique identifiers assigned to the destinations. The response value will be an array of the destinations.
         * Each destination is also an array of three elements which can be accessed with keys <i>number</i>, 
         * <i>statusCode</i>, and <i>destinationId</i>.
         * </p><br />
         * <p>
         * Secondly, if the message is non-personalised and the total number of destinations is greater than
         * 400, the SMS server will immediately return a token and then proceed to submit the message
         * to the destinations. In this case, the response value is not an array but a single string value which
         * is the token assigned to the message. If there is the need, the returned token may be used to query the
         * submit status of the destinations later.
         * </p><br />
         * <p>
         * Thirdly, if the message was personalised or scheduled, the response value will be a token that identifies
         * the message just as explained previously.
         * </p>
         */
        public function getResponseValue(){
            return $this->responsevalue;
        }
    }
?>
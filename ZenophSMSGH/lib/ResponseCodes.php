<?php

    class ZenophSMSGH_RESPONSECODE {
        /**
         * Indicates that the request was successfully completed.
         */
        const SUCCESS = 1400;   
        
        /**
         * An error indicator for user authentication failure.
         */
        const ERR_AUTH = 1401;
        
        /**
         * An error indicator for missing or invalid message type.
         */
        const ERR_MSGTYPE = 1402;
        
        /**
         * An error indicator for missing or invalid message value.
         */
        const ERR_MESSAGE = 1403;
        
        /**
         * An error indicator for missing destinations parameter.
         */
        const ERR_DEST = 1404;
        
        /**
         * An error indicator for missing or invalid message sender identifier.
         */
        const ERR_SENDER = 1405;
        
        /**
         * An error indicator for invalid delivery report specifier.
         * <p>This error code will not be returned since delivery report specifier is 
         * currently not used.
         * </p>
         */
        const ERR_DLVRPT = 1406;
        
        /**
         * An error indicator for missing or invalid URL for wap push message.
         * <p>
         * This error code will not be returned since wap push messages are currently not supported.
         * </p>
         */
        const ERR_WAPPUSH_URL = 1407;
        
        /**
         * An error indicator for missing values parameter for personalised message.
         */
        const ERR_VALUES = 1408;
        
        /**
         * An error indicator for missing value for a destination in a personalised message.
         */
        const ERR_MISSING_VAL = 1409;
        
        /**
         * An error indicator for missing or invalid request parameter.
         */
        const ERR_PARAMETER = 1500;
        
        /**
         * An error indicator for request validation.
         */
        const ERR_VALIDATION = 1501;
        
        /**
         * An error indicator for insufficient credits balance for submitting message to destinations.
         */
        const ERR_INSUFF_CREDIT = 1502;
        
        /**
         * An error indicator for incompatible API library.
         */
        const ERR_INCOMPAT_API = 1503;
        
        /**
         * An error indicator for invalid or unknown user route for destination.
         */
        const ERR_ROUTING = 1504;
        
        /**
         * An error indicator for missing or invalid token for getting submit status of destinations.
         */
        const ERR_TOKEN = 1505;
        
        /**
         * An error indicator for missing or invalid filter for getting submit status of destinations.
         */
        const ERR_FILTER = 1506;
        
        /**
         * An error indicator for errors when processing request.
         */
        const ERR_REQUEST = 1507;
        
        /**
         * An error indicator for null response after message submission.
         */
        const ERR_NULL_RESPONSE = 1508;
        
        /**
         * An error indicator for unknown request error.
         */
        const ERR_UNKNOWN = 1509;  

        /**
         * An error indicator for missing or invalid account login.
         */
        const ERR_LOGIN = 1600;
        
        /**
         * An error indicator for invalid date and time for scheduling message.
         */
        const ERR_SCHEDDATE = 1601;
        
        /**
         * An error indicator for invalid time zone offset from GMT for scheduling message.
         */
        const ERR_SCHEDOFFSET = 1602;
        
        /**
         * An error indicator for rejected destination.
         */
        const ERR_REJECTED = 1603;

        /**
         * Indicates that message is pending submission to particular destination.
         */
        const PENDING = 1800;
        
        /**
         * Indicates that the website is under maintenance and that messages cannot be submitted.
         */
        const MAINTENANCE_MODE = 1801;
    }
?>
<?php

    class ZenophSMSGH_MESSAGETYPE {
        /**
         * Indicates a regular message in GSM 03.38 character set.
         */
        const TEXT = 0;
        
        /**
         * Indicates a regular message in unicode character set.
         */
        const UNICODE = 1;
        
        /**
         * Indicates a flash message in GSM 03.38 character set.
         */
        const FLASH_TEXT = 2;
        
        /**
         * Indicates a flash message in unicode character set.
         */
        const FLASH_UNICODE = 3;
    }
?>
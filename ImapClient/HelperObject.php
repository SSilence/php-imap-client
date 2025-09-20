<?php

namespace SSilence\ImapClient;

/**
 * Helper class for dynamic properties in PHP 8.2+
 * This class allows dynamic property assignment which is deprecated 
 * on stdClass in PHP 8.2+
 */
#[\AllowDynamicProperties]
class HelperObject
{
    // This class intentionally left empty to allow dynamic properties
}
<?php

namespace SSilence\ImapClient;

use SSilence\ImapClient\ImapClientException;

class AdapterForOutgoingMessage
{
    /*
     * Connect config
     */
    private $config;

    /*
     * Array ['to'=>'', 'subject'=>'' and other]
     */
    private static $options;

    public function __construct(array $connectConfig)
    {
        $this->config = $connectConfig;
    }

    public static function setOptions(array $options)
    {
        self::$options = $options;
    }

    /*
     * Example
     */
    /*
    public function send()
    {
        $outMessage = new OutgoingMessage();
        $outMessage->setFrom(self::$options['from']);
        $outMessage->setTo(self::$options['to']);
        $outMessage->setSubject(self::$options['subject']);
        $outMessage->setMessage(self::$options['message']);
        $outMessage->send();
    }
    */

    public function send()
    {
        throw new ImapClientException('Not implemented');
    }
}
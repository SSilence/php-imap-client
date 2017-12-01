<?php
/**
 * Copyright (C) 2016-2017  SSilence
 * For the full license, please see LICENSE. 
 */

namespace SSilence\ImapClient;

/**
 * Class AdapterForOutgoingMessage is adapter for outgoing messages.
 *
 * @copyright  Copyright (c) Tobias Zeising (http://www.aditu.de)
 * @author     Tobias Zeising <tobias.zeising@aditu.de>
 */
class AdapterForOutgoingMessage
{
    /**
     * Connect config
     *
     * @var array
     */
    private $config;

    /**
     * Options
     *
     * Array ['to'=>'', 'subject'=>'' and other]
     *
     * @var array
     */
    private static $options;

    /**
     * Constructor
     *
     * Called when the class is made.
     *
     * @param array $connectConfig
     */
    public function __construct(array $connectConfig)
    {
        $this->config = $connectConfig;
    }

    /**
     * Set the options of this class
     *
     * @param array $options
     * @return void
     */
    public static function setOptions(array $options)
    {
        self::$options = $options;
    }

    /**
     * Send an email. Not implemented
     *
     * @throws ImapClientException
     */
    public function send()
    {
        throw new ImapClientException('Not implemented');
    }
}

<?php
/**
 * Copyright (C) 2016-2017  SSilence
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 */

namespace SSilence\ImapClient;

/**
 * Class AdapterForOutgoingMessage is adapter for outgoing messages
 *
 * @package    SSilence\ImapClient
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
     * @return void
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

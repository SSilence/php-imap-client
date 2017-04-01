<?php

namespace SSilence\ImapClient;

use SSilence\ImapClient\ImapClientException;

/**
 * Adapter for outgoing messages
 *
 * Copyright (C) 2016-2017  SSilence
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * @package    protocols
 * @copyright  Copyright (c) Tobias Zeising (http://www.aditu.de)
 * @author     Tobias Zeising <tobias.zeising@aditu.de>
 */

class AdapterForOutgoingMessage
{
    /**
     * Connect config
     */
    private $config;

    /**
     * Array ['to'=>'', 'subject'=>'' and other]
     */
    private static $options;

	/**
	 * Called when the class is made. 
	 */
    public function __construct(array $connectConfig)
    {
        $this->config = $connectConfig;
    }

	/**
	 * Set the options of this class
	 */
    public static function setOptions(array $options)
    {
        self::$options = $options;
    }

	/**
	 * Send an email. Not impelmented
	 */
    public function send()
    {
        throw new ImapClientException('Not implemented');
    }
}
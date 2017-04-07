<?php

namespace SSilence\ImapClient;

/**
 * Class for all incoming message attachments
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

class IncomingMessageAttachment
{

    private $raw;
    private $_name;
    private $_body;

    /**
     * The constructor
     * @param array The raw attachment array
     */
    public function __construct ($raw)
    {
        $this->raw = $raw;
    }

    /**
     * Returns the raw attachment array for those who want
     * to use it for things that aren't implemented yet
     * @return array
     */
    public function getRaw ()
    {
        return $this->raw;
    }

    /**
     * Returns the name of the attachment along with file extension
     * @return string
     */
    public function getName ()
    {
        if ($this->_name === null)
        {
            foreach ($this->raw->structure->dparameters as $param)
            {
                if ($param->attribute == 'filename')
                {
                    $this->_name = $param->value;
                    break;
                };
            };
        };
        return $this->_name;
    }

    /**
     * Returns the body of the e-mail
     * @return string
     */
    public function getBody ()
    {
        if ($this->_body === null)
        {
            $this->_body = $this->raw->body;
        };
        return $this->_body;
    }

};

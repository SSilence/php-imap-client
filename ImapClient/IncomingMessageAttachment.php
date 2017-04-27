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
 * Class IncomingMessageAttachment for all incoming message attachments
 *
 * @package    SSilence\ImapClient
 * @copyright  Copyright (c) Tobias Zeising (http://www.aditu.de)
 * @author     Tobias Zeising <tobias.zeising@aditu.de>
 */
class IncomingMessageAttachment
{

    /**
     * Name current attachment
     *
     * @var string
     */
    public $name;

    /**
     * Body current attachment
     *
     * @var string
     */
    public $body;

    /**
     * Incoming object
     *
     * Incoming SSilence\ImapClient\Section object
     *
     * @var Section
     */
    private $_incomingObject;

    /**
     * The constructor
     *
     * Set $this->name and $this->body
     *
     * @param Section $incomingObject
     * @return IncomingMessageAttachment
     */
    public function __construct (Section $incomingObject)
    {
        $this->_incomingObject = $incomingObject;
        $this->getName();
        $this->getBody();
    }

    /**
     * Returns the name of the attachment along with file extension
     *
     * @return string
     */
    private function getName ()
    {
        // Check for different types of inline attachments. 
        if ($this->_incomingObject->structure->ifdparameters)
        {
            foreach ($this->_incomingObject->structure->dparameters as $param)
            {
                if ($param->attribute == 'filename')
                {
                    $this->name = $param->value;
                    break;
                };
            }
        }
        elseif($this->_incomingObject->structure->ifparameters)
        {
            foreach ($this->_incomingObject->structure->parameters as $param)
            {
                if ($param->attribute == 'name')
                {
                    $this->name = $param->value;
                    break;
                };
            }
        };
    }

    /**
     * Returns the body of the e-mail
     *
     * @return string
     */
    private function getBody ()
    {
        $this->body = $this->_incomingObject->body;
    }

}

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

use \Exception;

/**
 * Class ImapClientException is used to make a custom Exception for our library
 *
 * @package    SSilence\ImapClient
 * @copyright  Copyright (c) Tobias Zeising (http://www.aditu.de)
 * @author     Tobias Zeising <tobias.zeising@aditu.de>, sergey144010
 */
class ImapClientException extends Exception
{
    /**
     * Get info about errors
     *
     * @return string
     */
    public function getInfo()
    {
        $error  = $this->getMessage().PHP_EOL;
        $error .= 'File: '.$this->getFile().PHP_EOL;
        $error .= 'Line: '.$this->getLine().PHP_EOL;
        return $error;
    }
}

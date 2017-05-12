<?php
/**
 * Copyright (C) 2016-2017  SSilence
 * For the full license, please see LICENSE.
 */

namespace SSilence\ImapClient;

use Exception;

/**
 * Class ImapClientException is used to make a custom Exception for our library.
 *
 * @copyright  Copyright (c) Tobias Zeising (http://www.aditu.de)
 * @author     Tobias Zeising <tobias.zeising@aditu.de>, sergey144010
 */
class ImapClientException extends Exception
{
    /**
     * Get info about the error(s).
     *
     * @return string
     */
    public function getInfo()
    {
        $error = $this->getMessage().PHP_EOL;
        $error .= 'File: '.$this->getFile().PHP_EOL;
        $error .= 'Line: '.$this->getLine().PHP_EOL;

        return $error;
    }
}

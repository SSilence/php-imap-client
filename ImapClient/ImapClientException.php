<?php
namespace SSilence\ImapClient;

use Exception;

/**
 * Class ImapClientException is used to make a custom Exception for our library.
 */
class ImapClientException extends Exception
{
    /**
     * Get info about the error(s).
     *
     * @return string
     */
    public function getInfo() {
        $error = $this->getMessage().PHP_EOL;
        $error .= 'File: '.$this->getFile().PHP_EOL;
        $error .= 'Line: '.$this->getLine().PHP_EOL;
        return $error;
    }
}

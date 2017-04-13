<?php
/**
 * Created by PhpStorm.
 * User: Sergey144010
 * Date: 11.04.2017
 * Time: 18:56
 */

namespace SSilence\ImapClient;

use SSilence\ImapClient\ImapClientException;

class Section
{
    private $_structure;
    private $_body;

    public function __set($property, $value)
    {
        switch($property)
        {
            case 'structure':
                $this->_structure = $value;
                break;
            case 'body':
                $this->_body = $value;
                break;
            default:
                throw new ImapClientException('Section object have only "structure" and "body" properties.');
        };
    }

    public function __get($property)
    {
        switch($property)
        {
            case 'structure':
                return $this->_structure;
                break;
            case 'body':
                return $this->_body;
                break;
            default:
                throw new ImapClientException('Section object have only "structure" and "body" properties.');
        }
    }

    public function __isset($property)
    {
        switch($property)
        {
            case 'structure':
                return $this->_structure;
                break;
            case 'body':
                return $this->_body;
                break;
            default:
                throw new ImapClientException('Section object have only "structure" and "body" properties.');
        }
    }

    public function __unset($property)
    {
        throw new ImapClientException('Section object not supported unset.');
    }

    public function __toString()
    {
        return $this->_body;
    }
}
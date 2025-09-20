<?php
namespace SSilence\ImapClient;

/**
 * Class for all incoming messages.
 */
class Section implements \JsonSerializable {
    /**
     * Structure current section.
     *
     * @var object
     */
    private $_structure;

    /**
     * Body current section.
     *
     * @var string
     */
    private $_body;

    /**
     * Set current property.
     *
     * @param string        $property it is property of the called object
     * @param object|string $value    it is value of the called object
     *
     * @throws ImapClientException
     */
    public function __set($property, $value) {
        switch ($property) {
            case 'structure':
                $this->_structure = $value;
                break;
            case 'body':
                $this->_body = $value;
                break;
            default:
                throw new ImapClientException('Section object have only "structure" and "body" properties.');
        }
    }

    /**
     * Get current property.
     *
     * @throws ImapClientException
     */
    public function __get($property) {
        switch ($property) {
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

    /**
     * Check isset() current object property.
     *
     * @throws ImapClientException
     */
    public function __isset($property) {
        switch ($property) {
            case 'structure':
                return isset($this->_structure);
            case 'body':
                return isset($this->_body);
            default:
                throw new ImapClientException('Section object have only "structure" and "body" properties.');
        }
    }

    /**
     * Unset current object property.
     *
     * @throws ImapClientException
     */
    public function __unset($property) {
        throw new ImapClientException('Section object not supported unset.');
    }

    /**
     * Return $this->_body when object convert to string.
     *
     * @return string
     */
    public function __toString() {
        if (!$this->_body) {
            return '';
        }
        return $this->_body;
    }

    /**
     * Returns the private properties of the object when serializing,
     * like this json_encode().
     *
     * @return array
     */
    public function jsonSerialize(): mixed {
        $properties = get_object_vars($this);
        $outProperties = array();
        foreach ($properties as $propertie => $value) {
            if ($propertie[0] === '_') {
                $namePropertie = substr($propertie, 1);
                $outProperties[$namePropertie] = $this->$propertie;
            } else {
                $outProperties[$propertie] = $this->$propertie;
            }
        }

        return $outProperties;
    }
}

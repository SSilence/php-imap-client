<?php
namespace SSilence\ImapClient;

/**
 * Class IncomingMessageAttachment for all incoming message attachments.
 */
class IncomingMessageAttachment {
    /**
     * Name current attachment.
     *
     * @var string
     */
    public $name;

    /**
     * Body current attachment.
     *
     * @var string
     */
    public $body;

    /**
     * Incoming object.
     *
     * Incoming SSilence\ImapClient\Section object
     *
     * @var Section
     */
    private $_incomingObject;

    /**
     * The constructor.
     *
     * Set $this->name and $this->body
     *
     * @param Section $incomingObject
     *
     * @return IncomingMessageAttachment
     */
    public function __construct(Section $incomingObject) {
        $this->_incomingObject = $incomingObject;
        $this->getName();
        $this->getBody();
    }

    /**
     * Returns the name of the attachment along with file extension.
     *
     * @return string
     */
    protected function getName() {
        // Check for different types of inline attachments.
        if (is_object($this->_incomingObject->structure) &&
            property_exists($this->_incomingObject->structure, 'ifdparameters') &&
            $this->_incomingObject->structure->ifdparameters) {
            foreach ($this->_incomingObject->structure->dparameters as $param) {
                if (strtolower($param->attribute) === 'filename') {
                    $this->name = $param->value;
                    break;
                }
            }
        } elseif (is_object($this->_incomingObject->structure) &&
            property_exists($this->_incomingObject->structure, 'ifparameters') &&
            $this->_incomingObject->structure->ifparameters) {
            foreach ($this->_incomingObject->structure->parameters as $param) {
                if (strtolower($param->attribute) === 'name') {
                    $this->name = $param->value;
                    break;
                }
            }
        }
    }

    /**
     * Returns the body of the e-mail.
     *
     * @return string
     */
    protected function getBody() {
        $this->body = $this->_incomingObject->body;
    }
}

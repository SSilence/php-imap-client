<?php
/**
 * Copyright (C) 2016-2017  SSilence
 * For the full license, please see LICENSE.
 */

namespace SSilence\ImapClient;

/**
 * Class for all incoming messages.
 *
 * This class get message and generates a message structure of the form:
 * ```php
 * $incomingMessage = new IncomingMessage();
 * $incomingMessage->header;
 * $incomingMessage->message;
 * $incomingMessage->attachments;
 * $incomingMessage->section;
 * $incomingMessage->structure;
 * $incomingMessage->debug;
 * ```
 * And marks the message read.
 * TODO: Format class correctly.
 *
 * @copyright  Copyright (c) Tobias Zeising (http://www.aditu.de)
 * @author    Sergey144010
 */
class IncomingMessage
{
    /**
     * Used to handle sections of the e-mail easier.
     */
    const SECTION_ATTACHMENTS = 1;

    /**
     * Used to handle sections of the e-mail easier.
     */
    const SECTION_BODY = 2;

    /**
     * Do not use decode incoming message.
     */
    const NOT_DECODE = 'not_decode';

    /**
     * Use decode incoming message.
     */
    const DECODE = 'decode';

    /**
     * Header of the message.
     *
     * @var object
     */
    public $header;

    /**
     * The message.
     *
     * @var object
     */
    public $message;

    /**
     * Attachments.
     *
     * @var array
     */
    public $attachments;

    /**
     * Section of the message.
     *
     * @var string|array
     */
    public $section;

    /**
     * Structure of the message.
     *
     * @var object
     */
    public $structure;

    /**
     * Debug on or off.
     *
     * @var object
     */
    public $debug;

    /**
     * The imap string.
     *
     * @var resource
     */
    private $imapStream;

    /**
     * ID of the message.
     *
     * @var int
     */
    private $id;

    /**
     * UID of the message.
     *
     * @var int
     */
    private $uid;

    /**
     * Count the attachments.
     *
     * @var int
     */
    private $countAttachment;

    /**
     * Disable/enable decode current incoming message.
     *
     * @var string
     */
    private $_decode;

    /**
     * Called when the class has a new instance made of it.
     *
     * @param resource $imapStream
     * @param int      $id
     * @param string   $decode
     *
     * @return IncomingMessage
     */
    public function __construct($imapStream, $id, $decode = self::DECODE)
    {
        $this->imapStream = $imapStream;
        if (is_array($id)) {
            $identifier = $id;
            if (isset($identifier['id'])) {
                $this->id = $identifier['id'];
                $this->uid = null;
            }
            if (isset($identifier['uid'])) {
                $this->uid = $identifier['uid'];
                $this->id = null;
            }
            unset($identifier);
        }
        if (is_int($id)) {
            $this->id = $id;
        }

        if (isset($decode)) {
            $this->_decode = $decode;
        }

        $this->init();
    }

    /**
     * Main process.
     *
     * @return void
     */
    protected function init()
    {
        $structure = $this->imapFetchstructure();
        $this->structure = $structure;
        if (isset($structure->parts)) {
            $countSection = count($structure->parts);
            $this->countAttachment = $countSection - 1;
        }
        $this->getCountSection();
        $this->getHeader();
        $this->getAttachments();
        $this->getBody();
        if ($this->_decode === self::DECODE) {
            $this->decode();
        }
    }

    /**
     * Get headers in the current message.
     *
     * Set
     * ```php
     * $this->header
     * $this->header->details
     * ```
     *
     * @return void
     */
    protected function getHeader()
    {
        $header = $this->imapFetchOverview();
        $this->header = $header[0];
        $this->header->details = $this->imapHeaderInfo();
    }

    /**
     * Returns current object.
     *
     * Set $this->debug
     *
     * @return void
     */
    public function debug()
    {
        $this->debug = $this;
    }

    /**
     * Get count section.
     *
     * We take $this->section and make a simple array from an array of arrays.
     * If getRecursiveSections($this->structure) set $this->section to NULL,
     * then we think that there is only one section in the letter.
     * We install $this->section[0] = [0],
     * and then we will take this into account in subsequent processing.
     * Namely here getSection() and $this->getSectionStructure()
     * or getSectionStructureFromIncomingStructure().
     * Because if the message id is correct and the structure is returned,
     * then there is exactly one section in the message.
     *
     * @return array sections
     */
    protected function getCountSection()
    {
        $this->getRecursiveSections($this->structure);
        $sections = array();
        if (!isset($this->section)) {
            $this->section[0] = array(0);
        }
        foreach ($this->section as $array) {
            foreach ($array as $section) {
                $sections[] = $section;
            }
        }
        $sections = array_unique($sections);
        sort($sections);
        $this->section = $sections;

        return $this->section;
    }

    /**
     * Bypasses the recursive parts current message.
     *
     * Counts sections based on $obj->parts.
     * And sets $this->section as an array of arrays or null.
     * Null if $obj->parts is not.
     *
     * @param object $obj
     * @param string $before
     *
     * @return void
     */
    protected function getRecursiveSections($obj, $before = null)
    {
        if (!isset($obj->parts)) {
            return;
        }
        $countParts = count($obj->parts);
        $out = array();
        $beforeSave = $before;
        foreach ($obj->parts as $key => $subObj) {
            if (!isset($beforeSave)) {
                $before = ($key + 1);
            } else {
                $before = $beforeSave.'.'.($key + 1);
            }
            $this->getRecursiveSections($subObj, $before);
            $out[] = (string) $before;
        }
        $this->section[] = $out;
    }

    /**
     * Gets all sections, or if parameter is specified sections by type.
     *
     * @param string $type
     *
     * @throws ImapClientException
     *
     * @return array
     */
    protected function getSections($type = null)
    {
        if (!$type) {
            return $this->section;
        }
        $types = null;
        switch ($type) {
            case self::SECTION_ATTACHMENTS:
                $types = TypeAttachments::get();
                break;
            case self::SECTION_BODY:
                $types = TypeBody::get();
                break;
            default:
                throw new ImapClientException('Section type not recognised/supported');
                break;
        }
        $sections = array();
        foreach ($this->section as $section) {
            $obj = $this->getSectionStructure($section);
            if (!isset($obj->subtype)) {
                continue;
            }
            if (in_array($obj->subtype, $types, false)) {
                $sections[] = $section;
            }
        }

        return $sections;
    }

    /**
     * Get attachments in the current message.
     *
     * Set
     * $this->attachments->name
     * $this->attachments->body
     * $this->attachments->info
     *
     * @return array
     */
    protected function getAttachments()
    {
        $attachments = array();
        foreach ($this->getSections(self::SECTION_ATTACHMENTS) as $section) {
            $obj = $this->getSection($section);
            $attachment = new IncomingMessageAttachment($obj);
            $objNew = new \stdClass();
            $objNew->name = $attachment->name;
            $objNew->body = $attachment->body;
            $objNew->info = $obj;
            $attachments[] = $objNew;
        }
        $this->attachments = $attachments;
    }

    /**
     * Get body current message.
     *
     * Set
     * $this->message->$subtype
     * $this->message->$subtype->charset
     * $this->message->text
     * $this->message->info[]
     * $this->message->types[]
     *
     * @return object
     */
    protected function getBody()
    {
        $objNew = new \stdClass();
        $i = 1;
        $subType = new SubtypeBody;
        foreach ($this->getSections(self::SECTION_BODY) as $section) {
            $obj = $this->getSection($section, array('class' => $subType));
            $subtype = strtolower($obj->__get('structure')->subtype);
            if (!isset($objNew->$subtype)) {
                $objNew->$subtype = $obj;
            } else {
                $subtype = $subtype.'_'.$i;
                $objNew->$subtype = $obj;
                $i++;
            }
            $objNew->info[] = $obj;
            $objNew->types[] = $subtype;
            /*
             * Set charset
             */
            foreach ($objNew->$subtype->__get('structure')->parameters as $parameter) {
                $attribute = strtolower($parameter->attribute);
                if ($attribute === 'charset') {
                    $value = strtolower($parameter->value);
                    /*
                     * Here must be array, but
                     */
                    //$objNew->$subtype->charset[] = $value;
                    $objNew->$subtype->charset = $value;
                }
            }
        }
        if (isset($objNew->plain)) {
            $objNew->text = $objNew->plain;
            $objNew->types[] = 'text';
        } else {
            $objNew->text = null;
        }
        $this->message = $objNew;
    }

    /**
     * Get a section message.
     *
     * Return object with 2 properties:
     * $obj->structure
     * $obj->body
     *
     * @param string     $section
     * @param array|null $options have one option $options['class']. It create object, which must be instance \SSilence\ImapClient\Section.
     *
     * @throws ImapClientException
     *
     * @return \SSilence\ImapClient\Section object
     */
    public function getSection($section, $options = null)
    {
        if (isset($options['class'])) {
            $sectionObj = new $options['class']();
            if ($sectionObj instanceof Section) {
            } else {
                throw new ImapClientException('Incoming class not instance \SSilence\ImapClient\Section');
            }
        } else {
            $sectionObj = new Section();
        }
        if ($section === 0) {
            /*
            If the message id is correct and the structure is returned,
            then there is exactly one section in the message.
            */
            $sectionObj->structure = $this->imapBodystruct(1);
            $sectionObj->body = $this->imapFetchbody(1);
        } else {
            $sectionObj->structure = $this->imapBodystruct($section);
            $sectionObj->body = $this->imapFetchbody($section);
        }

        return $sectionObj;
    }

    /**
     * Alias for getSectionStructureFromIncomingStructure();.
     *
     * @param string $section
     *
     * @return object|null
     */
    public function getSectionStructure($section)
    {
        return $this->getSectionStructureFromIncomingStructure($section);
    }

    /**
     * Get section structure from incoming structure.
     *
     * @param string $section
     *
     * @return object|null
     */
    protected function getSectionStructureFromIncomingStructure($section)
    {
        $pos = strpos($section, '.');
        if ($pos === false) {
            $section = (int) $section;
            if ($section === 0) {
                return $this->structure;
            }

            return $this->structure->parts[($section - 1)];
        }
        $sections = explode('.', $section);
        $count = count($sections);
        $outObject = null;
        foreach ($sections as $section) {
            $section = (int) $section;
            if (!isset($outObject)) {
                $outObject = $this->getObjectStructureFromParts($this->structure, ($section - 1));
            } else {
                $outObject = $this->getObjectStructureFromParts($outObject, ($section - 1));
            }
        }

        return $outObject;
    }

    /**
     * Get object structure from parts.
     *
     * @param object $inObject
     * @param int    $part
     *
     * @return object
     */
    protected function getObjectStructureFromParts($inObject, $part)
    {
        return $inObject->parts[$part];
    }

    /**
     * Get a specific section.
     *
     * @param string $section
     *
     * @return string
     */
    protected function imapFetchbody($section)
    {
        /*
     * Update note: We must add FT_PEEK to perserve the unread status of the email.
     * Documentation of this can see seen here: http://php.net/manual/en/function.imap-fetchbody.php under options
     */
        return imap_fetchbody($this->imapStream, $this->id, $section, FT_PEEK);
    }

    /**
     * Structure all messages.
     *
     * @return object
     */
    protected function imapFetchstructure()
    {
        return imap_fetchstructure($this->imapStream, $this->id);
    }

    /**
     * Structure specific section.
     *
     * @param string $section
     *
     * @return object
     */
    protected function imapBodystruct($section)
    {
        return imap_bodystruct($this->imapStream, $this->id, $section);
    }

    /**
     * Fetch a quick "Overview" on a message.
     *
     * @see http://php.net/manual/ru/function.imap-fetch-overview.php
     *
     * @throws ImapClientException
     *
     * @return object
     */
    protected function imapFetchOverview()
    {
        if (isset($this->id) && isset($this->uid)) {
            throw new ImapClientException('What to use id or uid?');
        }
        $sequence = null;
        $options = null;
        if (isset($this->id) && !isset($this->uid)) {
            $sequence = $this->id;
            $options = null;
        }
        if (!isset($this->id) && isset($this->uid)) {
            $sequence = $this->uid;
            $options = FT_UID;
        }

        return imap_fetch_overview($this->imapStream, $sequence, $options);
    }

    /**
     * Imap Header Info.
     *
     * Wrapper for http://php.net/manual/ru/function.imap-headerinfo.php
     *
     * @see http://php.net/manual/ru/function.imap-headerinfo.php
     *
     * @return object
     */
    protected function imapHeaderInfo()
    {
        return imap_headerinfo($this->imapStream, $this->id);
    }

    /**
     * Convert to utf8 if necessary.
     *
     * @param string $str utf8 encoded string
     *
     * @return string
     */
    protected function convertToUtf8($str)
    {
        if (mb_detect_encoding($str, 'UTF-8, ISO-8859-1, GBK') !== 'UTF-8') {
            $str = utf8_encode($str);
        }
        $str = iconv('UTF-8', 'UTF-8//IGNORE', $str);

        return $str;
    }

    /**
     * Wrapper for imap_mime_header_decode()
     * http://php.net/manual/ru/function.imap-mime-header-decode.php.
     *
     * @see http://php.net/manual/ru/function.imap-mime-header-decode.php
     *
     * @param string $string
     *
     * @return array
     */
    protected function imapMimeHeaderDecode($string)
    {
        return imap_mime_header_decode($string);
    }

    /**
     * Decodes and glues the title bar
     * http://php.net/manual/ru/function.imap-mime-header-decode.php.
     *
     * @see http://php.net/manual/ru/function.imap-mime-header-decode.php
     *
     * @param string $string
     *
     * @return string
     */
    protected function mimeHeaderDecode($string)
    {
        $cache = null;
        $array = $this->imapMimeHeaderDecode($string);
        foreach ($array as $object) {
            $cache .= $object->text;
        }

        return $cache;
    }

    /**
     * Decode incoming message.
     *
     * @return void
     */
    protected function decode()
    {
        $this->decodeHeader();
        $this->decodeBody();
        $this->decodeAttachments();
    }

    /**
     * Decode header.
     *
     * @return void
     */
    protected function decodeHeader()
    {
        if (isset($this->header->subject)) {
            $this->header->subject = $this->mimeHeaderDecode($this->header->subject);
        }
        if (isset($this->header->details->subject)) {
            $this->header->details->subject = $this->mimeHeaderDecode($this->header->details->subject);
        }
        if (isset($this->header->details->Subject)) {
            $this->header->details->Subject = $this->mimeHeaderDecode($this->header->details->Subject);
        }
        if (isset($this->header->from)) {
            $this->header->from = $this->mimeHeaderDecode($this->header->from);
        }
        if (isset($this->header->to)) {
            $this->header->to = $this->mimeHeaderDecode($this->header->to);
        }
    }

    /**
     * Decode attachments.
     *
     * @return void
     */
    protected function decodeAttachments()
    {
        foreach ($this->attachments as $key => $attachment) {
            /*
             * Decode body
             */
            switch ($attachment->info->structure->encoding) {
                case 3:
                    $this->attachments[$key]->body = imap_base64($attachment->body);
                    break;
                case 4:
                    $this->attachments[$key]->body = quoted_printable_decode($attachment->body);
                    break;
            }
            /*
             * Decode name
             */
            $this->attachments[$key]->name = $this->mimeHeaderDecode($attachment->name);
        }
    }

    /**
     * Decode body.
     *
     * @return void
     */
    protected function decodeBody()
    {
        foreach ($this->message->types as $typeMessage) {
            switch ($this->message->$typeMessage->structure->encoding) {
                case 3:
                    $this->message->$typeMessage->body = imap_base64($this->message->$typeMessage->body);
                    break;
                case 4:
                    $this->message->$typeMessage->body = imap_qprint($this->message->$typeMessage->body);
                    break;
            }
        }
    }

    /**
     * Info about this object.
     *
     * @return array
     */
    public function __debugInfo()
    {
        return array(
            'header' => $this->header,
            'message' => $this->message,
            'attachments' => $this->attachments,
            'section' => $this->section,
            'structure' => $this->structure,
            'debug' => $this->debug,
        );
    }
}

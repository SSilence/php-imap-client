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
 * Class for all incoming messages
 *
 * @package    SSilence\ImapClient
 * @copyright  Copyright (c) Tobias Zeising (http://www.aditu.de)
 * @authors    Tobias Zeising <tobias.zeising@aditu.de>, sergey144010
 */
class IncomingMessage
{

	/**
	 * Used to handle sections of the e-mail easier
	 */
	const SECTION_ATTACHMENTS = 1;

    /**
     * Used to handle sections of the e-mail easier
     */
	const SECTION_BODY = 2;

    /**
     * Do not use decode incoming message
     */
    const NOT_DECODE = 'not_decode';

    /**
     * Use decode incoming message
     */
    const DECODE = 'decode';

	/**
	 * Header of the message
     *
     * @var object
	 */
    public $header;

	/**
	 * The message
     *
     * @var object
	 */
    public $message;

	/**
	 * Attachments
     *
     * @var array
	 */
    public $attachments;

	/**
	 * Section of the message
     *
     * @var string|array
	 */
    public $section;

	/**
	 * Structure of the message
     *
     * @var object
	 */
    public $structure;

	/**
	 * Debug on or off
     *
     * @var object
	 */
    public $debug;

    /**
     * The imap string
     *
     * @var resource
     */
    private $imapStream;

	/**
	 * ID of the message
     *
     * @var integer
     */
    private $id;

	/**
	 * UID of the message
     *
     * @var integer
     */
    private $uid;

	/**
	 * Count the attachments
     *
     * @var integer
     */
    private $countAttachment;

    /**
     * Disable/enable decode current incoming message
     *
     * @var string
     */
    private $_decode;

    /**
     * Called when the class has a new instance made of it
     *
     * @param resource $imapStream
     * @param integer $id
     * @param string $decode
     * @return IncomingMessage
     */
    public function __construct($imapStream, $id, $decode = self::DECODE)
    {
        $this->imapStream = $imapStream;
        if(is_array($id)){
            $identifier = $id;
            if(isset($identifier['id'])){
                $this->id = $identifier['id'];
                $this->uid = null;
            };
            if(isset($identifier['uid'])){
                $this->uid = $identifier['uid'];
                $this->id = null;
            };
            unset($identifier);
        };
        if(is_int($id)){
            $this->id = $id;
        };

        if(isset($decode)){
            $this->_decode = $decode;
        };

        $this->init();
    }

    /**
     * Main process
     *
     * @return void
     */
    private function init()
    {
        $structure = $this->imapFetchstructure();
        $this->structure = $structure;
        if(isset($structure->parts)){
            $countSection = count($structure->parts);
            $this->countAttachment = $countSection-1;
        };
        $this->getCountSection();
        $this->getAttachments();
        $this->getBody();
        $this->getHeader();
        if($this->_decode == self::DECODE){
            $this->decode();
        };
    }

    /*
     * Get headers in the current message
     *
     * Set
     * $this->header
     * $this->header->details
     *
     * @return void
     */
    private function getHeader()
    {
        $header = $this->imapFetchOverview();
        $this->header = $header[0];
        $this->header->details = $this->imapHeaderInfo();
    }

    /**
     * Returns current object
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
     * Get count section
     *
     * Set $this->section
     *
     * @return array sections
     */
    private function getCountSection()
    {
        $this->getRecursiveSections($this->structure);
        $mas = explode(';',$this->section);
        $mas = array_unique($mas);
        foreach ($mas as $key=>$val) {
            if(empty($val)){
                unset($mas[$key]);
            };
        };
        foreach ($mas as $key => $section) {
            $obj = $this->getSection($section);
            if(empty($obj->body)){
                unset($mas[$key]);
            };
        };
        $this->section = $mas;
        return $this->section;
    }

    /**
     * Bypasses the recursive parts current message
     *
     * Set $this->section
     *
     * @param object $obj
     * @param integer $recursive
     * @return void
     */
    private function getRecursiveSections($obj, $recursive = 1)
    {
        $this->section .= $recursive.';';
        if(!isset($obj->parts)){
            return;
        };
        foreach($obj->parts as $key => $subObj){
            if($key != 0){
                $this->section .= $recursive.'.'.$key.';';
            };
            $this->getRecursiveSections($subObj, $recursive+1);
        };
    }

    /**
     * Gets all sections, or if parameter is specified sections by type
     *
     * @param string $type
     * @return array
     * @throws ImapClientException
     */
    private function getSections ($type = null)
    {
        if (!$type)
        {
            return $this->section;
        };
        $types = null;
        switch ($type)
        {
            case self::SECTION_ATTACHMENTS:
                $types = TypeAttachments::get();
                break;
            case self::SECTION_BODY:
                $types = TypeBody::get();
                break;
            default:
                throw new ImapClientException("Section type not recognised/supported");
                break;
        };
        $sections = [];
        foreach ($this->section as $section)
        {
            $obj = $this->getSection($section);
            if (!isset($obj->structure->subtype))
            {
                continue;
            };
            if (in_array($obj->structure->subtype, $types, false))
            {
                $sections[] = $section;
            };
        };
        return $sections;
    }

    /**
     * Get attachments in the current message
     *
     * Set
     * $this->attachments->name
     * $this->attachments->body
     * $this->attachments->info
     *
     * @return array
     */
    private function getAttachments()
    {
        $attachments = [];
        foreach ($this->getSections(self::SECTION_ATTACHMENTS) as $section)
        {
            $obj = $this->getSection($section);
            $attachment = new IncomingMessageAttachment($obj);
            $objNew = new \stdClass();
            $objNew->name = $attachment->name;
            $objNew->body = $attachment->body;
            $objNew->info = $obj;
            $attachments[] = $objNew;
        };
        $this->attachments = $attachments;
    }

    /**
     * Get body current message
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
    private function getBody()
    {
        $objNew = new \stdClass(); $i = 1;
        foreach ($this->getSections(self::SECTION_BODY) as $section)
        {
            $obj = $this->getSection($section, ['class'=>SubtypeBody::class]);
            $subtype = strtolower($obj->structure->subtype);
            if(!isset($objNew->$subtype)){
                $objNew->$subtype = $obj;
            }else{
                $subtype = $subtype.'_'.$i;
                $objNew->$subtype = $obj;
                $i++;
            };
            $objNew->info[] = $obj;
            $objNew->types[] = $subtype;
            /*
             * Set charset
             */
            foreach ($objNew->$subtype->structure->parameters as $parameter) {
                $attribute = strtolower($parameter->attribute);
                if($attribute == 'charset'){
                    $value = strtolower($parameter->value);
                    /*
                     * Here must be array, but
                     */
                    #$objNew->$subtype->charset[] = $value;
                    $objNew->$subtype->charset = $value;
                };
            };
        };
        if(isset($objNew->plain)){
            $objNew->text = $objNew->plain;
            $objNew->types[] = 'text';
        }else{
            $objNew->text = null;
        };
        $this->message = $objNew;
    }

    /**
     * Get a section message
     *
     * Return object with 2 properties:
     * $obj->structure
     * $obj->body
     *
     * @param string $section
     * @param array $options. Nave one option $options['class']. It create object, which must be instance \SSilence\ImapClient\Section.
     * @return \SSilence\ImapClient\Section object
     * @throws ImapClientException
     */
    public function getSection($section, $options = null)
    {
        if(isset($options['class'])){
            $sectionObj = new $options['class'];
            if($sectionObj instanceof Section){
            }else{
                throw new ImapClientException('Incoming class not instance \SSilence\ImapClient\Section');
            };
        }else{
            $sectionObj = new Section();
        };
        $sectionObj->structure = $this->imapBodystruct($section);
        $sectionObj->body = $this->imapFetchbody($section);
        return $sectionObj;
    }

    /**
     * Get a specific section
     *
     * @param string $section
     * @return string
     */
    private function imapFetchbody($section)
    {
        return imap_fetchbody($this->imapStream, $this->id, $section);
    }

    /**
     * Structure all messages
     *
     * @return object
     */
    private function imapFetchstructure()
    {
        return imap_fetchstructure($this->imapStream, $this->id);
    }

    /**
     * Structure specific section
     *
     * @param string $section
     * @return object
     */
    private function imapBodystruct($section)
    {
        return imap_bodystruct($this->imapStream, $this->id, $section);
    }

    /**
     * Fetch a quick "Overview" on a message
     *
     * @return object
     * @throws ImapClientException
     */
    private function imapFetchOverview()
    {
        if(isset($this->id) && isset($this->uid)){
            throw new ImapClientException('What to use id or uid?');
        };
        $sequence = null;
        $options = null;
        if(isset($this->id) && !isset($this->uid)){
            $sequence = $this->id;
            $options = null;
        };
        if(!isset($this->id) && isset($this->uid)){
            $sequence = $this->uid;
            $options = FT_UID;
        };
        return imap_fetch_overview($this->imapStream, $sequence, $options);
    }

    /*
     * Imap Header Info
     *
     * Wrapper for http://php.net/manual/ru/function.imap-headerinfo.php
     *
     * @return object
     */
    private function imapHeaderInfo()
    {
        return imap_headerinfo($this->imapStream, $this->id);
    }

    /**
     * Convert to utf8 if necessary.
     *
     * @param string $str utf8 encoded string
     * @return string
     */
    private function convertToUtf8($str) {
        if (mb_detect_encoding($str, "UTF-8, ISO-8859-1, GBK")!="UTF-8") {
            $str = utf8_encode($str);
        }
        $str = iconv('UTF-8', 'UTF-8//IGNORE', $str);
        return $str;
    }

    /*
     * Wrapper for imap_mime_header_decode()
     * http://php.net/manual/ru/function.imap-mime-header-decode.php
     *
     * @param string $string
     * @return array
     */
    private function imapMimeHeaderDecode($string)
    {
        return imap_mime_header_decode($string);
    }

    /*
     * Decodes and glues the title bar
     * http://php.net/manual/ru/function.imap-mime-header-decode.php
     *
     * @param string $string
     * @return string
     */
    private function mimeHeaderDecode($string)
    {
        $cache = null;
        $array = $this->imapMimeHeaderDecode($string);
        foreach ($array as $object) {
            $cache .= $object->text;
        };
        return $cache;
    }

    /*
     * Decode incoming message
     */
    private function decode()
    {
        $this->decodeHeader();
        $this->decodeBody();
        $this->decodeAttachments();
    }

    private function decodeHeader()
    {
        if(isset($this->header->subject)){
            $this->header->subject = $this->mimeHeaderDecode($this->header->subject);
        };
        if(isset($this->header->details->subject)){
            $this->header->details->subject = $this->mimeHeaderDecode($this->header->details->subject);
        };
        if(isset($this->header->details->Subject)){
            $this->header->details->Subject = $this->mimeHeaderDecode($this->header->details->Subject);
        };
        if(isset($this->header->from)){
            $this->header->from = $this->mimeHeaderDecode($this->header->from);
        };
        if(isset($this->header->to)){
            $this->header->to = $this->mimeHeaderDecode($this->header->to);
        };
    }

    private function decodeAttachments()
    {
        foreach ($this->attachments as $key => $attachment) {
            /*
             * Decode body
             */
            switch ($attachment->info->structure->encoding)
            {
                case 3:
                    $this->attachments[$key]->body = imap_base64($attachment->body);
                    break;
                case 4:
                    $this->attachments[$key]->body = quoted_printable_decode($attachment->body);
                    break;
            };
            /*
             * Decode name
             */
            $this->attachments[$key]->name = $this->mimeHeaderDecode($attachment->name);
        }
    }

    private function decodeBody()
    {
        foreach ($this->message->types as $typeMessage) {

            switch ($this->message->$typeMessage->structure->encoding)
            {
                case 3:
                    $this->message->$typeMessage->body = imap_base64($this->message->$typeMessage->body);
                    break;
                case 4:
                    $this->message->$typeMessage->body = imap_qprint($this->message->$typeMessage->body);
                    break;
            };

        }

    }

    public function __debugInfo()
    {
        return [
            'header' => $this->header,
            'message' => $this->message,
            'attachments' => $this->attachments,
            'section' => $this->section,
            'structure' => $this->structure,
            'debug' => $this->debug,
        ];
    }
}

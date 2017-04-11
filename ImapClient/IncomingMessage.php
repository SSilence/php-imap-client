<?php

namespace SSilence\ImapClient;

use SSilence\ImapClient\ImapClientException;
use SSilence\ImapClient\TypeAttachments;
use SSilence\ImapClient\TypeBody;

/**
 * Class for all imcoming messages
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

class IncomingMessage
{

	/**
	 * Used to handle sections of the e-mail easier
	 */
	const SECTION_ATTACHMENTS = 1;
	const SECTION_BODY = 2;

	/**
	 * Header of the message
	 */
    public $header;
	/**
	 * The message
	 */
    public $message;
	/**
	 * Attachment
	 */
    public $attachment;
	/**
	 * Section of the message
	 */
    public $section;
	/**
	 * Structure of the message
	 */
    public $structure;
	/**
	 * Debug on or off
	 */
    public $debug;

	/**
	 * The imap string
	 */
    private $imapStream;
	/**
	 * ID of the message
	 */
    private $id;
	/**
	 * UID of the message
	 */
    private $uid;
	/**
	 * Count the attachments
	 */
    private $countAttachment;

    private $_attachments;

	/**
	 * Called when the class has a new instance made of it
	 */
    public function __construct($imapStream, $id)
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
        $this->getAttachment();
        $this->getBody();
        $this->getHeader();
        $this->decode();
    }

    /*
     * Get headers in the current message
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
     * and
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
     * Set $this->section
     *
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
     * OOP way of getting attachments as objects
     *
     * @return array. IncomingMessageAttachment object.
     */
    public function getAttachments ()
    {
        if ($this->_attachments === null)
        {
            $this->_attachments = [];
            foreach ($this->attachment as $attachment)
            {
                $this->_attachments[] = new IncomingMessageAttachment($attachment);
            };
        };
	return $this->_attachments;
    }

    /**
     * Get attachments in the current message
     *
     * @return array
     */
    private function getAttachment()
    {
        $attachments = [];
        foreach ($this->getSections(self::SECTION_ATTACHMENTS) as $section)
        {
            $obj = $this->getSection($section);
            switch ($obj->structure->encoding)
            {
                case 3:
                    $obj->body = imap_base64($obj->body);
                    break;
                case 4:
                    $obj->body = quoted_printable_decode($obj->body);
                    break;
            };
            $attachments[] = $obj;
        };
        $this->attachment = $attachments;
    }

    /**
     * Get body current message
     *
     * @return object
     */
    private function getBody()
    {
        $objNew = new \stdClass();
        foreach ($this->getSections(self::SECTION_BODY) as $section)
        {
            $obj = $this->getSection($section);
            switch ($obj->structure->encoding)
            {
                case 3:
                    $obj->body = imap_base64($obj->body);
                    break;
                case 4:
                    $obj->body = imap_qprint($obj->body);
                    break;
            };

            $subtype = strtolower($obj->structure->subtype);
            $objNew->$subtype = $obj->body;
            $objNew->info[] = $obj;
        };
        $this->message = $objNew;
    }

    /**
     * Get a section message
     *
     * @return object \stdClass
     */
    public function getSection($section)
    {
        $stdClass = new \stdClass();
        $stdClass->structure = $this->imapBodystruct($section);
        $stdClass->body = $this->imapFetchbody($section);
        return $stdClass;
    }

    /**
     * Get a specific section
     *
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
        if(isset($this->header->subject)){
            $this->header->subject = $this->mimeHeaderDecode($this->header->subject);
        };
        if(isset($this->header->details->subject)){
            $this->header->details->subject = $this->mimeHeaderDecode($this->header->details->subject);
        };
        if(isset($this->header->details->Subject)){
            $this->header->details->Subject = $this->mimeHeaderDecode($this->header->details->Subject);
        };
    }
}

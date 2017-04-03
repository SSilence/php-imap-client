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
	 * Header of the message
	 */
    public $header;
    /**
     * Detail header of the message
     */
    public $headerDetail;
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
        $header = $this->imapFetchOverview();
        $this->header = $header[0];
        $this->headerDetail = $this->imapHeaderInfo();
        $structure = $this->imapFetchstructure();
        $this->structure = $structure;
        if(isset($structure->parts)){
            $countSection = count($structure->parts);
            $this->countAttachment = $countSection-1;
        };
        $this->getCountSection();
        $this->getAttachment();
        $this->getBody();
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
     * Get attachments in the current message
     *
     * @return array
     */
    private function getAttachment()
    {
        $types = new TypeAttachments();
        $types = $types->get();
        $attachments = [];
        foreach ($this->section as $section) {
            $obj = $this->getSection($section);
            if(!isset($obj->structure->subtype)){continue;};
            if(in_array($obj->structure->subtype, $types, false)){
                switch ($obj->structure->encoding) {
                    case 0:
                    case 1:
                        $obj->body = imap_8bit($obj->body);
                        break;
                    case 2:
                        $obj->body = imap_binary($obj->body);
                        break;
                    case 3:
                        $obj->body = imap_base64($obj->body);
                        break;
                    case 4:
                        $obj->body = quoted_printable_decode($obj->body);
                        break;
                };
                $attachments[] = $obj;
            };
        }
        $this->attachment = $attachments;
    }

    /**
     * Get body current message
     *
     * @return object
     */
    private function getBody()
    {
        $types = new TypeBody();
        $types = $types->get();
        $objNew = new \stdClass();
        foreach ($this->section as $section) {
            $obj = $this->getSection($section);
            if(!isset($obj->structure->subtype)){continue;};
            if(in_array($obj->structure->subtype, $types, false)){
                switch ($obj->structure->encoding) {
                    /*
                    case 0:
                    case 1:
                        $obj->body = imap_8bit($obj->body);
                        break;
                    case 2:
                        $obj->body = imap_binary($obj->body);
                        break;
                    */
                    case 3:
                        $obj->body = imap_base64($obj->body);
                        break;
                    case 4:
                        $obj->body = quoted_printable_decode($obj->body);
                        break;
                };

                $subtype = strtolower($obj->structure->subtype);
                $objNew->$subtype = $obj->body;
                $objNew->info[] = $obj;
            };
        }
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
}
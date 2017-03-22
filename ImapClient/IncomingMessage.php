<?php

namespace SSilence\ImapClient;

use SSilence\ImapClient\ImapClientException;
use SSilence\ImapClient\TypeAttachments;
use SSilence\ImapClient\TypeBody;

class IncomingMessage
{
    public $header;
    public $message;
    public $attachment;
    public $section;
    public $structure;
    public $debug;

    private $imapStream;
    private $id;
    private $uid;
    private $countAttachment;

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

    private function init()
    {
        $header = $this->imapFetchOverview();
        $this->header = $header[0];
        $structure = $this->imapFetchstructure();
        $this->structure = $structure;
        if(isset($structure->parts)){
            $countSection = count($structure->parts);
            $this->countAttachment = $countSection-1;
        };
        $this->getCountSection();
        $this->getAttachment();
        $this->getBody();
        #$this->debug();
    }

    public function debug()
    {
        $this->debug = $this;
    }

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

    /*
     *
     * @return
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

    /*
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
                #$obj->body = imap_base64($obj->body);
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

    /*
     * @return array
     */
    private function getBody()
    {
        $types = new TypeBody();
        $types = $types->get();

        #$messages = [];
        $objNew = new \stdClass();
        foreach ($this->section as $section) {
            $obj = $this->getSection($section);
            if(!isset($obj->structure->subtype)){continue;};
            if(in_array($obj->structure->subtype, $types, false)){

                #$obj->body = imap_base64($obj->body);

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

                $subtype = strtolower($obj->structure->subtype);
                $objNew->$subtype = $obj->body;
                $objNew->info[] = $obj;
                #$messages[] = $obj;
            };
        }
        $this->message = $objNew;
    }

    /*
     * Get section message
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

    /*
     * Get specific section
     */
    private function imapFetchbody($section)
    {
        return imap_fetchbody($this->imapStream, $this->id, $section);
    }

    /*
     * Structure all message
     */
    private function imapFetchstructure()
    {
        return imap_fetchstructure($this->imapStream, $this->id);
    }

    /*
     * Structure specific section
     */
    private function imapBodystruct($section)
    {
        return imap_bodystruct($this->imapStream, $this->id, $section);
    }

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

}
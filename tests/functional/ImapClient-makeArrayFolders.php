<?php

namespace program;

require_once "../../ImapClient/ImapClientException.php";
require_once "../../ImapClient/ImapConnect.php";
require_once "../../ImapClient/ImapClient.php";

use SSilence\ImapClient\ImapClientException;
use SSilence\ImapClient\ImapConnect;
use SSilence\ImapClient\ImapClient;

class ImapClientTest extends ImapClient
{
    public $arrayForTest;

    public function __construct(){}

    public function makeArrayFoldersTest(array $subFolders)
    {
        return $this->makeArrayFolders($subFolders);
    }

    public function getFoldersTest($separator = null, $type = 0)
    {
        #$folders = imap_list($this->imap, $this->mailbox, "*");
        if($type == 1){
            #return str_replace($this->mailbox, "", $folders);
        };
        if($type == 0){
            #$array = str_replace($this->mailbox, "", $folders);
            if(!isset($separator)){ $separator = '.'; };
            $outArray = [];
            foreach ($this->arrayForTest as $folders) {
                $subFolders = explode($separator, $folders);
                $countSubFolders = count($subFolders);
                if($countSubFolders > 1){
                    $arrMake = $this->makeArrayFolders($subFolders);
                    $kv = each($arrMake);
                    if(!isset($outArray[$kv['key']])){
                        $outArray[$kv['key']] = $kv['value'];
                    }else{
                        $outArray[$kv['key']] = array_merge($outArray[$kv['key']], $kv['value']);
                    };
                }else{
                    $outArray[$subFolders[0]] = [];
                };
            };
            return $outArray;
        };
        return null;
    }
}

$imap = new ImapClientTest();

$subFolders = ['Test1', 'Sub1', 'Sub2'];
var_dump($imap->makeArrayFoldersTest($subFolders));

$imap->arrayForTest = [
    'Trash',
    'Test1',
    'Test1.Sub1',
    'Test1.Sub1.Sub2',
    'Junk',
    'Drafts',
    'Sent',
    'INBOX',
];
var_dump($imap->getFoldersTest());

$imap->arrayForTest = [
    'Trash',
    'Test1',
    'Test1/Sub1',
    'Test1/Sub1/Sub2',
    'Junk',
    'Drafts',
    'Sent',
    'INBOX',
];
var_dump($imap->getFoldersTest('/'));
<?php
/**
 * Created by PhpStorm.
 * User: Sergey144010
 * Date: 25.04.2017
 * Time: 2:19
 */

namespace SSilence\ImapClient\Tests;


class TestMessage1 extends Message
{
    public function __construct()
    {
        $envelope["from"]= "joe@example.com";
        $envelope["to"]  = "foo@example.com";
        $envelope["cc"]  = "bar@example.com";

        $envelope["subject"]  = "Subject";

        $part1["type"] = TYPEMULTIPART;
        $part1["subtype"] = "mixed";

        $filename = "/tmp/imap.c.gz";
        $filename = 'TestFileName.rar';
        #$fp = fopen($filename, "r");
        #$contents = fread($fp, filesize($filename));
        #fclose($fp);

        $part2["type"] = TYPEAPPLICATION;
        $part2["encoding"] = ENCBINARY;
        $part2["subtype"] = "octet-stream";
        $part2["description"] = basename($filename);
        $part2["contents.data"] = '000dvsdvsdv000';

        $part3["type"] = TYPETEXT;
        $part3["subtype"] = "plain";
        $part3["description"] = "description3";
        $part3["contents.data"] = "contents.data3\n\n\n\t";

        $body[1] = $part1;
        $body[2] = $part2;
        $body[3] = $part3;

        $this->body = imap_mail_compose($envelope, $body);
        #$this->body = nl2br(imap_mail_compose($envelope, $body));
    }
}
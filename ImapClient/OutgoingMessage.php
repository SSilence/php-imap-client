<?php

namespace SSilence\ImapClient;


class OutgoingMessage
{
    /*
     * Send message via imap_mail
     *
     * @return vooid
     */
    public function send($properties)
    {
        /*
        if(!isset($property['to'])){$to=null;}else{$to=$property['to'];};
        if(!isset($property['subject'])){$subject=null;}else{$subject=$property['subject'];};
        if(!isset($property['message'])){$message=null;}else{$message=$property['message'];};
        if(!isset($property['additional_headers'])){$additional_headers=null;}else{$additional_headers=$property['additional_headers'];};
        if(!isset($property['cc'])){$cc=null;}else{$cc=$property['cc'];};
        if(!isset($property['bcc'])){$bcc=null;}else{$bcc=$property['bcc'];};
        if(!isset($property['rpath'])){$rpath=null;}else{$rpath=$property['rpath'];};
*/
        $allowedProperties = ['to', 'subject', 'message', 'additional_headers', 'cc', 'bcc', 'rpath'];
        $properties = Helper::preparingProperties($properties, $allowedProperties);
        imap_mail(
            $properties->to,
            $properties->subject,
            $properties->message,
            $properties->additional_headers,
            $properties->cc,
            $properties->bcc,
            $properties->rpath
        );
    }


}
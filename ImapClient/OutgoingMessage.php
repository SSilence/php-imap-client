<?php
/**
 * Copyright (C) 2016-2017  SSilence
 * For the full license, please see LICENSE.
 */

namespace SSilence\ImapClient;

/**
 * Class for all outgoing messages.
 *
 * Due to the fact that this class is a W.I.P, I have not added
 * comments as I assume they will be added later...
 *
 * @copyright  Copyright (c) Tobias Zeising (http://www.aditu.de)
 * @author     Tobias Zeising <tobias.zeising@aditu.de>, sergey144010
 */
class OutgoingMessage
{
    /**
     * Message To.
     *
     * @see http://php.net/manual/ru/function.imap-mail.php
     *
     * @var string
     */
    private $to;

    /**
     * Message Subject.
     *
     * @see http://php.net/manual/ru/function.imap-mail.php
     *
     * @var string
     */
    private $subject;

    /**
     * Message Message.
     *
     * @see http://php.net/manual/ru/function.imap-mail.php
     *
     * @var string
     */
    private $message;

    /**
     * Message additional_headers.
     *
     * @see http://php.net/manual/ru/function.imap-mail.php
     *
     * @var
     */
    private $additional_headers;

    /**
     * Message CC.
     *
     * @see http://php.net/manual/ru/function.imap-mail.php
     *
     * @var string
     */
    private $cc;

    /**
     * Message BCC.
     *
     * @see http://php.net/manual/ru/function.imap-mail.php
     *
     * @var
     */
    private $bcc;

    /**
     * Message rpath.
     *
     * @see http://php.net/manual/ru/function.imap-mail.php
     *
     * @var string
     */
    private $rpath;

    /**
     * For send() method.
     */
    private $properties;

    /**
     * For createMimeMessage() method.
     */
    private $envelope;

    /**
     * For createBody() method.
     *
     * @var string
     */
    private $body;

    /**
     * Send message via imap_mail.
     *
     * @return void
     */
    public function send()
    {
        $mimeMessage = $this->createMimeMessage();
        $this->message = $mimeMessage;
        $this->preparingSend();
        imap_mail(
            $this->properties->to,
            $this->properties->subject,
            $this->properties->message,
            $this->properties->additional_headers,
            $this->properties->cc,
            $this->properties->bcc,
            $this->properties->rpath
        );
    }

    /**
     * Preparing properties.
     *
     * @return void
     */
    protected function preparingSend()
    {
        $allowedProperties = array(
            'to', 'subject', 'message', 'additional_headers', 'cc', 'bcc', 'rpath',
        );
        $properties = array(
            'to' => $this->to,
            'subject' => $this->subject,
            'message' => $this->message,
            'additional_headers' => $this->additional_headers,
            'cc' => $this->cc,
            'bcc' => $this->bcc,
            'rpath' => $this->rpath,
        );
        $this->properties = Helper::preparingProperties($properties, $allowedProperties);
    }

    /**
     * Create Mime Message.
     *
     * @see http://php.net/manual/ru/function.imap-mail-compose.php
     *
     * @return string
     */
    public function createMimeMessage()
    {
        $this->createBody();

        $envelopeAllowedType = array(
            'remail', 'return_path', 'date', 'from', 'reply_to', 'in_reply_to',
            'subject', 'to', 'cc', 'bcc', 'message_id', 'custom_headers',
        );
        /* @var $envelope array */
        $envelope = Helper::preparingProperties($this->envelope, $envelopeAllowedType, Helper::OUT_ARRAY);
        $bodyAllowedType = array(
            'type', 'encoding', 'charset', 'type.parameters', 'subtype',
            'id', 'description', 'disposition.type', 'disposition', 'contents.data',
            'lines', 'bytes', 'md5',
        );
        /* @var $body array */
        foreach ($this->body as $key => $part) {
            $this->body[$key] = Helper::preparingProperties($part, $bodyAllowedType, Helper::OUT_ARRAY);
        }
        $body = $this->body;

        return imap_mail_compose($envelope, $body);
    }

    /**
     * Create body.
     *
     * @return void
     */
    public function createBody()
    {
        $this->envelope['date'] = '29.03.2017';
        $this->envelope['message_id'] = '81';

        $part1['type'] = TYPEMULTIPART;
        $part1['subtype'] = 'mixed';

        $part3['type'] = TYPETEXT;
        $part3['subtype'] = 'plain';
        $part3['description'] = 'description3';
        $part3['contents.data'] = "contents.data3\n\n\n\t";

        $body[1] = $part1;
        //$body[2] = $part2;
        $body[3] = $part3;

        $this->body = $body;
    }

    /**
     * Set attachment.
     */
    public function setAttachment()
    {
    }

    /**
     * Set From.
     *
     * @param string $from
     */
    public function setFrom($from)
    {
        $this->envelope['from'] = $from;
        $this->additional_headers = 'From: '.$from;
    }

    /**
     * Set To.
     *
     * @param string $to
     */
    public function setTo($to)
    {
        $this->to = $to;
        $this->envelope['to'] = $to;
    }

    /**
     * Set Subject.
     *
     * @param string $subject
     */
    public function setSubject($subject)
    {
        $this->subject = $subject;
        $this->envelope['subject'] = $subject;
    }

    /**
     * Set Message.
     *
     * @param string $message
     */
    public function setMessage($message)
    {
        $this->message = $message;
    }
}

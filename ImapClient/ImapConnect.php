<?php

namespace SSilence\ImapClient;

use SSilence\ImapClient\ImapClientException;

/*
 * Class for connecting to imap server
 *
 * Examples:
 *
 * 1. Direct mailbox setup
 * $connect = new ImapConnect();
 * $connect->connect('{server.imap:431/imap/ssl/novalidate-cert}INBOX', 'user', 'pass');
 * $imap = $connect->getImap();
 * $mailbox = $connect->getResponseMailbox();
 *
 * 2. Same as above
 * $connect = new ImapConnect();
 * $connect->setMailbox('{server.imap:431/imap/ssl/novalidate-cert}INBOX');
 * $connect->connect(null, 'user', 'pass');
 * $imap = $connect->getImap();
 * $mailbox = $connect->getResponseMailbox();
 *
 * 3. A more convenient way for IDE
 * $connect = new ImapConnect();
 * $connect->prepareFlags(ImapConnect::SERVICE_IMAP, ImapConnect::ENCRYPT_SSL, ImapConnect::NOVALIDATE_CERT);
 * $connect->prepareMailbox('server.imap', 431);
 * $connect->connect(null, 'user', 'pass');
 * $imap = $connect->getImap();
 * $mailbox = $connect->getResponseMailbox();
 *
 * 4. Same as above in 3 example
 * $connect = new ImapConnect();
 * $connect->prepareFlags([
 *     'service' => ImapConnect::SERVICE_IMAP,
 *     'encrypt' => ImapConnect::ENCRYPT_SSL,
 *     'validateCertificates' => ImapConnect::NOVALIDATE_CERT
 * ]);
 * $connect->prepareMailbox([
 *     'remote_system_name' => 'server.imap',
 *     'port' => 431
 * ]);
 * $connect->connect([
 *     'username' => 'user',
 *     'password' => 'pass'
 * ]);
 * $imap = $connect->getImap();
 * $mailbox = $connect->getResponseMailbox();
 *
 * To view the current mailbox string you can use everywhere getMailbox() method.
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
class ImapConnect
{
    const SERVICE_IMAP = 'imap';
    const SERVICE_POP3 = 'pop3';
    const SERVICE_NNTP = 'nntp';
    const ENCRYPT_SSL = 'ssl';
    const ENCRYPT_TLS = 'tls';
    const ENCRYPT_NOTLS = 'notls';
    const VALIDATE_CERT = 'validate';
    const NOVALIDATE_CERT = 'novalidate';
    const DEBUG = 'debug';
    const SECURE = 'secure';
    const NORSH = 'norsh';
    const READONLY = 'readonly';
    const ANONYMOUS = 'anonymous';


    public $imap;
    public $mailbox;
    public $flags;

    /*
     * The connection to the server
     *
     * Description of the method imap_open() to look at the link below
     * http://php.net/manual/ru/function.imap-open.php
     *
     * Use as follows:
     *
     * connect( null, 'username', 'password' )
     * or
     * connect([
     *     'username' => 'Name',
     *     'password' => 'Pass'
     * ])
     *
     * @param string|array $mailbox
     * @param string $username
     * @param string $password
     * @param int $options
     * @param int $n_retries
     * @param array $params
     * @return void|ImapClientException
     */
    public function connect($mailbox, $username = null, $password = null, $options = 0, $n_retries = 0, $params = [])
    {
        /*
         * If first parameter method is array
         */
        if(isset($mailbox) && is_array($mailbox)) {
            $config = $mailbox;
            if(isset($config['mailbox'])){
                $mailbox = $config['mailbox'];
            }else{
                $mailbox = null;
            };
            if(isset($config['username'])){
                $username = $config['username'];
            }else{
                $username = null;
            };
            if(isset($config['password'])){
                $password = $config['password'];
            }else{
                $password = null;
            };
            if(isset($config['options'])){
                $options = $config['options'];
            }else{
                $options = 0;
            };
            if(isset($config['n_retries'])){
                $n_retries = $config['n_retries'];
            }else{
                $n_retries = 0;
            };
            if(isset($config['params'])){
                $params = $config['params'];
            }else{
                $params = [];
            };
        };

        if (!function_exists('imap_open')) {
            throw new ImapClientException('Imap function not available');
        };
        if(!isset($mailbox) && isset($this->mailbox)){
            $mailbox = $this->mailbox;
        };
        if(empty($mailbox) || is_bool($mailbox)){
            throw new ImapClientException('Mailbox is not installed');
        };
        if(!is_string($mailbox)){
            throw new ImapClientException('Mailbox must be an string');
        };
        if(!is_string($username)){
            throw new ImapClientException('Username must be an string');
        };
        if(!is_string($password)){
            throw new ImapClientException('Password must be an string');
        };
        if(!is_int($options)){
            throw new ImapClientException('Options must be an integer');
        };
        if(!is_int($n_retries)){
            throw new ImapClientException('N_retries must be an integer');
        };
        if(isset($params) && !is_array($params)){
            throw new ImapClientException('Params must be an array');
        };

        /*
        $array = [$mailbox, $username , $password, $options, $n_retries, $params];
        foreach ($array as $val) {
            var_dump($val);
        };
        return;
        */

        if(empty($options) && empty($n_retries) && empty($params)){
            $this->imap = @imap_open($mailbox, $username , $password);
        }else{
            $this->imap = @imap_open($mailbox, $username , $password, $options, $n_retries, $params);
        };
        if ($this->imap === false) {
            throw new ImapClientException('Error connecting to '.$mailbox);
        };
    }

    /*
     * Set string mailbox
     *
     * @param string $mailbox
     * @return void
     */
    public function setMailbox($mailbox)
    {
        $this->mailbox = $mailbox;
    }

    /*
     * Get string mailbox
     */
    public function getMailbox()
    {
        return $this->mailbox;
    }

    /*
     * Get string response mailbox
     *
     * @return string
     */
    public function getResponseMailbox()
    {
        $imap_obj = imap_check($this->imap);
        return $imap_obj->Mailbox;
    }

    /*
     * Get resource imap
     */
    public function getImap()
    {
        return $this->imap;
    }

    /*
     * Get string flags
     */
    public function getFlags()
    {
        return $this->flags;
    }

    /*
     * Prepare Mailbox string
     *
     * Sets $this->mailbox to this type of
     * {server.imap:431/imap/ssl/novalidate-cert}INBOX
     *
     * Use as follows:
     *
     * prepareMailbox( null, $port = 431, null)
     * or
     * prepareMailbox([
     *     'port' => 431
     * ])
     *
     * @param string|array $remote_system_name
     * @param string $port
     * @param string $flags can use prepareFlags() method but not necessarily
     * @param string $mailbox_name
     * @return void
     */
    public function prepareMailbox($remote_system_name = null, $port = null, $flags = null, $mailbox_name = null)
    {
        /*
         * If first parameter method is array
         */
        if(isset($remote_system_name) && is_array($remote_system_name)){
            $config = $remote_system_name;
            if(isset($config['remote_system_name'])){
                $remote_system_name = $config['remote_system_name'];
            }else{
                $remote_system_name = null;
            };
            if(isset($config['port'])){
                $port = $config['port'];
            }else{
                $port = null;
            };
            if(isset($config['flags'])){
                $flags = $config['flags'];
            }else{
                $flags = null;
            };
            if(isset($config['mailbox_name'])){
                $mailbox_name = $config['mailbox_name'];
            }else{
                $mailbox_name = null;
            };
        };

        if(!isset($remote_system_name) && isset($this->mailbox)){
            $remote_system_name = $this->mailbox;
        };
        if(empty($remote_system_name)){
            throw new ImapClientException('Mailbox is not installed');
        };
        /*
        if(is_null($port) && is_null($flags) && is_null($mailbox_name)){
            $this->mailbox = $remote_system_name;
            return;
        };
        */
        if(isset($port)){
            $port = ':'.$port;
        };
        if(!isset($flags) && isset($this->flags)){
            $flags = $this->flags;
        };
        $this->mailbox = '{'.$remote_system_name.$port.$flags.'}'.$mailbox_name;
    }

    /*
     * Prepare Flags
     *
     * http://php.net/manual/ru/function.imap-open.php
     * Section - mailbox - Optional flags for names
     *
     * Use as follows:
     *
     * prepareFlags( null, null, null, ImapConnect::VALIDATE_CERT, null)
     * or
     * prepareFlags([
     *     'validateCertificates' => ImapConnect::VALIDATE_CERT
     * ])
     *
     * @param string|array $service use appropriate constant like ImapConnect::SERVICE_IMAP
     * @param string $encrypt use appropriate constant
     * @param string $validateCertificates use appropriate constant
     * @param string $secure use appropriate constant
     * @param string $norsh use appropriate constant
     * @param string $readonly use appropriate constant
     * @param string $anonymous use appropriate constant
     * @param string $debug use appropriate constant
     * @return string|null
     */
    public function prepareFlags(
        $service = null,
        $encrypt = null,
        $validateCertificates = null,
        $secure = null,
        $norsh = null,
        $readonly = null,
        $anonymous = null,
        $debug = null
    )
    {
        /*
         * If first parameter method is array
         */
        if(isset($service) && is_array($service)){

            $config = $service;

            if(isset($config['service'])){
                $service = $config['service'];
            }else{
                $service = null;
            };
            if(isset($config['encrypt'])){
                $encrypt = $config['encrypt'];
            }else{
                $encrypt = null;
            };
            if(isset($config['validateCertificates'])){
                $validateCertificates = $config['validateCertificates'];
            }else{
                $validateCertificates = null;
            };
            if(isset($config['secure'])){
                $secure = $config['secure'];
            }else{
                $secure = null;
            };
            if(isset($config['norsh'])){
                $norsh = $config['norsh'];
            }else{
                $norsh = null;
            };
            if(isset($config['readonly'])){
                $readonly = $config['readonly'];
            }else{
                $readonly = null;
            };
            if(isset($config['anonymous'])){
                $anonymous = $config['anonymous'];
            }else{
                $anonymous = null;
            };
            if(isset($config['debug'])){
                $debug = $config['debug'];
            }else{
                $debug = null;
            };
        };

        $flags = null;
        if(isset($service) && $service === self::SERVICE_IMAP){
            $flags .= '/imap';
        };
        if(isset($service) && $service === self::SERVICE_POP3){
            $flags .= '/pop3';
        };
        if(isset($service) && $service === self::SERVICE_NNTP){
            $flags .= '/nntp';
        };
        if(isset($encrypt) && $encrypt === self::ENCRYPT_NOTLS){
            $flags .= '/notls';
        };
        if(isset($encrypt) && $encrypt === self::ENCRYPT_SSL){
            $flags .= '/ssl';
        };
        if(isset($encrypt) && $encrypt === self::ENCRYPT_TLS){
            $flags .= '/tls';
        };
        if(isset($validateCertificates) && $validateCertificates === self::VALIDATE_CERT){
            $flags .= '/validate-cert';
        };
        if(isset($validateCertificates) && $validateCertificates === self::NOVALIDATE_CERT){
            $flags .= '/novalidate-cert';
        };
        if(isset($secure) && $secure === self::SECURE){
            $flags .= '/secure';
        };
        if(isset($norsh) && $norsh === self::NORSH){
            $flags .= '/norsh';
        };
        if(isset($readonly) && $readonly === self::READONLY){
            $flags .= '/readonly';
        };
        if(isset($anonymous) && $anonymous === self::ANONYMOUS){
            $flags .= '/anonymous';
        };
        if(isset($debug) && $debug === self::DEBUG){
            $flags .= '/debug';
        };
        $this->flags = $flags;
    }
}

<?php
namespace SSilence\ImapClient;

class AdapterForOutgoingMessage {
    private $config;
    private static $options;

    public function __construct(array $connectConfig) {
        $this->config = $connectConfig;
    }

    public static function setOptions(array $options) {
        self::$options = $options;
    }

    public function send() {
        throw new ImapClientException('Not implemented');
    }
}

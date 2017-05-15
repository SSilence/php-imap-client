<?php
/**
 * Copyright (C) 2016-2017  SSilence
 * For the full license, please see LICENSE.
 */

namespace SSilence\ImapClient;

/**
 * Class SubtypeBody.
 *
 * @copyright  Copyright (c) Tobias Zeising (http://www.aditu.de)
 * @author     Tobias Zeising <tobias.zeising@aditu.de>, sergey144010
 */
class SubtypeBody extends Section
{
    /**
     * Charset current section body.
     *
     * @var string
     */
    public $charset;
    
    /**
     * This is just a blank function to maybe fix travis..
     */
    public function travisIsABaby() {
      // Blank function
    }
}

<?php
/*
+-----------------------------------------------------------------------+
| Copyright (c) 2010, Sam Beam                                          |
| All rights reserved.                                                  |
|                                                                       |
| Redistribution and use in source and binary forms, with or without    |
| modification, are permitted provided that the following conditions    |
| are met:                                                              |
|                                                                       |
| o Redistributions of source code must retain the above copyright      |
|   notice, this list of conditions and the following disclaimer.       |
| o Redistributions in binary form must reproduce the above copyright   |
|   notice, this list of conditions and the following disclaimer in the |
|   documentation and/or other materials provided with the distribution.|
| o The names of the authors may not be used to endorse or promote      |
|   products derived from this software without specific prior written  |
|   permission.                                                         |
|                                                                       |
| THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS   |
| "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT     |
| LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR |
| A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT  |
| OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, |
| SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT      |
| LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, |
| DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY |
| THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT   |
| (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE |
| OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.  |
|                                                                       |
+-----------------------------------------------------------------------+
| Author: Sam Beam <sbeam@onsetcorps.net>                               |
+-----------------------------------------------------------------------+
 */
/**
* Nimble Mailer is a wrapper for the php mail() function
* Allowing html and text email templates to be bundled into one script
* also allowing multiple emails to be sent
*
* @package Nimble
* @uses Test::deliver_foo() will send the email for you public instance method foo in the subclass
* @uses Test::create_foo() will create the email for you public instance method foo in the subclass and return the object it --Note-- this does not send the email
* @todo queue support
*
* @copyright (c) 2010 Sam Beam
*
* Changelog 
*   Mar 17 2010 - sbeam
*       modified to be a non-static class that works with all php5+
*       and uses PEAR::Mail and Mail_mime for expediency
*/
require_once('Mail.php');
require_once('Mail/mime.php');

class NimbleMailer {

    const EMAIL_FROM_NAME = null;
    const EMAIL_BCC_RECIP = null;

    protected $from;
    protected $returns_to;
    protected $sender;
    protected $recipients;

    // how to send : mail, sendmail or smtp
    protected $mailmethod = 'mail';
    protected $mailparams = array();

    protected $attachments = array();

    function __construct() {
      $this->nimble = Nimble::getInstance();
      $this->view_path = $this->nimble->config['view_path'];
    }

    /**
     * overloader simplified from the original static NiceDog version, tho
     * now this seems to be an awkward way of adding an argument to a function
     *
     * call:
     *      deliver_*   - prepare and deliver a single mail
     *      create_*    - prepare mail and return body
     *      queue_*     - queue mail using Mail_Queue
     *
     * @param string $method
     * @param array $args 
     * @return mixed
     */
    function __call($method, $args) {
        $matches = array();
        if (preg_match('/^(deliver|create|queue)_(.+)$/', $method, $matches)) {

            if (!is_callable(array($this, $matches[2]))) {
                trigger_error("Method '{$matches[2]}' is not defined in " . get_class($this), E_USER_ERROR);
            }

            call_user_func_array(array($this, $matches[2]), $args);

            $this->load_templates($matches[2]);

            if ($matches[1] == 'deliver') {
                $this->envelope();
                return $this->send();
            }
            elseif ($matches[1] == 'create') {
                $this->envelope();
                return $this->headers_to_str() . $this->_mailbody;
            }
            elseif ($matches[1] == 'queue') {
                $this->envelope();
                return $this->queue();
            }
        }
        else {
            trigger_error("Invalid method '$method' called on " . get_class($this), E_USER_ERROR);
        }
    }


    /**
      * Renders the email templates and stores the data in the $this->_content class variable
      *
      * @param string $name         name of function we are in, ie the template type
      */
    private function load_templates($name) {
        $class = get_class($this);
        $view_class_folder = strtolower(Inflector::underscore($class));

        $tp_html = FileUtils::join($this->view_path, $view_class_folder, $name . '.php');
        if (file_exists($tp_html)) {
            $this->prep_template($tp_html, 'html');
        }

        $tp_txt = FileUtils::join($this->view_path, $view_class_folder, $name . '.txt');
        if (file_exists($tp_txt)) {
            $this->prep_template($tp_txt, 'txt');
        }

    }



    /**
      * Renders the email templates and stores the data in the $this->_content class variable
      * @param string $name file path + filename of template to call
      * @param string $type type of email template (html|text)
      */
    private function prep_template($name, $type) {
        $vars = get_object_vars($this);
        ob_start();
        if (file_exists($name)){
            if (count($vars)>0) {
                foreach($vars as $key => $value){
                    if($key == 'nimble') {continue;}
                        $$key = $value;
                }
            }
            require($name);

            if (!empty($mail_subject)) {
                $this->subject = $mail_subject;
            }

        }else if(empty($name)){
            return;
        } else {
            throw new NimbleException('View ['.$name.'] Not Found');
        }
        $this->_content[$type] = ob_get_clean();
    }

    /**
     * build array of headers based on class properties like $from, $returns_to, $sender
     *
     * sets $this->headers
     *
     * @return array
     */
    private function _build_headers() {
        $from_name = (self::EMAIL_FROM_NAME)? self::EMAIL_FROM_NAME : (isset($_SERVER['HTTP_HOST']))? $_SERVER['HTTP_HOST'] : 'your website';

        $this->from = preg_replace('/\(\);:<>,\\\"\s/', '', $this->from); // clean out all special chars.

        $this->headers = array('From' => $from_name . "<" . $this->from . ">", 
                               'X-Mailer' => 'php/'.get_class($this),
                               'X-Sender' => $this->from);

        if ($this->returns_to) {
            $this->headers['Return-path'] = $this->returns_to;
        }
        if ($this->sender) {
            $this->headers['Reply-to'] = $this->sender;
        }
        if (self::EMAIL_BCC_RECIP) {
            $this->headers['BCC'] = self::EMAIL_BCC_RECIP;
        }

        foreach ($this->headers as $k => $v) { // paranoia against header injection.
            $this->headers[$k] = preg_replace('/[\n\r]+/', ' ', $v);
        }
        return $this->headers;
    }


    /**
     * build headers and assemble body of message. If there is an HTML template or any
     * attachments, makes a multipart message using Mail_mime. Otherwise a plain text message.
     *
     * sets $this->_mailbody
     */
    protected function envelope() {
        $this->_build_headers();

        if (empty($this->_content['html']) and empty($this->attachments)) {
            $this->_mailbody = $this->_content['text'];
        }
        else {
            $mm = new Mail_mime($crlf);

            if (!empty($this->_content['html']))
                $mm->setHTMLBody($this->_content['html']);

            if (!empty($this->_content['text']))
                $mm->setTXTBody($this->_content['text']);

            foreach ($this->attachments as $file => $type) {
                $mm->addAttachment($file, $type);
            }

            $this->_mailbody = $mm->get();
            $this->headers = $mm->headers($this->headers);
        }
    }

    /**
     * create PEAR::mail object and drop
     */
    protected function send($name) {
        $mail =& Mail::factory($this->mailmethod, $this->mailparams);
        $mail->send($this->recipients, $this->headers, $this->_mailbody);
    }
    

    function queue() {  // TODO
    }

    /**
     * take n/v pairs in $this->headers(), make a string
     *
     * @return string
     */
    function headers_to_str() {
        $hdrs = '';
        foreach ($this->headers as $k => $v) {
            $hdrs .= "$k: $v\n";
        }
        $hdrs .= "\n";
        return $hdrs;
    }
}

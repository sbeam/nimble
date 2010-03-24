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

    protected $from;
    protected $returns_to;
    protected $sender;
    protected $recipients;
    public $subject = '';

    protected $EMAIL_FROM_NAME;
    protected $EMAIL_BCC_RECIP;

    // how to send : mail, sendmail or smtp
    protected $mailmethod = 'mail';
    protected $mailparams = array();

    protected $attachments = array();

    private $_tplvars = array();

    public $charset = 'iso-8859-1';


    function __construct() {
        $this->nimble = Nimble::getInstance();
        $this->view_path = $this->nimble->config['view_path'];

        if (defined('NIMBLE_IS_TESTING') && NIMBLE_IS_TESTING && defined('FAKEMAIL_PORT')) {
            $this->mailmethod = 'smtp';
            $this->mailparams['port'] = FAKEMAIL_PORT;
        }
    }


    /**
     * attach any given thing $v and make available to templates as var named by $k
     *
     * @param $k string
     * @param $v mixed
     */
    public function set_template_var($k, $v) {
        $this->_tplvars[$k] = $v;
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

        $file_root = FileUtils::join($this->view_path, $view_class_folder, $name);
        $tp_html = $file_root . '.php';
        if (file_exists($tp_html)) {
            $this->prep_template($tp_html, 'html');
        }

        $tp_txt =  $file_root . '.txt';
        if (file_exists($tp_txt)) {
            $this->prep_template($tp_txt, 'txt');
        }
        if (empty($this->_content))
            throw new NimbleException("No valid templates found at $file_root");

    }



    /**
      * Renders the email templates and stores the data in the $this->_content class variable
      * @param string $name file path + filename of template to call
      * @param string $type type of email template (html|text)
      */
    private function prep_template($name, $type) {
        ob_start();
        if (file_exists($name)) {

            foreach($this->_tplvars as $key => $value) {
                $$key = $value;
            }
            require($name);

            if (!empty($mail_subject)) {
                $this->subject = $mail_subject;
            }
        }
        elseif (empty($name)) {
            return;
        } 
        else {
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
        $from_name = ($this->EMAIL_FROM_NAME)? $this->EMAIL_FROM_NAME : ((isset($_SERVER['HTTP_HOST']))? $_SERVER['HTTP_HOST'] : '');

        $this->from = preg_replace('/\(\);:<>,\\\"\s/', '', $this->from); // clean out all special chars.

        if (strtolower($this->charset) == 'utf-8')
            $this->subject = "=?UTF-8?B?" . base64_encode($this->subject) . "?=";

        $this->headers = array('From' => "\"$from_name\" <{$this->from}>", 
                               'X-Mailer' => 'php/'.get_class($this),
                               'X-Sender' => $this->from,
                               'Subject' => $this->subject);

        if ($this->returns_to) {
            $this->headers['Return-path'] = $this->returns_to;
        }
        if ($this->sender) {
            $this->headers['Reply-to'] = $this->sender;
        }
        if ($this->EMAIL_BCC_RECIP) {
            $this->headers['BCC'] = $this->EMAIL_BCC_RECIP;
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
            if (empty($this->_content['txt'])) {
                trigger_error("No content found for the message", E_USER_WARNING);
                return;
            }

            $this->headers['Content-type'] = 'text/plain; charset=' . $this->charset;
            $this->_mailbody = $this->_content['txt'];
        }
        else {
            $mm = new Mail_mime($crlf);

            if (!empty($this->_content['html']))
                $mm->setHTMLBody($this->_content['html']);

            if (!empty($this->_content['txt']))
                $mm->setTXTBody($this->_content['txt']);

            foreach ($this->attachments as $file => $type) {
                $mm->addAttachment($file, $type);
            }

            $params = array();
            if ($this->charset != 'iso-8859-1') {
                $params = array('html_charset'  => $this->charset, 
                                'text_charset'  => $this->charset, 
                                'head_charset'  => $this->charset);
            }
            $this->_mailbody = $mm->get($params);
            $this->headers = $mm->headers($this->headers);
        }
    }

    /**
     * create PEAR::mail object and drop
     */
    protected function send() {
        $mail =& Mail::factory($this->mailmethod, $this->mailparams);
        PEAR::setErrorHandling(PEAR_ERROR_RETURN);
        return $mail->send($this->recipients, $this->headers, $this->_mailbody);
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

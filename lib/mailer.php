<?php
	require_once(dirname(__FILE__) . '/base.php');
	/**
		* Nimbler Mailer is a wrapper for the php mail() function
		* Allowing html and text email templates to be bundled into one script
		* also allowing multiple emails to be sent
		* ----NOTE!!----
		* This script is not suitable yet for mass mailing in since the mail() 
		* function opens a new SMTP socket for each call of mail() this script 
		* will break down and become slow around 5-10 emails depending on server load
		* @todo queue support
		* @package Nimble
		* @version php 5.3 only
		* @uses Test::deliver_foo() will send the email for you public instance method foo in the subclass
		* @uses Test::create_foo() will create the email for you public instance method foo in the subclass and return the object it --Note-- this does not send the email
		*/
	class NimbleMailer {
		//configs
		var $view_path = '';
		var $nimble = NULL;
		//settings
		var $recipiants = array();
		var $from = '';
		var $subject = '';
		var $recipiant = '';
		//optional settings
		var $time = '';
		var $headers = '';
		//internals
		var $_divider = '';
		var $_content = '';
		var $_prepaired_message = '';
		
		
		/**
			* This class has 3 required variables that need to be set
			* $from (string), $subject (string), $recipiant | $recipiants (string || array)
			* Options Variables
			* $time (time), $headers (string)
			*/
		public function __construct() {
			$this->nimble = Nimble::getInstance();
			$this->view_path = $this->nimble->config['view_path'];
			$this->class = get_called_class();
			$this->time = time();
			$this->_divider = '------=_' . (rand(10000000, 99999999999) * 2);
			$this->_content = array();
		}
		
		
		/**
			* @see __callStatic
			*/
		public function __call($method, $args) {
			self::__callStatic($method, $args);
		}
		
		/**
			* Magic method for catching static method calls
			* @param string $method
			* @param array $args 
			*/
		public static function __callStatic($method, $args) {
			$matches = array();
			$class = get_called_class();
			$klass = new $class();
			$class_folder = strtolower(Inflector::underscore($class));
			if(preg_match('/^(deliver|create|queue)_(.+)$/', $method, $matches)) {
				switch($matches[1]) {
					case 'deliver':
						$klass->load_method($matches[2], $args);
						//php template
						$klass->prep_template(FileUtils::join($klass->view_path, $class_folder, $matches[2] . '.php'), 'html');
						//text template
						$text_template = FileUtils::join($klass->view_path, $class_folder, $matches[2] . '.txt');
						if(file_exists($text_template)) {
							$klass->prep_template($text_template, 'text');
						}
						$klass->output_message();
						$klass->send_mail();
						return true;
					break;
					case 'create':
						$klass->load_method($matches[2], $args);
						//php template
						$klass->prep_template(FileUtils::join($klass->view_path, $class_folder, $matches[2] . '.php'), 'html');
						//text template
						$text_template = FileUtils::join($klass->view_path, $class_folder, $matches[2] . '.txt');
						if(file_exists($text_template)) {
							$klass->prep_template($text_template, 'text');
						}
						$klass->output_message();
						return $klass;
					break;
					case 'queue':
						//Not implimented yet
					break;
				}
			}
		}
		/**
			* Invokes the model method that defines the settings for this message
			* @param string $method
			* @param array $args
			*/
		private function load_method($method, $args) {
			call_user_func_array(array($this, $method), $args);
			if(!empty($this->recipiant)) {
				$this->recipiants = array($this->recipiant);
			}
			if(!is_array($this->recipiants) && is_string($this->recipiants)) {
				$this->recipiants = array($this->recipiants);
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
			}else if(empty($name)){
				return;
      } else {
          throw new NimbleException('View ['.$name.'] Not Found');
      }

			$this->_content[$type] = ob_get_clean();
		}
		
		
		/**
			* Renders a partial template
			* @param string $name name of partial to load and render
			*/
		public function render_partial($name) {
			$vars = get_object_vars($this);
			$partial = FileUtils::join($this->view_path, strtolower($this->class), $name);
			if(!file_exists($partial)) {
				$partial = $name;
			}
			ob_start();
			if (count($vars)>0) {
         foreach($vars as $key => $value){
						if($key == 'nimble') {continue;}
             $$key = $value;
         }
			}
			require($partial);
			return ob_get_clean();
			
			
		}
		
		/**
			* Creates the default email headers
			* @return string
			*/
		private function create_headers() {
				$date = date(DATE_RFC822, $this->time);
				$headers  = '';
				$headers  = "MIME-Version: 1.0\r\n";
				$headers .= "From: " . $this->from . "\n";
				$headers .= "Content-Type: multipart/alternative; boundary=\"" . $this->_divider . "\"; charset=ISO-8859-1\n" .
										"Content-Transfer-Encoding: binary\n" . 
										"X-Mailer: Thunderbird 2.0.0.22 (Macintosh/20090605)\n" .
										"User-Agent: 	Thunderbird 2.0.0.22 (Macintosh/20090605)\n" .
										"Date: $date";

				if(isset($this->headers) && !empty($this->headers)) $headers .= $this->headers;
				return $headers;
			}
			
			
			/**
				* Assembles the message body of the email
				*/
			public function output_message() {
				if (isset($this->_content['html']) && !empty($this->_content['html']))
				{
					$html_message = $this->_content['html'];

					$html_message =  "--" . $this->_divider . 
									"\nContent-Disposition: inline\n" .
									"Content-Transfer-Encoding: 8bit\n" . 
									"Content-Type: text/html\n" .
									"Content-length: " . strlen($html_message) . "\n\n" . $html_message . "\n";
				}
				if (isset($this->_content['text'])) 
				{
					$text_message = $this->_content['text'];

					$text_message = "This is a multi-part message in MIME format.\n\n" . "--" . $this->_divider . 
									"\nContent-Disposition: inline\n" .
									"Content-Transfer-Encoding: 8bit\n" .
									"Content-Type: text/plain\n" .
									"Content-length: " . strlen($text_message) . "\n\n". $text_message;
				}				


				$message = '';
				if(!empty($text_message)) $message .= $text_message;
				if(!empty($html_message)) $message .= "\n\n" . $html_message . "\n" . "--" . $this->_divider . "--";

				$this->_prepaired_message = $message;
			}
		
		/**
			* Sends the email
			*/
		private function send_mail() {
			foreach($this->recipiants as $to) {
				mail($to, $this->subject, $this->_prepaired_message, $this->create_headers());
			}
		}
		/**
			* Public version of send_email()
			* @see send_email()
			*/
		public function send() {
			$this->send_mail();
		}
		
	}

?>
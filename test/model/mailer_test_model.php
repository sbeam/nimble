<?php

	class MailerTestModel extends NimbleMailer {
		
		public function foo($string) {
			$this->recipiant = 'sdavis@stsci.edu';
			$this->subject = 'WHOA';
			$this->from = 'sdavis@stsci.edu';
			$this->string = $string;
		}
		
		
		public function bar($string) {
			$this->recipiants = 'sdavis@stsci.edu';
			$this->subject = 'WHOA2';
			$this->from = 'sdavis@stsci.edu';
			$this->string = $string;
		}
		
	}

?>
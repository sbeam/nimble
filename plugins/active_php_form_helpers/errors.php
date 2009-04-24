<?php


	function error_messages_for($class) {
		if(empty($class->errors)) {
			return;
		}
		$errors = $class->errors;
		$out = array(TagHelper::tag('ul', array('class' => 'errors')));
		foreach($errors as $error) {
			$key = array_keys($error);
			$key = $key[0];
			array_push($out, TagHelper::content_tag('li', $error[$key]));
		}
		array_push($out, TagHelper::close_tag('ul'));
	
	
		return join("\n", $out);
	}

	
	
	
?>
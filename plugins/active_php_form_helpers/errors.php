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
			array_push($out, TagHelper::content_tag('li', TagHelper::content_tag('p', $error[$key])));
		}
		array_push($out, TagHelper::close_tag('ul'));
	
		$message = TagHelper::content_tag('h2' , "Errors occured saving this record", array('class' => 'error_message'));
		return TagHelper::content_tag('div',  $message . join("\n", $out), array('class' => 'error_container'));
	}

	
	
	
?>
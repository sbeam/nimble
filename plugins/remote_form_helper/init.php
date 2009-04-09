<?php
	/**
	* Form Helper init.php  
	* @package Plugins
	* @requires prototype javascript library
	* @link http://www.prototypejs.org
	*/

	class RemoteFormHelper {
					
			/**
			* Instance Methods
			*/
			
			public function __construct() {
				(string) $this->out = '';
				(string) $this->id = '';
			}
			
			public static function RemoteFormFor($name, $url, $id='remote_form', $collection=array(), $options = array()) {
				$self = new self();
				return $self->__remoteFormFor($name, $url, $id='remote_form', $collection, $options);
			}
			
			public function __remoteFormFor($name, $url, $id='remote_form', $collection= array(), $options=array()) {
				$this->id = $id;
				$this->name = $name;
				$options = array_merge(array('id' => $id, 'method' => 'post', 'action' => $url, 
											 'onsubmit' => "new Ajax.Request('" . $url . "',{asynchronous:true, evalScripts:true, parameters:Form.serialize(this)}); return false;"));
				$this->out = FormTagHelper::tag('form', $options, true) . "\n";
				return $this;
			}
			
			public function __toString() {
				return $this->out;
			}
			
			public function text_field($id, $name, $options=array()) {
				$id = $this->id . '_' . $id;
				$name = $this->name . '[' . $name . ']';
				return FormTagHelper::text_field($id, $name, $options);
			}
			
			public function checkbox($id, $name, $options=array()){
				$id = $this->id . '_' . $id;
				$name = $this->name . '[' . $name . ']';
				return FormTagHelper::checkbox($id, $name, $options);
			}
			
			public function hidden_field($id, $name, $value, $options=array()){
				$id = $this->id . '_' . $id;
				$name = $this->name . '[' . $name . ']';
				return FormTagHelper::hidden_field($id, $name, $value, $options);
			}
			
			public function submit($value, $options=array()) {
				$name = $this->name . '[' . $value . ']';
				return FormTagHelper::submit($value, array_merge($options, array('name' => $name)));
			}
			public function image_submit($image, $options=array()) {
				$name = $this->name . '[' . $value . ']';
				return FormTagHelper::image_submit($value, array_merge($options, array('name' => $name)));
			}
			
			public function label($contents, $id, $options=array()) {
				$id = $this->id . '_' . $id;
				return FormTagHelper::label($contents, $id, $options);
			}
			
			public function textarea($id, $name, $value='', $options=array()) {
				$id = $this->id . '_' . $id;
				$name = $this->name . '[' . $name . ']';
				return FormTagHelper::textarea($id, $name, $value, $options);
			}
			
			public function end() {
				return FormTagHelper::close_tag('form');
			}
			
			
		}

?>
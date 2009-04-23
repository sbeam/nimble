<?php
	
	/**
	*  @package Support
	*  A Tag helping class
	*/
	class TagHelper {
		private static $BOOLEAN_ATTRS = array('disabled', 'readonly', 'multiple', 'checked'); 
		/**
		* Creates an element
		* @param string $name Tag name
		* @param array $options key => value pairs for tag attributes
		* @param boolean $open Leave the tag open ex. <test> || <test/>
		* @param boolean $escape escape attribtes like links etc.. 
		* note: if you need mixed escapeing manualy escape the attribute value with htmlspecialchars()
		* @return string
		*/
		public static function tag($name, $options=array(), $open=false, $escape=false) {
			$out = "<" . $name;
			 if(!empty($options)){
			 	$out .= ' ' . join(' ', self::tag_options($options, $escape));
			 }
			 $out .= $open ? ">" : " />";
			 return $out;
		}
		/**
		* Creates a content element ex. <div>foo</div>
		* @param string $name Tag name
		* @param string $content What goes inside the tag
		* @param array $options key => value pair tag attributes @see TagHelper::tag_options()
		* @return string
		*/
		public static function content_tag($name, $content, $options=array()) {
			return self::tag($name, $options, true) . $content . self::close_tag($name);
		}
		/**
		* Creates a cosing tag ex. </tag>
		* @param string $name tag name
		* @return string
		*/
		public static function close_tag($name) {
			return '</' . $name . '>';
		}
		/**
		* Create tag attributes
		* @param array $options key => value pairs for tag attributes
		* @param boolean $escape escape attribtes like links etc.. 
		* note: if you need mixed escapeing manualy escape the attribute value with htmlspecialchars()
		* @return string
		*/
		public static function tag_options($options, $escape=true) {
			if(isset($options) && !empty($options)){
				$attrs = array();
				if($escape) {
					foreach($options as $key => $value){
						if(in_array(self::$BOOLEAN_ATTRS)) {
							array_push($attrs, trim($key) . '="' . $value .'"');
						}else{
							array_push($attrs, trim($key) . '="' . htmlspecialchars($value) .'"');	
						}
					}
				}else{
					foreach($options as $key => $value){
						array_push($attrs, trim($key) . '="' . $value .'"');
					}
				}
				return $attrs;
			}
		}
	
	}
	/**
	*  @package Support
	*  A form tag helping class
	*/
	
	class FormTagHelper extends TagHelper {
		
		/**
		*  Creates and HTML label tag
		*  @param string $contents What goes between the label tag
		*  @param string $id The help id of the element that this label is attached
		*  @param array $options key => value pairs for tag attributes
		*/
		public static function label($contents, $id, $options=array()) {
			$options = array_merge($options, array('for' => $id));
			return self::content_tag('label', $contents, $options);
		}
		
		/**
		*  Creates and HTML text_field input tag
		*  @param string $id The help id of the element
		*  @param string $name of the element
		*  @param array $options key => value pairs for tag attributes
		*/
		public static function text_field($id, $name, $options=array()){
			$options = array_merge($options, array('id' => $id, 'name' => $name, 'type' => 'text'));
			return self::tag('input', $options);
		}
		
		/**
		*  Creates and HTML checkbox input tag
		*  @param string $id The help id of the element
		*  @param string $name of the element
		*  @param array $options key => value pairs for tag attributes
		*/
		public static function checkbox($id, $name, $options=array()){
			$options = array_merge($options, array('id' => $id, 'name' => $name, 'type' => 'checkbox', 'value' => '1'));
			return self::hidden_field($id='', $name, 0) . self::tag('input', $options);
		}
		
		/**
		*  Creates and HTML submit button
		*  @param string $id The help id of the element
		*  @param array $options key => value pairs for tag attributes
		*/
		public static function submit($name, $options=array()) {
			$options = array_merge($options, array('type' => 'submit', 'value' => $name));
			return self::tag('input', $options);
		}
		
		/**
		*  Creates and HTML image submit tag
		*  @param string $image url of the image. Best if used with an image helper
		*  @param array $options key => value pairs for tag attributes
		*/
		public static function image_submit($image, $options=array()) {
			$options = array_merge($options, array('src' => $image, 'type' => 'image'));
			return self::tag('input', $options);
		}
		
		/**
		*  Creates and HTML hidden input tag
		*  @param string $id The help id of the element
		*  @param array $options key => value pairs for tag attributes
		*/
		public static function hidden_field($id, $name, $value, $options=array()) {
			$options = array_merge($options, array('name' => $name, 'id' => $id, 'value' => $value, 'type' => 'hidden'));
			return self::tag('input', $options);
		}
		
		/**
		*  Creates and HTML textarea tag
		*  @param string $id The help id of the element
		*  @param string $name of the element
		*  @param string $value What goes in the textarea
		*  @param array $options key => value pairs for tag attributes
		*/
		public static function textarea($id, $name, $value, $options=array()) {
			$options = array_merge($options, array('name' => $name, 'id' => $id));
			return self::content_tag('textarea', $value, $options);
		}
		
		
		public static function select($id, $name, $content, $options = array()) {
			$options = array_merge($options, array('name' => $name, 'id' => $id));
			return self::content_tag('select', $content, $options);
		}
		
		public static function option($name, $value, $options = array()) {
			$options = array_merge($options, array('value' => $value));
			return self::content_tag('option', $name, $options);
		}
		
		
	}
	
	
	/**
	* Form Builder class to assist in building forms from objects
	* @package Support
	* @uses <?= $form = new Form(arrya('method' => 'POST', 'path' => url_for('MyController', 'create'), 'object' => new Task)); ?>
	* @uses <?= $form->text_field('title') ?>
	*/
	
	class Form {
	
		/**
		* Form
		* @param array $array array('method' => 'GET', 'path' => '/', 'object' => '{object or string}')
		*/
		public function __construct($array) {
			$defaults = array('method' => 'GET', 'path' => '/');
			$this->config = array_merge($defaults, $array);
			$this->obj = $this->config['object'];
		}
		
		
		/**
		*  Creates and HTML select tag
		*  @uses $form->select('project_id', collect(function($project){return array($project->id, $project->title);}, Project::find_all()));
		*  @see function collect
		*  @param string $name of element
		*  @param array $collection array of array's ex. array(array(0,'Bob'), array(1, 'Joe'))
		*  @param array $options key => value pairs for tag attributes
		*/
		public function select($name, $collection, $options=array()) {
			$value = $this->fetch_value($name);
			$option_a = array();
			foreach($collection as $option) {
				if($value == $option[0]) {
					array_push($option_a, FormTagHelper::option($option[1], $option[0], array('selected' => 'SELECTED')));
				}else{
					array_push($option_a, FormTagHelper::option($option[1], $option[0]));
				}
			}
			$content = join("\n", $option_a);
			return FormTagHelper::select($this->get_id($name), $this->get_name($name), $content, $options);
		}
		
		
		/**
		*  Creates and HTML label tag
		*  @param string $name of element
		*  @param array $options key => value pairs for tag attributes
		*/
		public function label($name, $options=array()) {
			return FormTagHelper::label(Inflector::humanize($name), $this->get_id($name), $options);
		}
		
		/**
		*  Creates and HTML text_field input tag
		*  @param string $name of the element
		*  @param array $options key => value pairs for tag attributes
		*/
		public function text_field($name, $options=array()){
			$options = array_merge($options, array('value' => $this->fetch_value($name)));
			return FormTagHelper::text_field($this->get_id($name), $this->get_name($name), $options);
		}
		
		/**
		*  Creates and HTML checkbox input tag
		*  @param string $name of the element
		*  @param array $options key => value pairs for tag attributes
		*/
		public function checkbox($name, $options=array()){
			$options = array_merge($options, array('value' => $this->fetch_value($name)));
			return FormTagHelper::checkbox($this->get_id($name), $this->get_name($name), $options);
		}
		
		/**
		*  Creates and HTML submit button
		*  @param array $options key => value pairs for tag attributes
		*/
		public function submit($name, $options=array()) {
			return FormTagHelper::submit($name, $options);
		}
		
		/**
		*  Creates and HTML image submit tag
		*  @param string $image url of the image. Best if used with an image helper
		*  @param array $options key => value pairs for tag attributes
		*/
		public function image_submit($image, $options=array()) {
			return FormTagHelper::image_submit($image, $options);
		}
		
		/**
		*  Creates and HTML hidden input tag
		*  @param array $options key => value pairs for tag attributes
		*/
		public function hidden_field($name, $options=array()) {
			return FormTagHelper::hidden_field($this->get_id($name), $this->get_name($name), $this->fetch_value($name), $options);
		}
		
		/**
		*  Creates and HTML textarea tag
		*  @param string $name of the element
		*  @param array $options key => value pairs for tag attributes
		*/
		public function textarea($name, $options=array()) {
			return FormTagHelper::textarea($this->get_id($name), $this->get_name($name), $this->fetch_value($name), $options);
		}
		
		private function get_id($name) {
			return strtolower(get_class($this->obj) . '_' . $name);
		}
		
		private function get_name($name) {
			return strtolower(get_class($this->obj) . '[' . $name . ']');
		}
		
		
		private function fetch_value($name) {
			if(isset($this->obj) && !is_string($this->obj) && $this->obj->is_set($name)) {
				return $this->obj->$name;
			}else{
				return '';
			}
		}
		/**
		* Close form tag
		*/
		public function end() {
			return TagHelper::close_tag('form');
		}
		
		private function get_form_name() {
			if(isset($this->obj) && !empty($this->obj) && !is_string($this->obj)) {
				return strtolower(get_class($this->obj));
			}else{
				return $this->obj;
			}
		}
		
		
		public function __toString() {
			return TagHelper::tag('form', array('name' => strtolower($this->get_form_name()), 'method' => $this->config['method'], 'action' => $this->config['path']));
		}
		
		
	}
	
	
	
	


?>
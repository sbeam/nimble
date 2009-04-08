<?php

	/**
	*  @package Support
	*  A form tag helping class
	*/
	
	class FormTagHelper extends Taghelper {
		
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
		*  Creates and HTML input text_field tag
		*  @param string $id The help id of the element
		*  @param string $name of the element
		*  @param array $options key => value pairs for tag attributes
		*/
		public static function text_field($id, $name, $options=array()){
			$options = array_merge($options, array('id' => $id, 'name' => $name, 'type' => 'text'));
			return self::tag('input', $options);
		}
		
		/**
		*  Creates and HTML input checkbox tag
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
		
		
	}
	
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

?>
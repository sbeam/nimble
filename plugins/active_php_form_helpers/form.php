<?php

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
			$options = $this->has_errors($name, $options);
			$value = $this->fetch_value($name);
			$option_a = array();
			if(empty($value)) {
				array_push($option_a, FormTagHelper::option('-- Select One --', ''));
			}
			
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
			$options = $this->has_errors($name, $options);
			$options = array_merge(array('value' => $this->fetch_value($name)), $options);
			return FormTagHelper::text_field($this->get_id($name), $this->get_name($name), $options);
		}
    /**
		*  Creates and HTML file_field input tag
		*  @param string $name of the element
		*  @param array $options key => value pairs for tag attributes
		*/
		public function file_field($name, $options=array()){
			$options = $this->has_errors($name, $options);
			return FormTagHelper::file_field($this->get_id($name), $this->get_name($name), $options);
		}
    /**
		*  Creates and HTML password input tag
		*  @param string $name of the element
		*  @param array $options key => value pairs for tag attributes
		*/
		public function password($name, $options=array()){
			$options = $this->has_errors($name, $options);
			return FormTagHelper::password($this->get_id($name), $this->get_name($name), $options);
		}
		
		/**
		*  Creates and HTML checkbox input tag
		*  @param string $name of the element
		*  @param array $options key => value pairs for tag attributes
		*/
		public function checkbox($name, $options=array()){
			$options = $this->has_errors($name, $options);
			$options = array_merge($options, array('value' => $this->fetch_value($name)));
			return FormTagHelper::checkbox($this->get_id($name), $this->get_name($name), $options);
		}
		
		/**
		*  Creates and HTML submit button
		*  @param array $options key => value pairs for tag attributes
		*/
		public function submit($name, $options=array()) {
			$options = $this->has_errors($name, $options);
			return FormTagHelper::submit($name, $options);
		}
		
		/**
		*  Creates and HTML image submit tag
		*  @param string $image url of the image. Best if used with an image helper
		*  @param array $options key => value pairs for tag attributes
		*/
		public function image_submit($image, $options=array()) {
			$options = $this->has_errors($name, $options);
			return FormTagHelper::image_submit($image, $options);
		}
		
		/**
		*  Creates and HTML hidden input tag
		*  @param array $options key => value pairs for tag attributes
		*/
		public function hidden_field($name, $options=array()) {
			$options = $this->has_errors($name, $options);
			return FormTagHelper::hidden_field($this->get_id($name), $this->get_name($name), $this->fetch_value($name), $options);
		}
		
		/**
		*  Creates and HTML textarea tag
		*  @param string $name of the element
		*  @param array $options key => value pairs for tag attributes
		*/
		public function textarea($name, $options=array()) {
			$options = $this->has_errors($name, $options);
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
		
		
		private function has_errors($name, $options) {
			if(isset($this->obj) && !empty($this->obj) && !is_string($this->obj)) {
				//if this has been processed - skip the processing
				if(!isset($this->errors)) {
					$this->errors = array();
					foreach($this->obj->errors as $error) {
						foreach($error as $col => $value) {
							$this->errors[$col] = $value;
						}
					}
				}

				if(in_array($name, array_keys($this->errors))) {
					if(isset($options['class'])) {
						$options['class'] = $options['class'] . ' ' . 'fieldWithErrors ';
					}else{
						$options['class'] = 'fieldWithErrors ';
					}
				}
			}
		
		
			return $options;
		}
		
		
		private function get_form_name() {
			if(isset($this->obj) && !empty($this->obj) && !is_string($this->obj)) {
				return strtolower(get_class($this->obj));
			}else{
				return $this->obj;
			}
		}
		
		
		public function __toString() {
    
      if(strtolower($this->config['method']) == 'put' || strtolower($this->config['method']) == 'delete') {
        $method = 'POST';
        $extra = FormTagHelper::hidden_field('_method', '_method', $this->config['method']);
      }else{
        $method = $this->config['method'];
      }
    
			$return = TagHelper::tag('form', array('name' => strtolower($this->get_form_name()), 'method' => $method, 'action' => $this->config['path']));
      if(isset($extra)) {
        $return = $return . $extra;
      }
      
      return $return;
		}
		
		
	}
	
?>
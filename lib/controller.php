<?php
/* create an alias so its backward compatable */

class C extends Controller{}

class Controller {
    var $http_format;
		var $layout = true;
    var $layout_tamplate = 'views/layout.php';
    var $headers;
		var $filters = array('before' => array(), 'after' => array());
		/* set the incoming http format default is html */
		public function __construct(){
			$this->http_format = 'html';
		}
		
		/* Method that invokes the before filters 
			 Before Filters run before the action in the controller
		*/
		public function run_before_filters($method) {
			$filters = $this->filters['before'];
			$this->process_filters($method, $filters);
		}
		/* adds a before filter call this in the childs construct method */
		public function add_before_filter($method, $options = array()) {
			$this->filters['before'][$method] = $options;
		}
		/* adds an after filter call this in the childs construct method */		
		public function add_after_filter($method, $options = array()) {
			$this->filters['after'][$method] = $options;
		}
		
		/* Method that invokes the after filters
			 After Filters run after the action has been called
		*/
		public function run_after_filters($method) {
			$filters = $this->filters['after'];
			$this->process_filters($method, $filters);
		}
		
		/* This method processes the filters and calls the methods 
			 Note the methods being called should be public protected methods in the child class
		*/
		private function process_filters($method, $filters) {
			foreach($filters as $fmethod=>$options) {
				/* process the only methods */
				if(array_key_exists('only', $options)) {
					if(in_array($method, $options['only'])) {
						call_user_func(array($this, $fmethod));
					}
					unset($options['only']);
				}
				/* process the except methods */
				if(array_key_exists('except', $options)) {
					if(!in_array($method, $options['except'])) {
						call_user_func(array($this, $fmethod));
					}
					unset($options['except']);
				}
				/* for any methods that are not in the sub array except or only */
				foreach($options as $options_method) {
					call_user_func(array($this, $options_method));
				}
			}
		}
		
		/* returns the html format */
		public function html_format() {
			return $this->html_format;
		}
		
    
    /* Render function return php rendered in a variable */
    public function render($file)
    {
        if ($this->layout==false){
            return $this->open_template($file); 
        } else {
           $this->content = $this->open_template($file); 
           return $this->open_template($this->layout_tamplate); 
        }
    }

    /* Render partial function */
    public function render_partial($file)
    {
        return $this->open_template($file);
    }
    
    /* Open template to render and return php rendered in a variable using ob_start/ob_end_clean */
    private function open_template($name)
    {
        $vars = get_object_vars($this);
        ob_start();
        if (file_exists($name)){
            if (count($vars)>0)
                foreach($vars as $key => $value){
                    $$key = $value;
                }        
            require($name);
        } else {
            throw new NiceDogException('View ['.$name.'] Not Found');
        }
        $out = ob_get_contents();
        ob_end_clean();
        return $out;
    }   
    
    /* Add information in header */
    public function header($text){
        $this->headers[] = $text;
    }    
    
    /* 
       Redirect page to annother place using header, 
       $now indicates that dispacther will not wait all process
    */
    public function redirect($url,$now=false)
    {
        if(!$now)
        $this->header("Location: {$url}");
        else header("Location: {$url}");
    }

		public function redirect_to($url) {
			$this->redirect($url, true);
		}
    
}

?>

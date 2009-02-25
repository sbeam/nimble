<?php
/* create an alias so its backward compatable */

class C extends Controller{}

class Controller {
    var $http_format;
		var $layout = true;
    var $layout_tamplate = 'views/layout.php';
    var $headers;

		/* set the incoming http format default is html */
		public function __construct(){
			$this->http_format = 'html';
		}
		
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

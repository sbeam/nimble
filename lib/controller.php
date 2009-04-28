<?php
	require_once(dirname(__FILE__) . '/exception.php');
	/**
	* An alias to Controller for backwards compatibility with Nice Dog.
	* @package Nimble
	* @see class Controller
	*/
class C extends Controller {}

	/**
 	* Controller handles user interaction with the site.
	* An alias to Controller for backwards compatibility with Nice Dog.
	* @package Nimble
	*/
class Controller {
	var $nimble;
    var $format;
    var $layout = true;
    var $layout_template;
    var $headers;
    var $filters = array('before' => array(), 'after' => array());
	var $has_rendered = false;

    /**
     * The expected output format for this controller.
     * @var string
     */
    var $http_format = 'html';
	/**
	*
	* @return instance of the Nimble class
	*/
	public function nimble() {
		return Nimble::getInstance();;
	}
	/**
	*
	* @return path to the application.php template
	*/
	public function set_layout_template() {
		$this->layout_template = $this->nimble()->config['default_layout'];
	}
	
	
    /**
     * Load a plugin for this controller and its rendered view.
     * @param string,... $plugins The plugins to load.
     */
    public function load_plugins() {
        $args = func_get_args();
        if(count($args) == 0) { return false; }
        Nimble::require_plugins($args);
    }

    /**
     * Run filters before the controller's action is invoked.
     * @param string $method The controller action that is being invoked.
     */
    public function run_before_filters($method) {
        $filters = $this->filters['before'];
        $this->process_filters($method, $filters);
    }

    /**
     * Add a before filter.
     * @param string $method The controller action to which this filter is attached.
     * @param array $options The options for this filter.
     */
    public function add_before_filter($method, $options = array()) {
        $this->filters['before'][$method] = $options;
    }

    /**
     * Add an after filter.
     * @param string $method The controller action to which this filter is attached.
     * @param array $options The options for this filter.
     */
    public function add_after_filter($method, $options = array()) {
        $this->filters['after'][$method] = $options;
    }

    /**
     * Run filters after the controller's action is invoked.
     * @param string $method The controller action that is being invoked.
     */
    public function run_after_filters($method) {
        $filters = $this->filters['after'];
        $this->process_filters($method, $filters);
    }

    /**
     * Process all filters for this controller.
     * @param string $method The controller method that's being called.
     * @param array $filters The filters to execute.
     */
    private function process_filters($method, $filters) {
        foreach($filters as $fmethod=>$options) {
            // process the only methods
            if(array_key_exists('only', $options)) {
                if(in_array($method, $options['only'])) {
                    call_user_func(array($this, $fmethod));
                }
            }

            // process the except methods
            if(array_key_exists('except', $options)) {
                if(!in_array($method, $options['except'])) {
                    call_user_func(array($this, $fmethod));
                }
            }

            // process the method if its global
            if(!array_key_exists('except', $options) && !array_key_exists('only', $options)) {
                call_user_func(array($this, $fmethod));
            }
        }
    }

    /**
     * Return the current format.
     * @return string The current format.
     */
    public function format() { return $this->format; }

    /**
     * Include a PHP file, inject the controller's properties into that file, and echo the output.
     * If $this->layout == false, will act the same as Controller::render_partial.
     * @param string $file The view file to render, relative to the base of the application.
     */
    public function render($file)
    {
		if($this->has_rendered){
			throw new NimbleException('Double Render Error: Your may only render once per action');
		}
	
		$this->has_rendered = true;
        if ($this->layout==false){
            echo $this->open_template(FileUtils::join(Nimble::getInstance()->config['view_path'], $file)); 
        } else {
           $this->content = $this->open_template(FileUtils::join(Nimble::getInstance()->config['view_path'], $file)); 
		   echo $this->open_template($this->layout_template); 
        }
    }

    /**
     * Include a PHP file, inject the controller's properties into that file, and return the output.
     * @param string $file The view file to render, relative to the base of the application.
     * @return string The rendered view file.
     */
    public function render_partial($file)
    {
        return $this->open_template(FileUtils::join(Nimble::getInstance()->config['view_path'], $file));
    }

    /**
     * Open a view template file, inject the controller's properties into that file, and execute the file, capturing and returning the output.
     * @param string $name The view file to render, relative to the base of the application.
     * @return string The rendered view file.
     */
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
		}else if(empty($name)){
			return;
        } else {
            throw new NimbleException('View ['.$name.'] Not Found');
        }
        $out = ob_get_contents();
        ob_end_clean();
        return $out;
    }

    /**
     * Add an HTTP header to be included in the output.
     * @param string $text The header to add.
     */
    public function header($text){ $this->headers[] = $text; }

    /**
     * Redirect to another URL.
     * @param string $url The URL to redirect to.
     * @param boolean $now If true, redirect immediately
     */
    public function redirect($url,$now=false)
    {
        if(!$now) {
            $this->header("Location: {$url}");
        }else{
            header("Location: {$url}");
            exit();
        }
    }

    /**
     * Redirect to another URL immediately.
     * @param string $url The URL to redirect to.
     */
    public function redirect_to($url)
    {
        $this->redirect($url, true);
    }
}

?>

<?php
require_once(dirname(__FILE__) . '/exception.php');

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
    var $headers = array(array('Content-Type: text/html', 200));
    var $filters = array('before' => array(), 'after' => array());
    var $has_rendered = false;
    var $template = '';
    var $rendered_partials = array();
    var $default_template_ext = 'php';
    var $respond_to_formats = array();
    var $default_format = 'html';
    var $headers_by_format = array('xml' => array('Content-Type: text/xml', 200));

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
        return Nimble::getInstance();
    }
    /**
     * @param string path to template you want to use as layout
     * @return string path to the application.php template
     */
    public function set_layout_template($template ='') {

        if (empty($template)) {
            if (!empty($this->layout_template))
                $template = $this->layout_template;
            else 
                $template = $this->nimble()->config['default_layout'];
        }
            
        if (file_exists($template)) {
            $this->layout_template = $template;
        } else {
            $template = FileUtils::join(Nimble::getInstance()->config['view_path'], 'layout', $template .'.php');
            if (file_exists($template)) {
                $this->layout_template = $template;
            }
            else {
                throw new NimbleException("Could not find layout template '$template'");
            }
        }

        return $this->layout_template;
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
        $this->run_filters('before', $method);
    }

    /**
     * Run filters after the controller's action is invoked.
     * @param string $method The controller action that is being invoked.
     */
    public function run_after_filters($method) {
        $this->run_filters('after', $method);
    }

    /**
     * Runs filters given the type and method to match
     * @param string $type (before | after)
     * @param string $method The method the controller if trying to invoke
     */
    private function run_filters($type, $method) {
        $_methods = get_class_methods($this);
        foreach($_methods as $_method) {
            $matches = array();
            $regex = "/^{$type}_filter($|_(for|except)_([0-9a-z_]+)$)/";
            if(preg_match($regex, $_method, $matches)) {
                if(isset($matches[2])) {
                    $hash = array_flip(explode('_and_', $matches[3]));
                    switch($matches[2]) {
                    case 'for':
                        if(isset($hash[$method])) {
                            # Nimble::log("run_filters() -> $_method()", NIMBLE_LOG_DEBUG);
                            call_user_func(array($this, $_method));
                        }
                        break;
                    case 'except':
                        if(!isset($hash[$method])) {
                            # Nimble::log("run_filters() -> $_method()", NIMBLE_LOG_DEBUG);
                            call_user_func(array($this, $_method));
                        }
                        break;
                    default:
                        return;
                        break;
                    }
                }else{
                    # Nimble::log("run_filters() -> $_method()", NIMBLE_LOG_DEBUG);
                    call_user_func(array($this, $_method));
                }
            }
        }
    }

    /**
     * Return the current format.
     * @return string The current format.
     */
    public function format()
    { 
        return $this->format; 
    }

    /**
     * Include a PHP file, inject the controller's properties into that file, and echo the output.
     * If $this->layout == false, will act the same as Controller::render_partial.
     * @param string $file The view file to render, relative to the base of the application.
     */
    public function render($file) {
        if($this->has_rendered){
            throw new NimbleException('Double Render Error: Your may only render once per action');
        }
        $this->has_rendered = true;

        if ($file === null) return;

        if (!preg_match('/\.[a-zA-Z0-9]+$/', $file)) { // no haz extension?
            $format = $this->format();
            $ext = ($format && $format != $this->default_format && in_array($format, $this->respond_to_formats))? $format : $this->default_template_ext;
            $file .= ".$ext";
        }

        $this->template = FileUtils::join(Nimble::getInstance()->config['view_path'], $file);
        Nimble::log("rendering $file", NIMBLE_LOG_DEBUG);

        if ($this->layout==false){
            echo $this->open_template($this->template); 
        } else {
            $this->content = $this->open_template($this->template); 

            if (empty($this->layout_template))
                throw new NimbleException("Layout Template is not set for this controller");

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
        $this->rendered_partials[] = $file;
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
    public function header($text, $code=''){ 
        $this->headers[] = array($text, $code); 
    }

    /**
     * Redirect to another URL.
     * @param string $url The URL to redirect to.
     * @param boolean $now If true
     */
    public function redirect($url, $now=false)
    {
        Nimble::log("redirecting to $url", NIMBLE_LOG_DEBUG);
        if($now && self::nimble()->test_mode == false){
            header("Location: {$url}", true, 302);
            exit();
        }else{
            $this->header("Location: {$url}", 302);
            $this->has_rendered = true;
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


    /**
     * respond to a given request format directly, using a specific function/method 
     * - instead of the default Controller->render() action which loads a view.
     *
     * Would be nice to use a lambda function with a closure here, so we could pass the controller
     * itself to the function at runtime. But for php<5.3 have to settle for this.
     *
     * example call, in a controller action:
     *      $this->respond_to('xml', array('XMLview', 'build'), array('loginResult', $this->result));
     *      # pre-supposes that xml_view plugin is loaded via Nimble::plugins('xml_view')
     *
     * @param $format string        format we are expecting to respond to (xml, json, etc)
     * @param $func mixed           function name, reference or array suitable for call_user_func_array()
     * @param $args array opt       arguments to be passed to the function
     * @throws NimbleException       
     */
    public function respond_to($format, $func=null, $args=array()) {
        if ($this->format() == $format) {
            if (is_callable($func)) {
                $funcname = (is_array($func))? $func[0].'::'.$func[1] : (string)$funcname;
                Nimble::log("responding to $format with $funcname", NIMBLE_LOG_DEBUG);
                echo call_user_func_array($func, $args);
                $this->has_rendered = true;
                if (isset($this->headers_by_format[$format])) {
                    $this->headers[] = $this->headers_by_format[$format];
                }
            }
            else {
                throw new NimbleException('Uncallable function given to respond_to()');
            }
        }
    }
}


/**
 * An alias to Controller for backwards compatibility with Nice Dog.
 * @package Nimble
 * @see class Controller
 */
class C extends Controller {}


/* vim: set ts=4 sw=4 expandtab: */

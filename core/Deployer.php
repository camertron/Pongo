<?php

require_once(dirname(__FILE__) . "/lib/Command.php");

//when inherited, acts as a base class for all deployment scripts
class Deployer {

    //list of modules to deploy
    private $modules = array();
    //list of servers to deploy to
    public $server_list = array();
    //holds the current list of commands (cleared and reassigned for each loop in execute())
    private $cmds = array();

    //executes the given function on the list of servers specified by the $server_list array
    public function execute($function, $pong_file) {
        $bundles = array();

        for ($s = 0; $s < count($this->server_list); $s++) {
            $server_name = $this->server_list[$s];
            $server = $pong_file->server_list->$server_name;

            //make sure server exists in list
            if ($server != null) {
                //reset commands array
                $this->cmds = array();

                if (method_exists($this, $function)) {
                    //call the function on this server
                    $this->$function($server, $server_name);
                } else {
                    //function doesn't exist, so try to include, instantiate, and call
                    $pieces = explode(":", $function);

                    if (count($pieces) > 1) {
                        $path = implode("/", array_slice($pieces, 0, count($pieces) - 1));
                        $class = basename($path);
                        $function = $pieces[count($pieces) - 1];

                        //include module
                        if (!$this->include_module($path, getcwd()))
                            throw new Exception("Couldn't include module " . $path . "  Make sure it exists as deploy/modules/" . $path . ".php");

                        //instantiate, add action, build command list
                        $mod_instance = new $class();
                        $mod_instance->actions[] = $function;
                        $this->add_module($mod_instance);
                    }
                }

                $bundles[] = new Bundle($server, $this->cmds);
            }
        }

        return $bundles;
    }

    //alias of run() - use for readability where run() might be awkward
    public function add_commands($to_add, $escape = true) {
        $this->run($to_add, $escape);
    }

    //takes a Command, an array of Commands, or a string, and adds it/them to $this->cmds
    public function run($to_add, $escape = true) {
        if (is_array($to_add)) {
            //print_r($to_add);
            $this->cmds = array_merge($this->cmds, $to_add);
        } else if (is_string($to_add))
            $this->cmds[] = new Command($to_add, $escape);
        else if (get_class($to_add) == "Command")
            $this->cmds[] = $to_add;
        else
            throw new Exception("Couldn't run command.  Invalid type passed to Deployer::run().");
    }

    //includes a module from a /modules/ directory either relative to $path (if not an empty string),
    //or relative to the pongo core
    public function include_module($module, $path = "") {
        $file_core = $this->join_paths(dirname(__FILE__), "/modules/", $module . ".php");
        $file_custom = $this->join_paths($path, "modules/", $module . ".php");

        //always include the custom module if it exists
        //this means that custom modules override core ones
        if (file_exists($file_custom)) {
            require_once($file_custom);
            return true;
        } else if (file_exists($file_core)) {
            require_once($file_core);
            return true;
        } else {
            return false;
        }
    }

    //overridable functions
    //executed before each module
    public function before_module($server, $module, $index) {
        
    }

    //executed after every module
    public function after_module($server, $module, $index) {

    }

    //disables the remote website by showing an "under construction" splash screen
    public function web_disable() {
        
    }

    //enables the remote website by removing the "under construction" splash screen
    public function web_enable() {

    }

    //module CRUD (no remove or functions yet - probably not necessary)
    public function add_module($new_module) {
        //add module to list
        $this->modules[] = $new_module;

        //get commands from module
        $this->before_module($server_name, $new_module, count($this->modules) - 1);
        $this->add_commands($new_module->execute());  //Module::deploy handles calling all the requested actions
        $this->after_module($server_name, $new_module, count($this->modules) - 1);
    }

    public function module_count() {
        return count($this->modules);
    }

    //get, but don't change - once a module has been added it can't be changed
    public function get_module($index) {
        return $this->modules[$index];
    }

    //intelligently joins paths together
    //accepts an indeterminate number of arguments
    function join_paths() {
        $args = func_get_args();
        $arg_count = func_num_args();
        $final = array();

        for ($i = 0; $i < $arg_count; $i++) {
            $arg = $args[$i];

            //permit a leading slash
            if ($i > 0) {
                //check for beginning slash
                switch ($arg[0]) {
                    case "/":
                    case "\\":
                        $arg = substr($arg, 1);
                }
            }

            //check for ending slash
            switch ($arg[strlen($arg) - 1]) {
                case "/":
                case "\\":
                    $arg = substr($arg, 0, strlen($arg) - 1);
            }

            $final[] = $arg;
        }

        return implode($final, "/");
    }

}

?>
<?php

    abstract class Module {

        public $name = "";
        public $description = "";
        public $actions = array();
        public $sudo = true;
		public $cmds = array();

        //echo command (can't be named echo because that's a PHP reserved word)
        public function echo_c($string) {
            return new Command("echo '" . $string . "'");
        }

        public function get_sudo() {
            if ($this->sudo)
                return "sudo ";
            else
                return "";
        }

        //DON'T override this function
        public function execute() {
			$this->cmds = array();	
			
	        foreach ($this->actions as $action)
	            $this->$action();

	        return $this->cmds;
        }

		//takes a Command, an array of Commands, or a string, and adds it/them to $this->cmds
	    public function run($to_add, $escape = true) {
	        if (is_array($to_add)) {
	            $this->cmds = array_merge($this->cmds, $to_add);
	        } else if (is_string($to_add))
	            $this->cmds[] = new Command($this->get_sudo() . $to_add, $escape);
	        else if (get_class($to_add) == "Command")
	            $this->cmds[] = $to_add;
	        else
	            throw new Exception("Couldn't run command.  Invalid type passed to Deployer::run().");
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
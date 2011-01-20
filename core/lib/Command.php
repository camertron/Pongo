<?php

    class Command {

        //the command to execute on the remote server
        public $command_string;
        public $escape;

        public function __construct($command_string, $escape = true) {
            $this->command_string = $command_string;
            $this->escape = $escape;
        }

        public function get_command() {
            //other processing can go here too
            if ($this->escape)
                return escapeshellcmd($this->command_string);
            else
                return $this->command_string;
        }
    }

?>
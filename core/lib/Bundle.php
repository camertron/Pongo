<?php

	class Bundle {
		public $server;
		public $cmds;
		public $bundle_string;
		
		public function __construct($init_server, $init_cmds) {
			$this->server = $init_server;
			$this->cmds = $init_cmds;
			$bundle_string = "";
		}
		
		public function prepare() {
			$cmd_text = array();

            foreach ($this->cmds as $cmd)
                $cmd_text[] = $cmd->get_command();
            
            $this->bundle_string = implode("; ", $cmd_text);
		}
	}

?>
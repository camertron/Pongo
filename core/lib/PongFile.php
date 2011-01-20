<?php

    class PongFile {

        public $server_list = array("my_server" => array("ssh_host" => "www.example.com", "ssh_port" => "22", "ssh_username" => "my_user"));

        public function __construct() {

        }

        public static function from_file($file) {
            $final = new PongFile();
            $final->server_list = json_decode(file_get_contents($file));
			
			foreach ($final->server_list as $name => &$server) {
				$server->name = $name;
			}
			
            return $final;
        }

        public function to_file($file) {
            $handle = fopen($file, 'w');
            fwrite($handle, json_encode($this->server_list));
            fclose($handle);
        }

    }

?>
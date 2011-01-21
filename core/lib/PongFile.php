<?php

    class PongFile {

        public $server_list;

        public function __construct() {
			//set up default server list
			$this->server_list = array("production" => array("ssh_host" => "www.production.com", "ssh_port" => "22", "ssh_username" => "my_production_user"));
			$this->server_list["staging"] = array("ssh_host" => "www.staging.com", "ssh_port" => "22", "ssh_username" => "my_staging_user");
			$this->server_list["testing"] = array("ssh_host" => "www.testing.com", "ssh_port" => "22", "ssh_username" => "my_testing_user");
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
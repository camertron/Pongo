<?php
	
	//Encapsulates SSH2 output so that it can be read by line.
	//Author: Cameron C. Dutro
	//Date: Wednesday, January 19th, 2011

	class SSH2_stream {
		private $exec_id;
		private $ssh2;
		private $eof;
		private $lines;
		
		public function __construct($init_exec_id, $init_ssh2) {
			$this->exec_id = $init_exec_id;
			$this->ssh2 = $init_ssh2;
			$this->eof = false;
			$this->lines = array();
		}
		
		public function read() {
			if ($this->eof) {
				return false;
			} else {
				if (count($this->lines) > 0) {
					return array_shift($this->lines);
				} else {
	            	$chunk = $this->ssh2->_get_channel_packet($this->exec_id);
            
					if (($chunk === false) || ($chunk === true)) {
						$this->eof = true;
						return false;
					} else {
						$this->lines = explode("\n", trim($chunk));
						return array_shift($this->lines);
					}
				}
			}
		}
		
		public function is_eof() {
			return $this->eof;
		}
	}

?>
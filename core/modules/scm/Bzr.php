<?php

    class Bzr extends Module {

        public $name = "Bazaar content versioning system module.";
        public $description = "Provides a wrapper around shell access to Bazaar, enabling the user to check out or export code to a remote server.";

        public function install() {
            $this->run("apt-get install bzr");
        }
    }

?>
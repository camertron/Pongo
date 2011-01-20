<?php

    class Git extends Module {

        public $name = "Git content versioning system module.";
        public $description = "Provides a wrapper around shell access to Git, enabling the user to check out or export code to a remote server.";

        public function install() {
            $this->run("apt-get install git-core");
        }
    }

?>
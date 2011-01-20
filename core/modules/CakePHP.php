<?php

    class CakePHP extends Module {

        public $name = "Cake installer and manager module.";
        public $description = "Installs, configures, and manages a CakePHP instance.";
        public $install_path = "/usr/local/cake";

        public function install() {
            $this->run("mkdir /usr/local/cake");
            $this->run("git clone git://github.com/cakephp/cakephp.git " . $this->install_path);
        }
    }

?>
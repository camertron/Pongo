<?php

    class Pear extends Module {

        public $name = "Pear installer and manager module.";
        public $description = "Installs and manages a Pear packages.";
        public $channel = "";
        public $flags = "";
        public $package = "";

        public function install() {
            $this->run("apt-get install php-pear");
        }

        public function channel_discover() {
            $this->run("pear channel-discover " . $this->channel);
        }

        public function install_module() {
            $this->run("pear install " . $this->flags . " " . $this->package);
        }
    }

?>
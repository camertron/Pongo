<?php

    class PropelORM extends Pear {

        public $name = "Propel installer and manager module.";
        public $description = "Installs, configures, and manages a Propel instance.";
        public $install_generator = true;
        public $install_runtime = true;

        public function install() {
            $this->channel = "pear.propelorm.org";
            $this->flags = "-a";
            $this->channel_discover();

            $this->package = "propel/propel_generator";
            $this->install_module();

            $this->package = "propel/propel_runtime";
            $this->install_module();
        }
    }

?>
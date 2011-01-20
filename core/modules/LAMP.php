<?php

    require_once(dirname(__FILE__) . '/Pear.php');

    class LAMP extends Module {

        public $name = "LAMP (Linux, Apache, MySQL, PHP) installer and manager module.";
        public $description = "Installs, configures, and manages a LAMP instance.";
        public $mod_rewrite = true;
        public $no_prompt = true;
        public $packages = array(LAMPPackage::APACHE2,
            LAMPPackage::PHP5,
            LAMPPackage::APACHE_PHP,
            LAMPPackage::MYSQL_SERVER,
            LAMPPackage::PHP_MYSQL);

        public function install() {
            $pkg_apt = array();
            $cmds = array();

            foreach ($this->packages as $package) {
                switch ($package) {
                    case LAMPPackage::APACHE2:
                        $pkg_apt[] = "apache2";
                        break;
                    case LAMPPackage::PHP5:
                        $pkg_apt[] = "php5";
                        break;
                    case LAMPPackage::APACHE_PHP:
                        $pkg_apt[] = "libapache2-mod-php5";
                        break;
                    case LAMPPackage::MYSQL_SERVER:
                        $pkg_apt[] = "mysql-server";
                        break;
                    case LAMPPackage::MYSQL_CLIENT:
                        $pkg_apt[] = "mysql_client";
                        break;
                    case LAMPPackage::PHP_MYSQL:
                        $pkg_apt[] = "php5-mysql";
                        break;
                    case LAMPPackage::PHP_MY_ADMIN:
                        $pkg_apt[] = "phpmyadmin";
                        break;
                }
            }

            $cmds = array();
            $this->run($sudo . "apt-get install " . implode(" ", $pkg_apt));

            if ($this->mod_rewrite)
                $this->run($sudo . "a2enmod rewrite");

            //restart apache and print instructional message
            $this->run("/etc/init.d/apache2 restart");
            $this->echo_c("Dont forget to change your document root in /etc/apache2/sites-available/default.");

            return $cmds;
        }

    }

    class LAMPPackage {
        const APACHE2 = 1;
        const PHP5 = 2;
        const APACHE_PHP = 3;
        const MYSQL_SERVER = 4;
        const MYSQL_CLIENT = 5;
        const PHP_MYSQL = 6;
        const PHP_MY_ADMIN = 7;
    }

?>
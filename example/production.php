<?php

    require_once(dirname(__FILE__) . "/../core/Deployer.php");

    //import custom modules here
    //require_once("modules/WorkerModule.php");

    class ProductionDeployer extends Deployer {

        //the servers this deploy will affect
        public $server_list = array("my_server");

        //if specified, each of these functions will called once for every server in $server_list
        //they all require a signature like this: public function func($server, $server_name)

        //production deployment of code from a subversion repository
        public function deploy($server, $server_name) {
            
            //add support for necessary modules
            $this->include_module("scm/Svn");

            //create instance of module
            $svn = new Svn();
            $svn->command = "export"; //could also be "checkout", "update", whatever else you want
            $svn->protocol = SvnProtocol::SVN_SSH;
            $svn->username = "user";
            $svn->repo_url = "my_repo_url";
            $svn->dest_path = "/var/www";
            $svn->regulate = true;  //appends dest_path with a directory named with the current timestamp, then symlinks it to a directory named "current"
            $svn->use_sudo = true;
            $svn->actions[] = "deploy";
            $this->add_module($svn);

            //add extra commands here that should be executed after the deployment happens

            //symlink the settings directory from local to production
            $this->run("sudo rm /var/www/current/settings/current");
            $this->run("sudo ln -s /var/www/current/settings/production /var/www/current/settings/current");
        }

		//installs a bunch of stuff from the included modules
        public function setup_new_server($server, $server_name) {
            $this->include_module("LAMP");
            $lamp = new LAMP();
            $lamp->packages[] = LAMPPackage::PHP_MY_ADMIN;
            $lamp->actions[] = "install";
            $this->add_module($lamp);

            $this->include_module("scm/Bzr");
            $bzr = new Bzr();
            $bzr->actions[] = "install";
            $this->add_module($bzr);

            $this->include_module("scm/Git");
            $git = new Git();
            $git->actions[] = "install";
            $this->add_module($git);

            $this->include_module("scm/Svn");
            $svn = new Svn();
            $svn->actions[] = "install";
            $this->add_module($svn);

            $this->include_module("CakePHP");
            $cake = new CakePHP();
            $cake->actions[] = "install";
            $this->add_module($cake);

            $this->include_module("Pear");
            $pear = new Pear();
            $pear->actions[] = "install";
            $this->add_module($pear);

            $this->include_module("PropelORM");
            $propel = new PropelORM();
            $propel->actions[] = "install";
            $this->add_module($propel);

            //install cURL support
            $this->run("sudo apt-get install curl libcurl3 libcurl3-dev php5-curl php5-mcrypt --fix-missing");

            //other commands here...
        }

		//supposed to clean up unused deploy directories
		//script can be found at core/ex/scm_cleanup.php
        public function cleanup() {
            $this->run("cd /var/www/");
            $this->run("php scm_cleanup.php");
        }
    }

    //must instantiate deployer and assign to $deployer variable in order to put this truck in gear
    $deployer = new ProductionDeployer();
?>
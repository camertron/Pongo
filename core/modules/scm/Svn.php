<?php

    class Svn extends Module {

        public $name = "Subversion content versioning system module.";
        public $description = "Provides a wrapper around shell access to Subversion, enabling the user to check out or export code to a remote server.";
        //module-specific values
        public $protocol = SvnProtocol::SVN_SSH;
        public $username = "";
		public $password = "";
        public $repo_url = "";
        public $dest_path = "";
        public $command = "";
        public $args = array();
        public $cleanup_script = null;
        public $regulate = false;

        public function deploy() {
            $cmd .= "svn " . $this->command . " ";

            switch ($this->protocol) {
                case SvnProtocol::HTTP:
                    $cmd .= "http://";
                    break;
                case SvnProtocol::HTTPS:
                    $cmd .= "https://";
                    break;
                case SvnProtocol::SVN:
                    $cmd .= "svn://";
                    break;
                case SvnProtocol::SVN_SSH:
                    $cmd .= "svn+ssh://";
                    break;
            }

            if ($this->username != "")
                $cmd .= $this->username . "@";

            $cmd .= $this->repo_url;

            if ($this->dest_path != "") {
                $cmd .= " ";

                //add an ending slash to dest_path if it doesn't already have one
                switch ($this->dest_path[strlen($this->dest_path) - 1]) {
                    case "/":
                    case "\\":
                        break;
                    default:
                        $this->dest_path .= "/";
                        break;
                }

                //if the user wants to maintain deployment folders
                //and symlink to the current one, then do it for 'em!
                if ($this->regulate) {
                    //put code into a folder marked with the current timestamp (so it's unique)
                    $regulate_folder = time();
                    $cmd .= $this->dest_path . $regulate_folder;
                }
                else
                    $cmd .= $this->dest_path;
            }

            if (count($this->args) > 0)
                $cmd .= " " . implode(" " . $this->args);

            $this->run($cmd);

            //add additional commands
            //symlink regulated folder to /current/ folder
            if ($this->regulate) {
                $this->run("chmod -R 777 " . $this->dest_path . $regulate_folder);
                $this->run("rm " . $this->dest_path . "current");
                $this->run("ln -s " . $this->dest_path . $regulate_folder . " " . $this->dest_path . "current");
                $this->run("/etc/init.d/apache2 restart");
            }

            //run cleanup script if necessary (usually removes old code directories)
            if ($cleanup_script != null)
                $this->run("php " . $cleanup_script);

            return $cmds;
        }

        public function install() {
            $this->run("apt-get install subversion");
        }
    }

    class SvnProtocol {
        const HTTP = 1;
        const HTTPS = 2;
        const SVN = 3;
        const SVN_SSH = 4;
    }

?>
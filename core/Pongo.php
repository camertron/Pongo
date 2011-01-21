<?php

//include SSH access via phpseclib
set_include_path(get_include_path() . PATH_SEPARATOR . dirname(__FILE__) . '/phpseclib');

//libs
require_once(dirname(__FILE__) . "/lib/PongFile.php");
require_once(dirname(__FILE__) . "/lib/Command.php");
require_once(dirname(__FILE__) . "/lib/Bundle.php");

//modules
require_once(dirname(__FILE__) . '/modules/Module.php');

//extras
require_once(dirname(__FILE__) . "/Thread.php");
require_once("Net/SSH2.php");

# This is the main driver script for Pongo
# usage: pong [path] distribution function, i.e. pong production deploy OR pong ~/myproject/pong production deploy
//process command
if (count($argv) >= 2) {
    switch ($argv[2]) {
        case "pongify":
            pongify($argv);
            break;
        default:
            deploy($argv);
            break;
    }
}

function pongify($argv) {
    //need to invent some kind of logging system...
    $pong_file = getcwd() . "/pongfile";

    if (file_exists($pong_file))
        unlink($pong_file);

    $file = new PongFile();
    $file->to_file($pong_file);
    echo("Created file " . $pong_file . "\n");
}

function minimize($code) {
    $code = str_replace(" ", "", $code);
    $code = str_replace("\r", "", $code);
    $code = str_replace("\n", "", $code);
    $code = str_replace("\t", "", $code);

    return $code;
}

function deploy($argv) {
    $args = get_deploy_args($argv);

    if ($args["args_valid"]) {

        //eventually put a try/catch around this??
        //this is where the magic happens
        //(we get $deployer for free for requiring the deploy file - it MUST be instantiated by the included file
        //and must inherit from Deployer)
        require_once($args["deploy_file"]);

        $functions = $args["functions"];
        $pong_file = PongFile::from_file($args["pong_file"]);
        $bundles = array();
        $bundle_text = array();

        //call each function for each bundle (i.e. each server/connection)
        foreach ($functions as $function) {
            $cur_bundles = $deployer->execute($function, $pong_file);

            for ($i = 0; $i < count($cur_bundles); $i++) {
                if (count($bundles) <= $i)
                    $bundles[$i] = $cur_bundles[$i];
                else
                    $bundles[$i]["cmds"] = array_merge($bundles[$i]["cmds"], $cur_bundles[$i]["cmds"]);
            }
        }

        //glue together all commands in each bundle by calling each bundle's prepare() function
        //prepare bundles separately so execution isn't slowed down by the formation of the command strings
        foreach ($bundles as $bundle) {
            $bundle->prepare();
        }

		//get password from user to execute commands over SSH
		if (! in_array("dry-run", $args["flags"])) {
			echo("Password: ");
			$password = get_password();
			echo("\r\n");
		}

        //thread pool
        $pool = array();

        //start each bundle in its own thread
        foreach ($bundles as $bundle) {
            if (in_array("dry-run", $args["flags"])) {
                echo("[" . $bundle->server->name . "] " . $bundle->bundle_string . "\r\n");
            } else {
                $cur_thread = new Thread("execute_over_ssh");
                $cur_thread->start($bundle, $password);
                $pool[] = $cur_thread;
            }
        }

		//don't exit until all threads have finished
        do {
            $dead = true;

            foreach ($pool as $swimmer) {
                $dead &= ( !$swimmer->isAlive());
            }
        } while (!$dead);
    }
    else
        echo("Deploy failed:\n" . $args["arg_log"]);
}

function execute_over_ssh($bundle, $password) {
    $ssh = new Net_SSH2($bundle->server->ssh_host, $bundle->server->ssh_port);
    if (!$ssh->login($bundle->server->ssh_username, $password)) {
        exit("[" . $bundle->server->name . "] " . "Login failed!\r\n");
    }

    $stream = $ssh->exec($bundle->bundle_string);

    while (!$stream->is_eof()) {
        $chunk = $stream->read();

        if ($chunk !== false)
            echo("[" . $bundle->server->name . "] " . $chunk . "\r\n");
    }
}

/**
 * Get a password from the shell.
 * Author: DASPRiD (http://www.dasprids.de/blog/2008/08/22/getting-a-password-hidden-from-stdin-with-php-cli)
 *
 * This function works on *nix systems only and requires shell_exec and stty.
 *
 * @param  boolean $stars Wether or not to output stars for given characters
 * @return string
 */
function get_password($stars = false)
{
    // Get current style
    $oldStyle = shell_exec('stty -g');

    if ($stars === false) {
        shell_exec('stty -echo');
        $password = rtrim(fgets(STDIN), "\n");
    } else {
        shell_exec('stty -icanon -echo min 1 time 0');

        $password = '';
        while (true) {
            $char = fgetc(STDIN);

            if ($char === "\n") {
                break;
            } else if (ord($char) === 127) {
                if (strlen($password) > 0) {
                    fwrite(STDOUT, "\x08 \x08");
                    $password = substr($password, 0, -1);
                }
            } else {
                fwrite(STDOUT, "*");
                $password .= $char;
            }
        }
    }

    // Reset old style
    shell_exec('stty ' . $oldStyle);

    // Return the password
    return $password;
}


function get_deploy_args($argv) {
    $arg_index = 1;
    $args_valid = true;
    $arg_log = "";

    $path = $argv[1];
    $arg_index++;

    $distribution = $argv[$arg_index];
    $functions = array();
    $flags = array();

    if (count($argv) > $arg_index) {
        for ($i = $arg_index + 1; $i < count($argv); $i++) {
            //if it begins with a double dash, it's a flag
            if (substr($argv[$i], 0, 2) == "--")
                $flags[] = substr($argv[$i], 2);
            else
                $functions[] = $argv[$i];
        }
    }

    switch ($path[strlen($path) - 1]) {
        case "/":
        case "\\":
            break;
        default:
            $path .= "/";
            break;
    }

    $deploy_file = $path . $distribution . ".php";
    $pong_file = $path . "pongfile";

    //validate arguments
    if (!file_exists($deploy_file)) {
        $args_valid = false;
        $arg_log .= "Could not find deploy file: " . $deploy_file . "\n";
    }

    if (!file_exists($pong_file)) {
        $args_valid = false;
        $arg_log .= "Could not find pongfile: " . $pong_file . "\n";
    }

    return array("args_valid" => $args_valid,
        "arg_log" => $arg_log,
        "distribution" => $distribution,
        "function" => $function,
        "deploy_file" => $deploy_file,
        "pong_file" => $pong_file,
        "functions" => $functions,
        "flags" => $flags,
        "path" => $path);
}

?>
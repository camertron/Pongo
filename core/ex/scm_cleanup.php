<?php

    $dir = dirname(__FILE__);   //directory to clean (the directory this file is in)
    $keep = 7;                  //number of old branch folders to keep
    $dirs = array();            //final array of existing directories

    //add 3 to account for . , .. , and current (which will all never be deleted)
    $keep += 3;
    
    //find all folders in directory specified by $dir
    if ($dh = opendir($dir)) {
        
        while (($file = readdir($dh)) !== false) {

            //make sure current entry is a folder and that it's not a dot, then add to list
            if ($file != 'tmp' && $file != '..' && $file != '.' && is_dir($dir . '/' . $file))
                $dirs[] = $dir . '/' . $file;
        }

        //close and sort so most recent sink to the bottom of the list
        closedir($dh);
        sort($dirs);

        //delete unecessary directories (don't delete $keep of them)
        //rmdirr = recursive delete
        for ($i = 0; $i < count($dirs) - $keep; $i ++) {
            echo("Removing " . $dirs[$i] . "\r\n");
            passthru("sudo rm -rf " . $dirs[$i]);
        }
    }

?>
<?php

namespace Woojin\Utility\Helper;

use ZipArchive;

class ZipHelper implements IHelper
{
    /**
     * creates a compressed zip file
     * 
     * @param  array   $files      
     * @param  string  $destination
     * @param  boolean $overwrite  
     * @return boolean              
     */
    public function create($files = array(), $destination = '', $overwrite = false) 
    {
        //if the zip file already exists and overwrite is false, return false
        if (file_exists($destination) && !$overwrite) { 
            return false; 
        }

        //vars
        $valid_files = array();

        //if files were passed in...
        if (is_array($files)) {
            //cycle through each file
            foreach ($files as $file) {
                //make sure the file exists
                if (file_exists($file)) {
                    $valid_files[] = $file;
                }
            }
        }

        //if we have good files...
        if (count($valid_files)) {
            //create the archive
            $zip = new ZipArchive();

            if ($zip->open($destination, $overwrite ? ZIPARCHIVE::OVERWRITE : ZIPARCHIVE::CREATE) !== true) {
                return false;
            }

            //add the files
            foreach ($valid_files as $file) {
                $pathParts = explode('/', $file);

                $local = (is_array($pathParts)) ? $pathParts[count($pathParts) - 1] : $file;
                $zip->addFile($file, $local);
            }
            //debug
            //echo 'The zip archive contains ',$zip->numFiles,' files with a status of ',$zip->status;
            
            //close the zip -- done!
            $zip->close();
            
            //check to make sure the file exists
            return file_exists($destination);
        } else {
            return false;
        }
    }
}

<?php

error_reporting(-1);
ini_set('display_errors', 'On');

foreach ($_GET as $key => $val) {
    echo 'key:' . $key . ', val:' . $val . "<br />";
}

if (array_key_exists('cleaner', $_GET) && 'avenue-jocoonopa-1986' === $_GET['cleaner']) {
    echo 'Cache clean task start!';

    $dir = __DIR__ . '/../app/cache';
    $it = new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS);
    $files = new RecursiveIteratorIterator($it,
                 RecursiveIteratorIterator::CHILD_FIRST);
    foreach($files as $file) {
        if ($file->isDir()){
            rmdir($file->getRealPath());
            echo $file->getRealPath() . " be removed!<br/>";
        } else {
            unlink($file->getRealPath());
        }
    }

    if (isset($_GET['image']) && $_GET['image'] === 'do') {
        $dir = __DIR__ . '/cache';
        $it = new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS);
        $files = new RecursiveIteratorIterator($it,
                     RecursiveIteratorIterator::CHILD_FIRST);
        foreach($files as $file) {
            if ($file->isDir()){
                rmdir($file->getRealPath());
            } else {
                unlink($file->getRealPath());
            }
        }
    }
} else {
    throw new \Exception();
}
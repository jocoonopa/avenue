<?php

if ($_GET['cleaner'] == 'avenue-jocoonopa-1986') {
    $dir = __DIR__ . '/app/cache';
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
} else {
    throw new \Exception();
}
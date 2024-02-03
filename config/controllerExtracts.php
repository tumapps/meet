<?php
$controllers = [];
foreach (new DirectoryIterator(dirname(__DIR__) . '/modules') as $index => $fileinfo) {
    if ($fileinfo->isDir() && !$fileinfo->isDot() ) {
        $controllers[] = $fileinfo->getFilename();
    }
}
return $controllers;
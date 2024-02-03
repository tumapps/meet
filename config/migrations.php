<?php
$path = [];
foreach (new DirectoryIterator(dirname(__DIR__) . '/modules') as $index => $fileinfo) {
    if ($fileinfo->isDir() && !$fileinfo->isDot()) {
        $path[] = '@' . $fileinfo->getFilename().'/migrations';
    }
}
return $path;

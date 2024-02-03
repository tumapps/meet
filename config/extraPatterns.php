<?php
$routes = [];
foreach (new DirectoryIterator(dirname(__DIR__) . '/modules') as $index => $fileinfo) {
    if ($fileinfo->isDir() && !$fileinfo->isDot() && $fileinfo->getFilename() !== 'dashboard') {
        $dir = dirname(__DIR__) . "/modules/" . $fileinfo->getFilename() . "/routers";
        foreach (glob("{$dir}/*.php") as $filename) {
            $route = require($filename);
            $routes = array_merge($routes, $route);
        }
    }
}

return $routes;

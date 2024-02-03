<?php
$aliases = [
    '@bower' => '@vendor/bower-asset',
    '@npm'   => '@vendor/npm-asset',
    '@helpers' => '@app/providers/components',
    '@coder' => '@app/providers/code',
    '@swagger' => '@app/providers/swagger',
    '@ui' => '@app/providers/interface',
    '@cmd' => '@app/providers/console',
    '@modules' => '@app/modules',
];
foreach (new DirectoryIterator(dirname(__DIR__) . '/modules') as $index => $fileinfo) {
    if ($fileinfo->isDir() && !$fileinfo->isDot()) {
        $aliases['@' . $fileinfo->getFilename()] = '@app/modules/' . $fileinfo->getFilename();
    }
}
return $aliases;

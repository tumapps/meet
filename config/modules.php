<?php
$modules = [
    'admin' => [
        'class' => 'mdm\admin\Module',
        'controllerMap' => [
            'assignment' => [
               'class' => 'mdm\admin\controllers\AssignmentController',
               /* 'userClassName' => 'app\models\User', */
               'idField' => 'user_id',
               'usernameField' => 'username',
               //'searchClass' => 'app\models\UserSearch'
           ],
       ],
    ]
];
foreach (new DirectoryIterator(dirname(__DIR__) . '/modules') as $index => $fileinfo) {
    if ($fileinfo->isDir() && !$fileinfo->isDot() && $fileinfo->getFilename() !== 'dashboard') {
        $modules[$fileinfo->getFilename()] = [
            'class' => $fileinfo->getFilename() . '\\Module'
        ];
    }
}
return $modules;

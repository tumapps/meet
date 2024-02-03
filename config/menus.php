<?php
$userMenu = [
    ['title' => 'Dashboard', 'icon' => 'home', 'url' => '/site/index'],
    ['title' => 'Transactions', 'icon' => 'money-bill-transfer', 'url' => '/site/index'],
    ['title' => 'Customers', 'icon' => 'address-book', 'url' => '/site/index'],
    ['title' => 'Metadata', 'icon' => 'network-wired', 'url' => '/site/index'],
    ['title' => 'Reports', 'icon' => 'chart-line', 'url' => '/site/index'],
    ['title' => 'IAM & Admin', 'icon' => 'shield', 'submenus' => [
        ['title' => 'User Management', 'url' => '/accounts/index'],
        ['title' => 'Manage Roles', 'url' => '/role/index'],
        ['title' => 'Manage Permissions', 'url' => '/permission/index'],
    ]],
    ['title' => 'Settings', 'icon' => 'cog fa-spin', 'submenus' => [
        ['title' => 'General Settings', 'url' => '/settings/index', 'param' => ['id' => 'general']],
        ['title' => 'Email Settings', 'url' => '/settings/index', 'param' => ['id' => 'email']],
    ]],

];
//Do not edit the code below unless you know what you are doing
$api =[];
foreach (new DirectoryIterator(Yii::getAlias('@modules')) as $index => $fileinfo) {
    $module  = '\\' . $fileinfo->getFilename() . '\\Module';
    if (property_exists($module, 'name')) {
        $apis[] = ['title' => Yii::$app->getModule($fileinfo->getFilename())->name, 'url' => '/site/docs', 'param' => ['mod' => $fileinfo->getFilename()]];
    }
}
//$api = [['title' => 'API Docs', 'icon' => 'code', 'submenus' => $apis]];
return array_merge($userMenu, $api);

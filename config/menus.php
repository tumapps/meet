<?php
require_once __DIR__ . '/wrapper.php';
$userMenu = [
    ['title' => 'Dashboard', 'icon' => 'home', 'url' => 'home/index'],
    ['title' => 'IAM & Admin', 'icon' => 'shield', 'submenus' => [
        ['title' => 'User Management', 'url' => 'accounts/index'],
        ['title' => 'Manage Roles', 'url' => 'role/index'],
        ['title' => 'Manage Permissions', 'url' => 'permission/index'],
        ['title' => 'Manage Rules', 'url' => 'rule/index'],
    ]],
    ['title' => 'Settings', 'icon' => 'cog fa-spin', 'submenus' => [
        ['title' => 'General Settings', 'url' => 'settings/index', 'param' => ['id' => 'general']],
        ['title' => 'Email Settings', 'url' => 'settings/index', 'param' => ['id' => 'email']],
    ]],

];
return array_merge($userMenu, (new ConfigWrapper())->load('apiMenus'));

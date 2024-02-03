<?php
function dbDriver($selector = null)
{
    $connection = [ 
        'class' => 'yii\db\Connection',
    ];
    switch ($_SERVER[$selector . '_DRIVER']) {
        case "mssql":
            $connection = array_merge($connection, [
                'driverName' => 'sqlsrv',
                'dsn' => "sqlsrv:Server={$_SERVER[$selector . '_HOST']};Database={$_SERVER[$selector . '_DATABASE']}",
            ]);
            break;
        case "pgsql":
            $connection = array_merge($connection, [
                'dsn' => "pgsql:host={$_SERVER[$selector . '_HOST']};port={$_SERVER[$selector . '_PORT']};dbname={$_SERVER[$selector . '_DATABASE']}",
            ]);
            break;
        default: // mysql
            $connection = array_merge($connection, [
                'dsn' => "mysql:host={$_SERVER[$selector . '_HOST']};port={$_SERVER[$selector . '_PORT']};dbname={$_SERVER[$selector . '_DATABASE']}",
            ]);
    }
    $connection = array_merge($connection, [
        'username' => $_SERVER[$selector . '_USERNAME'],
        'password' => $_SERVER[$selector . '_PASSWORD'],
        'charset' => 'utf8',
        'enableSchemaCache' => true,
        'schemaCacheDuration' => 60,
        'schemaCache' => 'cache',
    ]);
    return $connection;
}
return [
    'core' => dbDriver('CORE_DB'),
    'maintenance' => dbDriver('MAINTENANCE_DB'),
];

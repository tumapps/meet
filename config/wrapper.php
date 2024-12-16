<?php
class ConfigWrapper
{
    public $_aliases;
    public $_modules;
    public $_tokens;
    public $_params;
    public function __construct()
    {
        $this->_aliases = [
            '@bower' => '@vendor/bower-asset',
            '@npm'   => '@vendor/npm-asset',
            '@helpers' => '@app/providers/components',
            '@coder' => '@app/providers/code',
            '@swagger' => '@app/providers/swagger',
            '@ui' => '@app/providers/interface',
            '@cmd' => '@app/providers/console',
            '@modules' => '@app/modules',
            // '@views' => '@app/providers/interface',
        ];
        $this->_modules = [
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
        $this->_tokens = [
            '{id}' => '<id:\\d[\\d,]*>',
            '{key}' => '<key:[a-zA-Z0-9_\-\/]+>',
            '{crypt_id}' => '<crypt_id:[a-zA-Z0-9\\-]+>',
        ];
        $this->_params = [
            'pageSize' => [10 => 10, 25 => 25, 50 => 50, 100 => 100],
            'pageSizeLimit' => 100,
            'defaultPageSize' => 25,
            'safeEndpoints' => (isset($_SERVER['APP_SAFE_ENDPOINTS'])) ? explode(',', $_SERVER['APP_SAFE_ENDPOINTS']) : ['error'],
            'jwt.issuer' => 'http://example.com',
            'jwt.audience' => 'http://example.org',
            'jwt.id' => '4f1g23a12aa',
            'jwt_secret' => getenv('JWT_SECRET'),
            'adminEmail' => 'tum@gmail.com',
            'user.passwordResetTokenExpire' => 900,
            'allowedDomains' => 'http://localhost:9000',
            'passwordResetLink' => 'http://10.17.0.24/request-password-reset?token=',
            'confirmationLink' => 'http://10.17.0.24/meeting/',
            'loginUrl' => 'http://10.17.0.24',
            'menus' => [
                    ['route' => 'default.dashboard', 'label' => 'Dashboard', 'icon' => 'home'], // sec, user
                    ['route' => 'admin', 'label' => 'Dashboard', 'icon' => 'home'], // admin
                    ['route' => 'appointments', 'label' => 'Appointments', 'icon' => 'calendar'], // same
                    ['route' => 'default.users', 'label' => 'Users', 'icon' => 'user'], // admin
                    ['route' => 'availability', 'label' => 'Availability', 'icon' => 'clock'], // user,
                    ['route' => 'meetings-approval', 'label' => 'Pending', 'icon' => 'inbox'], // registra
                    ['route' => 'venues', 'label' => 'venues', 'icon' => 'location-dot'], // registra
                    ['route' => 'events', 'label' => 'Events', 'icon' => 'calendar'], // regitra
                    ['route' => 'roles', 'label' => 'Roles', 'icon' => 'shield'], // admin
                    ['route' => 'permissions', 'label' => 'Permissions', 'icon' => 'gears'], // admin
                    ['route' => 'venue-management', 'label' => 'Spaces', 'icon' => 'location-dot'], // registra
            ],
            'mdm.admin.configs' => [
                
            ],
        ];
    }
    public function load($item)
    {
        $wrapper = $routes = [];
        $wrapper['aliases'] = $this->_aliases;
        $wrapper['modules'] = $this->_modules;
        $wrapper['tokens'] = $this->_tokens;
        $wrapper['params'] = $this->_params;
        foreach (new DirectoryIterator(dirname(__DIR__) . '/modules') as $index => $fileinfo) {
            if ($fileinfo->isDir() && !$fileinfo->isDot()) {
                $wrapper['aliases']['@' . $fileinfo->getFilename()] = '@app/modules/' . $fileinfo->getFilename();
                $wrapper['controllers'][] = $fileinfo->getFilename();
                $wrapper['migrationPaths'][] = '@' . $fileinfo->getFilename() . '/migrations';
                if ($fileinfo->getFilename() !== 'main') {
                    $wrapper['modules'][$fileinfo->getFilename()] = [
                        'class' => $fileinfo->getFilename() . '\\Module'
                    ];
                    if ($fileinfo->getFilename() !== 'dashboard') {
                        $dir = dirname(__DIR__) . "/modules/" . $fileinfo->getFilename() . "/routers";
                        foreach (glob("{$dir}/*.php") as $filename) {
                            $route = require($filename);
                            $routes = array_merge($routes, $route);
                        }
                        $wrapper['routes'] = $routes;
                    }
                }
            }
            $module  = '\\' . $fileinfo->getFilename() . '\\Module';
            if (property_exists($module, 'name')) {
                $wrapper['apiMenus'][] = ['title' => Yii::$app->getModule($fileinfo->getFilename())->name, 'url' => 'api/swagger', 'param' => ['mod' => $fileinfo->getFilename()]];
            }
        }
        if (!empty($wrapper['apiMenus'])) {
            $wrapper['apiMenus'] = [['title' => 'API Docs', 'icon' => 'code', 'submenus' => $wrapper['apiMenus']]];
            if ($_SERVER['ENVIRONMENT'] == 'dev') {
                $wrapper['apiMenus'][] = ['title' => 'Gii', 'icon' => 'code-fork', 'url' => '/gii'];
            }
        }
        return $wrapper[$item];
    }
    public function dbDriver($selector = null)
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
}

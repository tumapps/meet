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
            // '{id}' => '<id:\\d[\\d,]*>',
            '{id}' => '<id:[a-zA-Z0-9_\\-]+>',
            '{appointment_id}' => '<appointment_id:[a-zA-Z0-9_\-\/]+>',
            '{attendee_id}' => '<attendee_id:[a-zA-Z0-9_\-\/]+>',
            '{role_name}' => '<role_name:[a-zA-Z0-9_\-\/]+>',
            '{key}' => '<key:[a-zA-Z0-9_\-\/]+>',
            '{crypt_id}' => '<crypt_id:[a-zA-Z0-9\\-]+>',
            '{space_id}' => '<space_id:[a-zA-Z0-9_\\-]+>',
            '{date}' => '<date:[0-9]{4}-[0-9]{2}-[0-9]{2}>',
            '{time}' => '<time:[0-9]{2}:[0-9]{2}>',
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
            'secret_key' => '8213515c4730e7ca9ee8e8135a2f4e87180f8882a324299a057a8deafcd1d028',
            'adminEmail' => $_SERVER['SYSTEM_ADMIN_EMAIL'] ?? 'francisyuppie@gmail.com',
            'user.passwordResetTokenExpire' => 900,
            'allowedDomains' => '*',
            'passwordResetLink' => $_SERVER['FRONTEND_BASE_URL'] . $_SERVER['PASSWORD_RESET_LINK'],
            'confirmationLink' => $_SERVER['FRONTEND_BASE_URL'] . $_SERVER['CONFIRMATION_LINK'],
            'loginUrl' => $_SERVER['FRONTEND_BASE_URL'],

            'menus' => [
                ['title' => 'Dashboard', 'icon' => 'home', 'route' => 'home',   'roles' => ['user']],
                ['title' => 'Appointments', 'icon' => 'table', 'route' => 'appointments', 'roles' => ['user']],
                ['title' => 'Availability', 'icon' => 'calendar', 'route' => 'availability', 'roles' => ['user']],
                ['title' => 'Dashboard', 'icon' => 'home', 'route' => 'admin', 'roles' => ['su']],

                [
                    'title' => 'Spaces',
                    'icon' => 'building',
                    'route' => 'spaces',
                    'roles' => ['registrar'],
                    'children' => [
                        ['title' => 'Space Requests', 'icon' => 'message', 'route' => 'meetings-approval',  'roles' => ['registrar']],
                        ['title' => 'All Spaces', 'icon' => 'building', 'route' => 'venue-management', 'roles' => ['registrar']]
                    ]
                ],
                [
                    'title' => 'Events',
                    'icon' => 'calendar-days',
                    'route' => 'all-events',
                    'roles' => ['registrar'],
                    'children' => [
                        ['title' => 'Calendar', 'icon' => 'calendar', 'route' => 'eventscalendar', 'roles' => ['registrar']],
                        ['title' => 'All Events', 'icon' => 'calendar', 'route' => 'all-events', 'roles' => ['registrar']]
                    ]
                ],
                [
                    'title' => 'IAM',
                    'icon' => 'shield-halved',
                    'route' => 'iam',
                    'roles' => ['su'],
                    'children' => [
                        ['title' => 'Users', 'icon' => 'users', 'route' => 'default.users', 'roles' => ['su']],
                        ['title' => 'Roles', 'icon' => 'shield', 'route' => 'roles', 'roles' => ['su']],
                        ['title' => 'Permissions', 'icon' => 'lock', 'route' => 'permissions','roles' => ['su']]
                    ]
                ],
                ['title' => 'Settings', 'icon' => 'gear','route' => 'settings', 'roles' => ['su']]
            ],

            'africasTalkingApi' => $_SERVER['AT_API_KEY'],
            'africasTalkingUserName' => $_SERVER['AT_USERNAME'],
            'mdm.admin.configs' => [],
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

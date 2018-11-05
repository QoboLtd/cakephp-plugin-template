<?php
use Cake\Core\Configure;
use Cake\Filesystem\Folder;

//$pluginName = 'Foobar';
if (empty($pluginName)) {
    throw new \RuntimeException('Plugin name is not configured');
}

require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';

if (! defined('DS')) {
    define('DS', DIRECTORY_SEPARATOR);
}

define('ROOT', dirname(__DIR__));
define('APP_DIR', 'src');

define('TMP', ROOT . DS . 'tmp' . DS);
define('LOGS', TMP . 'logs' . DS);
define('CACHE', TMP . 'cache' . DS);
define('SESSIONS', TMP . 'sessions' . DS);

define('CAKE_CORE_INCLUDE_PATH', ROOT . DS . 'vendor' . DS . 'cakephp' . DS . 'cakephp');
define('CORE_PATH', CAKE_CORE_INCLUDE_PATH . DS);
define('CAKE', CORE_PATH . 'src' . DS);
define('CORE_TESTS', ROOT . DS . 'tests' . DS);
define('CORE_TEST_CASES', CORE_TESTS . 'TestCase');
define('TEST_APP', CORE_TESTS . 'test_app' . DS);

// Point app constants to the test app.
define('APP', TEST_APP . APP_DIR . DS);
define('WWW_ROOT', TEST_APP . 'webroot' . DS);
define('CONFIG', TEST_APP . 'config' . DS);


Configure::write('App', [
    'namespace' => $pluginName . '\Test\App',
    'paths' => [
        'templates' => [
            APP . 'Template' . DS
        ]
    ]
]);
Configure::write('debug', true);

//@codingStandardsIgnoreStart
@mkdir(CACHE);
@mkdir(CACHE . 'models');
@mkdir(CACHE . 'persistent');
@mkdir(CACHE . 'views');
@mkdir(LOGS);
@mkdir(SESSIONS);
//@codingStandardsIgnoreEnd

require_once CORE_PATH . 'config' . DS . 'bootstrap.php';

date_default_timezone_set('UTC');
mb_internal_encoding('UTF-8');

$cache = [
    'default' => [
        'engine' => 'File'
    ],
    '_cake_core_' => [
        'className' => 'File',
        'prefix' => strtolower($pluginName) . '_myapp_cake_core_',
        'path' => CACHE . 'persistent/',
        'serialize' => true,
        'duration' => '+10 seconds'
    ],
    '_cake_model_' => [
        'className' => 'File',
        'prefix' => strtolower($pluginName) . '_my_app_cake_model_',
        'path' => CACHE . 'models/',
        'serialize' => 'File',
        'duration' => '+10 seconds'
    ]
];

Cake\Cache\Cache::setConfig($cache);
Cake\Core\Configure::write('Session', [
    'defaults' => 'php'
]);

// Ensure default test connection is defined
if (!getenv('db_dsn')) {
    putenv('db_dsn=sqlite:///:memory:');
}

Cake\Datasource\ConnectionManager::setConfig('default', [
    'url' => getenv('db_dsn'),
    'quoteIdentifiers' => true,
    'timezone' => 'UTC'
]);

Cake\Datasource\ConnectionManager::setConfig('test', [
    'url' => getenv('db_dsn'),
    'quoteIdentifiers' => true,
    'timezone' => 'UTC'
]);

// Alias AppController to the test App
class_alias($pluginName . '\Test\App\Controller\AppController', 'App\Controller\AppController');
// If plugin has routes.php/bootstrap.php then load them, otherwise don't.
$loadPluginRoutes = file_exists(ROOT . DS . 'config' . DS . 'routes.php');
$loadPluginBootstrap = file_exists(ROOT . DS . 'config' . DS . 'bootstrap.php');
Cake\Core\Plugin::load($pluginName, ['path' => ROOT . DS, 'autoload' => true, 'routes' => $loadPluginRoutes, 'bootstrap' => $loadPluginBootstrap]);

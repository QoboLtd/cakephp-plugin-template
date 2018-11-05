<?php
use Cake\Cache\Cache;
use Cake\Chronos\Chronos;
use Cake\Core\Configure;
use Cake\Core\Plugin;
use Cake\Datasource\ConnectionManager;
use Cake\Log\Engine\FileLog;
use Cake\Log\Log;

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

Configure::write('debug', true);
Configure::write('App', [
    'namespace' => 'App',
    'encoding' => 'UTF-8',
    'base' => false,
    'baseUrl' => false,
    'dir' => APP_DIR,
    'webroot' => 'webroot',
    'wwwRoot' => WWW_ROOT,
    'fullBaseUrl' => 'http://localhost',
    'imageBaseUrl' => 'img/',
    'jsBaseUrl' => 'js/',
    'cssBaseUrl' => 'css/',
    'paths' => [
        'templates' => [APP . 'Template' . DS]
    ]
]);

Cache::setConfig([
    '_cake_core_' => [
        'className' => 'File',
        'prefix' => strtolower($pluginName) . '_test_app_cake_core_',
        'serialize' => true
    ],
    '_cake_model_' => [
        'className' => 'File',
        'prefix' => strtolower($pluginName) . '_test_app_cake_model_',
        'serialize' => true
    ]
]);

// Ensure default test connection is defined
if (! getenv('db_dsn')) {
    putenv('db_dsn=sqlite:///:memory:');
}

ConnectionManager::setConfig('test', [
    'url' => getenv('db_dsn'),
    'quoteIdentifiers' => true,
    'timezone' => 'UTC'
]);

Configure::write('Session', [
    'defaults' => 'php'
]);

Log::setConfig([
    'debug' => [
        'engine' => FileLog::class,
        'levels' => ['notice', 'info', 'debug'],
        'file' => 'debug',
        'path' => LOGS
    ],
    'error' => [
        'engine' => FileLog::class,
        'levels' => ['warning', 'error', 'critical', 'alert', 'emergency'],
        'file' => 'error',
        'path' => LOGS
    ]
]);

Chronos::setTestNow(Chronos::now());

ini_set('intl.default_locale', 'en_US');
ini_set('session.gc_divisor', '1');

loadPHPUnitAliases();

// Fixate sessionid early on, as php7.2+
// does not allow the sessionid to be set after stdout
// has been written to.
session_id('cli');

// if plugin has routes.php/bootstrap.php then load them.
$loadPluginRoutes = file_exists(ROOT . DS . 'config' . DS . 'routes.php');
$loadPluginBootstrap = file_exists(ROOT . DS . 'config' . DS . 'bootstrap.php');
Plugin::load($pluginName, ['path' => ROOT . DS, 'routes' => $loadPluginRoutes, 'bootstrap' => $loadPluginBootstrap]);

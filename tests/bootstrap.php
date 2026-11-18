<?php
declare(strict_types=1);

/**
 * CakePHP(tm) : Rapid Development Framework (https://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 * @link      https://cakephp.org CakePHP(tm) Project
 * @since     3.0.0
 * @license   https://opensource.org/licenses/mit-license.php MIT License
 */

use Cake\Chronos\Chronos;
use Cake\Core\Configure;
use Cake\Database\Connection;
use Cake\Database\Driver\Mysql;
use Cake\Datasource\ConnectionManager;
use Cake\TestSuite\ConnectionHelper;
use Migrations\TestSuite\Migrator;

/**
 * Test runner bootstrap.
 *
 * Add additional configuration/setup your application needs when running
 * unit tests in this file.
 */
require dirname(__DIR__) . '/vendor/autoload.php';

require dirname(__DIR__) . '/config/bootstrap.php';

$databaseTestUrl = getenv('DATABASE_TEST_URL');
$databaseTestUrlParts = is_string($databaseTestUrl) ? parse_url($databaseTestUrl) : false;
$databaseTestScheme = is_array($databaseTestUrlParts)
    ? strtolower((string)($databaseTestUrlParts['scheme'] ?? ''))
    : '';
$databaseTestName = is_array($databaseTestUrlParts)
    ? ltrim((string)($databaseTestUrlParts['path'] ?? ''), '/')
    : '';

if (
    !is_string($databaseTestUrl)
    || $databaseTestUrl === ''
    || !is_array($databaseTestUrlParts)
    || !in_array($databaseTestScheme, ['mysql', 'mariadb'], true)
    || empty($databaseTestUrlParts['host'])
    || $databaseTestName !== 'daetf2_test'
) {
    throw new RuntimeException('DATABASE_TEST_URL must target MySQL/MariaDB database daetf2_test.');
}

// CakePHP's DSN map uses the MySQL driver for MariaDB servers as well.
$connectionUrl = preg_replace('/^mariadb:/i', 'mysql:', $databaseTestUrl, 1);
try {
    $parsedTestDatasource = ConnectionManager::parseDsn($connectionUrl);
} catch (InvalidArgumentException) {
    throw new RuntimeException('DATABASE_TEST_URL is invalid.');
}
$parsedTestHost = trim((string)($parsedTestDatasource['host'] ?? ''), '[]');
$urlTestHost = trim((string)$databaseTestUrlParts['host'], '[]');
if (
    ($parsedTestDatasource['database'] ?? null) !== 'daetf2_test'
    || $parsedTestHost !== $urlTestHost
    || array_key_exists('read', $parsedTestDatasource)
    || array_key_exists('write', $parsedTestDatasource)
    || array_key_exists('unix_socket', $parsedTestDatasource)
) {
    throw new RuntimeException('DATABASE_TEST_URL resolves to an unsupported test datasource.');
}

$parsedTestDatasource['className'] = Connection::class;
$parsedTestDatasource['driver'] = Mysql::class;
ConnectionManager::drop('test');
ConnectionManager::setConfig('test', $parsedTestDatasource);

$boundTestDatasource = ConnectionManager::getConfig('test');
if (
    !is_array($boundTestDatasource)
    || ($boundTestDatasource['database'] ?? null) !== 'daetf2_test'
    || ($boundTestDatasource['driver'] ?? null) !== Mysql::class
) {
    throw new RuntimeException('The test datasource is not bound to daetf2_test.');
}

try {
    $selectedTestDatabase = ConnectionManager::get('test')
        ->execute('SELECT DATABASE()')
        ->fetchColumn(0);
} catch (Throwable) {
    throw new RuntimeException('Unable to verify the configured test datasource.');
}
if ($selectedTestDatabase !== 'daetf2_test') {
    throw new RuntimeException('The test datasource is not connected to daetf2_test.');
}

if (empty($_SERVER['HTTP_HOST']) && !Configure::read('App.fullBaseUrl')) {
    Configure::write('App.fullBaseUrl', 'http://localhost');
}

// Fixate now to avoid one-second-leap-issues
Chronos::setTestNow(Chronos::now());

// Fixate sessionid early on, as php7.2+
// does not allow the sessionid to be set after stdout
// has been written to.
session_id('cli');

// Connection aliasing needs to happen before migrations are run.
// Otherwise, table objects inside migrations would use the default datasource
ConnectionHelper::addTestAliases();

// Use migrations to build test database schema.
//
// Will rebuild the database if the migration state differs
// from the migration history in files.
//
// If you are not using CakePHP's migrations you can
// hook into your migration tool of choice here or
// load schema from a SQL dump file with
// use Cake\TestSuite\Fixture\SchemaLoader;
// (new SchemaLoader())->loadSqlFiles('./tests/schema.sql', 'test');

(new Migrator())->run();

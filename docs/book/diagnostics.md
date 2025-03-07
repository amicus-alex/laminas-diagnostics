# Diagnostic Checks

laminas/laminas-diagnostics provides several "just add water" checks you can
use straight away.

The following built-in tests are currently available:

## ApcFragmentation

Make sure that [APC memory fragmentation level](https://www.php.net/apc/) is below a
given threshold:

```php
<?php
use Laminas\Diagnostics\Check\ApcFragmentation;

// Display a warning with fragmentation > 50% and failure when above 90%
$fragmentation = new ApcFragmentation(50, 90);
```

## ApcMemory

Check [APC memory usage percent](https://www.php.net/apc/) and make sure it's below a
given threshold.

```php
<?php
use Laminas\Diagnostics\Check\ApcMemory;

// Display a warning with memory usage is above 70% and a failure above 90%
$checkFreeMemory = new ApcMemory(70, 90);
```

## Callback

Run a function (callback) and use its return value as the result:

```php
<?php
use Laminas\Diagnostics\Check\Callback;
use Laminas\Diagnostics\Result\Success;
use Laminas\Diagnostics\Result\Failure;

$checkDbFile = new Callback(function() {
    $path = __DIR__ . '/data/db.sqlite';
    if (is_file($path) && is_readable($path) && filesize($path)) {
        return new Success('Db file is ok');
    }

    return new Failure('There is something wrong with the db file');
});
```

> ### Callback signature
>
> The callback must return either a `boolean` (true for success, false for
> failure), or a valid instance of
> [ResultInterface](https://github.com/laminas/laminas-diagnostics/tree/master/src/Result/ResultInterface.php).
> All other objects will result in an exception, and scalars (i.e. a string) will
> be interpreted as warnings.

## ClassExists

Check if a class (or an array of classes) exists. For example:

```php
<?php
use Laminas\Diagnostics\Check\ClassExists;

$checkLuaClass = new ClassExists('Lua');
$checkRbacClasses = new ClassExists([
    'ZfcRbac\Module',
    'ZfcRbac\Controller\Plugin\IsGranted'
]);
```

## CouchDBCheck

Check if a connection to a given CouchDB server is possible.

```php
<?php
use Laminas\Diagnostics\Check\CouchDBCheck;

// Simple check without credentials
$couchDbNoCredentials = new CouchDBCheck(['url' => 'http://127.0.0.1:5984']);

// Check with user and password
$couchDbSettings = [
    'protocol' => 'http',
    'host'     => '127.0.0.1',
    'port'     => '5984',
    'username' => 'my_username',
    'password' => 'I0z&+oFP^FHdd9%i',
    'dbname'   => 'my_database',
];
$couchDbWithCredentials = new CouchDBCheck($couchDbSettings);
```

## CpuPerformance

Benchmark CPU performance and return failure if it is below the given ratio. The
baseline for performance calculation is the speed of an Amazon EC2 Micro Instance
(Q1 2013). You can specify the expected performance for the test, where a ratio
of `1.0` (one) means at least the speed of EC2 Micro Instance. A ratio of `2`
would mean "at least double the performance of EC2 Micro Instance" and a
fraction of `0.5` means "at least half the performance of Micro Instance".

The following check will test if current server has at least half the CPU power
of EC2 Micro Instance:

```php
<?php
use Laminas\Diagnostics\Check\CpuPerformance;

$checkMinCPUSpeed = new CpuPerformance(0.5); // at least 50% of EC2 micro instance
```

## DirReadable

Check if a given path (or array of paths) points to a directory and it is
readable.

```php
<?php
use Laminas\Diagnostics\Check\DirReadable;

$checkPublic = new DirReadable('public/');
$checkAssets = new DirReadable([
    __DIR__ . '/assets/img',
    __DIR__ . '/assets/js',
]);
```

## DirWritable

Check if a given path (or array of paths) points to a directory and if it can be
written to.

```php
<?php
use Laminas\Diagnostics\Check\DirWritable;

$checkTemporary = new DirWritable('/tmp');
$checkAssets    = new DirWritable([
    __DIR__ . '/assets/customImages',
    __DIR__ . '/assets/customJs',
    __DIR__ . '/assets/uploads',
]);
```

## DiskFree

Check if there is enough remaining free disk space.

The first parameter is the minimum disk space, which can be supplied as an
integer (in bytes, e.g. `1024`) or as a string with a multiplier (IEC, SI or
Jedec; e.g.  `"150MB"`). The second parameter is the path to check; on \*NIX
systems it is an ordinary path (e.g. `/home`), while on Windows systems it is a
drive letter (e.g.  `"C:"`).

```php
<?php
use Laminas\Diagnostics\Check\DiskFree;

$tempHasAtLeast100Megs  = new DiskFree('100MB', '/tmp');
$homeHasAtLeast1TB      = new DiskFree('1TiB',  '/home');
$dataHasAtLeast900Bytes = new DiskFree(900, __DIR__ . '/data/');
```

## DiskUsage

Check if the disk usage is below warning/critical percent thresholds.

The first parameter is the warning threshold, which can be supplied as an
integer (in percent, e.g. `80`). The second parameter is the critical
threshold, which is also supplied as an integer (in percent, e.g. `90`). The
third parameter is the disk path to check; on \*NIX systems it is an ordinary
path (e.g. `/tmp`), while on Windows systems it is a drive letter (e.g.  `C:`).

```php
<?php
use Laminas\Diagnostics\Check\DiskUsage;

$diskUsageNix = new DiskUsage(80, 90, '/tmp');
$diskUsageWin = new DiskUsage(80, 90,  'C:');
```

## ExtensionLoaded

Check if a PHP extension (or an array of extensions) is currently loaded.

```php
<?php
use Laminas\Diagnostics\Check\ExtensionLoaded;

$checkMbstring    = new ExtensionLoaded('mbstring');
$checkCompression = new ExtensionLoaded([
    'rar',
    'bzip2',
    'zip',
]);
```

## HttpService

Attempt connection to a given HTTP host or IP address and try to load a web
page. The check also supports checking response codes and page contents.

```php
<?php
use Laminas\Diagnostics\Check\HttpService;

// Try to connect to google.com
$checkGoogle = new HttpService('www.google.com');

// Check port 8080 on localhost
$checkLocal = new HttpService('127.0.0.1', 8080);

// Check that the page exists (response code must equal 200)
$checkPage = new HttpService('www.example.com', 80, '/some/page.html', 200);

// Check page content
$checkPageContent = new HttpService(
    'www.example.com',
    80,
    '/some/page.html',
    200,
    '<title>Hello World</title>'
);
```

## GuzzleHttpService

Attempt connection to a given HTTP host or IP address and try to load a web page
using [Guzzle](https://docs.guzzlephp.org/en/stable/). The check also supports
checking response codes and page contents.

The constructor signature of the `GuzzleHttpService` is as follows:

```php
/**
 * @param string|Psr\Http\Message\RequestInterface|GuzzleHttp\Message\RequestInterface $requestOrUrl
 *     The absolute url to check, or a fully-formed request instance.
 * @param array $headers An array of headers used to create the request
 * @param array $options An array of guzzle options to use when sending the request
 * @param int $statusCode The response status code to check
 * @param null $content The response content to check
 * @param null|GuzzleHttp\ClientInterface $guzzle Instance of guzzle to use
 * @param string $method The method of the request
 * @param mixed $body The body of the request (used for POST, PUT and DELETE requests)
 * @throws InvalidArgumentException
 */
public function __construct(
    $requestOrUrl,
    array $headers = [],
    array $options = [],
    $statusCode = 200,
    $content = null,
    $guzzle = null,
    $method = 'GET',
    $body = null
)
```

Examples:

```php
<?php
use Laminas\Diagnostics\Check\GuzzleHttpService;

// Try to connect to google.com
$checkGoogle = new GuzzleHttpService('www.google.com');

// Check port 8080 on localhost
$checkLocal = new GuzzleHttpService('127.0.0.1:8080');

// Check that the page exists (response code must equal 200)
$checkPage = new GuzzleHttpService('www.example.com/some/page.html');

// Check page content
$checkPageContent = new GuzzleHttpService(
    'www.example.com/some/page.html',
    [],
    [],
    200,
    '<title>Hello World</title>'
);

// Check that the post request returns the content
$checkPageContent = new GuzzleHttpService(
    'www.example.com/user/update',
    [],
    [],
    200,
    '{"status":"success"}',
    'POST',
    ['post_field' => 'post_value']
);
```

You can send JSON data by either providing a `Content-Type` header that includes
a JSON content type, or creating a request instance with JSON content:

```php
// Send page content
$checkPageContent = new GuzzleHttpService(
    'api.example.com/ping',
    ['Content-Type' => 'application/json'],
    [],
    200,
    null,
    null,
    'POST',
    ['ping' => microtime()]
);

// Assuming Guzzle 6:
use GuzzleHttp\Psr7\Request;
$request = new Request(
    'POST',
    'http://api.example.com/ping',
    ['Content-Type' => 'application/json'],
    json_encode(['ping' => microtime()])
);
$checkPageContent = new GuzzleHttpService($request);
```

## Memcache

Attempt to connect to given Memcache server.

```php
<?php
use Laminas\Diagnostics\Check\Memcache;

$checkLocal  = new Memcache('127.0.0.1'); // default port
$checkBackup = new Memcache('10.0.30.40', 11212);
```

## Memcached

Attempt to connect to the given Memcached server.

```php
<?php
use Laminas\Diagnostics\Check\Memcached;

$checkLocal  = new Memcached('127.0.0.1'); // default port
$checkBackup = new Memcached('10.0.30.40', 11212);
```

## MongoDb

Check if a connection to a given MongoDb server is possible.

```php
<?php
use Laminas\Diagnostics\Check\Mongo;

$mongoCheck = new Mongo('mongodb://127.0.0.1:27017');
// and with user/password
$mongoCheck = new Mongo('mongodb://user:password@127.0.0.1:27017');
```

## OpCacheMemory

Check [OPcache memory usage percent](https://www.php.net/opcache) and make sure it's below a
given threshold.

```php
<?php
use Laminas\Diagnostics\Check\OpCacheMemory;

// Display a warning with memory usage is above 70% and a failure above 90%
$opCacheMemory = new OpCacheMemory(70, 90);
```

## PDOCheck

Check if a connection to a given database server is possible.

```php
<?php
use Laminas\Diagnostics\Check\PDOCheck;

$pdoMySql = new PDOCheck('mysql://localhost/my_database', 'my_username', 'oFPZc!W&zV>,YCrz');
$pdoSqlite = new PDOCheck('sqlite:example.db', '', '');
```

## PhpVersion

Check if the current PHP version matches the given requirement. The test accepts
2 parameters: baseline version and optional
[comparison operator](https://www.php.net/version_compare).

```php
<?php
use Laminas\Diagnostics\Check\PhpVersion;

$require545orNewer  = new PhpVersion('5.4.5');
$rejectBetaVersions = new PhpVersion('5.5.0', '<');
```

## PhpFlag

Make sure that the provided PHP flag(s) is enabled or disabled (as defined in
`php.ini`). You can use this test to alert the user about unsafe or
behavior-changing PHP settings.

```php
<?php
use Laminas\Diagnostics\Check\PhpFlag;

// This check will fail if use_only_cookies is not enabled
$sessionOnlyUsesCookies = new PhpFlag('session.use_only_cookies', true);

// This check will fail if safe_mode has been enabled
$noSafeMode = new PhpFlag('safe_mode', false);

// The following will fail if any of the flags is enabled
$check = new PhpFlag([
    'expose_php',
    'ignore_user_abort',
    'html_errors',
], false);
```

## ProcessRunning

Check if a given unix process is running. This check supports PIDs and process
names.

```php
<?php
use Laminas\Diagnostics\Check\ProcessRunning;

$checkApache = new ProcessRunning('httpd');
$checkProcess1000 = new ProcessRunning(1000);
```

## RabbitMQ

Validate that a RabbitMQ service is running.

```php
<?php
use Laminas\Diagnostics\Check\RabbitMQ;

$rabbitMQCheck = new RabbitMQ('localhost', 5672, 'guest', 'guest', '/');
```

## Redis

Validate that a Redis service is running.

```php
<?php
use Laminas\Diagnostics\Check\Redis;

$redisCheck = new Redis('localhost', 6379, 'secret');
```

## SecurityAdvisory

Run a security check of libraries locally installed by
[Composer](https://getcomposer.org/) against [SensioLabs Security Advisory
database](https://security.symfony.com/), and warn about potential
security vulnerabilities.

```php
<?php
use Laminas\Diagnostics\Check\SecurityAdvisory;

// Warn about any packages that might have security vulnerabilities
// and require updating
$security = new SecurityAdvisory();

// Check another composer.lock
$security = new SecurityAdvisory('/var/www/project/composer.lock');
```

## StreamWrapperExists

Check if a given stream wrapper (or an array of wrappers) is available. For
example:

```php
<?php
use Laminas\Diagnostics\Check\StreamWrapperExists;

$checkOGGStream   = new StreamWrapperExists('ogg');
$checkCompression = new StreamWrapperExists([
    'zlib',
    'bzip2',
    'zip',
]);
```

## DoctrineMigration

Make sure all migrations are applied:

```php
<?php
use Doctrine\Migrations\Configuration\Configuration;
use Doctrine\ORM\EntityManager;
use Laminas\Diagnostics\Check\DoctrineMigration;

$em = EntityManager::create(/* config */);
$migrationConfig = new Configuration($em);
$check = new DoctrineMigration($migrationConfig);
```

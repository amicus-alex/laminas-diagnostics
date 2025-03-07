<?php

namespace Laminas\Diagnostics\Check;

use Exception;
use InvalidArgumentException;
use Laminas\Diagnostics\Result\Failure;
use Laminas\Diagnostics\Result\ResultInterface;
use Laminas\Diagnostics\Result\Success;
use Memcache as MemcacheService;

use function class_exists;
use function gettype;
use function is_array;
use function is_string;
use function microtime;
use function sprintf;

/**
 * Check if MemCache extension is loaded and given server is reachable.
 */
class Memcache extends AbstractCheck
{
    /** @var string */
    protected $host;

    /** @var int */
    protected $port;

    /**
     * @param string $host
     * @param int    $port
     * @throws InvalidArgumentException
     */
    public function __construct($host = '127.0.0.1', $port = 11211)
    {
        if (! is_string($host)) {
            throw new InvalidArgumentException(sprintf(
                'Cannot use %s as host - expecting a string',
                gettype($host)
            ));
        }

        $port = (int) $port;
        if ($port < 1) {
            throw new InvalidArgumentException(sprintf(
                'Invalid port number %d - expecting a positive integer',
                $port
            ));
        }

        $this->host = $host;
        $this->port = $port;
    }

    /**
     * @see CheckInterface::check()
     *
     * @return ResultInterface
     */
    public function check()
    {
        if (! class_exists('Memcache', false)) {
            return new Failure('Memcache extension is not loaded');
        }

        try {
            $memcache = new MemcacheService();
            $memcache->addServer($this->host, $this->port);

            $startTime = microtime(true);
            /** @var false|array<string, false|array<string, int|string>> $stats */
            $stats        = @$memcache->getExtendedStats();
            $responseTime = microtime(true) - $startTime;

            $authority   = sprintf('%s:%d', $this->host, $this->port);
            $serviceData = null;

            if (
                ! is_array($stats)
                || ! isset($stats[$authority])
                || false === $stats[$authority]
            ) {
                // Attempt a connection to make sure that the server is really down
                if (! @$memcache->connect($this->host, $this->port)) {
                    return new Failure(sprintf(
                        'No memcache server running at host %s on port %s',
                        $this->host,
                        $this->port
                    ));
                }
            } else {
                $serviceData = [
                    "responseTime" => $responseTime,
                    "connections"  => (int) $stats[$authority]['curr_connections'],
                    "uptime"       => (int) $stats[$authority]['uptime'],
                ];
            }
        } catch (Exception $e) {
            return new Failure($e->getMessage());
        }

        return new Success(sprintf(
            'Memcache server running at host %s on port %s',
            $this->host,
            $this->port
        ), $serviceData);
    }
}

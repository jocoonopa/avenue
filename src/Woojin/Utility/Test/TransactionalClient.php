<?php
namespace Woojin\Utility\Test;

use Symfony\Bundle\FrameworkBundle\Client as BaseClient;

class TransactionalClient extends BaseClient
{
    static protected $connection;
    protected $requested = false;

    protected function doRequest($request)
    {
        if ($this->requested) {
            $this->kernel->shutdown();
            $this->kernel->boot();
        }

        $this->injectConnection();
        self::$connection->beginTransaction();
        $response = $this->kernel->handle($request);

        self::$connection->rollback();
        self::$connection = NULL;

        return $response;
    }

    protected function injectConnection()
    {
        if (null === self::$connection) {
            self::$connection = $this->getContainer()->get('doctrine.dbal.default_connection');
        }
    }
}
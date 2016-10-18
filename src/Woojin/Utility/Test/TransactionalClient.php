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

        if ($this->enableTransaction($request)) {
            self::$connection->beginTransaction();
        }

        $response = $this->kernel->handle($request);

        if ($this->enableTransaction($request)) {
            self::$connection->rollback();
        }

        self::$connection = NULL;

        return $response;
    }

    protected function injectConnection()
    {
        if (null === self::$connection) {
            self::$connection = $this->getContainer()->get('doctrine.dbal.default_connection');
        }
    }

    protected function enableTransaction($request)
    {
        return false === strpos($request->getQueryString(), '_disableTransaction=1');
    }
}

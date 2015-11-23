<?php

namespace Woojin\Utility\YahooApi\Adapter;

use Doctrine\ORM\EntityManager;

use Woojin\Utility\YahooApi\Client;
use Woojin\Utility\Avenue\Avenue;

class Syncer
{
    protected $em;
    protected $client;
    protected $response;

    public function __construct(EntityManager $em, Client $client)
    {
        $this->em = $em;
        $this->client = $client;
    }

    public function hasTask($input)
    {
        if (is_array($input)) {
            foreach ($input as $product) {
                if ($product->getYahooId()) {
                    return true;
                }
            }

            return false;
        } else {
            return $input->getYahooId();
        }
    }

    public function delete($input)
    {
        if (!$this->hasTask($input)) {
            return false;
        }

        $response = $this->client->offline($input);

        if (empty($response)) {
            throw new \Exception('yahoo delete error');
        }
        
        if ('ok' === $response->Response->Status) {
            $response = $this->client->delete($input);

            $yahooIds = array();

            if (isset($response->Response->SuccessList->ProductId)) {
                foreach ($response->Response->SuccessList->ProductId as $product) {
                    $yahooIds[] = $product->Id;
                }
            }
        }

        if (empty($yahooIds)) {
            return false;
        }

        $qb = $this->em->createQueryBuilder();

        $products = $qb->select('g')
            ->from('WoojinGoodsBundle:GoodsPassport', 'g')
            ->where($qb->expr()->in('g.yahooId', $yahooIds))
            ->getQuery()
            ->getResult()
        ;

        foreach ($products as $product) {
            $product->setYahooId(null);
            $this->em->persist($product);
        }

        $this->em->flush();

        return true;
    }
}
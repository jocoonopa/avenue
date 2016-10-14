<?php

namespace Woojin\ApiBundle\Controller;

use JMS\Serializer\SerializerBuilder;
use Woojin\Utility\Handler\ResponseHandler;

trait HelperTrait
{
    private function _getResponse($data, $_format)
    {
        $serializer = SerializerBuilder::create()->build();
        $jsonResponse = $serializer->serialize($data, $_format);
        $responseHandler = new ResponseHandler;

        return $responseHandler->getResponse($jsonResponse, $_format);
    }

    /**
     * Execute registed validaters，if exists error it return response with error msg,
     * otherwise return null
     *
     * @param  array  $validaters
     * @return array
     */
    protected function execValidaters(array $validaters)
    {
        foreach ($validaters as $methodName => $regists) {
            if (!call_user_func_array(array($this, $methodName), $regists['params'])) {
                return $regists['response'];
            }
        }

        return array();
    }

    private function _isNotNull($val)
    {
        return NULL !== $val;
    }
}

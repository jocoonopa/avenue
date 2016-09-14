<?php

namespace Woojin\Utility\Handler;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ResponseHandler
{
    public $response;
    
    public function getETag(Request $request, $res, $_format = 'json')
    {
        $response = new Response($res);
        $response->setETag(md5($response->getContent()));
        $response->setPublic(); // make sure the response is public/cacheable
        $response->headers->set('Content-Type', $this->getContentType($_format));
        $response->isNotModified($request);

        return $response;
    }

    public function getNoncached(Request $request, $res, $_format = 'json')
    {
        $response = new Response($res);
        $response->headers->set('Content-Type', $this->getContentType($_format));

        return $response;
    }

    public function getResponse($res, $_format)
    {
        $response = new Response($res);
        $response->headers->set('Content-Type', $this->getContentType($_format));

        return $response;
    }

    protected function getContentType($_format)
    {
        $contentType = null;

        switch ($_format)
        {
            case 'xml':
                $contentType = 'application/xml';
                break;

            case 'json':
                $contentType =  'application/json';
                break;

            case 'html':
            default:
                $contentType = 'text/html';
                break;
        }

        return $contentType;
    }

    public function getConvertResponse($entity, $_format = 'json')
    {
        $serializer = \JMS\Serializer\SerializerBuilder::create()->build();
        
        $convert = $serializer->serialize($entity, $_format);

        return $this->getResponse($convert, $_format);
    }
}
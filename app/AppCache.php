<?php

require_once __DIR__.'/AppKernel.php';

use Symfony\Bundle\FrameworkBundle\HttpCache\HttpCache;
use Symfony\Component\HttpFoundation\Request;

class AppCache extends HttpCache
{
    // protected function invalidate(Request $request, $catch = false) 
    // {
    //     if ($request->getMethod() == 'POST') {
    //         $request->setMethod($request->request->get('_method', 'POST'));
    //     }

    //     return parent::invalidate($request, $catch);
    // }
}

<?php

namespace Woojin\StoreBundle;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;

class FilesManager
{
	protected $context;
	protected $em;

    public function __construct(\Doctrine\ORM\EntityManager $em, TokenStorage $context)
    {
        $this->context = $context;
        $this->em = $em;
    }

	public function deleteDirFiles($path)
	{
		$files = glob( $path ); // get all file names

        if (!$files || !is_array($files)) {
            return new Response('ok');
        }
        
        foreach ($files as $file) // iterate files
            if (is_file($file))
                unlink($file); // delete file
        
		return new Response('ok');
	}
}
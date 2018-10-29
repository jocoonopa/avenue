<?php

use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Config\Loader\LoaderInterface;

class AppKernel extends Kernel
{
    public function registerBundles()
    {
        $bundles = array(
            new Symfony\Bundle\FrameworkBundle\FrameworkBundle(),
            new Symfony\Bundle\SecurityBundle\SecurityBundle(),
            new Symfony\Bundle\TwigBundle\TwigBundle(),
            new Symfony\Bundle\MonologBundle\MonologBundle(),
            new Symfony\Bundle\SwiftmailerBundle\SwiftmailerBundle(),
            new Symfony\Bundle\AsseticBundle\AsseticBundle(),
            
            new Doctrine\Bundle\DoctrineBundle\DoctrineBundle(),
            
            new Sensio\Bundle\FrameworkExtraBundle\SensioFrameworkExtraBundle(),
            
            new Woojin\GoodsBundle\WoojinGoodsBundle(),
            new Woojin\StoreBundle\WoojinStoreBundle(),
            new Woojin\BaseBundle\WoojinBaseBundle(),
            new Woojin\SecurityBundle\WoojinSecurityBundle(),
            new Woojin\OrderBundle\WoojinOrderBundle(),
            new Woojin\UserBundle\WoojinUserBundle(),   
            new Woojin\AgencyBundle\WoojinAgencyBundle(),
            new Woojin\FrontBundle\WoojinFrontBundle(),
            new Woojin\ApiBundle\WoojinApiBundle(),
            
            new FOS\JsRoutingBundle\FOSJsRoutingBundle(),
            //new FOS\RestBundle\FOSRestBundle(),
            
            new Liuggio\ExcelBundle\LiuggioExcelBundle(),

            new Lsw\ApiCallerBundle\LswApiCallerBundle(),
            
            //new ServerGrove\Bundle\ShellAliasBundle\ServerGroveShellAliasBundle(),
            
            new Nelmio\ApiDocBundle\NelmioApiDocBundle(),
            
            // new Genemu\Bundle\FormBundle\GenemuFormBundle(),
            
            new JMS\SerializerBundle\JMSSerializerBundle(),

            new Gregwar\CaptchaBundle\GregwarCaptchaBundle(),
            new Gregwar\ImageBundle\GregwarImageBundle(),

            new SunCat\MobileDetectBundle\MobileDetectBundle()
        );

        if (in_array($this->getEnvironment(), array('dev', 'test'))) {
            $bundles[] = new Symfony\Bundle\WebProfilerBundle\WebProfilerBundle();
            $bundles[] = new Sensio\Bundle\DistributionBundle\SensioDistributionBundle();
            $bundles[] = new Sensio\Bundle\GeneratorBundle\SensioGeneratorBundle();
        }

        return $bundles;
    }

    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $loader->load(__DIR__.'/config/config_'.$this->getEnvironment().'.yml');
    }
}

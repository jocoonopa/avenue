<?php 

namespace Woojin\Utility\Resolver;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\DependencyInjection\ContainerInterface;
use SunCat\MobileDetectBundle\DeviceDetector\MobileDetector;

class DeviceResolver
{
    protected $session;

    protected $dectector;

    public function __construct(Session $session, MobileDetector $dectector)
    {
        $this->session = $session;

        $this->dectector = $dectector;
    }

    public function isM()
    {
        return !$this->isForcedDesktop() && $this->isMobileDevice();
    }

    public function setForce(Request $request)
    {
        $force = $request->query->get('device');

        switch ($force)
        {
            case 'm':
                $this->session->set('isForcedDesktop', false);
                break;

            case 'd':
                $this->session->set('isForcedDesktop', true);
                break;

            default:
                break;
        }

        return $this;
    }

    protected function isMobileDevice()
    {
        return ($this->dectector->isMobile() || $this->dectector->isTablet());
    }

    protected function isForcedDesktop()
    {
        return true === $this->session->get('isForcedDesktop');
    }
}
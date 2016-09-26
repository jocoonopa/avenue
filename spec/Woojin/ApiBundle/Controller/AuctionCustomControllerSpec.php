<?php

namespace spec\Woojin\ApiBundle\Controller;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class AuctionCustomControllerSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('Woojin\ApiBundle\Controller\AuctionCustomController');
        $this->shouldHaveType('Symfony\Bundle\FrameworkBundle\Controller\Controller');
    }

    function it_shows_the_custom_information_in_response()
    {
        $this->showAction()->shouldBeAnInstanceOf('Symfony\Component\HttpFoundation\Response');
    }

    function it_create_new_custom_for_auction()
    {
        $this->createAction()->shouldBeAnInstanceOf('Symfony\Component\HttpFoundation\Response');
    }

    function it_update_exists_custom_for_auction()
    {
        $this->updateAction()->shouldBeAnInstanceOf('Symfony\Component\HttpFoundation\Response');
    }

    function it_deletes_misscreate_custom_for_auction()
    {
        $this->deleteAction()->shouldBeAnInstanceOf('Symfony\Component\HttpFoundation\Response');
    }
}

<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Security\Http\Tests;

use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Security\Http\Firewall;
use Symfony\Component\Security\Http\Firewall\ExceptionListener;
use Symfony\Component\Security\Http\FirewallMapInterface;

class FirewallTest extends TestCase
{
    public function testOnKernelRequestRegistersExceptionListener()
    {
        $subscriber = $this->getMockBuilder(EventSubscriberInterface::class)->getMock();

        $listener = $this->getMockBuilder(ExceptionListener::class)->disableOriginalConstructor()->getMock();
        $listener
            ->expects($this->once())
            ->method('register')
            ->with($this->equalTo($subscriber))
        ;

        $request = $this->getMockBuilder(Request::class)->disableOriginalConstructor()->disableOriginalClone()->getMock();

        $map = $this->getMockBuilder(FirewallMapInterface::class)->getMock();
        $map
            ->expects($this->once())
            ->method('getListeners')
            ->with($this->equalTo($request))
            ->willReturn([[], $listener, null])
        ;

        $event = new RequestEvent($this->getMockBuilder(HttpKernelInterface::class)->getMock(), $request, HttpKernelInterface::MASTER_REQUEST);

        $firewall = new Firewall($map, $subscriber);
        $firewall->onKernelRequest($event);
    }

    public function testOnKernelRequestStopsWhenThereIsAResponse()
    {
        $called = [];

        $first = function () use (&$called) {
            $called[] = 1;
        };

        $second = function () use (&$called) {
            $called[] = 2;
        };

        $map = $this->getMockBuilder(FirewallMapInterface::class)->getMock();
        $map
            ->expects($this->once())
            ->method('getListeners')
            ->willReturn([[$first, $second], null, null])
        ;

        $event = $this->getMockBuilder(RequestEvent::class)
            ->setMethods(['hasResponse'])
            ->setConstructorArgs([
                $this->getMockBuilder(HttpKernelInterface::class)->getMock(),
                $this->getMockBuilder(Request::class)->disableOriginalConstructor()->disableOriginalClone()->getMock(),
                HttpKernelInterface::MASTER_REQUEST,
            ])
            ->getMock()
        ;
        $event
            ->expects($this->once())
            ->method('hasResponse')
            ->willReturn(true)
        ;

        $firewall = new Firewall($map, $this->getMockBuilder(EventSubscriberInterface::class)->getMock());
        $firewall->onKernelRequest($event);

        $this->assertSame([1], $called);
    }

    public function testOnKernelRequestWithSubRequest()
    {
        $map = $this->getMockBuilder(FirewallMapInterface::class)->getMock();
        $map
            ->expects($this->never())
            ->method('getListeners')
        ;

        $event = new RequestEvent(
            $this->getMockBuilder(HttpKernelInterface::class)->getMock(),
            $this->getMockBuilder(Request::class)->getMock(),
            HttpKernelInterface::SUB_REQUEST
        );

        $firewall = new Firewall($map, $this->getMockBuilder(EventSubscriberInterface::class)->getMock());
        $firewall->onKernelRequest($event);

        $this->assertFalse($event->hasResponse());
    }

    /**
     * @group legacy
     * @expectedDeprecation Not returning an array of 3 elements from Symfony\Component\Security\Http\FirewallMapInterface::getListeners() is deprecated since Symfony 4.2, the 3rd element must be an instance of Symfony\Component\Security\Http\Firewall\LogoutListener or null.
     */
    public function testMissingLogoutListener()
    {
        $subscriber = $this->getMockBuilder(EventSubscriberInterface::class)->getMock();

        $listener = $this->getMockBuilder(ExceptionListener::class)->disableOriginalConstructor()->getMock();
        $listener
            ->expects($this->once())
            ->method('register')
            ->with($this->equalTo($subscriber))
        ;

        $request = new Request();

        $map = $this->getMockBuilder(FirewallMapInterface::class)->getMock();
        $map
            ->expects($this->once())
            ->method('getListeners')
            ->with($this->equalTo($request))
            ->willReturn([[], $listener])
        ;

        $firewall = new Firewall($map, $subscriber);
        $firewall->onKernelRequest(new RequestEvent($this->getMockBuilder(HttpKernelInterface::class)->getMock(), $request, HttpKernelInterface::MASTER_REQUEST));
    }
}

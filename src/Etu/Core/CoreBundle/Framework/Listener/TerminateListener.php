<?php

namespace Etu\Core\CoreBundle\Framework\Listener;

use Etu\Core\CoreBundle\Framework\EtuKernel;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpKernel\Event\PostResponseEvent;

class TerminateListener
{
	/**
	 * @var Session
	 */
	protected $session;

	/**
	 * @param Session $session
	 */
	public function __construct(Session $session)
	{
		$this->session = $session;
	}

	/**
	 * @param PostResponseEvent $event
	 */
	public function onKernelTerminate(PostResponseEvent $event)
	{
		if (substr($event->getRequest()->get('_controller'), 0, 3) != 'Etu') {
			return;
		}

		if ($event->getRequest()->get('_route') == 'notifs_new') {
			return;
		}

		if ($event->getResponse()->headers->get('Content-Type') == 'application/json') {
			return;
		}

		if ($event->getResponse()->headers->get('Content-Type') == 'application/js') {
			return;
		}

		if ($event->getResponse()->headers->get('Content-Type') == 'application/xml') {
			return;
		}

		$this->session->set('etu.last_url', $event->getRequest()->getRequestUri());
	}
}

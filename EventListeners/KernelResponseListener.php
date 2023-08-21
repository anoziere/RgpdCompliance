<?php

namespace RgpdCompliance\EventListeners;

use Propel\Runtime\Exception\PropelException;
use RgpdCompliance\Service\LoginAttemptService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Thelia\Core\Event\TheliaEvents;
use Thelia\Core\Security\SecurityContext;
use Thelia\Mailer\MailerFactory;

class KernelResponseListener implements EventSubscriberInterface
{
    public const LOGIN_URL = '/login';

    public function __construct(
        private readonly SecurityContext $securityContext,
        private readonly LoginAttemptService $loginAttemptService,
        private readonly MailerFactory $mailerFactory,
    ) {
    }

    /**
     * @throws PropelException
     */
    public function logLoginAttempt(
        ResponseEvent $event
    ): void
    {
        if(
            $event->getRequest()->getMethod() !== Request::METHOD_POST
            || !str_contains($event->getRequest()->getPathInfo(), self::LOGIN_URL)
        ) {
            return;
        }

        if($this->securityContext->getCustomerUser() !== null) {
            //success
            return;
        }
        $email = $event->getRequest()->request->get('email')
            ?? $event->getRequest()->get('thelia_customer_login')['email']
        ;

        $ipAddress = $event->getRequest()->getClientIp();

        $this->loginAttemptService->createLoginAttempt($email, $ipAddress);
        $this->loginAttemptService->checkSendEmailNotification($email, $this->mailerFactory);
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::RESPONSE => ['logLoginAttempt', 256],
        ];
    }
}
<?php

namespace RgpdCompliance\EventListeners;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Thelia\Core\Event\TheliaEvents;

class CustomerLoginListener implements EventSubscriberInterface
{

    public static function getSubscribedEvents(): array
    {
        return [
            TheliaEvents::CUSTOMER_CREATEACCOUNT => ['checkPasswordComplexity', 256],
            TheliaEvents::CUSTOMER_UPDATEACCOUNT => ['checkPasswordComplexity', 256],
            TheliaEvents::CUSTOMER_UPDATEPROFILE => ['checkPasswordComplexity', 256]
        ];
    }
}
<?php

namespace RgpdCompliance\EventListeners;

use RgpdCompliance\RgpdCompliance;
use RgpdCompliance\Service\PasswordCheckerService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Thelia\Core\Event\Customer\CustomerCreateOrUpdateEvent;
use Thelia\Core\Event\TheliaEvents;
use Thelia\Core\Translation\Translator;
use Thelia\Exception\CustomerException;

class CustomerListener implements EventSubscriberInterface
{

    public function __construct(
        private readonly PasswordCheckerService $passwordCheckerService
    )
    {
    }

    public function checkPasswordComplexity(
        CustomerCreateOrUpdateEvent $event
    ): void
    {
        $plainPassword = $event->getPassword();
        if($plainPassword !== null && !$this->passwordCheckerService->isAValidPassword($plainPassword)) {
            $minLengthPassword = RgpdCompliance::getConfigValue(RgpdCompliance::CONFIG_NAME_PASSWORD_LENGTH);
            throw new CustomerException(Translator::getInstance()
                ?->trans('The password must be at least '.$minLengthPassword.' characters with a capital letter, a number and a special character')
            );
        }
    }

    public static function getSubscribedEvents(): array
    {
        return [
            TheliaEvents::CUSTOMER_CREATEACCOUNT => ['checkPasswordComplexity', 256],
            TheliaEvents::CUSTOMER_UPDATEACCOUNT => ['checkPasswordComplexity', 256],
            TheliaEvents::CUSTOMER_UPDATEPROFILE => ['checkPasswordComplexity', 256]
        ];
    }
}
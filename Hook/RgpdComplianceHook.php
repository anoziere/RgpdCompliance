<?php

namespace RgpdCompliance\Hook;

use RgpdCompliance\RgpdCompliance;
use Thelia\Core\Event\Hook\HookRenderEvent;
use Thelia\Core\Hook\BaseHook;

class RgpdComplianceHook extends BaseHook
{
    public function onModuleConfig(HookRenderEvent $event): void
    {
        $configurationVariables = [];
        foreach(RgpdCompliance::CONFIG_VARIABLES as $variableName => $defaultValue) {
            $configurationVariables[$variableName] = RgpdCompliance::getConfigValue($variableName, $defaultValue);
        }
        $event->add(
            $this->render(
                RgpdCompliance::DOMAIN_NAME.'-configuration.html',
                [
                    'configurationVariables' => $configurationVariables
                ]
            )
        );
    }
}
<?php

namespace RgpdCompliance\Form;

use RgpdCompliance\RgpdCompliance;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Thelia\Form\BaseForm;

class RgpdComplianceConfigurationForm extends BaseForm
{
    public static function getName(): string
    {
        return 'rgpdcompliance_form_config';
    }

    protected function buildForm(): void
    {
        $this->formBuilder
            ->add(
                RgpdCompliance::DOMAIN_NAME . '_password_min_length',
                IntegerType::class,
                ['label' => $this->translator->trans('Password minimun length', [], 'rgpdcompliance.bo.default')]
            )
            ->add(
                RgpdCompliance::DOMAIN_NAME . '_password_has_upper',
                ChoiceType::class,
                [
                    'label' => $this->translator->trans('Password should have at least one upper character', [], 'rgpdcompliance.bo.default'),
                    'choices' => [
                        $this->translator->trans('Yes', [], 'rgpdcompliance.bo.default') => true,
                        $this->translator->trans('No', [], 'rgpdcompliance.bo.default') => false,
                    ]
                ]
            )
            ->add(
                RgpdCompliance::DOMAIN_NAME . '_password_has_number',
                ChoiceType::class,
                [
                    'label' => $this->translator->trans('Password should have at least one number', [], 'rgpdcompliance.bo.default'),
                    'choices' => [
                        $this->translator->trans('Yes', [], 'rgpdcompliance.bo.default') => true,
                        $this->translator->trans('No', [], 'rgpdcompliance.bo.default') => false,
                    ]
                ]
            )
            ->add(
                RgpdCompliance::DOMAIN_NAME . '_password_has_special_chars',
                ChoiceType::class,
                [
                    'label' => $this->translator->trans('Password should have at least one special character', [], 'rgpdcompliance.bo.default'),
                    'choices' => [
                        $this->translator->trans('Yes', [], 'rgpdcompliance.bo.default') => true,
                        $this->translator->trans('No', [], 'rgpdcompliance.bo.default') => false,
                    ]
                ]
            );
    }
}

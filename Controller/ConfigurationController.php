<?php

namespace RgpdCompliance\Controller;

use OpenApi\Form\ConfigForm;
use OpenApi\OpenApi;
use RgpdCompliance\Form\RgpdComplianceConfigurationForm;
use RgpdCompliance\RgpdCompliance;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Thelia\Controller\Admin\BaseAdminController;
use Symfony\Component\Routing\Annotation\Route;
use Thelia\Core\Template\ParserContext;
use Thelia\Log\Tlog;

/**
 * @Route("/admin/module/RgpdCompliance", name="rgpdcompliance_configuration")
 */
class ConfigurationController extends BaseAdminController
{
    /**
     * @Route("/save", name="_save", methods="POST")
     */
    public function save(ParserContext $parserContext): RedirectResponse|Response|null
    {
        $configForm = $this->createForm(RgpdComplianceConfigurationForm::getName());

        try {
            $form = $this->validateForm($configForm);

            foreach(RgpdCompliance::CONFIG_VARIABLES as $variableName => $defaultValue) {
                $data = $form->get($variableName)->getData();
                RgpdCompliance::setConfigValue($variableName, $data === false ? 0 : $data);
            }
            return $this->generateSuccessRedirect($configForm);
        } catch (\Exception $exception) {
            Tlog::getInstance()->error($exception->getMessage());

            $configForm->setErrorMessage($exception->getMessage());

            $parserContext
                ->addForm($configForm)
                ->setGeneralError($exception->getMessage())
            ;

            return $this->generateErrorRedirect($configForm);
        }
    }
}
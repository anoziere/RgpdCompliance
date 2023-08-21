<?php

namespace RgpdCompliance;

use Exception;
use Propel\Runtime\Connection\ConnectionInterface;
use Propel\Runtime\Exception\PropelException;
use SplFileInfo;
use Symfony\Component\DependencyInjection\Loader\Configurator\ServicesConfigurator;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpFoundation\RequestStack;
use Thelia\Core\Translation\Translator;
use Thelia\Install\Database;
use Thelia\Model\LangQuery;
use Thelia\Model\Message;
use Thelia\Model\MessageQuery;
use Thelia\Module\BaseModule;

class RgpdCompliance extends BaseModule
{
    /** @var string */
    public const DOMAIN_NAME = 'rgpdcompliance';
    public const MESSAGE_CODE = 'send_notification_blocked';

    /** PASSWORD SETTINGS */
    public const CONFIG_PASSWORD_LENGTH = self::DOMAIN_NAME.'_password_min_length';
    public const CONFIG_PASSWORD_HAS_UPPER = self::DOMAIN_NAME.'_password_has_upper';
    public const CONFIG_PASSWORD_HAS_NUMBER = self::DOMAIN_NAME.'_password_has_number';
    public const CONFIG_PASSWORD_HAS_SPECIAL_CHARS = self::DOMAIN_NAME.'_password_has_special_chars';

    public const DEFAULT_VALUE_PASSWORD_LENGTH = 8;
    public const DEFAULT_VALUE_PASSWORD_HAS_UPPER = true;
    public const DEFAULT_VALUE_PASSWORD_HAS_SPECIAL_CHARS = true;
    public const DEFAULT_VALUE_PASSWORD_HAS_NUMBER = true;
    /** END PASSWORD SETTINGS */

    /** ACCOUNT SETTINGS BLOCKED TRYING TO LOGIN */
    public const CONFIG_MAX_TRY_LOGIN = self::DOMAIN_NAME.'_max_try_login';
    public const CONFIG_PERIOD_LOGIN_CHECK_FAILED = self::DOMAIN_NAME.'_period_login_check_failed';
    public const CONFIG_LOGIN_BLOCKED_DURATION = self::DOMAIN_NAME.'_login_blocked_duration';

    public const DEFAULT_VALUE_MAX_TRY_LOGIN = 5;
    public const DEFAULT_VALUE_PERIOD_LOGIN_CHECK_FAILED = 3600; //seconds
    public const DEFAULT_VALUE_LOGIN_BLOCKED_DURATION = 3600; //seconds
    /** END ACCOUNT SETTINGS BLOCKED TRYING TO LOGIN */


    public const CONFIG_VARIABLES = [
        //Password
        self::CONFIG_PASSWORD_LENGTH =>  self::DEFAULT_VALUE_PASSWORD_LENGTH,
        self::CONFIG_PASSWORD_HAS_UPPER =>  self::DEFAULT_VALUE_PASSWORD_HAS_UPPER,
        self::CONFIG_PASSWORD_HAS_SPECIAL_CHARS => self::DEFAULT_VALUE_PASSWORD_HAS_SPECIAL_CHARS,
        self::CONFIG_PASSWORD_HAS_NUMBER => self::DEFAULT_VALUE_PASSWORD_HAS_NUMBER,
        //Account blocked failed login
        self::CONFIG_MAX_TRY_LOGIN =>  self::DEFAULT_VALUE_MAX_TRY_LOGIN,
        self::CONFIG_PERIOD_LOGIN_CHECK_FAILED => self::DEFAULT_VALUE_PERIOD_LOGIN_CHECK_FAILED,
        self::CONFIG_LOGIN_BLOCKED_DURATION => self::DEFAULT_VALUE_LOGIN_BLOCKED_DURATION,
    ];

    /**
     * @throws PropelException
     */
    public function postActivation(ConnectionInterface $con = null): void
    {
        foreach(self::CONFIG_VARIABLES as $variableName => $defaultValue) {
            if (null === self::getConfigValue($variableName)) {
                self::setConfigValue($variableName, $defaultValue);
            }
        }
        if (!self::getConfigValue(self::DOMAIN_NAME.'_is_initialized', false)) {
            (new Database($con))->insertSql(null, array(__DIR__ . '/Config/TheliaMain.sql'));
            self::setConfigValue(self::DOMAIN_NAME.'_is_initialized', true);
        }
        $this->createEmailMessage();
    }

    /**
     * Defines how services are loaded in your modules
     *
     * @param ServicesConfigurator $servicesConfigurator
     */
    public static function configureServices(ServicesConfigurator $servicesConfigurator): void
    {
        $servicesConfigurator->load(self::getModuleCode().'\\', __DIR__)
            ->exclude([THELIA_MODULE_DIR . ucfirst(self::getModuleCode()). "/I18n/*"]);
    }

    /**
     * Execute sql files in Config/update/ folder named with module version (ex: 1.0.1.sql).
     *
     * @param $currentVersion
     * @param $newVersion
     * @param ConnectionInterface|null $con
     * @throws PropelException
     */
    public function update($currentVersion, $newVersion, ConnectionInterface $con = null): void
    {
        $updateDir = __DIR__.DS.'Config'.DS.'update';

        if (! is_dir($updateDir)) {
            return;
        }

        $finder = Finder::create()
            ->name('*.sql')
            ->depth(0)
            ->sortByName()
            ->in($updateDir);

        $database = new Database($con);

        /** @var SplFileInfo $file */
        foreach ($finder as $file) {
            if (version_compare($currentVersion, $file->getBasename('.sql'), '<')) {
                $database->insertSql(null, [$file->getPathname()]);
            }
        }
        $this->createEmailMessage();
    }

    /**
     * @throws PropelException
     */
    protected function createEmailMessage(): void
    {
        if (null !== MessageQuery::create()->findOneByName(self::MESSAGE_CODE)) {
            return;
        }
        $message = new Message();
        $message
            ->setName(self::MESSAGE_CODE)
            ->setHtmlTemplateFileName(self::MESSAGE_CODE . '.html')
            ->setHtmlLayoutFileName('')
            ->setSecured(0);

        $languages = LangQuery::create()->find();

        foreach ($languages as $language) {
            $locale = $language->getLocale();

            $message->setLocale($locale);

            $message->setSubject(
                Translator::getInstance()?->trans('Your account has been blocked', [], self::DOMAIN_NAME, $locale)
            );
            $message->setTitle(
                Translator::getInstance()?->trans('Account blocked notification', [],self::DOMAIN_NAME, $locale)
            );
        }

        $message->save();
    }

}

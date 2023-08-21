<?php

namespace RgpdCompliance\Service;

use DateTime;
use Propel\Runtime\Exception\PropelException;
use RgpdCompliance\Model\RgpdComplianceCustomerBlocked;
use RgpdCompliance\Model\RgpdComplianceCustomerBlockedQuery;
use RgpdCompliance\Model\RgpdComplianceLoginLogs;
use RgpdCompliance\Model\RgpdComplianceLoginLogsQuery;
use RgpdCompliance\RgpdCompliance;
use Thelia\Mailer\MailerFactory;
use Thelia\Model\Customer;
use Thelia\Model\CustomerQuery;

class LoginAttemptService
{
    /**
     * @throws PropelException
     */
    public function createLoginAttempt(
        string $email,
        ?string $ipAddress = null,
        ?Customer $customer = null
    ): RgpdComplianceLoginLogs
    {
        $loginLog = (new RgpdComplianceLoginLogs())
            ->setCustomerId($customer?->getId())
            ->setEmail($email)
            ->setIpAddress($ipAddress);
        $loginLog->save();
        return $loginLog;
    }

    public function countLoginAttempts(string $email): int
    {
        $periodLoginCheck = RgpdCompliance::getConfigValue(RgpdCompliance::CONFIG_PERIOD_LOGIN_CHECK_FAILED);
        $maxDate = (new DateTime())->modify('- '.$periodLoginCheck. 'seconds');
        return RgpdComplianceLoginLogsQuery::create()
            ->filterByEmail($email)
            ->filterByCreatedAt([
                'min' => $maxDate->getTimestamp()
            ])
            ->count();
    }

    /**
     * @throws PropelException
     */
    public function checkSendEmailNotification(string $email, MailerFactory $mailerFactory): void
    {
        $maxTryLogin = RgpdCompliance::getConfigValue(RgpdCompliance::CONFIG_MAX_TRY_LOGIN);

        if($this->countLoginAttempts($email) < $maxTryLogin) {
            return;
        }
        $customer = CustomerQuery::create()->findOneByEmail($email);
        if($customer === null) {
            return;
        }
        $customerBlocked = RgpdComplianceCustomerBlockedQuery::create()
            ->filterByCustomerId($customer->getId())
            ->filterByEndOfBlocking(['min' => time()])
            ->filterByEmailSent(true)
            ->findOne();
        if($customerBlocked !== null) {
            return;
        }

        $duration = RgpdCompliance::getConfigValue(RgpdCompliance::CONFIG_LOGIN_BLOCKED_DURATION);
        $endOfBlocking = (new DateTime())->modify('+ '.$duration. 'seconds');

        $newCustomerBlocked = (new RgpdComplianceCustomerBlocked())
            ->setCustomerId($customer->getId())
            ->setEndOfBlocking($endOfBlocking->getTimestamp())
            ->setEmailSent(false);
        $newCustomerBlocked->save();

        $this->sendEmailNotification($customer, $mailerFactory);

        $newCustomerBlocked->setEmailSent(true);
        $newCustomerBlocked->save();
    }

    private function sendEmailNotification(Customer $customer, MailerFactory $mailerFactory): void
    {
        $messageCode = RgpdCompliance::MESSAGE_CODE;
        $mailerFactory->sendEmailToCustomer(
            $messageCode,
            $customer
        );
    }

}
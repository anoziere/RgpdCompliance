
# This is a fix for InnoDB in MySQL >= 4.1.x
# It "suspends judgement" for fkey relationships until are tables are set.
SET FOREIGN_KEY_CHECKS = 0;

-- ---------------------------------------------------------------------
-- rgpd_compliance_login_logs
-- ---------------------------------------------------------------------

DROP TABLE IF EXISTS `rgpd_compliance_login_logs`;

CREATE TABLE `rgpd_compliance_login_logs`
(
    `id` INTEGER NOT NULL AUTO_INCREMENT,
    `customer_id` INTEGER,
    `email` VARCHAR(255),
    `ip_address` VARCHAR(255),
    `created_at` DATETIME,
    `updated_at` DATETIME,
    PRIMARY KEY (`id`),
    INDEX `fi_rgpd_compliance_login_logs_customer_id` (`customer_id`),
    CONSTRAINT `fk_rgpd_compliance_login_logs_customer_id`
        FOREIGN KEY (`customer_id`)
        REFERENCES `customer` (`id`)
        ON UPDATE RESTRICT
        ON DELETE CASCADE
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- rgpd_compliance_customer_blocked
-- ---------------------------------------------------------------------

DROP TABLE IF EXISTS `rgpd_compliance_customer_blocked`;

CREATE TABLE `rgpd_compliance_customer_blocked`
(
    `id` INTEGER NOT NULL AUTO_INCREMENT,
    `customer_id` INTEGER NOT NULL,
    `end_of_blocking` DATETIME,
    `email_sent` TINYINT(1) DEFAULT 0,
    `created_at` DATETIME,
    `updated_at` DATETIME,
    PRIMARY KEY (`id`),
    INDEX `fi_rgpd_compliance_customer_blocked_customer_id` (`customer_id`),
    CONSTRAINT `fk_rgpd_compliance_customer_blocked_customer_id`
        FOREIGN KEY (`customer_id`)
        REFERENCES `customer` (`id`)
        ON UPDATE RESTRICT
        ON DELETE CASCADE
) ENGINE=InnoDB;

# This restores the fkey checks, after having unset them earlier
SET FOREIGN_KEY_CHECKS = 1;

<?php

//#############################################

/** @var $installer Ess_M2ePro_Model_MySqlSetup */
$installer = $this;
$installer->startSetup();

//#############################################

$installer->run(<<<SQL

ALTER TABLE `m2epro_listings_products`
ADD COLUMN `additional_data` TEXT DEFAULT NULL after `status_changer`;

INSERT INTO `ess_config` (`group`, `key`, `value`, `notice`, `update_date`, `create_date`) VALUES
('/M2ePro/license/', 'component', '', 'Valid component', '2011-07-21 09:51:07', '2011-03-01 10:21:28');

UPDATE `m2epro_products_changes`
SET `creator_type` = 1
WHERE `creator_type` = 0;

ALTER TABLE `m2epro_listings_templates`
ADD COLUMN `use_ebay_local_shipping_rate_table` TINYINT(2) UNSIGNED NOT NULL after `use_ebay_tax_table`,
ADD INDEX `use_ebay_local_shipping_rate_table` (`use_ebay_local_shipping_rate_table`);

ALTER TABLE `m2epro_synchronizations_templates`
ADD COLUMN  `relist_send_data` TINYINT(2) UNSIGNED NOT NULL after `relist_filter_user_lock`,
ADD INDEX `relist_send_data` (`relist_send_data`);

UPDATE `m2epro_synchronizations_templates`
SET `relist_send_data` = 1;

ALTER TABLE `m2epro_accounts`
ADD COLUMN `orders_status_payment_complete_mode` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0 after `orders_status_checkout_incomplete`,
ADD INDEX `orders_status_payment_complete_mode` (`orders_status_payment_complete_mode`);

ALTER TABLE `m2epro_ebay_orders`
ADD COLUMN `shipping_tracking_details` VARCHAR(500) DEFAULT NULL after `shipping_selected_cost`,
ADD COLUMN `selling_manager_record_number` INT(11) UNSIGNED DEFAULT 0 after `ebay_order_id`,
ADD INDEX `selling_manager_record_number` (`selling_manager_record_number`);

ALTER TABLE `m2epro_selling_formats_templates`
ADD COLUMN `price_variation_mode` TINYINT(2) UNSIGNED NOT NULL after `currency`,
ADD INDEX `price_variation_mode` (`price_variation_mode`);

UPDATE `m2epro_selling_formats_templates`
SET `price_variation_mode` = 1;

SQL
);

//----------------------------------

if (Mage::getModel('M2ePro/Migration_Dispatcher')->isAlreadyWorked()) {

    $listingsTemplates = Mage::getModel('M2ePro/ListingsTemplates')->getCollection()->getItems();
    foreach ($listingsTemplates as $listingTemplate) {

        $productDetails = json_decode($listingTemplate->getData('product_details'),true);

        $modeName = 'product_details_isbn_mode';
        $CVName = 'product_details_isbn_cv';
        $CAName = 'product_details_isbn_ca';

        if (isset($productDetails[$modeName]) &&
            isset($productDetails[$CVName]) &&
            isset($productDetails[$CAName])) {

            if ($productDetails[$modeName] == Ess_M2ePro_Model_ListingsTemplates::PRODUCT_DETAIL_MODE_NONE && $productDetails[$CVName] != '' && $productDetails[$CAName] == '') {
                $productDetails[$modeName] = Ess_M2ePro_Model_ListingsTemplates::PRODUCT_DETAIL_MODE_CUSTOM_VALUE;
            } else if ($productDetails[$modeName] == Ess_M2ePro_Model_ListingsTemplates::PRODUCT_DETAIL_MODE_CUSTOM_VALUE && $productDetails[$CVName] == '' && $productDetails[$CAName] != '') {
                $productDetails[$modeName] = Ess_M2ePro_Model_ListingsTemplates::PRODUCT_DETAIL_MODE_CUSTOM_ATTRIBUTE;
            }
        }

        $modeName = 'product_details_epid_mode';
        $CVName = 'product_details_epid_cv';
        $CAName = 'product_details_epid_ca';

        if (isset($productDetails[$modeName]) &&
            isset($productDetails[$CVName]) &&
            isset($productDetails[$CAName])) {

            if ($productDetails[$modeName] == Ess_M2ePro_Model_ListingsTemplates::PRODUCT_DETAIL_MODE_NONE && $productDetails[$CVName] != '' && $productDetails[$CAName] == '') {
                $productDetails[$modeName] = Ess_M2ePro_Model_ListingsTemplates::PRODUCT_DETAIL_MODE_CUSTOM_VALUE;
            } else if ($productDetails[$modeName] == Ess_M2ePro_Model_ListingsTemplates::PRODUCT_DETAIL_MODE_CUSTOM_VALUE && $productDetails[$CVName] == '' && $productDetails[$CAName] != '') {
                $productDetails[$modeName] = Ess_M2ePro_Model_ListingsTemplates::PRODUCT_DETAIL_MODE_CUSTOM_ATTRIBUTE;
            }
        }

        $modeName = 'product_details_upc_mode';
        $CVName = 'product_details_upc_cv';
        $CAName = 'product_details_upc_ca';

        if (isset($productDetails[$modeName]) &&
            isset($productDetails[$CVName]) &&
            isset($productDetails[$CAName])) {

            if ($productDetails[$modeName] == Ess_M2ePro_Model_ListingsTemplates::PRODUCT_DETAIL_MODE_NONE && $productDetails[$CVName] != '' && $productDetails[$CAName] == '') {
                $productDetails[$modeName] = Ess_M2ePro_Model_ListingsTemplates::PRODUCT_DETAIL_MODE_CUSTOM_VALUE;
            } else if ($productDetails[$modeName] == Ess_M2ePro_Model_ListingsTemplates::PRODUCT_DETAIL_MODE_CUSTOM_VALUE && $productDetails[$CVName] == '' && $productDetails[$CAName] != '') {
                $productDetails[$modeName] = Ess_M2ePro_Model_ListingsTemplates::PRODUCT_DETAIL_MODE_CUSTOM_ATTRIBUTE;
            }
        }

        $modeName = 'product_details_ean_mode';
        $CVName = 'product_details_ean_cv';
        $CAName = 'product_details_ean_ca';

        if (isset($productDetails[$modeName]) &&
            isset($productDetails[$CVName]) &&
            isset($productDetails[$CAName])) {

            if ($productDetails[$modeName] == Ess_M2ePro_Model_ListingsTemplates::PRODUCT_DETAIL_MODE_NONE && $productDetails[$CVName] != '' && $productDetails[$CAName] == '') {
                $productDetails[$modeName] = Ess_M2ePro_Model_ListingsTemplates::PRODUCT_DETAIL_MODE_CUSTOM_VALUE;
            } else if ($productDetails[$modeName] == Ess_M2ePro_Model_ListingsTemplates::PRODUCT_DETAIL_MODE_CUSTOM_VALUE && $productDetails[$CVName] == '' && $productDetails[$CAName] != '') {
                $productDetails[$modeName] = Ess_M2ePro_Model_ListingsTemplates::PRODUCT_DETAIL_MODE_CUSTOM_ATTRIBUTE;
            }
        }

        $listingTemplate->setData('product_details',json_encode($productDetails));
        $listingTemplate->save();
    }
}

//#############################################

$installer->endSetup();

//#############################################
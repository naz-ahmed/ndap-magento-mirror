<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Model_Orders_Magento_Quote
{
    protected $_order = NULL;

    /** @var $_magentoQuote Mage_Sales_Model_Quote */
    protected $_magentoQuote = NULL;

    protected $_magentoOrderComments = array();

    protected $_currencyConvertRate = 1;

    protected $_productTaxClassId = 0; // 0 - None tax class

    protected $_storeShippingTaxClass = NULL;

    protected $_address = NULL;

    // ########################################

    public function setOrder(Ess_M2ePro_Model_Orders_Order $order)
    {
        $this->_order = $order;

        return $this;
    }

    /**
     * @return Ess_M2ePro_Model_Orders_Order
     */
    public function getOrder()
    {
        if (is_null($this->_order) || !$this->_order->getId()) {
            throw new Exception('Order was not set.');
        }

        return $this->_order;
    }

    // ########################################

    public function getMagentoQuote()
    {
        return $this->_magentoQuote;
    }

    public function getStoreShippingTaxClass()
    {
        return $this->_storeShippingTaxClass;
    }

    // ########################################

    public function prepareMagentoQuote()
    {
        $this->initializeQuote();
        $this->initializeProductsAndStore();
        $this->initializeCustomer();
        $this->initializeAddress();
        $this->initializeCurrency();
        $this->initializeQuoteItems();

        $this->initializeShippingMethod();
        $this->initializePaymentMethod();

        return $this;
    }

    // ########################################

    protected function initializeQuote()
    {
        $this->_magentoQuote = Mage::getModel('sales/quote');
        if ($this->getOrder()->getAccount()->isCustomerModeNew() ||
            $this->getOrder()->getAccount()->isCustomerModePredefined()) {

            $this->_magentoQuote->setCheckoutMethod('register');
        } else {
            $this->_magentoQuote->setCheckoutMethod('guest');
        }
        $this->_magentoQuote->save();

        return $this;
    }

    // ########################################

    protected function initializeProductsAndStore()
    {
        $account = $this->getOrder()->getAccount();
        $items = $this->getOrder()->getItemsCollection();

        $isStoreInitialized = false;

        foreach ($items as $item) {
            if (!is_null($item->getData('product_id')) && !is_null($item->getData('store_id')) && $isStoreInitialized) {
                continue;
            }

            if ($account->isOrdersListingsModeEnabled()) {
                $ebayItem = $this->getEbayItemByOrderItem($item);

                if ($ebayItem) {

                    if (is_null($item->getData('product_id')) || is_null($item->getData('store_id'))) {
                        $item->setData('product_id', $ebayItem->getData('product_id'));
                        $item->setData('store_id', $ebayItem->getData('store_id'));
                        $item->save();
                    }

                    if ($isStoreInitialized) {
                        continue;
                    }

                    if ($account->isStoreFromListingEnabled()) {
                        $this->initializeStore((int)$ebayItem->getData('store_id'));
                    } else {
                        $this->initializeStore((int)$account->getData('orders_listings_store_id'));
                    }

                    $isStoreInitialized = true;

                    continue;
                }
            }

            if ($account->isOrdersEbayModeDisabled()) {
                throw new Exception('3rd Party Orders Creation is disabled in eBay account settings.');
            }

            if ($account->isOrdersEbayModeEnabled()) {
                if (!is_null($item->getData('product_id')) && !is_null($item->getData('store_id'))) {
                    continue;
                }

                $product = $this->getProductBySku($item);

                if ($product) {
                    $productStoreId = Mage::helper('M2ePro/Sales')->getProductFirstStoreId($product); // do we need this method in helper?
                    if (!$productStoreId) {
                        throw new Exception('Product does not belong to any store.');
                    }

                    $item->setData('product_id', $product->getId());
                    $item->setData('store_id', $productStoreId);
                    $item->save();

                    continue;
                }
            }

            if ($account->isOrdersEbayModeEnabled() && $account->isEbayItemsImportEnabled()) {
                if (!is_null($item->getData('product_id')) && !is_null($item->getData('store_id'))) {
                    continue;
                }

                $product = $this->getNewProductByOrderItem($item);

                if ($product) {
                    $productStoreId = Mage::helper('M2ePro/Sales')->getProductFirstStoreId($product); // do we need this method in helper?
                    if (!$productStoreId) {
                        throw new Exception('Product does not belong to any store.');
                    }

                    $item->setData('product_id', $product->getId());
                    $item->setData('store_id', $productStoreId);
                    $item->save();

                    continue;
                }
            }

            throw new Exception('There\'s no associated product found for eBay item in magento catalog.');
        }

        if (!$isStoreInitialized) {
            $storeId = $account->getData('orders_ebay_store_id') ? $account->getData('orders_ebay_store_id')
                                                                 : Mage::helper('M2ePro/Sales')->getDefaultStoreId();
            $this->initializeStore($storeId);
        }

        return $this;
    }

    protected function getEbayItemByOrderItem(Ess_M2ePro_Model_Orders_OrderItem $item)
    {
        $ebayItem = Mage::getModel('M2ePro/EbayItems')->load($item->getData('item_id'), 'item_id');

        if (!$ebayItem->getId()) {
            return null;
        }

        return $ebayItem;
    }

    protected function getProductBySku(Ess_M2ePro_Model_Orders_OrderItem $item)
    {
        $sku = $item->getData('item_sku') ? $item->getData('item_sku')
                                          : Mage::helper('M2ePro')->convertStringToSku($item->getData('item_title'));

        $product = Mage::getModel('catalog/product')->loadByAttribute('sku', substr($sku, 0, 64));
        if ($product && $product->getId()) {
            // loadByAttribute returns product model without stock item
            return Mage::getModel('catalog/product')->load($product->getId());
        }

        return null;
    }

    protected function getNewProductByOrderItem(Ess_M2ePro_Model_Orders_OrderItem $item)
    {
        /** @var $ebayOrderItem Ess_M2ePro_Model_Orders_Ebay_OrderItem */
        $ebayOrderItem = Mage::getModel('M2ePro/Orders_Ebay_OrderItem');
        $ebayOrderItem->setAccount($this->getOrder()->getAccount());
        $ebayOrderItem->setOrderItem($item);

        $ebayItemInfo = $ebayOrderItem->getItemInfoFromEbay();

        if (!count($ebayItemInfo)) {
            return null;
        }

        /** @var $newProduct Ess_M2ePro_Model_Orders_Magento_Product */
        $newProduct = Mage::getModel('M2ePro/Orders_Magento_Product');
        $newProduct->setAccount($this->getOrder()->getAccount());
        $newProduct->initialize($ebayItemInfo);

        return $newProduct->createProduct();
    }

    protected function initializeStore($storeId = 0)
    {
        $storeId = $storeId > 0 ? $storeId : Mage::helper('M2ePro/Sales')->getDefaultStoreId();
        $store = Mage::getModel('core/store')->load($storeId);

        if (!$store->getId()) {
            throw new Exception('Store does not exist.');
        }

        $this->_magentoQuote->setStore($store);

        return $this;
    }

    // ########################################

    protected function initializeCustomer()
    {
        $account = $this->getOrder()->getAccount();
        /** @var $customer Mage_Customer_Model_Customer */
        $customer = Mage::getModel('customer/customer');

        if ($account->isCustomerModeNew()) {

            $shippingAddress = $this->getOrder()->getPreparedShippingAddress();

            $customer->setWebsiteId($account->getData('orders_customer_new_website'));
            $customer->loadByEmail($shippingAddress['email']);

            if (!$customer->getId()) {
                /** @var $customerBuilder Ess_M2ePro_Model_Orders_Magento_Customer */
                $customerBuilder = Mage::getModel('M2ePro/Orders_Magento_Customer');
                $customerBuilder->setAccount($this->getOrder()->getAccount());

                $customer = $customerBuilder->createCustomer($this->_magentoQuote->getStore(), $shippingAddress);

            } else if ($customer->getConfirmation() && $customer->isConfirmationRequired()) {
                // If this customer need confirmation, manual activate
                $customer->setConfirmation(null)->save();
            }

        } else if ($account->isCustomerModePredefined()) {

            $customer->load((int)$account->getData('orders_customer_exist_id'));
            if (is_null($customer->getId())) {
                throw new Exception('Customer with specified ID in eBay account settings does not exist.');
            }

        } else {

            $this->_magentoQuote->setCustomerId(null)
                         ->setCustomerEmail($this->getOrder()->getData('buyer_email'))
                         ->setCustomerIsGuest(true)
                         ->setCustomerGroupId(Mage_Customer_Model_Group::NOT_LOGGED_IN_ID);

            return $this;

        }

        $this->_magentoQuote->assignCustomer($customer);

        return $this;
    }

    // ########################################

    protected function initializeAddress()
    {
        $addressInfo = $this->getOrder()->getPreparedShippingAddress();
        unset($addressInfo['address_id']);

        $billingAddress = $this->_magentoQuote->getBillingAddress();
        $billingAddress->addData($addressInfo);
        $billingAddress->implodeStreetAddress();

        $shippingAddress = $this->_magentoQuote->getShippingAddress();
        $shippingAddress->setSameAsBilling(0); // maybe just set same as billing?
        $shippingAddress->addData($addressInfo);
        $shippingAddress->implodeStreetAddress();

        return $this;
    }

    // ########################################

    protected function initializeCurrency()
    {
        $currencyModel = Mage::getModel('directory/currency');

        // Get all base and allowed currencies
        // ----------
        $allowedCurrencies = $currencyModel->getConfigAllowCurrencies();
        $baseCurrency = $this->_magentoQuote->getStore()->getBaseCurrencyCode();
        // ----------

        // ----------
        $ebayOrderCurrency = $this->getOrder()->getData('currency') ? $this->getOrder()->getData('currency') : $baseCurrency;
        // ----------

        // ----------
        $magentoOrderCurrency = $baseCurrency;
        $magentoOrderConvertRate = 1;
        // ----------

        if (!in_array($ebayOrderCurrency, $allowedCurrencies)) {
            $comment = "Store and Order's Product Currency are different. Conversion from <b>{$ebayOrderCurrency} to {$magentoOrderCurrency}</b> is not performed. Currency <b>{$ebayOrderCurrency}</b> is unknown.";
            $this->addOrderComment($comment);
        } else {

            if ($ebayOrderCurrency != $baseCurrency) {

                $magentoOrderCurrency = $ebayOrderCurrency;
                $tempConvertRate = $currencyModel->load($baseCurrency)->getAnyRate($ebayOrderCurrency);

                if ($tempConvertRate != 0) {
                    $magentoOrderConvertRate = $tempConvertRate;

                    $comment = "Store and Order's Product Currency are different. Conversion from <b>{$ebayOrderCurrency} to {$baseCurrency}</b> performed using <b>" . round($magentoOrderConvertRate, 2) . '</b> as a rate.';
                    $this->addOrderComment($comment);

                    $comment = "Store and Order's Shipping Currency are different. Conversion from <b>{$ebayOrderCurrency} to {$baseCurrency}</b> performed using <b>" . round($magentoOrderConvertRate, 2) . '</b> as a rate.';
                    $this->addOrderComment($comment);
                } else {
                    $comment = "Store and Order's Product Currency are different. Conversion from <b>{$ebayOrderCurrency} to {$baseCurrency}</b> not performed. There is no rate amongst Currency Rates.";
                    $this->addOrderComment($comment);

                    $comment = "Store and Order's Shipping Currency are different. Conversion from <b>{$ebayOrderCurrency} to {$baseCurrency}</b> not performed. There is no rate amongst Currency Rates.";
                    $this->addOrderComment($comment);
                }
            }
        }

        $this->_currencyConvertRate = $magentoOrderConvertRate;

        // needed for conversion order currency to base store currency
        // ----------
        if (in_array($magentoOrderCurrency, $this->_magentoQuote->getStore()->getAvailableCurrencyCodes())) {
            $session = Mage::getModel('core/session')->init('store_'.$this->_magentoQuote->getStore()->getCode());
            $session->setCurrencyCode($magentoOrderCurrency);
        }
        // ----------

        return $this;
    }

    // ########################################

    protected function getMergedItems()
    {
        $items = $this->getOrder()->getItemsCollection();

        $result = array();
        $productIds = array();

        foreach ($items as $item) {
            if (!in_array($item->getData('product_id'), $productIds)) {
                $productIds[] = $item->getData('product_id');
                $result[] = $item;
                continue;
            }

            $result = $this->mergeItems($result, $item);
        }

        return $result;
    }

    protected function mergeItems(array $existItems, Ess_M2ePro_Model_Orders_OrderItem $item)
    {
        foreach ($existItems as &$existItem) {
            if ($existItem->getData('product_id') != $item->getData('product_id')) {
                continue;
            }

            $existItemOptions = $existItem->getOptions();
            $itemOptions = $item->getOptions();

            if ((is_null($existItemOptions) && is_null($itemOptions)) || $existItemOptions == $itemOptions) {
                $mergedItemsQty = $existItem->getData('qty_purchased') + $item->getData('qty_purchased');
                $existItem->setData('qty_purchased', $mergedItemsQty);

                return $existItems;
            }
        }

        $existItems[] = $item;

        return $existItems;
    }

    // ########################################

    protected function initializeQuoteItems()
    {
        $orderItems = $this->getMergedItems();

        foreach ($orderItems as $item) {
            /** @var $product Mage_Catalog_Model_Product */
            $product = Mage::getModel('catalog/product')->load($item->getData('product_id'));

            if (!$product->getId()) {
                throw new LogicException('Product does not exist. Probably it was deleted.');
            }

            if ($product->getStatus() == Mage_Catalog_Model_Product_Status::STATUS_DISABLED) {
                throw new LogicException('Product is disabled.');
            }

            $result = $this->addProductToQuote($product, $item);

            if (is_string($result)) {
                throw new Exception($result);
            }

            $tempQuoteItem = $this->_magentoQuote->getItemByProduct($product);

            if ($tempQuoteItem !== false) {
                $tempQuoteItem->setOriginalCustomPrice($this->getPrice($item));
                $tempQuoteItem->setNoDiscount(1);
            }
        }
    }

    protected function addProductToQuote(Mage_Catalog_Model_Product $product, Ess_M2ePro_Model_Orders_OrderItem $item)
    {
        /** @var $magentoProduct Ess_M2ePro_Model_MagentoProduct */
        $magentoProduct = Mage::getModel('M2ePro/MagentoProduct')->setProduct($product);

        if (!$magentoProduct->isSimpleType() &&
            !$magentoProduct->isGroupedType() &&
            !$magentoProduct->isConfigurableType() &&
            !$magentoProduct->isBundleType()) {
            // Parser hack -> Mage::helper('M2ePro')->__('Order Import does not support product type: %type%.')
            $tempMessageException = 'Order Import does not support product type: %type%.';
            throw new Exception(str_replace('%type%', $product->getTypeId(), $tempMessageException));
        }

        $request = new Varien_Object();

        // Prepare item options
        // ------------
        $ebayItemOptions = $item->getOptions(true);
        $tempEbayItemOptions = array();
        foreach ($ebayItemOptions as $key => $value) {
            $tempEbayItemOptions[trim(strtolower($key))] = trim(strtolower($value));
        }
        // ------------

        if ($magentoProduct->isSimpleType()) {
            $options = $this->getSimpleProductOptions($magentoProduct, $tempEbayItemOptions);
            if (count($options)) {
                $request->setOptions($options);
            }
        }

        if ($magentoProduct->isGroupedType()) {
            $product = $this->getRelatedGroupedProduct($magentoProduct, $tempEbayItemOptions);

            if (!$product || !$product->getId()) {
                throw new LogicException('There is no associated products found for grouped product.');
            }
        }

        if ($magentoProduct->isConfigurableType()) {
            $request->setSuperAttribute($this->getConfigurableProductOptions($magentoProduct, $tempEbayItemOptions));
        }

        if ($magentoProduct->isBundleType()) {
            // non-default store may not work for bundle product options
            //$product->setStoreId(Mage::helper('M2ePro/Sales')->getDefaultStoreId());
            $product->setStoreId($this->_magentoQuote->getStoreId());
            $request->setBundleOption($this->getBundleProductOptions($magentoProduct, $tempEbayItemOptions));
        }

        $request->setQty($item->getData('qty_purchased'));

        $price = $this->getPrice($item);
        $product->setPrice($price);
        $product->setSpecialPrice($price);
        $product->setTaxClassId($this->getProductTaxClassId($product));

        return $this->_magentoQuote->addProduct($product, $request);
    }

    // ########################################

    protected function getSimpleProductOptions(Ess_M2ePro_Model_MagentoProduct $magentoProduct, array $ebayItemOptions = array())
    {
        $customOptions = $magentoProduct->getProductVariationsForOrder();

        if (!count($ebayItemOptions)) {

            // Variation info unavailable - return first value for each required option
            $firstOptions = array();

            foreach ($customOptions as $option) {
                $firstOptions[$option['option_id']] = $option['values'][0]['value_id'];
            }

            // Parser hack -> Mage::helper('M2ePro')->__('The eBay item is listed as simple (variations are ignored). As required options are missing, the first options value(s) is used to create Magento Order.');
            count($firstOptions) && $this->getOrder()->addWarningLogMessage('The eBay item is listed as simple (variations are ignored). As required options are missing, the first options value(s) is used to create Magento Order.');

            return $firstOptions;
        }

        $mappedOptions = array();

        foreach ($customOptions as $option) {
            $ebayValueTitle = '';
            $ebayValueTitle == '' && $ebayValueTitle = isset($ebayItemOptions[$option['store_title']]) ? $ebayItemOptions[$option['store_title']] : '';
            $ebayValueTitle == '' && $ebayValueTitle = isset($ebayItemOptions[$option['title']]) ? $ebayItemOptions[$option['title']] : '';
            $ebayValueTitle == '' && $ebayValueTitle = isset($ebayItemOptions[$option['default_title']]) ? $ebayItemOptions[$option['default_title']] : '';

            if ($ebayValueTitle == '') {
                continue;
            }

            foreach ($option['values'] as $value) {
                if ($ebayValueTitle == $value['store_title'] ||
                    $ebayValueTitle == $value['title'] ||
                    $ebayValueTitle == $value['default_title']) {

                    $mappedOptions[$option['option_id']] = $value['value_id'];
                    break;
                }
            }
        }

        return $mappedOptions;
    }

    // ########################################

    protected function getRelatedGroupedProduct(Ess_M2ePro_Model_MagentoProduct $magentoProduct, array $ebayItemOptions = array())
    {
        $associatedProducts = $magentoProduct->getProductVariationsForOrder();

        $variationName = array_shift($ebayItemOptions);

        foreach ($associatedProducts as $product) {
            $relatedProduct = NULL;

            if ($product instanceof Mage_Catalog_Model_Product) {
                $relatedProduct = $product;
            } else if (is_numeric($product)) {
                $relatedProduct = Mage::getModel('catalog/product')->load((int)$product);
            }

            if (is_null($relatedProduct) || !$relatedProduct->getId()) {
                continue;
            }

            // return product if it's name is equal to variation name
            // or if variation name is unavailable return first associated product
            if (is_null($variationName) || trim(strtolower($relatedProduct->getName())) == $variationName) {

                // Parser hack -> Mage::helper('M2ePro')->__('The eBay item is listed as simple (variations are ignored). As required options are missing, the first related grouped product is used to create Magento Order.');
                is_null($variationName) && $this->getOrder()->addWarningLogMessage('The eBay item is listed as simple (variations are ignored). As required options are missing, the first related grouped product is used to create Magento Order.');

                return $relatedProduct;
            }
        }

        return NULL;
    }

    // ########################################

    protected function getConfigurableProductOptions(Ess_M2ePro_Model_MagentoProduct $magentoProduct, array $ebayItemOptions = array())
    {
        $configurableOptions = $magentoProduct->getProductVariationsForOrder();

        if (!count($ebayItemOptions)) {
            // Variation info unavailable - return first associated products

            $firstOptions = array();
            foreach ($configurableOptions as $option) {
                $firstOptions[$option['option_id']] = $option['values'][0]['value_id'];
            }

            // Parser hack -> Mage::helper('M2ePro')->__('The eBay item is listed as simple (variations are ignored). As required options are missing, the first options value(s) is used to create Magento Order.');
            count($firstOptions) && $this->getOrder()->addWarningLogMessage('The eBay item is listed as simple (variations are ignored). As required options are missing, the first options value(s) is used to create Magento Order.');

            return $firstOptions;
        }

        $mappedOptions = array();

        foreach ($configurableOptions as $option) {
            $ebayValueLabel = '';
            $ebayValueLabel == '' && $ebayValueLabel = isset($ebayItemOptions[$option['store_label']]) ? $ebayItemOptions[$option['store_label']] : '';
            $ebayValueLabel == '' && $ebayValueLabel = isset($ebayItemOptions[$option['frontend_label']]) ? $ebayItemOptions[$option['frontend_label']] : '';
            $ebayValueLabel == '' && $ebayValueLabel = isset($ebayItemOptions[$option['label']]) ? $ebayItemOptions[$option['label']] : '';

            if ($ebayValueLabel == '') {
                continue;
            }

            foreach ($option['values'] as $value) {
                if ($ebayValueLabel == $value['store_label'] ||
                    $ebayValueLabel == $value['default_label'] ||
                    $ebayValueLabel == $value['label']) {

                    $mappedOptions[$option['option_id']] = $value['value_id'];
                    break;
                }
            }
        }

        return $mappedOptions;
    }

    // ########################################

    protected function getBundleProductOptions(Ess_M2ePro_Model_MagentoProduct $magentoProduct, array $ebayItemOptions = array())
    {
        $bundleOptions = $magentoProduct->getProductVariationsForOrder();

        if (!count($ebayItemOptions)) {
            // Variation info unavailable - return first value for each required option

            $firstOptions = array();
            foreach ($bundleOptions as $option) {
                $firstOptions[$option['option_id']] = $option['values'][0]['value_id'];
            }

            // Parser hack -> Mage::helper('M2ePro')->__('The eBay item is listed as simple (variations are ignored). As required options are missing, the first options value(s) is used to create Magento Order.');
            count($firstOptions) && $this->getOrder()->addWarningLogMessage('The eBay item is listed as simple (variations are ignored). As required options are missing, the first options value(s) is used to create Magento Order.');

            return $firstOptions;
        }

        $mappedOptions = array();

        foreach ($bundleOptions as $option) {
            if (count($option['values']) == 1) {
                $mappedOptions[$option['option_id']] = $option['values'][0]['value_id'];
                continue;
            }

            if (!isset($ebayItemOptions[$option['default_title']])) {
                continue;
            }

            foreach ($option['values'] as $value) {
                if ($ebayItemOptions[$option['default_title']] == $value['name']) {
                    $mappedOptions[$option['option_id']] = $value['value_id'];
                    break;
                }
            }
        }

        return $mappedOptions;
    }

    // ########################################

    /**
     * @return Mage_Tax_Model_Calculation
     */
    private function getTaxCalculator()
    {
        return Mage::getSingleton('tax/calculation');
    }

    protected function getPrice(Ess_M2ePro_Model_Orders_OrderItem $item)
    {
        $order = $this->getOrder();

        $orderTaxPercent = (float)$order->getData('sales_tax_percent');
        $itemPrice = (float)$item->getData('price');

        if ($orderTaxPercent > 0) {

            if ($this->isConfigPriceIncludesTax() && $order->hasTax()) {
                $taxAmount = $this->getTaxCalculator()->calcTaxAmount($itemPrice, $orderTaxPercent, false, false);
                $itemPrice += $taxAmount;
            }

            if (!$this->isConfigPriceIncludesTax() && $order->hasVat()) {
                $taxAmount = $this->getTaxCalculator()->calcTaxAmount($itemPrice, $orderTaxPercent, true, false);
                $itemPrice -= $taxAmount;
            }
        }

        return round($itemPrice / $this->_currencyConvertRate, 2);
    }

    protected function isConfigPriceIncludesTax()
    {
        return (bool)Mage::getStoreConfig(Mage_Tax_Model_Config::CONFIG_XML_PATH_PRICE_INCLUDES_TAX, $this->_magentoQuote->getStore());
    }

    // ########################################

    protected function getProductTaxClassId(Mage_Catalog_Model_Product $product)
    {
        if ($this->_productTaxClassId) {
            return $this->_productTaxClassId;
        }

        $orderTaxPercent = (float)$this->getOrder()->getData('sales_tax_percent');
        if ($orderTaxPercent <= 0) {
            $this->_productTaxClassId = $product->getTaxClassId();
            return $this->_productTaxClassId;
        }

        // Getting tax percent for magento product
        // -------------------------
        $productTaxPercent = $this->getTaxCalculator()->getRate($this->getTaxRequest($product));
        // -------------------------

        if ($orderTaxPercent == $productTaxPercent) {
            $this->_productTaxClassId = $product->getTaxClassId();
            return $this->_productTaxClassId;
        }

        /** @var $taxClassModel Ess_M2ePro_Model_Orders_Magento_Tax */
        $taxClassModel = Mage::getModel('M2ePro/Orders_Magento_Tax');
        $this->_productTaxClassId = $taxClassModel->createProductTaxClass($this->_magentoQuote->getCustomerTaxClassId(),
                                                                          (float)$this->getOrder()->getData('sales_tax_percent'),
                                                                          (string)$this->_magentoQuote->getShippingAddress()->getCountryId());

        return $this->_productTaxClassId;
    }

    protected function getTaxRequest(Mage_Catalog_Model_Product $product)
    {
        $request = $this->getTaxCalculator()->getRateRequest(
            $this->_magentoQuote->getShippingAddress(),
            $this->_magentoQuote->getBillingAddress(),
            $this->_magentoQuote->getCustomerTaxClassId(),
            $this->_magentoQuote->getStore()
        );
        $request->setProductClassId($product->getTaxClassId());
        return $request;
    }

    // ########################################

    protected function initializeShippingMethod()
    {
        $order = $this->getOrder();

        $shippingMethodTitle = $order->getData('shipping_selected_service') == 'NotSelected'
                                   ? 'Not Selected Yet'
                                   : $order->getData('shipping_selected_service');

        $shippingMethodData = array(
            'title' => $shippingMethodTitle,
            'price' => $this->getShippingPrice()
        );

        Mage::unregister('ebayShippingData');
        Mage::register('ebayShippingData', $shippingMethodData);

        $this->_magentoQuote->getShippingAddress()->setShippingMethod('m2eproshipping_m2eproshipping');
        $this->_magentoQuote->getShippingAddress()->setCollectShippingRates(true);

        $this->_magentoQuote->collectTotals()->save();

        return $this;
    }

    protected function getShippingPrice()
    {
        $order = $this->getOrder();
        $originalShippingPrice = $shippingPrice = (float)$order->getData('shipping_selected_cost');

        // Calculate shipping tax
        // -------------------------
        $taxPercent = (float)$this->getOrder()->getData('sales_tax_percent');
        if ($taxPercent > 0) {

            // getConfig will initialize requested key with default value
            $this->_storeShippingTaxClass = $this->_magentoQuote->getStore()->getConfig(Mage_Tax_Model_Config::CONFIG_XML_PATH_SHIPPING_TAX_CLASS);

            if ($this->isConfigShippingIncludesTax() && $taxPercent != $this->getStoreShippingTaxPercent()) {
                $this->_magentoQuote->getStore()->setConfig(Mage_Tax_Model_Config::CONFIG_XML_PATH_SHIPPING_TAX_CLASS, $this->_productTaxClassId);
            }

            if ($order->hasTax() && !$order->isShippingIncludesTax() && $this->isConfigShippingIncludesTax()) {
                $taxAmount = $this->getTaxCalculator()->calcTaxAmount($originalShippingPrice, $taxPercent, false, false);
                $shippingPrice += $taxAmount;
            }

            if ($order->hasTax() && $order->isShippingIncludesTax() && !$this->isConfigShippingIncludesTax()) {
                $taxAmount = $this->getTaxCalculator()->calcTaxAmount($originalShippingPrice, $taxPercent, true, false);
                $shippingPrice -= $taxAmount;
            }
        }
        // -------------------------

        // Convert shipping price to currency
        $convertRate = $this->_currencyConvertRate != 0 ? $this->_currencyConvertRate : 1;
        $shippingPrice = $shippingPrice / $convertRate;

        return round($shippingPrice, 2);
    }

    protected function isConfigShippingIncludesTax()
    {
        return (bool)Mage::getStoreConfig(Mage_Tax_Model_Config::CONFIG_XML_PATH_SHIPPING_INCLUDES_TAX, $this->_magentoQuote->getStore());
    }

    protected function getStoreShippingTaxPercent()
    {
        $shippingTaxClass = (int)Mage::getStoreConfig(Mage_Tax_Model_Config::CONFIG_XML_PATH_SHIPPING_TAX_CLASS, $this->_magentoQuote->getStore());
        $request = new Varien_Object();
        $request->setProductTaxClassId($shippingTaxClass);

        return $this->getTaxCalculator()->getStoreRate($request);
    }

    // ########################################

    protected function initializePaymentMethod()
    {
        $order = $this->getOrder();

        $paymentMethodTitle = $order->getData('payment_used') != 'None' ? $order->getData('payment_used') : 'Not Selected Yet';
        $paymentDetails = array(
            'method'                => 'm2epropayment',
            'ebay_payment_method'   => $paymentMethodTitle,
            'ebay_order_id'         => $order->getData('ebay_order_id'),
            'ebay_final_value_fee'  => $order->getData('ebay_final_value_fee'),
            'external_transactions' => $this->getOrder()->getPreparedExternalTransactions()
        );

        Mage::unregister('ebayPaymentData');
        Mage::register('ebayPaymentData', $paymentDetails);

        $quotePaymentObj = $this->_magentoQuote->getPayment();
        $quotePaymentObj->importData($paymentDetails);
        $this->_magentoQuote->setPayment($quotePaymentObj);

        return $this;
    }

    // ########################################

    protected function addOrderComment($message)
    {
        $this->_magentoOrderComments[] = $message;
    }

    public function getOrderComments()
    {
        return $this->_magentoOrderComments;
    }

    // ########################################
}
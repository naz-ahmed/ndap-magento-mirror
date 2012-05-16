ListingTemplatesHandlers = Class.create();
ListingTemplatesHandlers.prototype = Object.extend(new CommonHandlers(), {

    //----------------------------------

    initialize: function()
    {
        this.setValidationCheckRepetitionValue('M2ePro-listing-tpl-title',
                                                M2ePro.text.title_not_unique_error,
                                                'ListingsTemplates', 'title', 'id',
                                                M2ePro.formData.id);

        Validation.add('M2ePro-validate-vat', M2ePro.text.validate_vat_error, function(value) {
            if (!value) {
                return true;
            }

            if (value.length > 6) {
                return false;
            }

            value = Math.ceil(value);

            return value >= 0 && value <= 30;

        });

        Validation.add('M2ePro-validate-shipping-methods', M2ePro.text.no_shipping_method_error, function(value, el) {
            if (value != 0 && value != 1) {
                return true;
            }

            var type = el.name.split('_')[0];

            return ShippingsHandlersObj.counter[type] != 0;
        });

        Validation.add('M2ePro-validate-payment-methods', M2ePro.text.validate_payment_method_error, function(value) {
            var isChecked = false;
            $$('input[name="payments[]"]').each(function(o) {
                if (o.checked) {
                    isChecked = true;
                }
            });

            return isChecked;
        });

        Validation.add('M2ePro-validate-cash-on-delivery', M2ePro.text.validate_cash_on_delivery_error, function(value) {
            if (!$('local_shipping_cash_on_delivery_cost_mode_tr').visible() ||
                $('local_shipping_cash_on_delivery_cost_mode').value == ListingTemplatesHandlersObj.CASH_ON_DELIVERY_COST_MODE_NONE) {
                return true;
            }

            return $$('input[value="COD"]')[0].checked;
        });

        this.multiVariationEnabledMarketplaces = [0, 2, 3, 15, 16, 71, 77, 100, 101, 193, 203, 205, 207, 211];
    },

    //----------------------------------

    duplicate_click: function($headId)
    {
        var attrSetEl = $('attribute_sets_fake');

        if (attrSetEl) {
            $('attribute_sets').remove();
            attrSetEl.observe('change', AttributeSetsHandlersObj.changeAttributeSets);
            attrSetEl.id = 'attribute_sets';
            attrSetEl.name = 'attribute_sets[]';
            attrSetEl.addClassName('M2ePro-validate-attribute-sets');

            AttributeSetsHandlersObj.confirmAttributeSets();
        }

        if ($('attribute_sets_breadcrumb')) {
            $('attribute_sets_breadcrumb').remove();
        }
        $('attribute_sets_container').show();
        $('attribute_sets_buttons_container').show();

        CommonHandlersObj.duplicate_click($headId);
    },

    //----------------------------------

    attribute_sets_confirm: function()
    {
        var self = ListingTemplatesHandlersObj;

        AttributeSetsHandlersObj.confirmAttributeSets();

        AttributeSetsHandlersObj.renderAttributesWithEmptyOption('categories_main_attribute', 'main_category_attribute_td');
        AttributeSetsHandlersObj.renderAttributesWithEmptyOption('categories_secondary_attribute', 'secondary_category_attribute_td', null, true);

        AttributeSetsHandlersObj.renderAttributesWithEmptyOption('condition_attribute', 'item_condition_attribute_container_td');

        AttributeSetsHandlersObj.renderAttributesWithEmptyOption('local_shipping_cash_on_delivery_cost_attribute', 'local_shipping_cash_on_delivery_cost_attribute_td');

        M2ePro.customData.attributesReceiving.each(function(item) {
            AttributeSetsHandlersObj.renderAttributesWithEmptyOption(item.id, item.container);
        });

        // show filds wich depends on attributes set
        $$('.requirie-attribute-set').invoke('show');
    },

    //----------------------------------

    marketplace_id_change: function()
    {
        var self = ListingTemplatesHandlersObj;

        self.hideEmptyOption($('marketplace_id'));

        if ($F('categories_mode') != '' && $F('categories_mode') == 0) {

            if (M2ePro.customData.categoriesAlreadyRendered) {
                CategoriesHandlersObj.resetCategories();
            } else {
                M2ePro.customData.categoriesAlreadyRendered = true;
            }

            $$('.ebay-cat').invoke('hide');
            $('main_ebay_category_confirm_div', 'secondary_ebay_category_confirm_div').invoke('hide');
            $('categories_mode').simulate('change');
        }

        // payments, shippings, refund
        self.loadAllAboutMarketplace();

        self.setVisibilityForMultiVariation(false);

        //$('refund_notice_tr').hide();
        $$('.refund').invoke('show');
    },

    loadAllAboutMarketplace: function()
    {
        var self = this;

        self.loadMarketplaceInformation(function(data) {
            ShippingsHandlersObj.prepareData(
            {shipping: data.shipping, shipping_locations: data.shipping_locations, packages: data.packages},  $('marketplace_id').value);
            self.loadPayments(data.payments);
            self.reloadRefund(data.return_policy);
        });
    },

    loadMarketplaceInformation: function(handler)
    {
        var url = M2ePro.url.getMarketplaceInfo + 'id/' + $('marketplace_id').value;
        new Ajax.Request(url, {onSuccess: function(transport) {
            handler(transport.responseText.evalJSON());
        }});
    },

    //----------------------------------

    account_id_change: function()
    {
        var self = ListingTemplatesHandlersObj;

        self.hideEmptyOption($('account_id'));

        if ($('account_id').value) {
            $('magento_block_listing_template_general_account_store').show();
            self.getEbayStoreByAccount();
        } else {
            $('magento_block_listing_template_general_account_store').hide();
        }
    },

    setVisibilityForMultiVariation: function(variationEnabled)
    {
        var self = ListingTemplatesHandlersObj;
        var marketplaceId = parseInt($('marketplace_id').value);

        var marketplaceVariationEnabled = self.multiVariationEnabledMarketplaces.indexOf(marketplaceId) != -1;

        $('magento_block_listing_template_general_multivariation').show();

        if (marketplaceVariationEnabled) {

            $('block_notice_listing_template_multivariation_marketplace_disabled').hide();

            if (variationEnabled) {
                $('block_notice_listing_template_multivariation_category_enabled').show();
                $('multivariation_settings').show();
                $('block_notice_listing_template_multivariation_category_disabled').hide();
                $('variation_enabled').value = 1;

            } else {
                $('block_notice_listing_template_multivariation_category_enabled').hide();
                $('multivariation_settings').hide();
                $('block_notice_listing_template_multivariation_category_disabled').show();
                $('variation_enabled').value = 0;
            }

        } else {
            $('block_notice_listing_template_multivariation_category_enabled').hide();
            $('block_notice_listing_template_multivariation_category_disabled').hide();
            $('multivariation_settings').hide();
            $('block_notice_listing_template_multivariation_marketplace_disabled').show();
        }
    },

    getEbayStoreByAccount: function()
    {
        var self = ListingTemplatesHandlersObj;

        var renderer = self._storeSelectRenderer;
        var reloader = self._reloadSomething;

        var successHandler = function(data)
        {
            if (data.information.title == '') {
                $('magento_block_listing_template_general_account_store').hide();
                return;
            }

            $('store_name').innerHTML = data.information.title;
            renderer(data.categories, 'store_categories_main_id');
            renderer(data.categories, 'store_categories_secondary_id');

            var selectedMainCategoryId = M2ePro.formData.store_categories_main_id;
            var selectedSecondaryCategoryId = M2ePro.formData.store_categories_secondary_id;

            if (selectedMainCategoryId != 0) {
                $('store_categories_main_id').value = selectedMainCategoryId;
            }

            if (selectedSecondaryCategoryId != 0) {
                $('store_categories_secondary_id').value = selectedSecondaryCategoryId;
            }
        }
        
        reloader(M2ePro.url.getEbayStoreByAccount + 'account_id/' + $('account_id').value, '', successHandler);
    },

    updateEbayStoreByAccount_click: function()
    {
        var self = ListingTemplatesHandlersObj;
        
        new Ajax.Request(
            M2ePro.url.updateEbayStoreByAccount + 'account_id/' + $('account_id').value,
            {
                onSuccess: self.getEbayStoreByAccount
            }
        );
    },

    //----------------------------------

    loadPayments: function(data)
    {
        var self = ListingTemplatesHandlersObj;

        var txt = '';
        data.each(function(item) {
            if (item.ebay_id != 'PayPal') {
                txt += '<div id="payment_' + item.ebay_id + '_div" class="payment-method-container"><label>' +
                       '<input type="checkbox" name="payments[]" value="' + item.ebay_id + '" class="M2ePro-validate-payment-methods" /> ' + item.title +
                       '</label></div>';
            }
        });

        $('payment_methods_td').innerHTML = txt;
        $('payment_methods_tr').show();

        $$('input[name="payments[]"]').each(function(item) {
            if (M2ePro.formData.payments.indexOf(item.value) != -1) {
                item.checked = true;
            }
        });

        if (ShippingsHandlersObj.cashOnDeliverySites.indexOf(ShippingsHandlersObj.marketplaceId) != -1) {
            $$('input[value="COD"]')[0].observe('click', ShippingsHandlersObj.setCashOnDeliveryVisibility);
        }

        ShippingsHandlersObj.setCashOnDeliveryVisibility();
    },

    payments_change: function(checked)
    {
        $('paypal_address_tr')[checked ? 'show' : 'hide']();
        $('immediate_payment_tr')[checked ? 'show' : 'hide']();

        if (checked == false) {
            $('pay_pal_immediate_payment').checked = false;
            ListingTemplatesHandlersObj.immediate_payment_change(false);
        }
    },

    immediate_payment_change: function(checked)
    {
        $('magento_block_listing_template_payment_additional')[checked ? 'hide' : 'show']();

        $$('input[name="payments[]"]').each(function(payment) {
            if (payment.value != 'PayPal' && checked) {
                payment.checked = false;
            }
        });
    },

    filterPayments: function(payments)
    {
        var self = ListingTemplatesHandlersObj;

        $('payment_methods_tr').show();
        $$('.payment-method-container input').each(function(item) {
            if (payments.indexOf(item.value) == -1) {
                item.checked = false;
                $('payment_' + item.value + '_div').hide();
            }
        });

        self.payments_change($('pay_pal_method').checked)
        self.immediate_payment_change($('pay_pal_immediate_payment').checked);
    },

    unFilterPayments: function()
    {
        $('payment_methods_tr').show();
        $$('.payment-method-container').invoke('show');
    },

    //----------------------------------

   
    refund_accepted_change: function()
    {
        if (this.value == 'ReturnsAccepted') {
            $('refund_option_tr')[$$('#refund_option option').length ? 'show' : 'hide']();
            $('refund_within_tr')[$$('#refund_within option').length ? 'show' : 'hide']();
            $('refund_shippingcost_tr')[$$('#refund_shippingcost option').length ? 'show' : 'hide']();

            $('refund_description_tr').show();
        } else {
            $$('.refund-accepted').invoke('hide');
        }
    },

    _simpleSelectRenderer: function(data, id)
    {
        var options = '';
        data.each(function(paris) {
            var key;
            if (typeof paris.key != 'undefined') {
                key = paris.key;
            } else if (typeof paris.ebay_id != 'undefined') {
                key = paris.ebay_id;
            } else if (typeof paris.category_id != 'undefined') {
                key = paris.category_id;
            } else {
                key = paris.id;
            }
            var val = (typeof paris.value != 'undefined') ? paris.value : paris.title;
            options += '<option value="' + key + '">' + val + '</option>\n';
        });

        $(id).update();
        $(id).insert(options);

        if (M2ePro.formData[id]) {
            $(id).value = M2ePro.formData[id];
        } else {
            $(id).value = '';
        }
    },

    _storeSelectRenderer: function(data, id)
    {
        var options = '';
        var leafCount = 0;
        data.each(function(paris) {

            var key = paris.id;           
            var val = paris.title;
            var isLeaf = paris.is_leaf;


            if (isLeaf == 0) {
                if (leafCount != 0) {
                    options += '</optgroup>';
                    leafCount--;
                }
                options += '<optgroup label="' + val + '">';
                leafCount++;
            }
            else
            {
                options += '<option value="' + key + '">' + val + '</option>\n';
            }
        });

        $(id).update();
        $(id).insert(options);

        if (M2ePro.formData[id]) {
            $(id).value = M2ePro.formData[id];
        } else {
            $(id).value = '';
        }
    },

    _reloadSomething: function(url, id, successHandler)
    {
        var defaultSuccessHandler = this._simpleSelectRenderer;

        new Ajax.Request(url, {
            onSuccess: function (transport)
            {
                var data = transport.responseText.evalJSON(true);
                successHandler ? successHandler(data, id) : defaultSuccessHandler(data, id);
            }
        });
    },

    reloadRefund: function(data)
    {
        var self = ListingTemplatesHandlersObj;

        var assoc = {
            returns_accepted      : 'refund_accepted',
            refund                : 'refund_option',
            returns_within        : 'refund_within',
            shipping_cost_paid_by : 'refund_shippingcost'
        };

        $H(assoc).each(function(item) {
            var _data = data[item.key].length ? data[item.key] : [];
            self._simpleSelectRenderer(_data, item.value);
        });
        $('refund_accepted').simulate('change');

        $('listingTemplatesTabs_tab_refund').removeClassName('changed');
    }
    
    //----------------------------------
});
ShippingsHandlers = Class.create();
ShippingsHandlers.prototype = Object.extend(new CommonHandlers(), {

    //----------------------------------

    initialize: function()
    {
        this.counter = {local: 0, international: 0, total: 0};
        this.getItFastNotSupportSites = [2, 210, 186, 201, 203, 207, 211, 217];
        this.cashOnDeliverySites = [101];

        this.taxSites = [0, 2, 100, 210];
        this.vatSites = [16, 23, 123, 193, 77, 186, 71, 205, 203, 101, 146, 3];
        this.shippingRateSites = [0, 3, 77];

        this.englishUnitSites = [0, 100];
        this.marketplaceId = 0;

        this.defaultOptions = {
            'measurement_system': $('measurement_system').innerHTML,
            'local_shipping_mode': $('local_shipping_mode').innerHTML,
            'international_shipping_mode': $('international_shipping_mode').innerHTML
        };

        Validation.add('M2ePro-location-or-postal-required', M2ePro.text.no_location_or_postal_error, function(value) {
            return $('address').value != '' || $('postal_code').value != '';
        });

        Validation.add('M2ePro-validate-international-ship-to-location', M2ePro.text.no_international_ship_to_location_error, function(value, el) {
            var isChecked = false;

            $$('input[name="'+el.name+'"]').each(function(o) {
                if (o.checked) {
                    isChecked = true;
                }
            });

            return isChecked;
        });
    },

    //----------------------------------

    prepareCalculatedShippingObservers: function()
    {
        $('measurement_system')
                .observe('change', ShippingsHandlersObj.measurement_system_change)
                .simulate('change');

        $('package_size_mode')
                .observe('change', ShippingsHandlersObj.package_size_mode_change)
                .simulate('change');

        $('package_size_ebay')
                .observe('change', ShippingsHandlersObj.package_size_ebay_change)
                .simulate('change');

        $('dimension_mode')
                .observe('change', ShippingsHandlersObj.dimension_mode_change)
                .simulate('change');

        $('weight_mode')
                .observe('change', ShippingsHandlersObj.weight_mode_change)
                .simulate('change');
    },

    setSettingsForMarketplace: function(marketplaceId)
    {
        var self = ShippingsHandlersObj;

        self.marketplaceId = parseInt(marketplaceId);
        var showCalculated = (marketplaceId == 0 || marketplaceId == 2 || marketplaceId == 15 || marketplaceId == 100 || marketplaceId == 210);
        var showFreight = (marketplaceId == 0 || marketplaceId == 3 || marketplaceId == 15);

        self.setLocalShippingTypes(showCalculated, showFreight);
        self.setInternationalShippingTypes(showCalculated);

        var showGetItFast = ($('local_shipping_mode').value == 0 || $('local_shipping_mode').value == 1);
        self.setGetItFastVisibility(showGetItFast);

        self.setVATVisibility();
        self.setSalesTaxVisibility();
        self.setShippingRateVisibility();

        if ($('taxation_tax_table_tr').visible() || $('taxation_vat_tr').visible()) {
            $('magento_block_listing_template_shipping_tax').show();
        } else {
            $('magento_block_listing_template_shipping_tax').hide();
        }
        
        if (self.englishUnitSites.indexOf(self.marketplaceId) != -1) {
            $('measurement_system').remove(1);
        } else {
            $('measurement_system').update(self.defaultOptions['measurement_system']);
        }

        M2ePro.customData.marketplaceWasChanged = true;
    },

    setSalesTaxVisibility: function()
    {
        var self = ShippingsHandlersObj;

        if (self.taxSites.indexOf(self.marketplaceId) == -1) {
            $('taxation_tax_table_tr').hide();
        } else {
            $('taxation_tax_table_tr').show();
        }
    },

    setVATVisibility: function()
    {
        var self = ShippingsHandlersObj;

        if (self.vatSites.indexOf(self.marketplaceId) == -1) {
            $('taxation_vat_tr').hide();
        } else {
            $('taxation_vat_tr').show();
        }
    },

    setShippingRateVisibility: function()
    {
        var self = ShippingsHandlersObj;

        if (self.shippingRateSites.indexOf(self.marketplaceId) == -1) {
            $('magento_block_listing_template_shipping_rate').hide();
        } else {
            $('magento_block_listing_template_shipping_rate').show();
        }
    },

    //----------------------------------

    setGetItFastVisibility: function(showGetItFast)
    {
        var self = ShippingsHandlersObj;

        if (self.getItFastNotSupportSites.indexOf(self.marketplaceId) == -1 && showGetItFast) {
            $('local_shipping_git_tr').show();
        } else {
            $('local_shipping_git_tr').hide();
        }
    },

    setInternationalShippingTypes: function(showCalculated)
    {
        var self = ShippingsHandlersObj;

        if (!showCalculated) {
            $('international_shipping_mode').remove(2);
        } else {
            $('international_shipping_mode').update(self.defaultOptions['international_shipping_mode']);
        }

        if (M2ePro.customData.marketplaceWasChanged) {
            $('international_shipping_mode').value = 4;
        }
        $('international_shipping_mode').simulate('change');
    },

    setLocalShippingTypes: function(showCalculated, showFreight)
    {
        var self = ShippingsHandlersObj;

        $('local_shipping_mode').update(self.defaultOptions['local_shipping_mode']);
        if (!showCalculated) {
            $('local_shipping_mode_calculated').remove();
        }

        if (!showFreight) {
            $('local_shipping_mode_freight').remove();
        }

        if (M2ePro.customData.marketplaceWasChanged) {
            $('local_shipping_mode').value = 0;
        }
        $('local_shipping_mode').simulate('change');
    },

    setCashOnDeliveryVisibility: function()
    {
        var self = ShippingsHandlersObj;

        var showCashOnDelivery = self.cashOnDeliverySites.indexOf(self.marketplaceId) != -1 &&
                                 ($('local_shipping_mode').value == 0 || $('local_shipping_mode').value == 1);

        if (self.cashOnDeliverySites.indexOf(self.marketplaceId) != -1 && showCashOnDelivery) {
            $('local_shipping_cash_on_delivery_cost_mode_tr').show();
            $('local_shipping_cash_on_delivery_cost_mode').simulate('change');
        } else {
            $('local_shipping_cash_on_delivery_cost_mode_tr').hide();
            $('local_shipping_cash_on_delivery_cost_ca_tr').hide();
            $('local_shipping_cash_on_delivery_cost_cv_tr').hide();
        }
    },

    //----------------------------------

    postal_code_change: function()
    {
        if ($('postal_code').value != '' &&
            $('originating_postal_code').value == '' &&
            ($('local_shipping_mode').value == 1 || $('international_shipping_mode').value == 1)) {
            
            $('originating_postal_code').value = $('postal_code').value;
        }
    },

    local_shipping_mode_change: function()
    {
        var self = ShippingsHandlersObj;

        $('magento_block_listing_template_shipping_international').hide();
        $('block_notice_listing_template_shipping_local').hide();
        $('block_notice_listing_template_shipping_freight').hide();
        $('local_shipping_methods_tr').hide();

        // clear selected shipping methods
        $$('#shipping_local_tbody .icon-btn').each(function(el) {
            self.removeRow( el,'local');
        });

        $$('.local-shipping-tr').invoke((this.value == 0 || this.value == 1) ? 'show' : 'hide');

        var showGetItFast = (this.value == 0 || this.value == 1);
        self.setGetItFastVisibility(showGetItFast);

        self.setCashOnDeliveryVisibility();

        self.checkCalculatedOptionsVisibility();

        if (this.value == 0 || this.value == 1) {

            $('magento_block_listing_template_shipping_international').show();
            $('local_shipping_methods_tr').show();

            if (this.value == 1) {
                $('local_handling_cost_tr').show();
                $('local_handling_cost_mode').simulate('change');
                $('postal_code').simulate('change');
            } else {
                $('local_handling_cost_tr').hide();
                $('local_handling_cost_mode').value = 0;
                $('local_handling_cost_mode').simulate('change');
            }
        } else if (this.value == 2) {
            $('block_notice_listing_template_shipping_freight').show();
            $('international_shipping_mode').value = 4;
            $('international_shipping_mode').simulate('change');
        } else if (this.value == 3) {
            $('block_notice_listing_template_shipping_local').show();
            $('international_shipping_mode').value = 4;
            $('international_shipping_mode').simulate('change');
        }
    },

    international_shipping_mode_change: function()
    {
        var self = ShippingsHandlersObj;

        // clear selected shipping methods
        $$('#shipping_international_tbody .icon-btn').each(function(el) {
            self.removeRow( el,'international');
        });

        $$('.international-shipping-tr').invoke((this.value == 0 || this.value == 1) ? 'show' : 'hide');

        self.checkCalculatedOptionsVisibility();

        if (this.value == 1) {
            $('international_handling_cost_tr').show();
            $('international_handling_cost_mode').simulate('change');
            $('postal_code').simulate('change');
        } else {
            $('international_handling_cost_tr').hide();
            $('international_handling_cost_mode').value = 0;
            $('international_handling_cost_mode').simulate('change');
        }
    },

    use_ebay_local_shipping_rate_table_change: function()
    {
        if (this.value == '1') {
            var fakeLocalShippingMode = new Element('select', {
                id: 'local_shipping_mode_fake',
                name: 'local_shipping_mode'
            }).update($('local_shipping_mode').innerHTML);

            fakeLocalShippingMode.value = 0;
            fakeLocalShippingMode.disabled = true;

            $('local_shipping_mode').value = 0;
            $('local_shipping_mode').hide();

            $('local_shipping_mode').insert({after: fakeLocalShippingMode});
        } else {
            $('local_shipping_mode_fake') && Element.remove($('local_shipping_mode_fake'));
            $('local_shipping_mode').show();
        }

        if (M2ePro.customData.shippingsWasRendered) {
            $('local_shipping_mode').simulate('change');
        }
    },

    local_shipping_cash_on_delivery_cost_mode_change: function()
    {
        $('local_shipping_cash_on_delivery_cost_cv_tr', 'local_shipping_cash_on_delivery_cost_ca_tr').invoke('hide');

        if (this.value == 1) {
            $('local_shipping_cash_on_delivery_cost_cv_tr').show();
        } else if (this.value == 2) {
            $('local_shipping_cash_on_delivery_cost_ca_tr').show();
        }
    },

    //----------------------------------

    addRow: function(type) // local|international
    {
        var self = ShippingsHandlersObj;
        var id = 'shipping_' + type + '_tbody';
        var i = self.counter.total;

        var tpl = $$('#block_listing_template_shipping_table_row_template_table tbody')[0].innerHTML;
        tpl = tpl.replace(/%i%/g, i);
        tpl = tpl.replace(/%type%/g, type);
        $(id).insert(tpl);

        var row = $('shipping_variant_' + type + '_' + i + '_tr');

        AttributeSetsHandlersObj.renderAttributesWithEmptyOption('shipping_cost_attribute[' + i + ']', row.select('.shipping-cost-ca')[0]);
        AttributeSetsHandlersObj.renderAttributesWithEmptyOption('shipping_cost_additional_attribute[' + i + ']', row.select('.shipping-cost-additional-ca')[0]);
        this.renderServices(row.select('.shipping-service')[0], type);

        self.shipping_mode_one_row_change(row, $(type + '_shipping_mode').value == 1);

        if (type == 'international') {
            tpl = $$('#block_listing_template_shipping_table_locations_row_template_table tbody')[0].innerHTML;
            tpl = tpl.replace(/%i%/g, i);
            $(id).insert(tpl);
            self.renderShipToLocationCheckboxes(i);
        }

        self.counter[type]++;
        self.counter.total++;

        if (type == 'local' && self.counter[type] >= 4) {
            $(id).up('table').select('tfoot')[0].hide();
        }
        if (type == 'international' && self.counter[type] >= 5) {
            $(id).up('table').select('tfoot')[0].hide();
        }

        return row;
    },
    
    removeRow: function(btn, type)
    {
        var self = ShippingsHandlersObj;

        var table = $(btn).up('table');

        if (type == 'international') {
            $(btn).up('tr').next().remove();
        }

        $(btn).up('tr').remove();

        self.counter[type]--;

        if (type == 'local' && self.counter[type] < 4) {
            table.select('tfoot')[0].show();
        }
        if (type == 'international' && self.counter[type] < 5) {
            table.select('tfoot')[0].show();
        }

        self.checkCalculatedOptionsVisibility();
    },

    //----------------------------------

    renderServices: function(el, type)
    {
        var txt = '';
        var isCalculatedType = $F(type + '_shipping_mode') == 1;
        var SelectedPackage = $('package_size_ebay').value;
        var categoryMethods = '';

        // not selected international shipping service
        if (type == 'international') {
            txt += '<option value="">--</option>';
        }

        $H(this.data.shipping).each(function(category) {

            categoryMethods = '';
            category.value.methods.each(function(service) {
                var isServiceOfSelectedDestination = (type == 'local' && service.is_international == 0) || (type == 'international' && service.is_international == 1);
                var isServiceOfSelectedType = ( isCalculatedType && service.is_calculated == 1) || (! isCalculatedType && service.is_flat == 1);
                if (isServiceOfSelectedDestination && isServiceOfSelectedType) {
                    if (isCalculatedType) {
                        if (service.data.ShippingPackage.indexOf(SelectedPackage) != -1) {
                            categoryMethods += '<option value="' + service.ebay_id + '">' + service.title + '</option>';
                        }
                    } else {
                        categoryMethods += '<option value="' + service.ebay_id + '">' + service.title + '</option>';
                    }
                }
            });

            if (categoryMethods != '') {
                noCategoryTitle = category[0] == '';
                if (noCategoryTitle) {
                    txt += categoryMethods;
                } else {
                    txt += '<optgroup label="' + category.value.title + '">' + categoryMethods + '</optgroup>';
                }
            }
        });

        $(el).update(txt);
    },

    renderShipToLocationCheckboxes: function(i)
    {
        var self = ShippingsHandlersObj;
        var txt = '';

        self.data.shipping_locations.each(function(location) {
            if (location.ebay_id == 'Worldwide') {
                txt += '<div><label><input type="checkbox" name="shippingLocation[' + i + '][]" value="' + location.ebay_id +
                       '" onclick="ShippingsHandlersObj.shippingLocation_change(this)" class="shipping-location M2ePro-validate-international-ship-to-location" /> <b>' + location.title + '</b></label></div>';
            } else {
                txt += '<label style="float: left; width: 133px;" class="nobr"><input type="checkbox" name="shippingLocation[' + i + '][]" value="' + location.ebay_id +
                       '" onclick="ShippingsHandlersObj.shippingLocation_change(this)" /> ' + location.title + '</label>'
            }
        });

        $$('#shipping_variant_locations_' + i + '_tr td')[0].innerHTML = '<div style="width: 800px;">' + txt + '</div><div style="clear: both; margin-bottom: 10px;" />';

        if (!M2ePro.formData.shippings[i]) {
            return;
        }

        var locations = [];
        M2ePro.formData.shippings[i].locations.each(function(item) {
            locations.push(item);
        });
        $$('input[name="shippingLocation[' + i + '][]"]').each(function(el) {
            if (locations.indexOf(el.value) != -1) {
                el.checked = true;
            }
            self.shippingLocation_change(el);
        });
    },

    //----------------------------------

    prepareData: function(data, marketplaceId)
    {
        var self = this;

        // clear selected shipping methods
        $$('#shipping_local_tbody .shipping-variant, #shipping_international_tbody .shipping-variant').each(function(el) {
            el.remove();
        });

        self.counter = {local: 0, international: 0, total: 0};

        // hide options for calculated
        //$('block_listing_template_shipping_calculated_options').hide();

        function renderSelectedShippings()
        {
            M2ePro.formData.shippings.each(function(shipping, i) {

                M2ePro.formData.shippings[i].locations = shipping.locations.evalJSON();
                var type = shipping.shipping_type == 1 ? 'international' : 'local';
                var row = self.addRow(type);
                row.select('.shipping-service')[0].value = shipping.shipping_value;
                row.select('.cost-mode')[0].value = shipping.cost_mode;

                if (shipping.cost_mode == 1) { // cv
                    row.select('.shipping-cost-cv')[0].value = shipping.cost_value;
                    row.select('.shipping-cost-additional')[0].value = shipping.cost_additional_items;
                } else if (shipping.cost_mode == 2) { // ca
                    row.select('.shipping-cost-ca select')[0].value = shipping.cost_value;
                    row.select('.shipping-cost-additional-ca select')[0].value = shipping.cost_additional_items;
                }

                row.select('.shipping-priority')[0].value = shipping.priority;
                row.select('.cost-mode')[0].simulate('change');
                row.select('.shipping-service')[0].simulate('change');
            });

            M2ePro.customData.shippingsWasRendered = true;
            $('listingTemplatesTabs_tab_shipping').removeClassName('changed');
        }

        self.data = data;
        $('package_size_ebay').update();

        if (data.packages != null) {
            var txt = '';
            data.packages.each(function(item) {
                txt += '<option value="' + item.ebay_id + '" dimensions_supported="' + item.dimensions_supported + '">' + item.title + '</option>';
            });
            $('package_size_ebay').insert(txt);
            $('package_size_ebay').value = M2ePro.formData.package_size_ebay;
            $('package_size_ebay').simulate('change');
            $('package_size_mode').simulate('change');
        }

        self.setSettingsForMarketplace(marketplaceId);

        // call this func only once
        M2ePro.customData.shippingsWasRendered || renderSelectedShippings();
    },

    //----------------------------------

    shipping_service_change: function(el)
    {
        var row = $(el).up('tr');
        var type = /local/.test(row.id) ? 'local' : 'international';

        if (type == 'local') {
            return;
        }

        if (el.value === '') {
            row.select('.cost-mode')[0].hide();
            row.select('.shipping-cost-cv')[0].hide();
            row.select('.shipping-cost-ca')[0].hide();
            row.select('.shipping-cost-additional')[0].hide();
            row.select('.shipping-cost-additional-ca')[0].hide();
        } else {
            row.select('.cost-mode')[0].show();
            row.select('.cost-mode')[0].simulate('change');
        }
    },

    cost_mode_change: function(el)
    {
        var row = $(el).up('tr');
        var val = $(el).value;

        var inputCostCV = row.select('.shipping-cost-cv')[0];
        var inputCostCA = row.select('.shipping-cost-ca')[0];
        var inputCostAddCV = row.select('.shipping-cost-additional')[0];
        var inputCostAddCA = row.select('.shipping-cost-additional-ca')[0];
        var inputPriority = row.select('.shipping-priority')[0];

        [inputCostCV, inputCostCA, inputCostAddCV, inputCostAddCA].invoke('hide');

        inputPriority.show();

        if (val == 1) { // custom value

            inputCostCV.show();
            inputCostCV.disabled = false;

            inputCostAddCV.show();
            inputCostAddCV.disabled = false;

        } else if (val == 2) { // custom attribute

            inputCostCA.show();
            inputCostAddCA.show();

        } else if (val == 3) { // calculated

        } else { // free

            var isLocalMethod = /local/.test(row.id);
            var isLocalTypeCalculated = $('local_shipping_mode').value == '1';

            if (isLocalMethod && isLocalTypeCalculated) {
                inputPriority.value = 0;
                inputCostCV.value = 0;
                inputCostAddCV.value = 0;

                [inputPriority, inputCostCV, inputCostAddCV].invoke('hide');

            } else {
                inputCostCV.show();
                inputCostCV.value = 0;
                inputCostCV.disabled = true;

                inputCostAddCV.show();
                inputCostAddCV.value = 0;
                inputCostAddCV.disabled = true;
            }
        }
    },

    shippingLocation_change: function(el)
    {
        var i = el.name.match(/\d+/);

        if (el.value == 'Worldwide') {
            if (el.checked) {
                $$('input[name="shippingLocation[' + i + '][]"]').each(function(item) {
                    if (item != el) { // do not disable "Worldwide"
                        item.checked = false;
                        item.disabled = true;
                    }
                });
            } else {
                $$('input[name="shippingLocation[' + i + '][]"]').each(function(item) {
                    item.disabled = false;
                });
            }
        }
    },

    shipping_mode_one_row_change: function(row, isCalculated)
    {
        var type = /local/.test(row.id) ? 'local' : 'international';

        if (isCalculated) {
            row.select('.cost-mode')[0].value = 3; // select calculated
            row.select('.shipping-mode-option-notcalc').invoke('remove');

            if (type == 'international' || $$('#shipping_local_tbody .cost-mode').length > 1) {
                // only one calculated shipping method can have free mode
                row.select('.shipping-mode-option-free').invoke('remove');
            }
        } else {
            row.select('.cost-mode')[0].value = 0; // select free
            row.select('.shipping-mode-option-calc')[0].remove();
        }

        ShippingsHandlersObj.renderServices(row.select('.shipping-service')[0], type);

        row.select('.cost-mode')[0].simulate('change');
        row.select('.shipping-service')[0].simulate('change');
    },

    //----------------------------------

    showCalculatedOptions: function(type)
    {
        var tableHtml = '<table id="block_listing_template_shipping_calculated_options" cellpadding="0" cellspacing="0">'+
                        $('block_listing_template_shipping_calculated_options').innerHTML+
                        '</table>';

        var specifiedOptions = [];
        $('block_listing_template_shipping_calculated_options').select('tbody tr td.value select').each(function(item) {
            specifiedOptions[item.name] = item.value;
        });
        $('block_listing_template_shipping_calculated_options').select('tbody tr td.value input').each(function(item) {
            specifiedOptions[item.name] = item.value;
        });

        $('block_listing_template_shipping_calculated_options').remove();
        $(type+ '_shipping_calculated_options_td').innerHTML = tableHtml;

        $('block_listing_template_shipping_calculated_options').select('tbody tr td.value select').each(function(item) {
            item.value = specifiedOptions[item.name];
        });
        $('block_listing_template_shipping_calculated_options').select('tbody tr td.value input').each(function(item) {
            item.value = specifiedOptions[item.name];
        });
        this.prepareCalculatedShippingObservers();
        $(type+ '_shipping_calculated_options_td').show();
    },

    checkCalculatedOptionsVisibility: function()
    {
        var visible = {};

        visible.local = $('local_shipping_mode').value == 1;
        visible.international = $('international_shipping_mode').value == 1 && !visible.local;

        $('local_shipping_calculated_options_td').hide();
        $('international_shipping_calculated_options_td').hide();
     
        visible.local && this.showCalculatedOptions('local');
        visible.international && this.showCalculatedOptions('international');
    },

    //----------------------------------

    setDimensionVisibility: function(showDimension)
    {
        if (showDimension) {
            $('dimensions_tr').show();
            $('dimension_mode').simulate('change');
        } else {
            $('dimensions_tr').hide();
            $('dimension_mode').value = 0;
            $('dimension_mode').simulate('change');
        }
    },

    package_size_ebay_change: function()
    {
        showDimension = parseInt($('package_size_ebay').options[$('package_size_ebay').selectedIndex].getAttribute('dimensions_supported'));
        ShippingsHandlersObj.setDimensionVisibility(showDimension);
    },

    //----------------------------------
    
    package_size_mode_change: function()
    {
        $('package_size_attribute_tr', 'package_size_ebay_predefined_tr').invoke('hide');

        if (this.value != 0) {
            if (this.value == 1) {
                $('package_size_ebay_predefined_tr').show();
                $('package_size_ebay').simulate('change');
            } else {
                $('package_size_attribute_tr').show();
                ShippingsHandlersObj.setDimensionVisibility(true);
            }
        }
    },

    dimension_mode_change: function()
    {
        $('dimensions_ca_tr', 'dimensions_cv_tr').invoke('hide');

        if (this.value != 0) {
            $(this.value == 1 ? 'dimensions_cv_tr' : 'dimensions_ca_tr').show();
        }
    },

    local_handling_cost_mode_change: function()
    {
        $('local_handling_cost_cv_tr', 'local_handling_cost_ca_tr').invoke('hide');

        if (this.value != 0) {
            $(this.value == 1 ? 'local_handling_cost_cv_tr' : 'local_handling_cost_ca_tr').show();
        }
    },

    international_handling_cost_mode_change: function()
    {
        $('international_handling_cost_cv_tr', 'international_handling_cost_ca_tr').invoke('hide');

        if (this.value != 0) {
            $(this.value == 1 ? 'international_handling_cost_cv_tr' : 'international_handling_cost_ca_tr').show();
        }
    },

    weight_mode_change: function()
    {
        $('weight_cv', 'weight_ca').invoke('hide');
        $(this.value == 1 ? 'weight_cv' : 'weight_ca').show();
    },

    measurement_system_change: function()
    {
        $$('.measurement-system-english, .measurement-system-metric').invoke('hide');
        $$(this.value == 1 ? '.measurement-system-english' : '.measurement-system-metric').invoke('show');
    }

    //----------------------------------
});
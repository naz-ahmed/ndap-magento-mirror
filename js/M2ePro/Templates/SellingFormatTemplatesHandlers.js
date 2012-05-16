SellingFormatTemplatesHandlers = Class.create();
SellingFormatTemplatesHandlers.prototype = Object.extend(new CommonHandlers(), {

    //----------------------------------

    initialize: function()
    {
        this.setValidationCheckRepetitionValue('M2ePro-price-tpl-title',
                                                M2ePro.text.title_not_unique_error,
                                                'SellingFormatTemplates', 'title', 'id',
                                                M2ePro.formData.id);

        this.defaultOptions = {
            'duration_ebay' : $('duration_ebay').innerHTML,
            'qty_mode'      : $('qty_mode').innerHTML,
            'buyitnow_price_mode' : $('buyitnow_price_mode').innerHTML
        };

        Validation.add('M2ePro-validate-price-coefficient', M2ePro.text.price_coef_error, function(value)
        {
            if (value == '') {
                return true;
            }

            if (value == '0') {
                return false;
            }

            return value.match(/^[+-]?\d+[.,]?\d*[%]?$/g);
        });

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
        AttributeSetsHandlersObj.confirmAttributeSets();

        AttributeSetsHandlersObj.renderAttributesWithEmptyOption('listing_type_attribute', 'listing_type_custom_attribute_td');
        AttributeSetsHandlersObj.renderAttributesWithEmptyOption('duration_attribute', 'duration_attribute_span');
        AttributeSetsHandlersObj.renderAttributesWithEmptyOption('qty_custom_attribute', 'qty_custom_attribute_td');

        AttributeSetsHandlersObj.renderAttributesWithEmptyOption('start_price_custom_attribute', 'start_price_custom_attribute_td');
        AttributeSetsHandlersObj.renderAttributesWithEmptyOption('reserve_price_custom_attribute', 'reserve_price_custom_attribute_td');
        AttributeSetsHandlersObj.renderAttributesWithEmptyOption('buyitnow_price_custom_attribute', 'buyitnow_price_custom_attribute_td');

        // best offer
        AttributeSetsHandlersObj.renderAttributesWithEmptyOption('best_offer_accept_attribute', 'best_offer_accept_attribute_td');
        AttributeSetsHandlersObj.renderAttributesWithEmptyOption('best_offer_reject_attribute', 'best_offer_reject_attribute_td');
    },

    //----------------------------------

    start_price_mode_change : function()
    {
        var self = SellingFormatTemplatesHandlersObj;
        self.setPriceAttributeVisibility(this.value, 'start_price_custom_attribute_tr', null);
    },

    reserve_price_mode_change : function()
    {
        var self = SellingFormatTemplatesHandlersObj;
        self.setPriceAttributeVisibility(this.value, 'reserve_price_custom_attribute_tr', 'note_reserve_price_custom_attribute');
    },

    buyitnow_price_mode_change : function()
    {
        var self = SellingFormatTemplatesHandlersObj;
        self.setPriceAttributeVisibility(this.value, 'buyitnow_price_custom_attribute_tr', 'note_use_buy_from');
    },

    //----------------------------------

    best_offer_mode_change : function()
    {
        var self = SellingFormatTemplatesHandlersObj;

        if (this.value == self.BEST_OFFER_MODE_YES) {
            
            if (!AttributeSetsHandlersObj.checkAttributeSetSelection()) {
                this.value = 0;
                return;
            }
            $('best_offer_respond_table').show();
            $('best_offer_accept_mode').simulate('change');
            $('best_offer_reject_mode').simulate('change');

        } else {
            $('best_offer_respond_table').hide();
        }
    },

    best_offer_accept_mode_change : function()
    {
        var self = SellingFormatTemplatesHandlersObj;
        self.setVisibility(this.value, 'best_acc_input_select_tr', 'best_acc_input_text_tr')
    },

    best_offer_reject_mode_change : function()
    {
        var self = SellingFormatTemplatesHandlersObj;
        self.setVisibility(this.value, 'best_offer_reject_value_select_tr', 'best_offer_reject_value_text_tr')
    },

    //----------------------------------

    listing_type_change: function(event)
    {
        var self = SellingFormatTemplatesHandlersObj;

        var tempQtyModeValue = $('qty_mode').value;
        var tempBuyItNowPriceModeValue = $('buyitnow_price_mode').value;

        $('duration_ebay').value = 3; // 3 days allowed for auction and for fixed price
        $('duration_ebay').show();
        $('qty_mode').simulate('change');

        $('duration_attribute_span').hide();
        $('duration_ebay_note', 'duration_attribute_note').invoke('hide');
        $('listing_type_custom_attribute_tr').hide();

        if (this.value == self.LISTING_TYPE_FIXED) { //fixed price

            $('buyitnow_price_mode').update(self.defaultOptions['buyitnow_price_mode']);
            $('buyitnow_price_mode').select('option[value="0"]')[0].remove();
            $('buyitnow_price_mode').value = tempBuyItNowPriceModeValue;

            $('qty_mode').update(self.defaultOptions['qty_mode'])
            $('qty_mode').value = tempQtyModeValue;

            $('duration_ebay').update(self.defaultOptions['duration_ebay']);
            $('durationId1').remove();

            if (M2ePro.formData.duration_ebay && M2ePro.formData.duration_ebay != 1) {
                $('duration_ebay').value = M2ePro.formData.duration_ebay;
            }

            $('price_variation_table').show();

            $('start_price_table').hide();
            $('reserve_price_table').hide();
            $('duration_ebay_note').show();

            $('magento_block_selling_format_template_best_offer').show();

        } else if (this.value == self.LISTING_TYPE_ATTRIBUTE) { // custom attribute

            if (!AttributeSetsHandlersObj.checkAttributeSetSelection()) {
                this.value = M2ePro.formData.listing_type_selected;
                return;
            }

            $('listing_type_custom_attribute_tr').show();

            $('duration_ebay').hide();
            $('duration_attribute_span').show();

            $('qty_mode').update(self.defaultOptions['qty_mode'])
            $('qty_mode').value = tempQtyModeValue;

            $('buyitnow_price_mode').update(self.defaultOptions['buyitnow_price_mode']);
            $('buyitnow_price_mode').value = tempBuyItNowPriceModeValue;

            $('price_variation_table').show();

            $('start_price_table').show();
            $('reserve_price_table').show();
            $('duration_attribute_note').show();

            $('magento_block_selling_format_template_best_offer').show();
            
        } else { //auction

            $('qty_mode').update(self.defaultOptions['qty_mode']);
            $('qty_mode').value = self.QTY_MODE_SINGLE; // single item
            $('qty_mode_product', 'qty_mode_cv', 'qty_mode_ca').invoke('remove');
            $('qty_mode_cv_tr', 'qty_mode_ca_tr').invoke('hide');
            $('duration_ebay').update(self.defaultOptions['duration_ebay']);
            $('durationId30', 'durationId100').invoke('remove');

            $('buyitnow_price_mode').update(self.defaultOptions['buyitnow_price_mode']);
            $('buyitnow_price_mode').value = tempBuyItNowPriceModeValue;

            if (M2ePro.formData.duration_ebay &&
                M2ePro.formData.duration_ebay != 30 &&
                M2ePro.formData.duration_ebay != 100) {
                $('duration_ebay').value = M2ePro.formData.duration_ebay;
            }

            $('price_variation_table').hide();

            $('start_price_table').show();
            $('reserve_price_table').show();
            $('duration_ebay_note').show();

            $('magento_block_selling_format_template_best_offer').hide();

            $('best_offer_mode').value = self.BEST_OFFER_MODE_NO;
            $('best_offer_mode').simulate('change');
        }
    },

    qty_mode_change: function()
    {
        var self = SellingFormatTemplatesHandlersObj;

        $('qty_mode_cv_tr', 'qty_mode_ca_tr').invoke('hide');

        if (this.value == self.QTY_MODE_NUMBER) {
            $('qty_mode_cv_tr').show();
        } else if (this.value == self.QTY_MODE_ATTRIBUTE) {

            if (!AttributeSetsHandlersObj.checkAttributeSetSelection()) {
                this.value = 0;
                return;
            }

            $('qty_mode_ca_tr').show();
        }
    },

    //----------------------------------

    setVisibility : function(value, firstName, secondName)
    {
        var self = SellingFormatTemplatesHandlersObj;

        if (value == self.BEST_OFFER_ACCEPT_MODE_NO) {

            $(firstName).hide();
            $(secondName).hide();

        } else if (value == self.BEST_OFFER_ACCEPT_MODE_PERCENTAGE) {

            $(firstName).hide();
            $(secondName).show();

        } else { // ca

            if (!AttributeSetsHandlersObj.checkAttributeSetSelection()) {
                this.value = 0;
                return;
            }

            $(secondName).hide();
            $(firstName).show();

        }
    },

    setPriceAttributeVisibility : function(value, elementName, elementNameNote)
    {
        var self = SellingFormatTemplatesHandlersObj;

        if (elementNameNote != null) {
             $(elementNameNote, elementNameNote + '2').invoke(value == 0 ? 'hide' : 'show');
        }

        if (value == self.PRICE_ATTRIBUTE) {
            if (AttributeSetsHandlersObj.checkAttributeSetSelection()) {
                $(elementName).show();
            } else {
                this.value = 1;
            }
        } else {
            $(elementName).hide();
        }
    }

    //----------------------------------
});
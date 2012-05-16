SpecificsHandlers = Class.create();
SpecificsHandlers.prototype = Object.extend(new CommonHandlers(), {

    //----------------------------------

    initialize: function() {},

    //----------------------------------

    renderConditions: function(condition_mode, condition_values)
    {
        var optionsString = '';

        // disabled or not required
        if (parseInt(condition_mode) <= 0) {
            optionsString = '<option value="-1">---</option>';
        }

        if (!(typeof condition_values == "undefined")) {
            var ids = [];
            condition_values.each(function(item) {
                optionsString += '<option value="' + item.id + '">' + item.title + '</option>'
                ids.push(item.id);
            });
        }
        
        $('condition_value').update();
        $('condition_value').insert(optionsString);
        
        if (ids.indexOf(M2ePro.formData.condition_value) != -1) {
            $('condition_value').value = M2ePro.formData.condition_value;
        } else {
            $('condition_value').selectedIndex = 0;
        }
    },

    //----------------------------------

    renderItemSpecifics: function(item_specifics)
    {
        var self = this;

        $('item_specifics_container').innerHTML = '';
        $('listing_template_specifics_item_specifics_container').hide();
        
        if (item_specifics.specifics.length == 0) {
            $('item_specifics_container').innerHTML = str_replace('%cat%', $('main_ebay_category_breadcrumbs_div').innerHTML, M2ePro.text.category_without_item_specific_message);
            $('listing_template_specifics_item_specifics_container').show();
            return;
        }

        var i = 0;
        item_specifics.specifics.each(function(attribute) {

            var template = $('item_specifics_template').innerHTML;
            
            template = template.replace(/%i%/g, i);
            
            template = template.replace(/%relation_mode%/g, item_specifics.mode);
            template = template.replace(/%relation_id%/g, item_specifics.mode_relation_id);

            template = template.replace(/%attribute_id%/g, attribute.id);
            template = template.replace(/%attribute_title%/g, attribute.title);

            var eBayRecommendedOptionsString = '';
            attribute.values.each(function(ebay_recommended) {
                eBayRecommendedOptionsString += '<option value="' + base64_encode(ebay_recommended.id) + '-|-||-|-' + base64_encode(ebay_recommended.value) + '">' + ebay_recommended.value + '</option>';
            });


            $('item_specifics_container').insert(template);

            $('item_specifics_value_ebay_recommended_'+i).insert(eBayRecommendedOptionsString);
            $('item_specifics_value_ebay_recommended_'+i).selectedIndex = -1;

            AttributeSetsHandlersObj.renderAttributesWithEmptyOption('item_specifics_value_custom_attribute_' + i,
                                                                     'item_specifics_value_custom_attribute_td_' + i);

            if (item_specifics.mode == self.MODE_ATTRIBUTE_SET) {
                $('item_specifics_value_mode_'+i+'_option_'+self.VALUE_MODE_CUSTOM_ATTRIBUTE).remove();
            }

            if (attribute.type == self.RENDER_TYPE_TEXT) {
                $('item_specifics_value_mode_'+i+'_option_'+self.VALUE_MODE_EBAY_RECOMMENDED).remove();
            }

            if (attribute.type == self.RENDER_TYPE_SELECT_ONE || attribute.type == self.RENDER_TYPE_SELECT_MULTIPLE) {
                $('item_specifics_value_mode_'+i+'_option_'+self.VALUE_MODE_CUSTOM_VALUE).remove();
            }

            if (attribute.type == self.RENDER_TYPE_SELECT_MULTIPLE || attribute.type == self.RENDER_TYPE_SELECT_MULTIPLE_OR_TEXT) {
                $('item_specifics_value_ebay_recommended_'+i).writeAttribute("multiple", "true");
                var tempOldName = $('item_specifics_value_ebay_recommended_'+i).readAttribute("name");
                $('item_specifics_value_ebay_recommended_'+i).writeAttribute("name", tempOldName + '[]');
            }

            M2ePro.formData.item_specifics.each(function(currentSelected) {

                if (currentSelected.mode != item_specifics.mode ||
                    currentSelected.mode_relation_id != item_specifics.mode_relation_id ||
                    currentSelected.attribute_id != attribute.id ||
                    currentSelected.attribute_title != attribute.title) {
                    return;
                }

                if (currentSelected.value_mode == self.VALUE_MODE_EBAY_RECOMMENDED) {
                    $('item_specifics_value_mode_'+i+'_option_'+self.VALUE_MODE_EBAY_RECOMMENDED).selected = true;
                    $$('#item_specifics_value_ebay_recommended_'+i+' option').each(function(tempOption){
                        currentSelected.value_data.each(function(tempSelected) {
                            var tempSearchValue = base64_encode(tempSelected.id) + '-|-||-|-' + base64_encode(tempSelected.value);
                            if(tempOption.value == tempSearchValue){
                                tempOption.selected = true;
                                return;
                            }
                        });
                    });
                }

                if (currentSelected.value_mode == self.VALUE_MODE_CUSTOM_VALUE) {
                    $('item_specifics_value_mode_'+i+'_option_'+self.VALUE_MODE_CUSTOM_VALUE).selected = true;
                    $('item_specifics_value_custom_value_'+i).setValue(currentSelected.value_data);
                }

                if (currentSelected.value_mode == self.VALUE_MODE_CUSTOM_ATTRIBUTE) {
                    $('item_specifics_value_mode_'+i+'_option_'+self.VALUE_MODE_CUSTOM_ATTRIBUTE).selected = true;
                    $$('#item_specifics_value_custom_attribute_'+i+' option').each(function(tempOption){
                        if(tempOption.value == currentSelected.value_data){
                            tempOption.selected = true;
                            return;
                        }
                    });
                }
            });

            self.item_specific_mode_change($('item_specifics_value_mode_'+i));
            
            i++;
        });

        $('listing_template_specifics_item_specifics_container').show();
    },

    item_specific_mode_change: function(element)
    {
        var self = this;
        
        var i = element.id.replace('item_specifics_value_mode_', '');

        $('item_specifics_value_ebay_recommended_container_' + i,
          'item_specifics_value_custom_value_container_' + i,
          'item_specifics_value_custom_attribute_container_' + i).invoke('hide');

        if (element.value == self.VALUE_MODE_EBAY_RECOMMENDED) {
            $('item_specifics_value_ebay_recommended_container_'+i).show();
        }
        if (element.value == self.VALUE_MODE_CUSTOM_VALUE) {
            $('item_specifics_value_custom_value_container_'+i).show();
        }
        if (element.value == self.VALUE_MODE_CUSTOM_ATTRIBUTE) {
            $('item_specifics_value_custom_attribute_container_'+i).show();
        }
    },

    //----------------------------------

    product_details_isbn_mode_change: function()
    {
        SpecificsHandlersObj.changeDetailsMode(this.value,'product_details_isbn_cv', 'product_details_isbn_ca_tr');
    },

    product_details_epid_mode_change: function()
    {
        SpecificsHandlersObj.changeDetailsMode(this.value, 'product_details_epid_cv', 'product_details_epid_ca_tr');
    },

    product_details_upc_mode_change: function()
    {
        SpecificsHandlersObj.changeDetailsMode(this.value, 'product_details_upc_cv', 'product_details_upc_ca_tr');
    },

    product_details_ean_mode_change: function()
    {
        SpecificsHandlersObj.changeDetailsMode(this.value, 'product_details_ean_cv', 'product_details_ean_ca_tr');
    },

    changeDetailsMode: function(mode, valueContent, attributeContent)
    {
        var self = SpecificsHandlersObj;

        if (mode == self.PRODUCT_DETAIL_MODE_NONE) {
            $(valueContent).hide();
            $(attributeContent).hide();
        } else if (mode == self.PRODUCT_DETAIL_MODE_CUSTOM_VALUE) {
            $(attributeContent).hide();
            $(valueContent).show();
        } else {
            $(valueContent).hide();
            $(attributeContent).show();
        }
    },

    setVisibilityForProductDetails: function(catalogEnabled)
    {
        if (catalogEnabled) {
            $('listing_template_specifics_details_container').show();
        } else {
            $('listing_template_specifics_details_container').hide();
        }
        $('listing_template_specifics_item_specifics_container').hide();
    }

    //----------------------------------
});
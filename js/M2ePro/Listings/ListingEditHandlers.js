ListingEditHandlers = Class.create();
ListingEditHandlers.prototype = Object.extend(new CommonHandlers(), {

    //----------------------------------

    initialize: function()
    {
        this.setValidationCheckRepetitionValue('M2ePro-listing-title',
                                                M2ePro.text.title_not_unique_error,
                                                'Listings', 'title', 'id',
                                                M2ePro.formData.id);

        Validation.add('M2ePro-input-datetime', M2ePro.text.wrong_date_time_format_error, function(value,el) {
            if ($(el).up('tr').visible()) {
                return value.match(/^\d{4}-\d{2}-\d{1,2}\s\d{2}:\d{2}:\d{2}$/g);
            }
            return true;
        });
    },

    //----------------------------------

    attribute_set_change: function(event)
    {   
        if ($('selling_format_template_autocomplete')) {
            $('selling_format_template_autocomplete').value = '';
        }
        if ($('description_template_autocomplete')) {
            $('description_template_autocomplete').value = '';
        } 
        if ($('listing_template_autocomplete')) {
            $('listing_template_autocomplete').value = '';
        } 
        if ($('selling_format_template_id')) {
            $('selling_format_template_id').value = '';
        } 
        if ($('description_template_id')) {
            $('description_template_id').value = '';
        } 
        if ($('listing_template_id')) {
            $('listing_template_id').value = '';
        } 
        
        if (M2ePro.autoCompleteData.flags.sellingFormatTemplatesDropDown) {
            ListingEditHandlersObj.reloadSellingFormatTemplates();
        } else {
            $('selling_format_template_autocomplete').remove();
            newInput = new Element('input', {
                id         : 'selling_format_template_autocomplete',
                selected_id: '',
                style      : 'width: 280px'
            });
            $('selling_format_template_cell').insert({top: newInput});
            AutoCompleteHandler.bind(
                "selling_format_template_autocomplete",
                M2ePro.autoCompleteData.url.getSellingFormatTemplatesBySet + 'attribute_set_id/' + $('attribute_set_id').value,
                M2ePro.formData.selling_format_template_id > 0 ? M2ePro.formData.selling_format_template_id : '',
                M2ePro.formData.selling_format_template_title,
                function (id) {$('selling_format_template_id').value = id}
            );
            $('selling_format_template_id').value = $('selling_format_template_autocomplete').readAttribute('selected_id');
        }

        if (M2ePro.autoCompleteData.flags.descriptionsTemplatesDropDown) {
            ListingEditHandlersObj.reloadDescriptionTemplates();
        } else {
            $('description_template_autocomplete').remove();
            newInput = new Element('input', {
                id         : 'description_template_autocomplete',
                selected_id: '',
                style      : 'width: 280px'
            });
            $('description_template_cell').insert({top: newInput});
            AutoCompleteHandler.bind(
                "description_template_autocomplete",
                M2ePro.autoCompleteData.url.getDescriptionTemplatesBySet + 'attribute_set_id/' + $('attribute_set_id').value,
                M2ePro.formData.description_template_id > 0 ? M2ePro.formData.description_template_id : '',
                M2ePro.formData.description_template_title,
                function (id) {$('description_template_id').value = id}
            );
            $('description_template_id').value = $('description_template_autocomplete').readAttribute('selected_id');
        }

        if (M2ePro.autoCompleteData.flags.listingsTemplatesDropDown) {
            ListingEditHandlersObj.reloadListingTemplates();
        } else {
            $('listing_template_autocomplete').remove();
            newInput = new Element('input', {
                id         : 'listing_template_autocomplete',
                selected_id: '',
                style      : 'width: 280px'
            });
            $('listing_template_cell').insert({top: newInput});
            AutoCompleteHandler.bind(
                "listing_template_autocomplete",
                M2ePro.autoCompleteData.url.getListingTemplatesBySet + 'attribute_set_id/' + $('attribute_set_id').value,
                M2ePro.formData.listing_template_id > 0 ? M2ePro.formData.listing_template_id : '',
                M2ePro.formData.listing_template_title,
                function (id) {$('listing_template_id').value = id}
            );
            $('listing_template_id').value = $('listing_template_autocomplete').readAttribute('selected_id');
        }
        
        ListingEditHandlersObj.hideEmptyOption(this);

        $$('button.add').each(function(obj) {
            var onclickAction = obj.readAttribute('onclick');

            if (onclickAction.match(/attribute_set_id\/[-]?\d+/)) {
                onclickAction = onclickAction.replace(/attribute_set_id\/[-]?\d+/, 'attribute_set_id/' + $('attribute_set_id').value + '/');
            } else {
                onclickAction = onclickAction.replace(/new\//, 'new/attribute_set_id/' + $('attribute_set_id').value + '/');
            }

            obj.writeAttribute('onclick', onclickAction);
        });
    },

    //----------------------------------

    reloadSellingFormatTemplates: function()
    {
        var attribute_set_id = $('attribute_set_id').value;
        if (typeof attribute_set_id == 'undefined' || attribute_set_id == null || attribute_set_id == '') {
            alert(M2ePro.text.attribute_set_not_selected_error);
            return;
        }

        ListingEditHandlersObj.reloadByAttributeSet(M2ePro.url.getSellingFormatTemplatesBySet + 'attribute_set_id/' + attribute_set_id, 'selling_format_template_id');
    },

    reloadListingTemplates: function()
    {
        var attribute_set_id = $('attribute_set_id').value;
        if (typeof attribute_set_id == 'undefined' || attribute_set_id == null || attribute_set_id == '') {
            alert(M2ePro.text.attribute_set_not_selected_error);
            return;
        }

        ListingEditHandlersObj.reloadByAttributeSet(M2ePro.url.getListingTemplatesBySet + 'attribute_set_id/' + attribute_set_id, 'listing_template_id');
    },

    reloadDescriptionTemplates: function()
    {
        var attribute_set_id = $('attribute_set_id').value;
        if (typeof attribute_set_id == 'undefined' || attribute_set_id == null || attribute_set_id == '') {
            alert(M2ePro.text.attribute_set_not_selected_error);
            return;
        }

        ListingEditHandlersObj.reloadByAttributeSet(M2ePro.url.getDescriptionTemplatesBySet + 'attribute_set_id/' + attribute_set_id, 'description_template_id');
    },

    reloadSynchronizationTemplates: function()
    {
        ListingEditHandlersObj.reloadByAttributeSet(M2ePro.url.getSynchronizationTemplates, 'synchronization_template_id');
    },

    //----------------------------------

    synchronization_template_id_change: function()
    {
        ListingEditHandlersObj.hideEmptyOption(this);
    },

    synchronization_start_type_change: function()
    {
        var self = ListingEditHandlersObj,
            value = $('synchronization_start_type').value;

        if (value == self.SYNCHRONIZATION_START_TYPE_THROUGH) {
            $('synchronization_start_through_value_container').show();
            $('synchronization_start_date_container').hide();
        } else if (value == self.SYNCHRONIZATION_START_TYPE_DATE) {
            $('synchronization_start_through_value_container').hide();
            $('synchronization_start_date_container').show();
        } else{
            $('synchronization_start_through_value_container').hide();
            $('synchronization_start_date_container').hide();
        }
    },

    synchronization_stop_type_change: function()
    {
        var self = ListingEditHandlersObj,
            value = $('synchronization_stop_type').value;

        if (value == self.SYNCHRONIZATION_STOP_TYPE_THROUGH) {
            $('synchronization_stop_through_value_container').show();
            $('synchronization_stop_date_container').hide();
        } else if (value == self.SYNCHRONIZATION_STOP_TYPE_DATE) {
            $('synchronization_stop_through_value_container').hide();
            $('synchronization_stop_date_container').show();
        } else {
            $('synchronization_stop_through_value_container').hide();
            $('synchronization_stop_date_container').hide();
        }
    },

    //----------------------------------

    reloadByAttributeSet: function(url, id)
    {
        new Ajax.Request(url, {
            onSuccess: function (transport)
            {
                var data = transport.responseText.evalJSON(true);
                var options = '';

                var firstItemValue = '';
                var currentValue = $(id).value;

                data.each(function(paris) {
                    var key = (typeof paris.key != 'undefined') ? paris.key : paris.id;
                    var val = (typeof paris.value != 'undefined') ? paris.value : paris.title;
                    options += '<option value="' + key + '">' + val + '</option>\n';

                    if (firstItemValue == '') {
                        firstItemValue = val;
                    }
                });

                $(id).update();
                $(id).insert(options);

                if (currentValue != '') {
                    $(id).value = currentValue;
                } else {
                    if (M2ePro.formData[id] > 0) {
                        $(id).value = M2ePro.formData[id];
                    } else {
                        $(id).value = firstItemValue;
                    }
                }
            }
        });
    }

    //----------------------------------
});
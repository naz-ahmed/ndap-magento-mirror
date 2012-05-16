DescriptionTemplatesHandlers = Class.create();
DescriptionTemplatesHandlers.prototype = Object.extend(new CommonHandlers(), {

    //----------------------------------

    initialize: function()
    {
        this.setValidationCheckRepetitionValue('M2ePro-description-tpl-title',
                                                M2ePro.text.title_not_unique_error,
                                                'DescriptionsTemplates', 'title', 'id',
                                                M2ePro.formData.id);
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

    preview_click: function()
    {
        this.submitForm(M2ePro.url.preview, true);
    },

    //----------------------------------

    attribute_sets_confirm: function(event)
    {
        var self = DescriptionTemplatesHandlersObj;

        AttributeSetsHandlersObj.confirmAttributeSets();

        AttributeSetsHandlersObj.renderAttributesWithEmptyOption('image_main_attribute', 'image_main_attribute_td');
        AttributeSetsHandlersObj.renderAttributes('select_attributes_for_title', 'select_attributes_for_title_span', 0, '150');
        AttributeSetsHandlersObj.renderAttributes('select_attributes_for_subtitle', 'select_attributes_for_subtitle_span', 0, '150');
        AttributeSetsHandlersObj.renderAttributes('select_attributes', 'select_attributes_span');

        new Ajax.Request( M2ePro.url.getAttributesForConfigurableProduct + 'attribute_sets/' + AttributeSetsHandlersObj.getConfirmedAttributeSets() + '/',
        {
            method: 'post',
            asynchronous : false,
            onSuccess: function (transport)
            {
                var data = transport.responseText.evalJSON(true);

                var optionsString = '<option value="">'+M2ePro.text.none_word+'</option>\n';
                data.each(function(item) {
                    var selectedOption = '';
                    if (item.value == M2ePro.formData.variation_configurable_images) {
                        selectedOption = ' selected="selected" ';
                    }
                    optionsString += '<option value="' + item.value + '" ' + selectedOption + '>' + item.title + '</option>\n';
                });

                $('variation_configurable_images').update(optionsString);
                $('variation_configurable_images_container').show();
            }
        });
    },

    //----------------------------------

    title_mode_change: function()
    {
        var self = DescriptionTemplatesHandlersObj;
        self.setTextVisibilityMode(this, 'custom_title_tr');
    },

    subtitle_mode_change: function()
    {
        var self = DescriptionTemplatesHandlersObj;
        self.setTextVisibilityMode(this, 'custom_subtitle_tr');
    },

    description_mode_change: function()
    {
        var self = DescriptionTemplatesHandlersObj;

        $$('.c-custom_description_tr').invoke('hide');

        if (this.value == self.DESCRIPTION_MODE_CUSTOM) {
            if (AttributeSetsHandlersObj.checkAttributeSetSelection()) {
                $$('.c-custom_description_tr').invoke('show');
            } else {
                this.value = 0;
            }
        }
    },

    setTextVisibilityMode: function(obj, elementName)
    {
        var self = DescriptionTemplatesHandlersObj;

        if (obj.value == 1) {

            if (!AttributeSetsHandlersObj.checkAttributeSetSelection()) {
                this.value = 0;
                obj.value = 0;
                return;
            }
            $(elementName).show();
            
        } else {
            $(elementName).hide();
        }
    },

    //----------------------------------

    image_main_mode_change: function()
    {
        var self = DescriptionTemplatesHandlersObj;

        if (this.value == self.IMAGE_MAIN_MODE_NONE) {
            $('gallery_images_mode_tr', 'variation_configurable_images_container').invoke('hide');
            $('gallery_images_mode').value = 0;
            $('variation_configurable_images').value = 0;
        } else {
            $('gallery_images_mode_tr', 'variation_configurable_images_container').invoke('show');
        }

        if (this.value == self.IMAGE_MAIN_MODE_ATTRIBUTE && !AttributeSetsHandlersObj.checkAttributeSetSelection()) {
            this.value = M2ePro.formData.image_main_mode;
            return;
        }

        $('image_main_attribute_tr')[this.value == self.IMAGE_MAIN_MODE_ATTRIBUTE ? 'show' : 'hide']();
    },

    //----------------------------------

    image_width_mode_change: function()
    {
        $('image_width_span')[this.value == 1 ? 'show' : 'hide']();
    },

    image_height_mode_change: function()
    {
        $('image_height_span')[this.value == 1 ? 'show' : 'hide']();
    },

    image_margin_mode_change: function()
    {
        $('image_margin_span')[this.value == 1 ? 'show' : 'hide']();
    },

    select_attributes_image_change: function()
    {
        $$('.all-products-images').invoke(this.value == 'media_gallery' ? 'show' : 'hide');
        if (this.value == 'image') {
            $('display_products_images').value = 'custom_settings';
        }
        $('display_products_images').simulate('change');
    },

    display_products_images_change: function()
    {
        $$('.products-images-custom-settings').invoke('hide');
        $$('.products-images-gallery-view').invoke('hide');

        if (this.value == 'gallery_view') {
            $$('.products-images-gallery-view').invoke('show');
        } else {
            $$('.products-images-custom-settings').invoke('show');
        }
    },

    insertGallery: function()
    {
        var template = '#' + $('select_attributes_image').value;

        if ($('image_width_mode').value == '1') {
            template += '[' + $('image_width').value + ',';
        } else {
            template += '[,';
        }
        
        if ($('image_height_mode').value == '1') {
            template += '' + $('image_height').value + ',';
        } else {
            template += ',';
        }

        if ($('image_margin_mode').value == '1') {
            template += '' + $('image_margin').value + ',';
        } else {
            template += ',';
        }

        if ($('select_attributes_image').value == 'media_gallery' && $('display_products_images').value == 'gallery_view')  {
            template += '2';
        } else if ($('image_linked_mode').value == '1') {
            template += '1';
        } else {
            template += "0";
        }

        if ($('select_attributes_image').value == 'media_gallery') {
            template += ',' + $('select_attributes_image_layout').value + ',' + $('select_attributes_image_count').value;
        }

        if ($('select_attributes_image').value == 'media_gallery' && $('display_products_images').value == 'gallery_view') {
            template += ',"' + $('gallery_hint_text').value + '"';
        } else if ($('select_attributes_image').value == 'media_gallery') {
            //media_gallery with empty gallery hint
            template += ',""';
        }

        template += ']#';

        AttributeSetsHandlersObj.appendToTextarea(template);
        DescriptionTemplatesHandlersObj.stopObservingImageAttributes();
    },

    //----------------------------------

    observeImageAttributes : function()
    {
        $('image_width_mode').observe('change', DescriptionTemplatesHandlersObj.image_width_mode_change);
        $('image_height_mode').observe('change', DescriptionTemplatesHandlersObj.image_height_mode_change);
        $('image_margin_mode').observe('change', DescriptionTemplatesHandlersObj.image_margin_mode_change);

        $('select_attributes_image')
                .observe('change', DescriptionTemplatesHandlersObj.select_attributes_image_change)
                .simulate('change');

        $('display_products_images')
                .observe('change', DescriptionTemplatesHandlersObj.display_products_images_change);
    },

    stopObservingImageAttributes : function()
    {
        $('image_width_mode').stopObserving();
        $('image_height_mode').stopObserving();
        $('image_margin_mode').stopObserving();

        $('select_attributes_image').stopObserving();

        dialog_image_window.close();
    },

    openInsertImageWindow : function()
    {
        dialog_image_window = Dialog.info('', {
            draggable: true,
            resizable: true,
            closable: true,
            className: "magento",
            windowClassName: "popup-window",
            title: M2ePro.text.adding_image_message,
            top: 150,
            width: 650,
            height: 350,
            zIndex: 100,
            recenterAuto: false,
            hideEffect: Element.hide,
            showEffect: Element.show,
            id: "new-image"
        });

        $('modal_dialog_message').insert($('image_insertion').innerHTML);

        $('new-image_close').writeAttribute('onclick', 'DescriptionTemplatesHandlersObj.stopObservingImageAttributes()');

        DescriptionTemplatesHandlersObj.observeImageAttributes();
    }

    //----------------------------------
});
CategoriesHandlers = Class.create();
CategoriesHandlers.prototype = Object.extend(new CommonHandlers(), {

    //----------------------------------

    initialize: function() {},

    //----------------------------------

    resetCategories: function()
    {
        $('main_ebay_category-1').insert('<option class="empty" value=""></option>');
        $('main_ebay_category-1').value = '';

        $('secondary_ebay_category-1').insert('<option class="empty" value=""></option>');
        $('secondary_ebay_category-1').value = '';

        $('categories_main_id').value = '';
        $('categories_secondary_id').value = '';
    },

    //----------------------------------

    categories_change: function(event)
    {
        var ddId = event.element().id;
        var type = ddId.slice(0, 4) == 'main' ? 'main' : 'secondary';
        $(type + '_ebay_category_cancel_button').hide();

        // (main|secondary)_ebay_category-i
        var i = ddId[ddId.length - 1];
        i = i - 0 + 1; // toInt
        ddId = ddId.slice(0, -1) + i;
        while (i <= 7) {
            $(ddId.slice(0, -1) + i).hide();
            i++;
        }

        if (!this.value && event.element().id == 'main_ebay_category-1') {
            $('categories_main_id').value = '';
        }

        if (CategoriesHandlersObj._isCategoryLeaf(this)) {
             CategoriesHandlersObj.showCategoryConfirm(ddId);
        } else {
            this.value && CategoriesHandlersObj.loadCategory(this.value, ddId); // do not load root here
        }

        if (!this.value) { // does category selected?
            $(type + '_ebay_category_confirm_div').hide();
        }
    },

    categories_mode_change: function()
    {
        if (!AttributeSetsHandlersObj.checkAttributeSetSelection()) {
            this.value = '';
            return;
        }

        if (!$F('marketplace_id')) {
            alert(M2ePro.text.marketplace_not_selected_error);
            this.value = '';
            return;
        }

        ListingTemplatesHandlersObj.hideEmptyOption(this);

        if (this.value == 1) { // custom attribute

            $('item_condition_value_container').hide();
            $('item_condition_attribute_container').show();

            $('main_ebay_category_tr', 'secondary_ebay_category_tr').invoke('hide');
            $('main_category_attribute_tr', 'secondary_category_attribute_tr').invoke('show');
            ListingTemplatesHandlersObj.setNotRequiried('categories_main_id');

            // show all payments, no matter what category selected
            ListingTemplatesHandlersObj.unFilterPayments();

            $('main_ebay_category_by_id_td', 'secondary_ebay_category_by_id_td').invoke('hide');

            ListingTemplatesHandlersObj.setVisibilityForMultiVariation(true);
            SpecificsHandlersObj.setVisibilityForProductDetails(true);
            ListingTemplatesHandlersObj.payments_change($('pay_pal_method').checked)
            ListingTemplatesHandlersObj.immediate_payment_change($('pay_pal_immediate_payment').checked);
        } else { // ebay cat. source

            $('main_ebay_category_tr', 'secondary_ebay_category_tr').invoke('show');

            $('item_condition_value_container').show();
            $('item_condition_attribute_container').hide();

            CategoriesHandlersObj.initCategory('main');
            ListingTemplatesHandlersObj.setRequiried('categories_main_id');

            CategoriesHandlersObj.initCategory('secondary');
        }
    },

    //----------------------------------

    initCategory: function(type) // type: main or secondary
    {
        $(type + '_category_attribute_tr').hide();

        var catId = $(type == 'main' ? 'categories_main_id' : 'categories_secondary_id').value;

        if (catId && catId != 0) {
            
            // show breadcrumb + change button
            $(type + '_ebay_category_breadcrumbs_div').show();
            $(type + '_ebay_category_change_button').show();
            if (type == 'main') {

                var categoriesId = '';

                for (i = 1; i <= 7; i++) {
                    el = $(type + '_ebay_category-' + i);
                    if (el.selectedIndex != -1) {
                        categoryId = el.value;
                        categoriesId += (categoriesId == '' ? '' : ',') + categoryId;
                    } else {
                        // select prev.
                        break;
                    }
                }

                CategoriesHandlersObj.loadAllAboutCategory(categoriesId);
            }
            type == 'secondary' && $('secondary_ebay_category_empty_button').show();
            $(type + '_ebay_category_by_id_td').hide();

        } else {
            // show selects
            $(type + '_ebay_category_tr').show();

            CategoriesHandlersObj.initCategoryEdit(type);
            // does 1st category selected?
            if (!$(type + '_ebay_category-1').value) {
                $(type + '_ebay_category_cancel_button').hide();
                CategoriesHandlersObj.loadCategory(0, type + '_ebay_category-1');
            }
        }
    },

    initCategoryEdit : function(type)
    {
        var className = type + '-ebay-category-hidden';

        $$('.' + className).each(function(item) {
            item.removeClassName(className);
            $(type + '_ebay_category-1').value && item.show();
        });

        $(type + '_ebay_category_breadcrumbs_div').hide();
        $(type + '_ebay_category_change_button').hide();
        type == 'secondary' && $('secondary_ebay_category_empty_button').hide();
        // does category selected and confirmed?
        $(type == 'main' ? 'categories_main_id' : 'categories_secondary_id').value && $(type + '_ebay_category_cancel_button').show();

        $(type + '_ebay_category_by_id_td').show();
    },

    //----------------------------------

    selectCategoryById: function(type)
    {
        var catId = $(type + '_ebay_category_by_id').value;
        if (!catId) {
            alert(M2ePro.text.fill_category_id_message);
            return;
        }

        var url = M2ePro.url.getCategoriesTreeByCategoryId + 'marketplace_id/' + $('marketplace_id').value + '/category_id/' + catId;

        new Ajax.Request(url, {onSuccess: function(transport) {
            var data = transport.responseText.evalJSON();
            if (data.error) {
                alert(data.error);
                return;
            }
            $(type + '_ebay_category_div').select('select').each(function(el) {
                $(el).hide();
            })
            var i = 1;
            CategoriesHandlersObj.loadCategory(0, type + '_ebay_category-' + i, type);
            data.selected.each(function(id) {
                $(type + '_ebay_category-' + i).value = id;
                i++;
                receiverId = type + '_ebay_category-' + i;
                CategoriesHandlersObj.loadCategory(id, receiverId, type);
            });

            CategoriesHandlersObj.confirmCategory(type);

            $(type + '_ebay_category_by_id').value = '';
        }});
    },

    emptyCategory: function(type)
    {
        $(type + '_ebay_category_breadcrumbs_div').hide();
        $(type + '_ebay_category_change_button').hide();
        $(type + '_ebay_category_empty_button').hide();
        $(type == 'main' ? 'categories_main_id' : 'categories_secondary_id').value = '';
        $(type + '_ebay_category_tr').show();
        $(type + '_ebay_category_by_id_td').show();

        var className = type + '-ebay-category-hidden';
        $$('.' + className).each(function(item) {
            item.removeClassName(className);
            item.hide();
        })

        CategoriesHandlersObj.loadCategory(0, type + '_ebay_category-1', type);
    },

    confirmCategory: function(type) // main or secondary
    {
        var breadcrumbs = [];
        var el = '';
        var categoryId = 0;
        var categoriesId = '';

        for (i = 1; i <= 7; i++) {
            el = $(type + '_ebay_category-' + i);
            if (el.visible()) {
                breadcrumbs.push(el.options[el.selectedIndex].innerHTML);
                el.addClassName(type + '-ebay-category-hidden');
                categoryId = el.value;
                categoriesId += (categoriesId == '' ? '' : ',') + categoryId;
            } else {
                // select prev.
                break;
            }
        }

        $(type + '_ebay_category_breadcrumbs_div').show().innerHTML = breadcrumbs.join(' > ');
        $(type + '_ebay_category_breadcrumbs_div').show().innerHTML += ' ('+categoryId+') '
        $(type + '_ebay_category_breadcrumbs_div').show();

        $(type + '_ebay_category_confirm_div').hide(); // confirm
        $(type + '_ebay_category_change_button').show(); // edit
        type == 'secondary' && $('secondary_ebay_category_empty_button').show();
        $(type + '_ebay_category_cancel_button').hide(); // cancel

        $(type + '_ebay_category_by_id_td').hide();

        if (type == 'main') {
            $('categories_main_id').value = categoryId // update hidden field
            CategoriesHandlersObj.loadAllAboutCategory(categoriesId);
        } else {
            $('categories_secondary_id').value = categoryId;
        }
    },

    showCategoryConfirm: function(id)
    {
        if (id.slice(0, 4) == 'main') {
            $('main_ebay_category_confirm_div').show();
        } else {
            $('secondary_ebay_category_confirm_div').show();
        }
    },

    hideCategoryConfirm: function(id)
    {
        if (id.slice(0, 4) == 'main') {
            $('main_ebay_category_confirm_div').hide();
        } else {
            $('secondary_ebay_category_confirm_div').hide();
        }
    },

    cancelCategoryEdit: function(type)
    {
        CategoriesHandlersObj.confirmCategory(type);
    },

    //----------------------------------
    
    loadCategory: function(parentCatId, receiverId, type)
    {
        var marketplace = $('marketplace_id').value;

        var key = 'cc-' + marketplace + parentCatId; // cache key
        type = type || 'secondary';

        ListingTemplatesHandlersObj.cc = ListingTemplatesHandlersObj.cc || [];

        function render(data)
        {
            if (!data.length) {
                return;
            }
                
            $(receiverId).update('<option value=""></option>');
            CategoriesHandlersObj._selectWithLeafRenderer(data, receiverId);

            $(receiverId).show();
            $(receiverId).removeClassName(type + '-ebay-category-hidden');
            CategoriesHandlersObj.hideCategoryConfirm(receiverId);
        }

        if (key in ListingTemplatesHandlersObj.cc) {
            render(ListingTemplatesHandlersObj.cc[key]);
            return;
        }

        url = M2ePro.url.getChildCategories + 'marketplace_id/' + marketplace + '/parent_id/' + parentCatId + '/';
        new Ajax.Request(url, {
            asynchronous : false,
            onSuccess: function (transport) {
                render(ListingTemplatesHandlersObj.cc[key] = transport.responseText.evalJSON(true));
            }
        });
    },

    loadAllAboutCategory: function(categories)
    {
        // get payments and item conditions for selected category
        CategoriesHandlersObj.loadCategoryInformation(categories, function(data) {
            ListingTemplatesHandlersObj.setVisibilityForMultiVariation(parseInt(data.variation_enabled) == 1);
            ListingTemplatesHandlersObj.filterPayments(data.payments);
            SpecificsHandlersObj.setVisibilityForProductDetails(parseInt(data.catalog_enabled) == 1);
            SpecificsHandlersObj.renderConditions(data.condition_mode, data.condition_values);
            SpecificsHandlersObj.renderItemSpecifics(data.item_specifics);
        });

        M2ePro.customData.categoriesAlreadyRendered = true;
    },

    loadCategoryInformation: function(categories, handler)
    {
        var url = M2ePro.url.getCategoryInformation + 'marketplace_id/' + $('marketplace_id').value + '/category_id/' + categories;
        new Ajax.Request(url, {onSuccess: function(transport) {
            handler(transport.responseText.evalJSON());
        }});
    },

    //----------------------------------

    _selectWithLeafRenderer: function(data, id)
    {
        var options = '';
        data.each(function(paris) {

            var key = paris.category_id;
            var val = (typeof paris.value != 'undefined') ? paris.value : paris.title;
            var is_leaf = (typeof paris.value != 'undefined') ? paris.value : paris.is_leaf;

			options += '<option is_leaf="' + is_leaf + '" value="' + key + '">' + val + '</option>\n';
        });

        $(id).insert(options);

        if (M2ePro.formData[id]) {
            $(id).value = M2ePro.formData[id];
        } else {
            $(id).value = '';
        }
    },

    _isCategoryLeaf : function(categorySelectObj)
    {
        var selectedIndex = categorySelectObj.selectedIndex;
        var isLeaf = false;

        if (selectedIndex < 0) {
            return isLeaf;
        }

	    isLeaf = categorySelectObj.options[selectedIndex].getAttribute('is_leaf') == 1;

        return isLeaf;
    },

    //----------------------------------

    store_categories_main_mode_change: function(event)
    {
        var self = CategoriesHandlersObj;

        var elements = {
            attribute : 'store_categories_main_attribute',
            select  : 'store_categories_main_id'
        };

        self.changeStoreCategoryMode(this, elements);
    },

    store_categories_secondary_mode_change: function(event)
    {
        var self = CategoriesHandlersObj;

        var elements = {
            attribute : 'store_categories_secondary_attribute',
            select  : 'store_categories_secondary_id'
        };

        self.changeStoreCategoryMode(this, elements);
    },

    changeStoreCategoryMode: function(obj, elements)
    {
        $(elements.attribute + '_tr', elements.select + '_tr').invoke('hide');

        if (obj.value != 0) {

            if (!AttributeSetsHandlersObj.checkAttributeSetSelection()) {
                obj.value = 0;
                return;
            }

            $(obj.value == 2 ? elements.attribute + '_tr' : elements.select + '_tr').show();
        }
    }

    //----------------------------------
});
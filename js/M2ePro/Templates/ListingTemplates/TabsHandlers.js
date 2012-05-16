TabsHandlers = Class.create();
TabsHandlers.prototype = Object.extend(new CommonHandlers(), {

    //----------------------------------

    initialize: function() {},

    //----------------------------------

    checkAttributeSetSelection: function()
    {
        if (!AttributeSetsHandlersObj.checkAttributeSetSelection()) {
            listingTemplatesTabsJsTabs.showTabContent($('listingTemplatesTabs_tab_general'));
        }
    },

    checkMarketplaceSelection: function()
    {
        if ($F('marketplace_id') === '') {
            alert(M2ePro.text.marketplace_not_selected_error);
            listingTemplatesTabsJsTabs.showTabContent($('listingTemplatesTabs_tab_general'));
        }
    },
    
    checkCategoriesSelection: function()
    {
        if ( $F('categories_mode') === '' ||
            ($F('categories_mode') == TabsHandlersObj.CATEGORIES_MODE_EBAY && $F('categories_main_id') == 0) ||
            ($F('categories_mode') == TabsHandlersObj.CATEGORIES_MODE_ATTRIBUTE && $F('categories_main_attribute') === '')) {
            alert(M2ePro.text.main_category_not_selected_error);
            listingTemplatesTabsJsTabs.showTabContent($('listingTemplatesTabs_tab_general'));
        }
    }

    //----------------------------------
});
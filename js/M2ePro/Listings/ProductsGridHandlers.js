ProductsGridHandlers = Class.create();
ProductsGridHandlers.prototype = Object.extend(new CommonHandlers(), {

    //----------------------------------

    initialize: function() {},

    //----------------------------------

    save_click: function(url)
    {
        if (this.getSelectedProducts()) {
            this.postForm(url, {
                selected_products: this.getSelectedProducts()
            });
        }
    },

    //----------------------------------

    save_and_list_click: function(url)
    {
        if (this.getSelectedProducts()) {
            this.postForm(url, {
                selected_products: this.getSelectedProducts(),
                do_list: 'true'
            });
        }
    },

    //----------------------------------

    setGridId:  function(id)
    {
        this.gridId = id;
    },
    
    getGridId:  function()
    {
        return this.gridId;
    },

    //----------------------------------

    getSelectedProducts: function()
    {
        var selectedProducts = window[this.getGridId() + '_massactionJsObject'].checkedString;
        if (!selectedProducts) {
            alert(M2ePro.text.select_items_message);
            return false;
        }
        return selectedProducts;
    }

    //----------------------------------
});
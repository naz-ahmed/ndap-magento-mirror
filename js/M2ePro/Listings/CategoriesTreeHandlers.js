CategoriesTreeHandlers = Class.create();
CategoriesTreeHandlers.prototype = Object.extend(new CommonHandlers(), {

    //----------------------------------

    initialize: function() {},

    //----------------------------------

    save_click: function(action)
    {
        array_unique(categories_selected_items);

        if (categories_selected_items.length <= 0) {
            alert(M2ePro.text.select_items_message);
            return;
        }

        var selectedCategories = implode(',',categories_selected_items);

        var url = action+'selected_categories/'+selectedCategories+'/';
        url += 'categories_add_action/' + $('categories_add_action').value + '/';
        url += 'categories_delete_action/' + $('categories_delete_action').value + '/';
        document.location.assign(url);
    },

    //----------------------------------

    tree_buildCategory: function(parent, config)
    {
        if (!config) {
            return null;
        }

        if (parent && config && config.length) {
            for (var i = 0; i < config.length; i++) {

                config[i].uiProvider = Ext.tree.CheckboxNodeUI;

                var node = new Ext.tree.TreeNode(config[i]);

                for (var j=0;j<initTreeSelectedNodes.length;j++) {
                    if (config[i].id == initTreeSelectedNodes[j][0]) {
                        initTreeSelectedNodes[j][1] = node;
                        initTreeSelectedNodes[j][1].attributes.checked = true;
                        break;
                    }
                }

                parent.appendChild(node);

                if (config[i].children) {
                    CategoriesTreeHandlersObj.tree_buildCategory(node, config[i].children);
                }

            }
        }
    },

    tree_processChildren: function(node, state)
    {
        if (!node.hasChildNodes()) {
            return false;
        }

        for (var i = 0; i < node.childNodes.length; i++) {
            node.childNodes[i].ui.check(state);
            if (node.childNodes[i].hasChildNodes()) {
                CategoriesTreeHandlersObj.tree_processChildren(node.childNodes[i], state);
            }
        }

        return true;
    },

    tree_categoryAdd: function(id, node)
    {
        categories_selected_items.push(id);
        array_unique(categories_selected_items);

        if (!node.isLeaf() && node.hasChildNodes()) {
            CategoriesTreeHandlersObj.tree_processChildren(node, node.attributes.checked);
        }
    },

    tree_categoryRemove: function(id, node)
    {
        while (categories_selected_items.indexOf(id) != -1) {
            categories_selected_items.splice(categories_selected_items.indexOf(id), 1);
        }

        array_unique(categories_selected_items);

        if (!node.isLeaf() && node.hasChildNodes()) {
            CategoriesTreeHandlersObj.tree_processChildren(node, node.attributes.checked);
        }
    },

    //----------------------------------

    categories_products_from_change: function() {

        var value = $('categories_products_from').value;

        if (value == 'all') {
            $$('.save_and_next_button').each(function(o) { o.hide(); });
            $$('.save_and_go_to_listings_list_button').each(function(o) { o.show(); });
            $$('.save_and_go_to_listing_view_button').each(function(o) { o.show(); });
        } else if (value == 'manual') {
            $$('.save_and_next_button').each(function(o) { o.show(); });
            $$('.save_and_go_to_listings_list_button').each(function(o) { o.hide(); });
            $$('.save_and_go_to_listing_view_button').each(function(o) { o.hide(); });
        } else {
            $$('.save_and_next_button').each(function(o) { o.hide(); });
            $$('.save_and_go_to_listings_list_button').each(function(o) { o.show(); });
            $$('.save_and_go_to_listing_view_button').each(function(o) { o.show(); });
        }
    }

    //----------------------------------
});
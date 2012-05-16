EbayItemsGridHandlers = Class.create();
EbayItemsGridHandlers.prototype = Object.extend(new CommonHandlers(), {

    //----------------------------------

    initialize: function(gridId, productIdCellIndex, productTitleCellIndex)
    {
        this.gridId = gridId;
        this.productIdCellIndex = productIdCellIndex;
        this.productTitleCellIndex = productTitleCellIndex;
    },

    //----------------------------------

    getCellContent : function(rowId,cellIndex)
    {
        eval('var rows = '+this.gridId+'JsObject.rows;');
        for(var i=0;i<rows.length;i++) {
            var row = rows[i];
            var cels = $(row).childElements();

            var checkbox = $(cels[0]).childElements();
            checkbox = checkbox[0];

            if (checkbox.value == rowId) {
                return trim(cels[cellIndex].innerHTML);
            }
        }

        return '';
    },

    getProductIdByRowId : function(rowId)
    {
        var temp = this.getCellContent(rowId,this.productIdCellIndex);

        var regExpImg= new RegExp('<img[^><]*>','gi');
        var regExpHr= new RegExp('<hr>','gi');

        temp = temp.replace(regExpImg,'');
        temp = temp.replace(regExpHr,'');

        return temp;
    },

    getProductNameByRowId : function(rowId)
    {
        var cellContent = this.getCellContent(rowId,this.productTitleCellIndex);
        var expr = new RegExp(/<span[^>]*>(.*?)<\/span>/i);
        var matches = expr.exec(cellContent);
        return matches[1];
    },

    //----------------------------------

    getSelectedItemsParts : function()
    {
        eval('var selectedProductsString = '+this.gridId+'_massactionJsObject.checkedString;');
        var selectedProductsArray = selectedProductsString.split(",");

        if (selectedProductsString == '' || selectedProductsArray.length == 0) {
            return new Array();
        }

        var maxProductsInPart = 10;

        if (selectedProductsArray.length <= 25) {
            maxProductsInPart = 5;
        }
        if (selectedProductsArray.length <= 15) {
            maxProductsInPart = 3;
        }
        if (selectedProductsArray.length <= 8) {
            maxProductsInPart = 2;
        }
        if (selectedProductsArray.length <= 4) {
            maxProductsInPart = 1;
        }

        var result = new Array();
        for (var i=0;i<selectedProductsArray.length;i++) {
            if (result.length == 0 || result[result.length-1].length == maxProductsInPart) {
                result[result.length] = new Array();
            }
            result[result.length-1][result[result.length-1].length] = selectedProductsArray[i];
        }

        return result;
    },

    //----------------------------------

    selectAll : function()
    {
        eval(this.gridId+'_massactionJsObject.selectAll();');
    },

    unselectAll : function()
    {
        eval(this.gridId+'_massactionJsObject.unselectAll();');
    },

    unselectAllAndReload : function()
    {
        this.unselectAll();
        eval(this.gridId+'JsObject.reload();');
    },

    selectByRowId : function(rowId)
    {
        this.unselectAll();
        
        eval('var rows = '+this.gridId+'JsObject.rows;');
        for(var i=0;i<rows.length;i++) {
            var row = rows[i];
            var cels = $(row).childElements();

            var checkbox = $(cels[0]).childElements();
            checkbox = checkbox[0];

            if (checkbox.value == rowId) {
                checkbox.checked = true;
                eval(this.gridId+'_massactionJsObject.checkedString = \''+rowId+'\';');
                break;
            }
        }
    },

    //----------------------------------

    afterInitPage : function()
    {
        var objectButton = $$('#'+this.gridId+'_massaction-form fieldset span.field-row button');

        var self = this;

        objectButton.each(function(s) {
            s.writeAttribute("onclick",'');
            s.observe('click', self.massactionSubmitClick );
        });
    },

    massactionSubmitClick : function(force)
    {
        if(typeof(force) != 'boolean') {
            force = false;
        }

        var self = EbayItemsGridHandlersObj;

        eval('var selectedProductsString = '+self.gridId+'_massactionJsObject.checkedString;');
        var selectedProductsArray = selectedProductsString.split(",");

        if (selectedProductsString == '' || selectedProductsArray.length == 0) {
            alert(M2ePro.text.select_items_message);
            return;
        }

        var selectAction = true;
        $$('select#'+self.gridId+'_massaction-select option').each(function(o) {
            if (o.selected && o.value == '') {
                alert(M2ePro.text.select_action_message);
                selectAction = false;
                return;
            }
        });

        if (!selectAction) {
            return;
        }
        
        if (!force && !confirm(CONFIRM)) {
            return;
        }

        CommonHandlersObj.scroll_page_to_top();
        
        $$('select#'+self.gridId+'_massaction-select option').each(function(o) {
            if (o.selected) {

                switch (o.value) {
                    case '':
                        alert(M2ePro.text.select_action_message);
                        return;

                    case 'list':
                        EbayActionsHandlersObj.runListProducts();
                        break;

                    case 'revise':
                        EbayActionsHandlersObj.runReviseProducts();
                        break;

                    case 'relist':
                        EbayActionsHandlersObj.runRelistProducts();
                        break;

                    case 'stop':
                        EbayActionsHandlersObj.runStopProducts();
                        break;

                    case 'stop_and_remove':
                        EbayActionsHandlersObj.runStopAndRemoveProducts();
                        break;
                }
            }
        });
    },

    //----------------------------------

    viewItemHelp : function(rowId, data)
    {
        $('lpv_grid_help_icon_open_'+rowId).hide();
        $('lpv_grid_help_icon_close_'+rowId).show();

        if ($('lp_grid_help_content_'+rowId) != null) {
            $('lp_grid_help_content_'+rowId).show();
            return;
        }

        var html = EbayItemsGridHandlersObj.createHelpTitleHtml(rowId);

        data = eval(base64_decode(data));
        for (var i=0;i<data.length;i++) {
            html += EbayItemsGridHandlersObj.createHelpActionHtml(data[i]);
        }

        html += EbayItemsGridHandlersObj.createHelpViewAllLogHtml(rowId);

        eval('var rows = '+this.gridId+'JsObject.rows;');
        for(var i=0;i<rows.length;i++) {
            var row = rows[i];
            var cels = $(row).childElements();

            var checkbox = $(cels[0]).childElements();
            checkbox = checkbox[0];

            if (checkbox.value == rowId) {
                row.insert({
                  after: '<tr id="lp_grid_help_content_'+rowId+'"><td class="help_line" colspan="'+($(row).childElements().length)+'">'+html+'</td></tr>'
                });
            }
        }
    },

    hideItemHelp : function(rowId)
    {
        if ($('lp_grid_help_content_'+rowId) != null) {
            $('lp_grid_help_content_'+rowId).hide();
        }

        $('lpv_grid_help_icon_open_'+rowId).show();
        $('lpv_grid_help_icon_close_'+rowId).hide();
    },

    //----------------------------------

    createHelpTitleHtml : function(rowId)
    {
        var productTitle = EbayItemsGridHandlersObj.getProductNameByRowId(rowId);
        var closeHtml = '<a href="javascript:void(0);" onclick="EbayItemsGridHandlersObj.hideItemHelp('+rowId+');" title="'+M2ePro.text.close_word+'"><span class="hl_close">&times;</span></a>';
        return '<div class="hl_header"><span class="hl_title">'+productTitle+'</span>'+closeHtml+'</div>';
    },

    createHelpActionHtml : function(action)
    {
        var classContainer = 'hl_container';

        if (action.type == 2) {
            classContainer += ' hl_container_success';
        } else if (action.type == 3) {
            classContainer += ' hl_container_warning';
        } else if (action.type == 4) {
            classContainer += ' hl_container_error';
        }

        var html = '<div class="'+classContainer+'">';
            html += '<div class="hl_date">'+action.date+'</div>' +
                    '<div class="hl_action">';

        if (action.initiator != '') {
            html += '<strong style="color: gray;">'+action.initiator+'</strong>&nbsp;&nbsp;';
        }

        html += '<strong>'+action.action+'</strong></div>' +
                    '<div style="clear: both"></div>' +
                        '<div style="padding-top: 3px;">';

        for (var i=0;i<action.items.length;i++) {

            var type = M2ePro.text.notice_word;

            if (action.items[i].type == 2) {
                type = '<span style="color: green;">'+M2ePro.text.success_word+'</span>';
            } else if (action.items[i].type == 3) {
                type = '<span style="color: orange;">'+M2ePro.text.warning_word+'</span>';
            } else if (action.items[i].type == 4) {
                type = '<span style="color: red;">'+M2ePro.text.error_word+'</span>';
            }

            html += '<div style="margin-top: 7px;"><div class="hl_messages_type">'+type+'</div><div class="hl_messages_text">'+action.items[i].description+'</div></div>';
        }

        html +=     '</div>' +
                '</div>';

        return html;
    },

    createHelpViewAllLogHtml : function(rowId)
    {
        var productId = strip_tags(EbayItemsGridHandlersObj.getProductIdByRowId(rowId));

        var url = '';
        if (this.gridId == 'listingsEbayGrid') {
            url = M2ePro.url.logViewUrl+'filter/'+base64_encode('ebay_item='+productId);
        } else {
            url = M2ePro.url.logViewUrl+'filter/'+base64_encode('product_id[from]='+productId+'&product_id[to]='+productId);
        }

        return '<div class="hl_footer"><a href="'+url+'">'+M2ePro.text.view_all_product_log_message+'</a></div>';
    }

    //----------------------------------
});
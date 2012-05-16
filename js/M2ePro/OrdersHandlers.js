OrdersHandlers = Class.create();
OrdersHandlers.prototype = Object.extend(new CommonHandlers(), {

    //----------------------------------

    initialize: function(gridId)
    {
        this.grid = eval(gridId+'JsObject');
        this.gridDefaultRowClickCallback = this.grid.rowClickCallback;
        this.grid.rowClickCallback = this.rowClickCallback;
    },

    rowClickCallback: function(grid, event)
    {
        if(['a', 'select', 'option'].indexOf(Event.element(event).tagName.toLowerCase())!=-1) {
            return;
        }

        var self = OrdersHandlersObj;
        var trElement = Event.findElement(event, 'tr');
        var tdElement = Event.findElement(event, 'td');

        if ($(tdElement).down('input')) {
            self.gridDefaultRowClickCallback(grid, event);
        } else {
            setLocation(trElement.title);
        }
    },

    //----------------------------------

    viewItemHelp: function(rowId, data)
    {
        // Disable grid callback
        // ------------------------------
        OrdersHandlersObj.grid.rowClickCallback = '';
        // ------------------------------

        $('orders_grid_help_icon_open_'+rowId).hide();
        $('orders_grid_help_icon_close_'+rowId).show();

        if ($('orders_grid_help_content_'+rowId) !== null) {
            $('orders_grid_help_content_'+rowId).show();

            // Restore grid callback
            // ------------------------------
            setTimeout(function() {
                OrdersHandlersObj.grid.rowClickCallback = OrdersHandlersObj.rowClickCallback;
            },250);
            // ------------------------------
            return;
        }

        var html = OrdersHandlersObj.createHelpTitleHtml(rowId);

        data = eval(base64_decode(data));
        for (var i=0;i<data.length;i++) {
            html += OrdersHandlersObj.createHelpActionHtml(data[i]);
        }

        html += OrdersHandlersObj.createHelpViewAllLogHtml(rowId);

        var row = $('orders_grid_help_icon_open_' + rowId).up('tr');
        row.insert(
            {after: '<tr id="orders_grid_help_content_'+rowId+'"><td class="help_line" colspan="'+($(row).childElements().length)+'">'+html+'</td></tr>'}
        );

        // Restore grid callback
        // ------------------------------
        setTimeout(function() {
            OrdersHandlersObj.grid.rowClickCallback = OrdersHandlersObj.rowClickCallback;
        },250);
        // ------------------------------
    },

    hideItemHelp: function(rowId)
    {
        // Disable grid callback
        // ------------------------------
        OrdersHandlersObj.grid.rowClickCallback = '';
        // ------------------------------

        if ($('orders_grid_help_content_'+rowId) != null) {
            $('orders_grid_help_content_'+rowId).hide();
        }

        $('orders_grid_help_icon_open_'+rowId).show();
        $('orders_grid_help_icon_close_'+rowId).hide();

        // Restore grid callback
        // ------------------------------
        setTimeout(function() {
            OrdersHandlersObj.grid.rowClickCallback = OrdersHandlersObj.rowClickCallback;
        },250);
        // ------------------------------
    },

    createHelpTitleHtml: function(rowId)
    {
        var ebayOrderNumber = $('orders_grid_help_icon_open_' + rowId).up('td').next().innerHTML;
        var orderTitle = ebayOrderNumber.replace(/<[^>]+>/g, '');
        var closeHtml = '<a href="javascript:void(0);" onclick="OrdersHandlersObj.hideItemHelp('+rowId+');" title="'+M2ePro.text.close_word+'"><span class="hl_close">&times;</span></a>';

        return '<div class="hl_header"><span class="hl_title">'+orderTitle+'</span>'+closeHtml+'</div>';
    },

    createHelpActionHtml: function(action)
    {
        var self = OrdersHandlersObj;

        var classContainer = 'hl_container';
        if (action.type == self.MESSAGE_TYPE_SUCCESS) {
            classContainer += ' hl_container_success';
        } else if (action.type == self.MESSAGE_TYPE_WARNING ) {
            classContainer += ' hl_container_warning';
        } else if (action.type == self.MESSAGE_TYPE_ERROR) {
            classContainer += ' hl_container_error';
        }

        var type = '<span style="color: green;">'+M2ePro.text.success_word+'</span>';
        if (action.type == self.MESSAGE_TYPE_WARNING) {
            type = '<span style="color: orange;">'+M2ePro.text.warning_word+'</span>';
        } else if (action.type == self.MESSAGE_TYPE_ERROR) {
            type = '<span style="color: red;">'+M2ePro.text.error_word+'</span>';
        }

        var html = '<div class="'+classContainer+'">';

        html += '<div class="hl_date">'+action.date+'</div>';
        html += '<div style="clear: both"></div>';

        html += '<div style="padding-top: 3px;"><div style="margin-top: 7px;">';
        html += '<div class="hl_messages_type">'+type+'</div><div class="hl_messages_text">'+action.text+'</div>';
        html += '</div></div>';

        html += '</div>';

        return html;
    },

    createHelpViewAllLogHtml: function(rowId)
    {
        var url = M2ePro.url.orderViewUrl + 'id/' + rowId + '/';
        return '<div class="hl_footer"><a href="'+url+'">'+M2ePro.text.view_all_order_logs_message+'</a></div>';
    }

    //----------------------------------
});
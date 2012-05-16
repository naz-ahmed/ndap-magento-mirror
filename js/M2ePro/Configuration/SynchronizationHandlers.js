SynchronizationHandlers = Class.create();
SynchronizationHandlers.prototype = Object.extend(new CommonHandlers(), {

    //----------------------------------
    
    initialize: function(synchProgressObj)
    {
        this.synchProgressObj = synchProgressObj;
    },

    //----------------------------------

    completeStep: function()
    {
        window.opener.completeStep = 1;
        window.close();
    },

    saveSettings : function(runSynch)
    {
        MagentoMessagesObj.clearAll();
        runSynch = runSynch || '';

        CommonHandlersObj.scroll_page_to_top();

        var self = this;
        new Ajax.Request( M2ePro.url.formSubmit + '?' + $('edit_form').serialize() ,
        {
            method: 'get',
            asynchronous: true,
            onSuccess: function(transport)
            {
                MagentoMessagesObj.addSuccess(M2ePro.text.synch_settings_saved_successfully);
                if (runSynch != '') {
                    eval('self.'+runSynch+'();');
                }
            }
        });
    },

    //----------------------------------

    runAllEnabledNow : function()
    {
        this.synchProgressObj.runTask(M2ePro.text.synch_running_all_enabled_tasks, M2ePro.url.runAllEnabledNow);
    },

    //----------------------------------

    runNowTemplates : function()
    {
        this.synchProgressObj.runTask(M2ePro.text.synch_running_templates, M2ePro.url.runNowTemplates);
    },

    runNowOrders : function()
    {
        this.synchProgressObj.runTask(M2ePro.text.synch_running_orders, M2ePro.url.runNowOrders);
    },

    runNowFeedbacks : function()
    {
        this.synchProgressObj.runTask(M2ePro.text.synch_running_feedbacks, M2ePro.url.runNowFeedbacks);
    },

    runNowEbayListings : function()
    {
        this.synchProgressObj.runTask(M2ePro.text.synch_running_3rd_party, M2ePro.url.runNowEbayListings);
    },

    runNowMessages : function()
    {
        this.synchProgressObj.runTask(M2ePro.text.synch_running_messages, M2ePro.url.runNowMessages);
    },

    //----------------------------------

    changeTemplatesMode : function()
    {
        var value = $('templates_mode').value;

        if (value == '1') {
            $('templates_run_now_container').show();
            $('inspector_mode_container').show();
        } else {
            $('templates_run_now_container').hide();
            $('inspector_mode_container').hide();
        }

        $('inspector_mode').value = 0;
        SynchronizationHandlersObj.changeInspectorMode();
    },

    changeInspectorMode : function()
    {
        var value = $('inspector_mode').value;

        if (value == '1') {
            $('inspector_interval_container').show();
        } else {
            $('inspector_interval_container').hide();
        }
    },

    changeOrdersMode : function()
    {
        var value = $('orders_mode').value;

        if (value == '1') {
            $('orders_run_now_container').show();
        } else {
            $('orders_run_now_container').hide();
        }
    },

    changeFeedbacksMode : function()
    {
        var value = $('feedbacks_mode').value;

        if (value == '1') {
            $('feedbacks_run_now_container').show();
        } else {
            $('feedbacks_run_now_container').hide();
        }
    },

    changeEbayListingsMode : function()
    {
        var value = $('ebay_listings_mode').value;

        if (value == '1') {
            $('ebay_listings_run_now_container').show();
        } else {
            $('ebay_listings_run_now_container').hide();
        }
    },

    changeMessagesMode : function()
    {
        var value = $('messages_mode').value;

        if (value == '1') {
            $('messages_run_now_container').show();
        } else {
            $('messages_run_now_container').hide();
        }
    }

    //----------------------------------
});
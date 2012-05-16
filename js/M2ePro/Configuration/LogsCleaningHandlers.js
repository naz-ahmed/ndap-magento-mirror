LogsCleaningHandlers = Class.create();
LogsCleaningHandlers.prototype = Object.extend(new CommonHandlers(), {

    //----------------------------------

    initialize: function(urlSave)
    {
        this.urlSave = urlSave;
    },

    //----------------------------------

    saveCleaningSettings : function()
    {
        editForm.submit(this.urlSave);
    },

    runNowLogs : function()
    {
        MagentoMessagesObj.addSuccess(M2ePro.text.clearing_all_logs_started_message);
        editForm.submit(this.urlSave+'button_action/run_now_logs/');
    },

    clearAllLogs : function()
    {
        if (!confirm(CONFIRM)) {
            return;
        }
        
        MagentoMessagesObj.addSuccess(M2ePro.text.clearing_all_logs_started_message);
        editForm.submit(this.urlSave+'button_action/clear_all_logs/');
    },

    //----------------------------------

    runNowLog : function(log)
    {
        log = str_replace('_',' ',log);
        MagentoMessagesObj.addSuccess(str_replace('%log%', log, M2ePro.text.clearing_log_started_message));
        editForm.submit(this.urlSave+'button_action/run_now/log/'+log+'/');
    },

    clearAllLog : function(log)
    {
        if (!confirm(CONFIRM)) {
            return;
        }

        log = str_replace('_',' ',log);
        MagentoMessagesObj.addSuccess(str_replace('%log%', log, M2ePro.text.clearing_log_started_message));
        editForm.submit(this.urlSave+'button_action/clear_all/log/'+log+'/');
    },

    //----------------------------------

    changeModeLog : function(log)
    {
        var value = $(log+'_log_mode').value;

        if (value == '0') {
            $(log+'_log_days_container').style.display = 'none';
            $(log+'_log_button_run_now_container').style.display = 'none';
        } else if (value == '1') {
            $(log+'_log_days_container').style.display = '';
            $(log+'_log_button_run_now_container').style.display = '';
        } else {
            $(log+'_log_days_container').style.display = 'none';
            $(log+'_log_button_run_now_container').style.display = 'none';
        }
    }

    //----------------------------------
});
SettingsHandlers = Class.create();
SettingsHandlers.prototype = Object.extend(new CommonHandlers(), {

    //----------------------------------
    
    initialize: function() {},

    //----------------------------------

    process : function(windowUrl)
    {
        if (!confirm(CONFIRM)) {
            return;
        }

        var self = SettingsHandlersObj;

        new Ajax.Request( M2ePro.url.startCustomUserInterface ,
        {
            method:'get',
            asynchronous: true,
            onSuccess: function(transport) {

                window.completeStep = 0;
                var win = window.open(windowUrl);
                if (win == null) {
                    return;
                }
                var intervalId = setInterval(function(){
                    if (win.closed) {
                        clearInterval(intervalId);
                        new Ajax.Request( M2ePro.url.endCustomUserInterface ,
                        {
                            method: 'get',
                            asynchronous: true,
                            onSuccess: function(transport) {

                                if (window.completeStep == 1) {
                                    new Ajax.Request( M2ePro.url.startMigration ,
                                    {
                                        method: 'get',
                                        asynchronous: true,
                                        onSuccess: function(transport) {
                                            MagentoMessagesObj.addSuccess(M2ePro.text.migration_successfully_completed_message);
                                        }
                                    });
                                }

                            }
                        });
                    }
                }, 1000);
            }
        });
    }

    //----------------------------------
});
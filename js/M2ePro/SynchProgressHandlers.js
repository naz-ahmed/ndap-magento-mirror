SynchProgressHandlers = Class.create();
SynchProgressHandlers.prototype = Object.extend(new CommonHandlers(), {

    //----------------------------------
    
    initialize : function(progressBarObj, wrapperObj)
    {
        this.stateExecuting = 'executing';
        this.stateInactive = 'inactive';

        this.resultTypeError = 'error';
        this.resultTypeWarning = 'warning';
        this.resultTypeSuccess = 'success';

        this.progressBarObj = progressBarObj;
        this.wrapperObj = wrapperObj;
        this.loadingMask = $('loading-mask');
    },

    //----------------------------------

    start : function(title, status)
    {
        title = title || '';
        status = status || '';

        var self = this;
        
        self.progressBarObj.reset();

        if (title != '') {
            self.progressBarObj.setTitle(title);
        }
        if (status != '') {
            self.progressBarObj.setStatus(status);
        }

        self.progressBarObj.show();

        self.wrapperObj.lock();
        self.loadingMask.setStyle({visibility: 'hidden'});
    },

    end : function()
    {
        var self = this;
        
        self.progressBarObj.reset();
        self.progressBarObj.hide();

        self.wrapperObj.unlock();
        self.loadingMask.setStyle({visibility: 'visible'});
    },

    //----------------------------------

    initPageCheckState : function(callBackWhenEnd)
    {
        callBackWhenEnd = callBackWhenEnd || '';
        
        var self = this;
        new Ajax.Request( M2ePro.url.synchCheckState ,
        {
            method: 'get',
            asynchronous: true,
            onSuccess: function(transport)
            {
                if (transport.responseText == self.stateExecuting) {

                    self.start(M2ePro.text.synch_already_running_message, M2ePro.text.synch_getting_information_message);

                    setTimeout(function() {
                        self.startGetExecutingInfo(callBackWhenEnd);
                    },2000);

                } else {

                    self.end();

                    if (callBackWhenEnd != '') {
                        eval(callBackWhenEnd);
                    }

                }
            }
        });
    },

    //----------------------------------
    
    runTask : function(title, url, callBackWhenEnd)
    {
        title = title || '';
        url = url || '';
        callBackWhenEnd = callBackWhenEnd || '';

        if (url == '') {
            return;
        }

        var self = this;
        new Ajax.Request( M2ePro.url.synchCheckState ,
        {
            method: 'get',
            asynchronous: true,
            onSuccess: function(transport)
            {
                if (transport.responseText == self.stateExecuting) {

                    self.start(M2ePro.text.synch_already_running_message, M2ePro.text.synch_getting_information_message);

                    setTimeout(function() {
                        self.startGetExecutingInfo('self.runTask(\'' + title + '\',\'' + url + '\',\'' + callBackWhenEnd + '\');');
                    },2000);

                } else {

                    self.start(title, M2ePro.text.synch_prepare_to_start_message);

                    new Ajax.Request( url ,
                    { method: 'get', asynchronous: true, onSuccess: function(transport) {} });

                    setTimeout(function() {
                        self.startGetExecutingInfo(callBackWhenEnd);
                    },2000);

                }
            }
        });
    },

    //----------------------------------

    startGetExecutingInfo : function(callBackWhenEnd)
    {
        callBackWhenEnd = callBackWhenEnd || '';

        var self = this;
        new Ajax.Request( M2ePro.url.synchGetExecutingInfo ,
        {
            method:'get',
            asynchronous: true,
            onSuccess: function(transport)
            {
                var data = transport.responseText.evalJSON(true);
                
                if (data.mode == self.stateExecuting) {

                    self.progressBarObj.setTitle(data.title);
                    if (data.percents <= 0) {
                        self.progressBarObj.setPercents(0,0);
                    } else if (data.percents >= 100) {
                        self.progressBarObj.setPercents(100,0);
                    } else {
                        self.progressBarObj.setPercents(data.percents,1);
                    }
                    self.progressBarObj.setStatus(data.status);
                    
                    self.wrapperObj.lock();
                    self.loadingMask.setStyle({visibility: 'hidden'});

                    setTimeout(function() {
                        self.startGetExecutingInfo(callBackWhenEnd);
                    },3000);

                } else {

                    self.progressBarObj.setPercents(100,0);

                    //-----------------
                    setTimeout(function() {

                        if (callBackWhenEnd != '') {
                            eval(callBackWhenEnd);
                        } else {

                            new Ajax.Request( M2ePro.url.synchGetLastResult ,
                            {
                                method: 'get',
                                asynchronous: true,
                                onSuccess: function(transport) {
                                    self.end();
                                    self.printFinalMessage(transport.responseText);
                                }
                            });

                        }

                    },1500);
                    //-----------------
                }
            }
        });
    },

    //----------------------------------

    printFinalMessage : function(resultType)
    {
        var self = this;
        if (resultType == self.resultTypeError) {
            MagentoMessagesObj.addError(str_replace('%url%', M2ePro.url.logViewUrl, M2ePro.text.synch_end_error_message));
        } else if (resultType == self.resultTypeWarning) {
            MagentoMessagesObj.addWarning(str_replace('%url%', M2ePro.url.logViewUrl, M2ePro.text.synch_end_warning_message));
        } else {
            MagentoMessagesObj.addSuccess(M2ePro.text.synch_end_success_message);
        }
    }

    //----------------------------------
});
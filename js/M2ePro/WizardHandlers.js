WizardHandlers = Class.create();
WizardHandlers.prototype = Object.extend(new CommonHandlers(), {

    //----------------------------------
    
    initialize : function() {},

    //----------------------------------

    skip : function(url)
    {
        if (!confirm(this.skip_confirm_text)) {
            return;
        }

        setLocation(url);
    },

    complete : function(url)
    {
        setLocation(url);
    },

    //----------------------------------

    setStatus : function(status, functionWhenComplete)
    {
        var self = WizardHandlersObj;

        new Ajax.Request( M2ePro.url.setStatus + 'status/' + status,
        {
            method: 'get',
            asynchronous: true,
            onSuccess: function(transport) {
                self.current_status = status;
                if (!(typeof functionWhenComplete == 'undefined')) {
                    eval(functionWhenComplete);
                }
            }
        });
    },

    renderStep : function(stepBlockId, stepStatus)
    {
        var self = WizardHandlersObj;

        $$('#'+stepBlockId+' .step_completed').each(function(obj) {
            obj.hide();
        });
        $$('#'+stepBlockId+' .step_skip').each(function(obj) {
            obj.hide();
        });
        $$('#'+stepBlockId+' .step_process').each(function(obj) {
            obj.hide();
        });
        $$('#'+stepBlockId+' .step_incomplete').each(function(obj) {
            obj.hide();
        });
        
        if (self.current_status >= stepStatus) {
            $(stepBlockId).show();
        } else {
            $(stepBlockId).hide();
        }

        if (self.current_status > stepStatus) {
            $$('#'+stepBlockId+' .step_completed').each(function(obj) {
                obj.show();
            });
            $$('#'+stepBlockId+' .step_container_buttons').each(function(obj) {
                obj.remove();
            });
            $(stepBlockId).writeAttribute('style','background-color: #F2EFEF !important; border-color: #008035 !important;');
        } else {
            $$('#'+stepBlockId+' .step_skip').each(function(obj) {
                obj.show();
            });
            $$('#'+stepBlockId+' .step_process').each(function(obj) {
                obj.show();
            });
            if (window.completeStep == 0) {
                $$('#'+stepBlockId+' .step_incomplete').each(function(obj) {
                    obj.show();
                });
            }
        }
    },

    //----------------------------------
    
    setStatusAndUpdate : function(stepBlockId, stepStatus, nextStepBlockId, nextStepStatus, functionWhenComplete)
    {
        var self = WizardHandlersObj;

        if (typeof nextStepBlockId == 'undefined') {
            nextStepBlockId = 'undefined';
        } else {
            nextStepBlockId = '\''+nextStepBlockId+'\'';
        }

        if (typeof functionWhenComplete == 'undefined') {
            functionWhenComplete = 'undefined';
        } else {
            functionWhenComplete = '\''+functionWhenComplete+'\'';
        }

        self.setStatus(nextStepStatus,'WizardHandlersObj.updateAfterSetStatus(\'' + stepBlockId + '\',' +
                                                                              stepStatus + ',' +
                                                                              nextStepBlockId + ',' +
                                                                              nextStepStatus +',' +
                                                                              functionWhenComplete +');');
    },

    updateAfterSetStatus : function(stepBlockId, stepStatus, nextStepBlockId, nextStepStatus, functionWhenComplete)
    {
        var self = WizardHandlersObj;
        
        self.renderStep(stepBlockId, stepStatus);

        if (!(typeof nextStepBlockId == 'undefined')) {
            self.renderStep(nextStepBlockId, nextStepStatus);
        }
        
        if (!(typeof functionWhenComplete == 'undefined')) {
            eval(functionWhenComplete);
        }
    },

    //----------------------------------
    
    processStep : function(stepWindowUrl, stepBlockId, stepStatus, nextStepBlockId, nextStepStatus, functionWhenComplete)
    {
        var self = WizardHandlersObj;

        window.completeStep = 0;
        var win = window.open(stepWindowUrl);
        var intervalId = setInterval(function(){
            if (win.closed) {
                clearInterval(intervalId);
                if (window.completeStep == 1) {
                    self.setStatusAndUpdate(stepBlockId, stepStatus, nextStepBlockId, nextStepStatus, functionWhenComplete);
                } else {
                    self.renderStep(stepBlockId, stepStatus);
                }
            }
        }, 1000);
    },

    skipStep : function(stepBlockId, stepStatus, nextStepBlockId, nextStepStatus, functionWhenComplete)
    {
        var self = WizardHandlersObj;
        self.setStatusAndUpdate(stepBlockId, stepStatus, nextStepBlockId, nextStepStatus, functionWhenComplete);
    },

    //----------------------------------

    callBackAfterMarketplaces : function()
    {
        var self = WizardHandlersObj;

        if (!self.migration_available) {
            self.setStatusAndUpdate('block_notice_wizard_installation_step_migration', self.STATUS_MIGRATION,
                                    'block_notice_wizard_installation_step_accounts', self.STATUS_ACCOUNTS);
        }
    },

    callBackAfterMigration : function()
    {
        new Ajax.Request( M2ePro.url.startMigration ,
        {
            method:'get',
            asynchronous: true,
            onSuccess: function(transport) {}
        });
    },

    callBackAfterEndConfiguration : function()
    {
        $('wizard_installation_complete').show();
    }

    //----------------------------------
});
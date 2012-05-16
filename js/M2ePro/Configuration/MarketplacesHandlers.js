MarketplacesHandlers = Class.create();
MarketplacesHandlers.prototype = Object.extend(new CommonHandlers(), {

    //----------------------------------

    initialize: function(synchProgressObj)
    {
        this.synchProgressObj = synchProgressObj;

        this.marketplacesForUpdate = new Array();
        this.marketplacesForUpdateCurrentIndex = 0;

        this.synchErrors = 0;
        this.synchWarnings = 0;
        this.synchSuccess = 0;
    },

    //----------------------------------

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
                MagentoMessagesObj.addSuccess(M2ePro.text.marketplaces_settings_save_success_message);
                if (runSynch != '') {
                    eval('self.'+runSynch+'();');
                }
            }
        });
    },

    //----------------------------------

    completeStep : function()
    {
        var self = this;
        var isMarketplaceSelected = false;

        $$('.marketplace_status_select').each(function(obj) {
            if (obj.value == 1) {
                isMarketplaceSelected = true;
            }
        });

        if (isMarketplaceSelected) {
            
            self.saveSettings('runSynchNow');

            var intervalId = setInterval(function(){
                if (typeof self.marketplacesUpdateFinished != 'undefined' && self.marketplacesUpdateFinished) {
                    clearInterval(intervalId);
                    window.opener.completeStep = 1;
                    window.close();
                }
            }, 1000);
        } else {
            MagentoMessagesObj.addError(M2ePro.text.marketplaces_settings_save_error_message);
        }
    },

    //----------------------------------

    runSynchNow : function()
    {
        var self = this;

        self.marketplacesForUpdate = new Array();
        self.marketplacesForUpdateCurrentIndex = 0;
        
        $$('select.marketplace_status_select').each(function(marketplaceObj) {
            var marketplaceId = marketplaceObj.readAttribute('marketplace_id');
            var marketplaceState = marketplaceObj.value;
            if (marketplaceState == 1) {
                $('synch_info_wait_'+marketplaceId).show();
                $('synch_info_process_'+marketplaceId).hide();
                $('synch_info_complete_'+marketplaceId).hide();
                self.marketplacesForUpdate[self.marketplacesForUpdate.length] = marketplaceId;
            } else {
                $('synch_info_wait_'+marketplaceId).hide();
                $('synch_info_process_'+marketplaceId).hide();
                $('synch_info_complete_'+marketplaceId).hide();
            }
        });

        if (self.marketplacesForUpdate.length == 0) {
            return;
        }

        self.marketplacesForUpdateCurrentIndex = 0;

        self.synchErrors = 0;
        self.synchWarnings = 0;
        self.synchSuccess = 0;

        self.runNextMarketplaceNow();
    },

    runNextMarketplaceNow : function()
    {
        var self = this;

        if (self.marketplacesForUpdateCurrentIndex > 0) {
            
            $('synch_info_wait_'+self.marketplacesForUpdate[self.marketplacesForUpdateCurrentIndex-1]).hide();
            $('synch_info_process_'+self.marketplacesForUpdate[self.marketplacesForUpdateCurrentIndex-1]).hide();
            $('synch_info_complete_'+self.marketplacesForUpdate[self.marketplacesForUpdateCurrentIndex-1]).show();

            var tempEndFlag = 0;
            if (self.marketplacesForUpdateCurrentIndex >= self.marketplacesForUpdate.length) {
                tempEndFlag = 1;
            }
            
            new Ajax.Request( M2ePro.url.synchGetLastResult ,
            {
                method:'get',
                asynchronous: true,
                onSuccess: function(transport) {
                    if (transport.responseText == self.synchProgressObj.resultTypeError) {
                        self.synchErrors++;
                    } else if (transport.responseText == self.synchProgressObj.resultTypeWarning) {
                        self.synchWarnings++;
                    } else {
                        self.synchSuccess++;
                    }

                    if (tempEndFlag == 1) {
                        if (self.synchErrors > 0) {
                            self.synchProgressObj.printFinalMessage(self.synchProgressObj.resultTypeError);
                        } else if (self.synchWarnings > 0) {
                            self.synchProgressObj.printFinalMessage(self.synchProgressObj.resultTypeWarning);
                        } else {
                            self.synchProgressObj.printFinalMessage(self.synchProgressObj.resultTypeSuccess);
                        }
                        self.synchErrors = 0;
                        self.synchWarnings = 0;
                        self.synchSuccess = 0;
                    }
                }
            });
        }

        if (self.marketplacesForUpdateCurrentIndex >= self.marketplacesForUpdate.length) {

            self.marketplacesForUpdate = new Array();
            self.marketplacesForUpdateCurrentIndex = 0;
            self.marketplacesUpdateFinished = true;
            
            self.synchProgressObj.end();

            return;
        }

        var marketplaceId = self.marketplacesForUpdate[self.marketplacesForUpdateCurrentIndex];
        self.marketplacesForUpdateCurrentIndex++;

        $('synch_info_wait_'+marketplaceId).hide();
        $('synch_info_process_'+marketplaceId).show();
        $('synch_info_complete_'+marketplaceId).hide();

        var titleProgressBar = $('marketplace_title_'+marketplaceId).innerHTML;
        self.synchProgressObj.runTask(titleProgressBar, M2ePro.url.runSynchNow + 'marketplace_id/' + marketplaceId,'MarketplacesHandlersObj.runNextMarketplaceNow();');
    },

    //----------------------------------

    changeStatus : function(id)
    {
        if ($('status_'+id).value == '1') {
            $('status_'+id).removeClassName('lacklustre_selected');
            $('status_'+id).addClassName('hightlight_selected');
        } else {
            $('status_'+id).removeClassName('hightlight_selected');
            $('status_'+id).addClassName('lacklustre_selected');
        }
    }

    //----------------------------------
});
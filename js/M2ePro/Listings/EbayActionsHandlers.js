EbayActionsHandlers = Class.create();
EbayActionsHandlers.prototype = Object.extend(new CommonHandlers(), {

    //----------------------------------

    initialize: function(listingId)
    {
        this.sendPartsResponses = new Array();
        this.listingId = listingId;
    },

    //----------------------------------

    startEbayActions : function(title,url,selectedProductsParts)
    {
        MagentoMessagesObj.clearAll();
        $('listing_view_errors_summary').hide();
        
        var self = this;
        new Ajax.Request( M2ePro.url.checkLockListing + 'id/' + self.listingId + '/' ,
        {
            method:'get',
            onSuccess: function(transport)
            {
                if (transport.responseText == 'locked') {
                    MagentoMessagesObj.addError(M2ePro.text.listing_locked_message);
                } else {
                    new Ajax.Request( M2ePro.url.lockListingNow + 'id/' + self.listingId + '/' ,
                    {
                        method:'get',
                        onSuccess: function(transport)
                        {
                            ListingProgressBarObj.reset();
                            ListingProgressBarObj.show(title);
                            GridWrapperObj.lock();
                            $('loading-mask').setStyle({visibility: 'hidden'});

                            self.sendPartsOfProducts(selectedProductsParts,selectedProductsParts.length,url);
                        }
                    });
                }
            }
        });        
    },

    sendPartsOfProducts : function(parts,totalPartsCount,url)
    {
        var self = this;
        
        if (parts.length == totalPartsCount) {
            self.sendPartsResponses = new Array();
        }

        if (parts.length == 0) {

            ListingProgressBarObj.setPercents(100,0);
            ListingProgressBarObj.setStatus(M2ePro.text.task_completed_message);

            new Ajax.Request( M2ePro.url.unlockListingNow + 'id/' + self.listingId + '/' ,
            {
                method:'get',
                onSuccess: function(transport)
                {
                    var combineResult = 'success';
                    for (var i=0;i<self.sendPartsResponses.length;i++) {
                        if (self.sendPartsResponses[i].result != 'success' && self.sendPartsResponses[i].result != 'warning') {
                            combineResult = 'error';
                            break;
                        }
                        if (self.sendPartsResponses[i].result == 'warning') {
                            combineResult = 'warning';
                        }
                    }

                    if (combineResult == 'error') {

                        var message = M2ePro.text.task_completed_error_message;
                        message = str_replace('%title%', ListingProgressBarObj.getTitle(), message);
                        message = str_replace('%url%', M2ePro.url.logViewUrl, message);

                        MagentoMessagesObj.addError(message);

                        var actionIds = '';
                        for (var i=0;i<self.sendPartsResponses.length;i++) {
                            if (actionIds != '') {
                                actionIds += ',';
                            }
                            actionIds += self.sendPartsResponses[i].action_id;
                        }

                        new Ajax.Request( M2ePro.url.getErrorsSummary + 'action_ids/' + actionIds + '/' ,
                        {
                            method:'get',
                            onSuccess: function(transportSummary)
                            {
                                $('listing_view_errors_summary').innerHTML = transportSummary.responseText;
                                $('listing_view_errors_summary').show();
                            }
                        });

                    } else if (combineResult == 'warning') {
                        var message = M2ePro.text.task_completed_warning_message;
                        message = str_replace('%title%', ListingProgressBarObj.getTitle(), message);
                        message = str_replace('%url%', M2ePro.url.logViewUrl, message);

                        MagentoMessagesObj.addWarning(message);
                    } else {
                        var message = M2ePro.text.task_completed_success_message;
                        message = str_replace('%title%', ListingProgressBarObj.getTitle(), message);

                        MagentoMessagesObj.addSuccess(message);
                    }

                    ListingProgressBarObj.hide();
                    ListingProgressBarObj.reset();
                    GridWrapperObj.unlock();
                    $('loading-mask').setStyle({visibility: 'visible'});

                    self.sendPartsResponses = new Array();

                    EbayItemsGridHandlersObj.unselectAllAndReload();
                }
            });

            return;
        }

        var part = parts.splice(0,1);
        part = part[0];
        var partString = implode(',',part);

        var partExecuteString = '';

        if (part.length <= 2) {

            for (var i=0;i<part.length;i++) {

                if (i != 0) {
                    partExecuteString += ', ';
                }

                var temp = EbayItemsGridHandlersObj.getProductNameByRowId(part[i]);

                if (temp != '') {
                    if (temp.length > 75) {
                        temp = temp.substr(0, 75) + '...';
                    }
                    partExecuteString += '"' + temp + '"';
                } else {
                    partExecuteString = part.length;
                    break;
                }
            }

        } else {
            partExecuteString = part.length;
        }

        partExecuteString += '';

        ListingProgressBarObj.setStatus(str_replace('%execute%', partExecuteString, M2ePro.text.sending_data_message));

        new Ajax.Request( url + 'id/' + self.listingId + '/selected_products/' + partString + '/' ,
        {
            method: 'get',
            onSuccess: function(transport)
            {
                if (!transport.responseText.isJSON()) {

                    if (transport.responseText != '') {
                        alert(transport.responseText);
                    }

                    ListingProgressBarObj.hide();
                    ListingProgressBarObj.reset();
                    GridWrapperObj.unlock();
                    $('loading-mask').setStyle({visibility: 'visible'});

                    self.sendPartsResponses = new Array();

                    EbayItemsGridHandlersObj.unselectAllAndReload();
                    
                    return;
                }

                self.sendPartsResponses[self.sendPartsResponses.length] = transport.responseText.evalJSON(true);

                var percents = (100/totalPartsCount)*(totalPartsCount-parts.length);

                if (percents <= 0) {
                    ListingProgressBarObj.setPercents(0,0);
                } else if (percents >= 100) {
                    ListingProgressBarObj.setPercents(100,0);
                } else {
                    ListingProgressBarObj.setPercents(percents,1);
                }

                setTimeout(function() {
                    self.sendPartsOfProducts(parts,totalPartsCount,url);
                },500);
            }
        });

        return;
    },

    //----------------------------------

    runListAllProducts : function()
    {
        if (!confirm(CONFIRM)) {
            return;
        }
        
        EbayItemsGridHandlersObj.selectAll();

        var selectedProductsParts = EbayItemsGridHandlersObj.getSelectedItemsParts();
        if (selectedProductsParts.length == 0) {
            alert(M2ePro.text.listing_empty_message);
            return;
        }

        this.startEbayActions(M2ePro.text.listing_all_items_message, M2ePro.url.runListProducts,selectedProductsParts);
    },

    //----------------------------------

    runListProducts : function()
    {
        var selectedProductsParts = EbayItemsGridHandlersObj.getSelectedItemsParts();
        if (selectedProductsParts.length == 0) {
            return;
        }

        this.startEbayActions(M2ePro.text.listing_selected_items_message, M2ePro.url.runListProducts,selectedProductsParts);
    },

    runReviseProducts : function()
    {
        var selectedProductsParts = EbayItemsGridHandlersObj.getSelectedItemsParts();
        if (selectedProductsParts.length == 0) {
            return;
        }

        this.startEbayActions(M2ePro.text.revising_selected_items_message, M2ePro.url.runReviseProducts,selectedProductsParts);
    },

    runRelistProducts : function()
    {
        var selectedProductsParts = EbayItemsGridHandlersObj.getSelectedItemsParts();
        if (selectedProductsParts.length == 0) {
            return;
        }

        this.startEbayActions(M2ePro.text.relisting_selected_items_message, M2ePro.url.runRelistProducts,selectedProductsParts);
    },

    runStopProducts : function()
    {
        var selectedProductsParts = EbayItemsGridHandlersObj.getSelectedItemsParts();
        if (selectedProductsParts.length == 0) {
            return;
        }

        this.startEbayActions(M2ePro.text.stopping_selected_items_message, M2ePro.url.runStopProducts,selectedProductsParts);
    },

    runStopAndRemoveProducts : function()
    {
        var selectedProductsParts = EbayItemsGridHandlersObj.getSelectedItemsParts();
        if (selectedProductsParts.length == 0) {
            return;
        }

        this.startEbayActions(M2ePro.text.stopping_and_removing_selected_items_message, M2ePro.url.runStopAndRemoveProducts,selectedProductsParts);
    }

    //----------------------------------
});
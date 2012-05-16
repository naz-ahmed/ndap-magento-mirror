FeedbacksHandlers = Class.create();
FeedbacksHandlers.prototype = Object.extend(new CommonHandlers(), {

    //----------------------------------

    initialize: function()
    {
        Validation.add('M2ePro-validate-max-length', M2ePro.text.response_text_error, function(value, el) {
            if (value.length < 2 || value.length > 80) {
                return false;
            }
            return true;
        });
    },

    //----------------------------------

    feedback_template_change: function()
    {
        $('feedback_text').value = $('feedback_template').value;
        $('feedback_text').focus();
    },

    //----------------------------------

    openFeedback: function(self,feedbackId,transactionId,itemId,buyerText)
    {
        editForm.validator.reset();
        
        var urlItemId = trim($(self).up('td').previous(4).innerHTML);
        var urlTransactionId = trim($(self).up('td').previous(5).innerHTML);

        $('feedback_id').value = feedbackId;
        $('transaction_id').innerHTML = urlTransactionId;
        $('item_id').innerHTML = urlItemId;
        $('buyer_text').innerHTML = buyerText;
        $('feedback_text').value = '';

        new Ajax.Request( M2ePro.url.getFeedbacksTemplates ,
        {
            method: 'get',
            asynchronous: true,
            parameters: {
                feedback_id: feedbackId
            },
            onSuccess: function(transport)
            {
                var feedbacksTemplates = transport.responseText.evalJSON()['feedbacks_templates'];
                var tempHtml = '';

                if (feedbacksTemplates.length != 0) {
                    tempHtml += '<option></option>';
                    for (var i = 0; i < feedbacksTemplates.length; i++) {
                        var feedbackTemplate = feedbacksTemplates[i];
                        var feedbackTemplateBody = feedbackTemplate['body'];

                        if (feedbackTemplateBody.length > 40) {
                            feedbackTemplateBody = feedbackTemplateBody.substr(0, 40) + '...';
                        }
                        tempHtml += '<option value="'+feedbackTemplate['body']+'">'+feedbackTemplateBody+'</option>';
                    }

                    $('new_feedback_label_text').hide();
                    $('template_feedback_label_text').show();

                    $('feedback_template_tr').show();
                } else {
                    $('template_feedback_label_text').hide();
                    $('new_feedback_label_text').show();

                    $('feedback_template_tr').hide();
                }
                $('feedback_template').update(tempHtml);
            }
        });

        $('magento_block_feedbacks_response').show();
		        
        var urlLastSymbol = window.location.href.charAt(window.location.href.length-1);
        if (urlLastSymbol == '#') {
            window.location.href = window.location.href;
        } else {
            window.location.href += '#';
        }
    },

    //----------------------------------

    cancelFeedback: function()
    {
        $('magento_block_feedbacks_response').hide();
        
        $('feedback_id').value = '';
        $('transaction_id').value = '';
        $('item_id').value = '';
    },


    //----------------------------------

    sendFeedback: function()
    {
        MagentoMessagesObj.clearAll();

        if (editForm.validate()) {
            var self = this;
            new Ajax.Request( M2ePro.url.formSubmit + '?' + $('edit_form').serialize() ,
            {
                method: 'get',
                asynchronous: true,
                onSuccess: function(transport)
                {
                    MagentoMessagesObj.addSuccess(M2ePro.text.feedback_sent_successfully);

                    self.cancelFeedback();

                    feedbacksGridJsObject.reload();
                }
            });
        }
    }

    //----------------------------------

});
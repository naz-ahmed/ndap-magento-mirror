AccountsHandlers = Class.create();
AccountsHandlers.prototype = Object.extend(new CommonHandlers(), {

    //----------------------------------

    initialize: function()
    {
        this.setValidationCheckRepetitionValue('M2ePro-account-title',
                                                M2ePro.text.title_not_unique_error,
                                                'Accounts', 'title', 'id',
                                                M2ePro.formData.id);

        Validation.add('M2ePro-account-token-session', M2ePro.text.get_token_error, function(value) {
            if (value != '') {
                return true;
            }
            return false;
        });

        Validation.add('M2ePro-account-customer-id', M2ePro.text.wrong_customer_id_error, function(value) {
            var checkResult = false;
            
            if ($('orders_customer_exist_id_container').getStyle('display') == 'none') {
                return true;
            }

            new Ajax.Request(M2ePro.url.checkCustomerId,
            {
                method: 'post',
                asynchronous : false,
                parameters : {
                    customer_id : value,
                    id    : M2ePro.formData.id
                },
                onSuccess: function (transport)
                {
                    checkResult = transport.responseText.evalJSON()['ok'];
                }
            });

            return checkResult;
        });

        Validation.add('M2ePro-account-feedback-templates', M2ePro.text.no_feedback_template_error, function(value) {
            if (value != 0) {
                var checkResult = false;

                new Ajax.Request(M2ePro.url.feedbacksTemplatesCheck,
                {
                    method: 'post',
                    asynchronous : false,
                    parameters : {
                        id : M2ePro.formData.id
                    },
                    onSuccess: function (transport)
                    {
                        checkResult = transport.responseText.evalJSON()['ok'];
                    }
                });

                return checkResult;
            }

            return true;
        });
    },

    //----------------------------------

    completeStep: function()
    {
        window.opener.completeStep = 1;
        window.close();
    },

    //----------------------------------

    get_token: function()
    {
        if ($('token_session').value == '') {
            $('token_session').value = '0';
        }
        if ($('token_expired_date').value == '') {
            $('token_expired_date').value = '0';
        }
        this.submitForm(M2ePro.url.getToken + 'id/' + M2ePro.formData.id);
    },

    //----------------------------------

    feedbacksReceive_change : function()
    {
        var self = AccountsHandlersObj;

        if ($('feedbacks_receive').value == self.FEEDBACKS_RECEIVE_YES) {
            $('magento_block_accounts_feedbacks_response').show();
        } else {
            $('magento_block_accounts_feedbacks_response').hide();

        }
        $('feedbacks_auto_response').value = self.FEEDBACKS_AUTO_RESPONSE_NONE;
        self.feedbacksAutoResponse_change();
    },

    feedbacksAutoResponse_change : function()
    {
        var self = AccountsHandlersObj;

        if ($('feedbacks_auto_response').value == self.FEEDBACKS_AUTO_RESPONSE_NONE) {
            $('block_accounts_feedbacks_templates').hide();
            $('feedbacks_auto_response_only_positive_container').hide();
        } else {
            $('block_accounts_feedbacks_templates').show();
            $('feedbacks_auto_response_only_positive_container').show();
        }
    },

    //----------------------------------

    feedbacksOpenAddForm : function()
    {
        $('block_accounts_feedbacks_form_template_title_add').show();
        $('block_accounts_feedbacks_form_template_title_edit').hide();

        $('feedbacks_templates_id').value = '';
        $('feedbacks_templates_body').value = '';

        $('block_accounts_feedbacks_form_template_button_cancel').show();
        $('block_accounts_feedbacks_form_template_button_add').show();
        $('block_accounts_feedbacks_form_template_button_edit').hide();

        $('magento_block_accounts_feedbacks_form_template').show();
        $('feedbacks_templates_body_validate').hide();
    },

    feedbacksOpenEditForm : function(id,body)
    {
        $('block_accounts_feedbacks_form_template_title_add').hide();
        $('block_accounts_feedbacks_form_template_title_edit').show();

        $('feedbacks_templates_id').value = id;
        $('feedbacks_templates_body').value = body;

        $('block_accounts_feedbacks_form_template_button_cancel').show();
        $('block_accounts_feedbacks_form_template_button_add').hide();
        $('block_accounts_feedbacks_form_template_button_edit').show();

        $('magento_block_accounts_feedbacks_form_template').show();
        $('feedbacks_templates_body_validate').hide();
    },

    feedbacksCancelForm : function()
    {
        $('block_accounts_feedbacks_form_template_title_add').hide();
        $('block_accounts_feedbacks_form_template_title_edit').hide();

        $('feedbacks_templates_id').value = '';
        $('feedbacks_templates_body').value = '';

        $('block_accounts_feedbacks_form_template_button_cancel').hide();
        $('block_accounts_feedbacks_form_template_button_add').hide();
        $('block_accounts_feedbacks_form_template_button_edit').hide();

        $('magento_block_accounts_feedbacks_form_template').hide();
        $('feedbacks_templates_body_validate').hide();
    },

    //----------------------------------

    feedbacksAddAction : function()
    {
        var self = AccountsHandlersObj;

        if ($('feedbacks_templates_body').value.length < 2 || $('feedbacks_templates_body').value.length > 80) {
            $('feedbacks_templates_body_validate').show();
            return;
        } else {
            $('feedbacks_templates_body_validate').hide();
        }

        new Ajax.Request(M2ePro.url.feedbacksTemplatesEdit,
        {
            method: 'post',
            asynchronous : false,
            parameters : {
                account_id : M2ePro.formData.id,
                body : $('feedbacks_templates_body').value
            },
            onSuccess: function (transport)
            {
                self.feedbacksCancelForm();
                eval('feedbacksTemplatesGrid'+M2ePro.formData.id+'JsObject.reload();');
            }
        });
    },

    feedbacksEditAction : function()
    {
        var self = AccountsHandlersObj;

        if ($('feedbacks_templates_body').value.length < 2 || $('feedbacks_templates_body').value.length > 80) {
            $('feedbacks_templates_body_validate').show();
            return;
        } else {
            $('feedbacks_templates_body_validate').hide();
        }
        
        new Ajax.Request(M2ePro.url.feedbacksTemplatesEdit,
        {
            method: 'post',
            asynchronous : false,
            parameters : {
                id : $('feedbacks_templates_id').value,
                account_id : M2ePro.formData.id,
                body : $('feedbacks_templates_body').value
            },
            onSuccess: function (transport)
            {
                self.feedbacksCancelForm();
                eval('feedbacksTemplatesGrid'+M2ePro.formData.id+'JsObject.reload();');
            }
        });
    },

    feedbacksDeleteAction : function(id)
    {
        if (!confirm(CONFIRM)) {
            return false;
        }

        var self = AccountsHandlersObj;

        new Ajax.Request(M2ePro.url.feedbacksTemplatesDelete,
        {
            method: 'post',
            asynchronous : false,
            parameters : {
                id : id
            },
            onSuccess: function (transport)
            {
                eval('feedbacksTemplatesGrid'+M2ePro.formData.id+'JsObject.reload();');
            }
        });
    },

    //----------------------------------

    ebayStoreUpdate : function()
    {
        var self = AccountsHandlersObj;
        self.submitForm(M2ePro.url.ebayStoreUpdate + 'back/' + base64_encode('edit') + '/tab/' + $$('#accountsTabs a.active')[0].name);
    },

    ebayStoreSelectCategory : function(id)
    {
        $('ebay_store_categories_selected_container').show();
        $('ebay_store_categories_selected').value = id;
    },

    ebayStoreSelectCategoryHide : function()
    {
        $('ebay_store_categories_selected_container').hide();
        $('ebay_store_categories_selected').value = '';
    },

    //----------------------------------

    ordersMode_change : function()
    {
        var self = AccountsHandlersObj;

        if ($('orders_mode').value == self.ORDERS_MODE_YES) {
            $('magento_block_accounts_orders_listings').show();
            $('magento_block_accounts_orders_ebay').show();
        } else {
            $('magento_block_accounts_orders_listings').hide();
            $('magento_block_accounts_orders_ebay').hide();
        }

        $('orders_listings_mode').value = self.ORDERS_LISTINGS_MODE_NO;
        $('orders_ebay_mode').value = self.ORDERS_EBAY_MODE_NO;

        self.ordersListingsMode_change();
        self.ordersEbayMode_change();
    },

    ordersListingsMode_change : function()
    {
        var self = AccountsHandlersObj;

        if ($('orders_listings_mode').value == self.ORDERS_LISTINGS_MODE_YES) {
            $('orders_listings_store_mode_container').show();
        } else {
            $('orders_listings_store_mode_container').hide();
        }

        $('orders_listings_store_mode').value = self.ORDERS_LISTINGS_STORE_MODE_NO;
        self.ordersListingsStoreMode_change();

        if ($('orders_listings_mode').value == self.ORDERS_LISTINGS_MODE_NO &&
            $('orders_ebay_mode').value == self.ORDERS_EBAY_MODE_NO) {

            $('magento_block_accounts_orders_customer').hide();
            $('orders_customer_mode').value = self.ORDERS_CUSTOMER_MODE_GUEST;
            self.ordersCustomerMode_change();
            $('magento_block_accounts_orders_status').hide();
            $('orders_status_mode').value = self.ORDERS_STATUS_MAPPING_DEFAULT;
            self.ordersStatusMode_change();
            $('magento_block_accounts_orders_creation_rules').hide();
            $('orders_status_checkout_incomplete').value = self.ORDERS_CHECKOUT_MODE_COMPLETED;
        } else {
            $('magento_block_accounts_orders_customer').show();
            $('magento_block_accounts_orders_status').show();
            $('magento_block_accounts_orders_creation_rules').show();
        }
    },

    ordersListingsStoreMode_change : function()
    {
        var self = AccountsHandlersObj;

        if ($('orders_listings_store_mode').value == self.ORDERS_LISTINGS_STORE_MODE_YES) {
            $('orders_listings_store_id_container').show();
        } else {
            $('orders_listings_store_id_container').hide();
        }

        $('orders_listings_store_id').value = '';
    },

    ordersEbayMode_change : function()
    {
        var self = AccountsHandlersObj;

        if ($('orders_ebay_mode').value == self.ORDERS_EBAY_MODE_YES) {
            $('orders_ebay_create_product_container').show();
            $('orders_ebay_store_id_container').show();
        } else {
            $('orders_ebay_create_product_container').hide();
            $('orders_ebay_store_id_container').hide();
        }

        $('orders_ebay_create_product').value = self.ORDERS_EBAY_CREATE_PRODUCT_NO;
        $('orders_ebay_store_id').value = '';

        if ($('orders_listings_mode').value == self.ORDERS_LISTINGS_MODE_NO &&
            $('orders_ebay_mode').value == self.ORDERS_EBAY_MODE_NO) {

            $('magento_block_accounts_orders_customer').hide();
            $('orders_customer_mode').value = self.ORDERS_CUSTOMER_MODE_GUEST;
            self.ordersCustomerMode_change();
            $('magento_block_accounts_orders_status').hide();
            $('orders_status_mode').value = self.ORDERS_STATUS_MAPPING_DEFAULT;
            self.ordersStatusMode_change();
            $('magento_block_accounts_orders_creation_rules').hide();
            $('orders_status_checkout_incomplete').value = self.ORDERS_CHECKOUT_MODE_COMPLETED;
        } else {
            $('magento_block_accounts_orders_customer').show();
            $('magento_block_accounts_orders_status').show();
            $('magento_block_accounts_orders_creation_rules').show();
        }
    },

    ordersEbayCreateProduct_change : function()
    {
        var self = AccountsHandlersObj;

        if ($('orders_ebay_create_product').value == self.ORDERS_EBAY_CREATE_PRODUCT_NO) {
            $('orders_ebay_create_product_note').hide();
        } else {
            $('orders_ebay_create_product_note').show();
        }
    },

    ordersCustomerMode_change : function()
    {
        var self = AccountsHandlersObj;

        if ($('orders_customer_mode').value == self.ORDERS_CUSTOMER_MODE_EXIST) {
            $('orders_customer_exist_id_container').show();
            $('orders_customer_exist_id').addClassName('M2ePro-account-product-id');
        } else {  // self.ORDERS_CUSTOMER_MODE_GUEST || self.ORDERS_CUSTOMER_MODE_NEW
            $('orders_customer_exist_id_container').hide();
            $('orders_customer_exist_id').removeClassName('M2ePro-account-product-id');
        }

        if ($('orders_customer_mode').value == self.ORDERS_CUSTOMER_MODE_NEW) {
            $('orders_customer_new_website_container').show();
            $('orders_customer_new_group_container').show();
            $('orders_customer_new_subscribe_news_container').show();
            $('orders_customer_new_send_notifications_container').show();
        } else { // self.ORDERS_CUSTOMER_MODE_GUEST || self.ORDERS_CUSTOMER_MODE_EXIST
            $('orders_customer_new_website_container').hide();
            $('orders_customer_new_group_container').hide();
            $('orders_customer_new_subscribe_news_container').hide();
            $('orders_customer_new_send_notifications_container').hide();
        }

        $('orders_customer_exist_id').value = '';
        $('orders_customer_new_website').value = '';
        $('orders_customer_new_group').value = '';
        $('orders_customer_new_subscribe_news').value = 0;
        $('orders_customer_new_send_notifications').value = 0;
    },

    ordersStatusMode_change : function()
    {
        var self = AccountsHandlersObj;

        // Reset dropdown selected values to default
        $('orders_status_checkout_completed').value = self.ORDERS_DEFAULT_STATUS_ON_CHECKOUT_COMPLETE;
        $('orders_status_payment_completed').value = self.ORDERS_DEFAULT_STATUS_ON_PAYMENT_COMPLETE;
        $('orders_status_shipping_completed').value = self.ORDERS_DEFAULT_STATUS_ON_SHIPPING_COMPLETE;

        // Default auto create invoice & shipping
        $('orders_status_invoice').checked = 1;
        $('orders_status_shipping').checked = 1;

        if ($('orders_status_mode').value == self.ORDERS_STATUS_MAPPING_DEFAULT) {
            // Mapping mode default
            $('orders_status_checkout_completed').disabled = 1;
            $('orders_status_payment_completed').disabled = 1;
            $('orders_status_shipping_completed').disabled = 1;
            $('orders_status_invoice').disabled = 1;
            $('orders_status_shipping').disabled = 1;
        } else {
            // Mapping mode custom
            $('orders_status_checkout_completed').disabled = 0;
            $('orders_status_payment_completed').disabled = 0;
            $('orders_status_shipping_completed').disabled = 0;
            $('orders_status_invoice').disabled = 0;
            $('orders_status_shipping').disabled = 0;
        }
    },

    ordersRule_change : function()
    {
        var self         = AccountsHandlersObj,
            checkoutMode = $('orders_status_checkout_incomplete').value,
            paymentMode  = $('orders_status_payment_complete_mode').value;

        if (checkoutMode == self.ORDERS_CHECKOUT_MODE_IGNORE && paymentMode == self.ORDERS_PAYMENT_MODE_IGNORE) {
            $('orders_combined_mode_tr').show();
        } else {
            $('orders_combined_mode_tr').hide();
        }
    }

    //----------------------------------
});
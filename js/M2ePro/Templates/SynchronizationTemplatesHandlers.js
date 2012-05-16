SynchronizationTemplatesHandlers = Class.create();
SynchronizationTemplatesHandlers.prototype = Object.extend(new CommonHandlers(), {

    //----------------------------------

    initialize: function()
    {
        this.setValidationCheckRepetitionValue('M2ePro-synchronization-tpl-title',
                                                M2ePro.text.title_not_unique_error,
                                                'SynchronizationsTemplates', 'title', 'id',
                                                M2ePro.formData.id);

        Validation.add('M2ePro-input-time', M2ePro.text.wrong_time_format_error, function(value) {
            return value.match(/^\d{2}:\d{2}$/g);
        });
    },

    //----------------------------------

    stopQty_change : function()
    {
        var self = SynchronizationTemplatesHandlersObj;

        if ($('stop_qty').value == self.STOP_QTY_NONE) {
            $('stop_qty_value_container').hide();
            $('stop_qty_value_max_container').hide();
        } else if ($('stop_qty').value == self.STOP_QTY_LESS) {
            $('stop_qty_item_min').hide();
            $('stop_qty_item').show();
            $('stop_qty_value_container').show();
            $('stop_qty_value_max_container').hide();
        } else if ($('stop_qty').value == self.STOP_QTY_BETWEEN) {
            $('stop_qty_item_min').show();
            $('stop_qty_item').hide();
            $('stop_qty_value_container').show();
            $('stop_qty_value_max_container').show();
        } else if ($('stop_qty').value == self.STOP_QTY_MORE) {
            $('stop_qty_item_min').hide();
            $('stop_qty_item').show();   
            $('stop_qty_value_container').show();
            $('stop_qty_value_max_container').hide();
        } else {
            $('stop_qty_value_container').hide();
            $('stop_qty_value_max_container').hide();
        }
    },

    relistMode_change : function()
    {
        var self = SynchronizationTemplatesHandlersObj;

        if ($('relist_mode').value == self.RELIST_MODE_NONE) {
            $('relist_list_mode_container').hide();
            $('relist_filter_user_lock_tr_container').hide();
            $('relist_send_data_tr_container').hide();
            $('magento_block_synchronization_template_relist_rules').hide();
            $('magento_block_synchronization_template_relist_schedule').hide();
        } else if ($('relist_mode').value == self.RELIST_MODE_YES) {
            $('relist_list_mode_container').show();
            $('relist_filter_user_lock_tr_container').show();
            $('relist_send_data_tr_container').show();
            $('magento_block_synchronization_template_relist_rules').show();
            $('magento_block_synchronization_template_relist_schedule').show();
        } else {
            $('relist_list_mode_container').hide();
            $('relist_filter_user_lock_tr_container').hide();
            $('relist_send_data_tr_container').hide();
            $('magento_block_synchronization_template_relist_rules').hide();
            $('magento_block_synchronization_template_relist_schedule').hide();
        }

        $('relist_schedule_type').value = self.RELIST_SCHEDULE_TYPE_IMMEDIATELY;
        self.relistScheduleType_change();
    },

    relistQty_change : function()
    {
        var self = SynchronizationTemplatesHandlersObj;

        if ($('relist_qty').value == self.RELIST_QTY_NONE) {
            $('relist_qty_value_container').hide();
            $('relist_qty_value_max_container').hide();
        } else if ($('relist_qty').value == self.RELIST_QTY_LESS) {
            $('relist_qty_item_min').hide();
            $('relist_qty_item').show();
            $('relist_qty_value_container').show();
            $('relist_qty_value_max_container').hide();
        } else if ($('relist_qty').value == self.RELIST_QTY_BETWEEN) {
            $('relist_qty_item_min').show();
            $('relist_qty_item').hide();
            $('relist_qty_value_container').show();
            $('relist_qty_value_max_container').show();
        } else if ($('relist_qty').value == self.RELIST_QTY_MORE) {
            $('relist_qty_item_min').hide();
            $('relist_qty_item').show();
            $('relist_qty_value_container').show();
            $('relist_qty_value_max_container').hide();
        } else {
            $('relist_qty_value_container').hide();
            $('relist_qty_value_max_container').hide();
        }
    },

    relistScheduleType_change : function()
    {
        var self = SynchronizationTemplatesHandlersObj;

        if ($('relist_schedule_type').value == self.RELIST_SCHEDULE_TYPE_IMMEDIATELY) {
            $('relist_schedule_through_value_container').hide();
            $('relist_schedule_week_container').hide();
            $('relist_schedule_week_time_container').hide();
            $('relist_schedule_week_time').value = 0;
            self.relistScheduleWeekTime_change();
        } else if ($('relist_schedule_type').value == self.RELIST_SCHEDULE_TYPE_THROUGH) {
            $('relist_schedule_through_value_container').show();
            $('relist_schedule_week_container').hide();
            $('relist_schedule_week_time_container').hide();
            $('relist_schedule_week_time').value = 0;
            self.relistScheduleWeekTime_change();
        } else if ($('relist_schedule_type').value == self.RELIST_SCHEDULE_TYPE_WEEK) {
            $('relist_schedule_through_value_container').hide();
            $('relist_schedule_week_container').show();
            $('relist_schedule_week_time_container').show();
            $('relist_schedule_week_time').value = 0;
            self.relistScheduleWeekTime_change();
        } else {
            $('relist_schedule_through_value_container').hide();
            $('relist_schedule_week_container').hide();
            $('relist_schedule_week_time_container').hide();
            $('relist_schedule_week_time').value = 0;
            self.relistScheduleWeekTime_change();
        }
    },

    relistScheduleWeekTime_change : function()
    {
        if ($('relist_schedule_week_time').value == 0) {
            $('relist_schedule_week_start_time_container').hide();
            $('relist_schedule_week_end_time_container').hide();
        } else {
            $('relist_schedule_week_start_time_container').show();
            $('relist_schedule_week_end_time_container').show();
        }
    }

    //----------------------------------
});
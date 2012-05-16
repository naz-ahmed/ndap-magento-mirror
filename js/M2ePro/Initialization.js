// Create main objects
// ----------------------------------
CommonHandlersObj = new CommonHandlers();

MagentoMessagesObj = new MagentoMessages();
MagentoBlocksObj = new MagentoBlocks();

ModuleNoticesObj = new BlockNotices('Module');
ServerNoticesObj = new BlockNotices('Server');

FieldsTipsObj = new FieldsTips();
// ----------------------------------

// Set main observers
// ----------------------------------
Event.observe(window, 'load', function() {

    CommonHandlersObj.initCommonValidators();

    $$('.block_notices_module').each(function(blockObj) {
        ModuleNoticesObj.observeModulePrepareStart(blockObj);
    });
    $$('.block_notices_server').each(function(blockObj) {
        ServerNoticesObj.observeServerPrepareStart(blockObj);
    });

    $$('div.entry-edit').each(function(blockObj) {

        if (blockObj.select('div.entry-edit-head').length == 0) {
            return;
        }

        blockObj.select('div.entry-edit-head')[0].innerHTML = '<div class="entry-edit-head-left" style="float: left; width: 86%;">' + blockObj.select('div.entry-edit-head')[0].innerHTML + '</div>' +
                                                              '<div class="entry-edit-head-right" style="float: right; width: 12%;"></div>';
        MagentoBlocksObj.observePrepareStart(blockObj);

        if (blockObj.select('div.fieldset div.hor-scroll table.form-list tr td.value p.note').length > 0) {
            FieldsTipsObj.observePrepareStart(blockObj);
        }
    });
});
// ----------------------------------
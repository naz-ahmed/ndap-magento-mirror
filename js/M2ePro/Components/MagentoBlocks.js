MagentoBlocks = Class.create();
MagentoBlocks.prototype = {

    // --------------------------------

    initialize: function() {},

    // --------------------------------

    show: function(blockClass,init)
    {
        blockClass = blockClass || '';
        if (blockClass == '') {
            return false;
        }

        $$('div.'+blockClass)[0].select('div.entry-edit-head div.entry-edit-head-right div.block_visibility_changer').each(function(o) {
            o.remove();
        });
        $$('div.'+blockClass)[0].select('div.entry-edit-head div.entry-edit-head-right div.block_tips_changer').each(function(o) {
            o.show();
        });

        var tempObj = $$('div.'+blockClass)[0].select('div.entry-edit-head div.entry-edit-head-left')[0];
        tempObj.writeAttribute("onclick", "MagentoBlocksObj.hide('"+blockClass+"','0');");

        var tempHtml = $$('div.'+blockClass)[0].select('div.entry-edit-head div.entry-edit-head-right')[0].innerHTML;
        var tempHtml2 = '<div class="block_visibility_changer collapseable" style="float: right; color: white; font-size: 11px; margin-left: 20px;">';
        tempHtml2 += '<a href="javascript:void(0);" onclick="MagentoBlocksObj.hide(\''+blockClass+'\',\'0\');" style="width: 20px; border: 0px;" class="open">&nbsp;</a>';
        tempHtml2 += '</div>';
        $$('div.'+blockClass)[0].select('div.entry-edit-head div.entry-edit-head-right')[0].innerHTML = tempHtml2 + tempHtml;

        deleteCookie(blockClass, '/', '');

        if (init == '0') {
            $$('div.'+blockClass+' div.fieldset')[0].show();
        } else {
            $$('div.'+blockClass+' div.fieldset')[0].show();
        }

        $$('div.'+blockClass+' div.entry-edit-head')[0].setStyle({marginBottom: '0px'});
        $$('div.'+blockClass+' div.fieldset')[0].setStyle({marginBottom: '15px'});

        return true;
    },

    hide: function(blockClass,init)
    {
        blockClass = blockClass || '';
        if (blockClass == '') {
            return false;
        }

        $$('div.'+blockClass)[0].select('div.entry-edit-head div.entry-edit-head-right div.block_visibility_changer').each(function(o) {
            o.remove();
        });
        $$('div.'+blockClass)[0].select('div.entry-edit-head div.entry-edit-head-right div.block_tips_changer').each(function(o) {
            o.hide();
        });

        var tempObj = $$('div.'+blockClass)[0].select('div.entry-edit-head div.entry-edit-head-left')[0];
        tempObj.writeAttribute("onclick", "MagentoBlocksObj.show('"+blockClass+"','0');");

        var tempHtml = $$('div.'+blockClass)[0].select('div.entry-edit-head div.entry-edit-head-right')[0].innerHTML;
        var tempHtml2 = '<div class="block_visibility_changer collapseable" style="float: right; color: white; font-size: 11px; margin-left: 20px;">';
        tempHtml2 += '<a href="javascript:void(0);" onclick="MagentoBlocksObj.show(\''+blockClass+'\',\'0\');" style="width: 20px; border: 0px;">&nbsp;</a>';
        tempHtml2 += '</div>';
        $$('div.'+blockClass)[0].select('div.entry-edit-head div.entry-edit-head-right')[0].innerHTML = tempHtml2 + tempHtml;

        setCookie( blockClass , 1 , 3*365 , '/' );

        if (init == '0') {
            $$('div.'+blockClass+' div.fieldset')[0].hide();
        } else {
            $$('div.'+blockClass+' div.fieldset')[0].hide();
        }

        $$('div.'+blockClass+' div.entry-edit-head')[0].setStyle({marginBottom: '15px'});
        $$('div.'+blockClass+' div.fieldset')[0].setStyle({marginBottom: '0px'});

        return true;
    },

    // --------------------------------

    observePrepareStart: function(blockObj)
    {
        var self = this;

        var tempCollapseable = blockObj.readAttribute('collapseable');
        if (typeof tempCollapseable == 'string' && tempCollapseable == 'no') {
            return;
        }

        var tempId = blockObj.readAttribute('id');
        if (typeof tempId != 'string') {
            tempId = 'magento_block_md5_' + md5(blockObj.innerHTML.replace(/[^A-Za-z]/g,''));
            blockObj.writeAttribute("id",tempId);
        }

        var blockClass = tempId + '_hide';
        blockObj.addClassName(blockClass);

        var tempObj = blockObj.select('div.entry-edit-head div.entry-edit-head-left')[0];
        tempObj.setStyle({cursor: 'pointer'});

        var isClosed = getCookie(blockClass);

        if (isClosed == '' || isClosed == '0') {
            self.show(blockClass,'1');
        } else {
            self.hide(blockClass,'1');
        }
    }

    // --------------------------------
}
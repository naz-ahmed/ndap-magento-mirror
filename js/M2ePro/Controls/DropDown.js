DropDown = Class.create();
DropDown.prototype = {

    // --------------------------------

    initialize : function() {},

    // --------------------------------

    observe : function()
    {
        var self = this;
        $$('.drop_down').each(function(node) {
            self.prepare(node);
        });
    },

    // --------------------------------

    prepare : function(node)
    {
        var ulObj = $(node).select('ul')[0];
        if (typeof ulObj == 'undefined') {
            return;
        }

        var tempHtml = '<ul>';
        $(ulObj).childElements().each(function(object) {
            tempHtml += '<li><a href="'+$(object).readAttribute('href')+'"';
            if ($(object).readAttribute('target') != null) {
                tempHtml += ' target="'+$(object).readAttribute('target');
            }
            tempHtml +=  '"><span>'+$(object).innerHTML+'</span></a></li>';
        });
        tempHtml += '</ul>';
        
        $(ulObj).remove();

        if (tempHtml != '') {
            tempHtml = '<div id="'+node.id+'_drop_down" class="drop_down_menu">' + tempHtml + '</div>';
            $(node).insert({ after: tempHtml });
        }

        $(node).observe('click', DropDownObj.toggleItems);
    },

    // --------------------------------

    toggleItems : function()
    {
        var tempId = this.id + '_drop_down';
        if ($(tempId).getStyle('display') == 'none') {
            var x = $(this).positionedOffset()[0];
            var y = $(this).positionedOffset()[1];
            y += $(this).getHeight();
            $(tempId).setStyle({
                left: x + 'px',
                top: y + 'px'
            });

            $(tempId).style.display = 'block';

            setTimeout(function() {
                $$('body')[0].observe('click', DropDownObj.hideItems);
            }, 100);
        } else {
            $(tempId).hide();
            $$('body')[0].stopObserving('click', DropDownObj.hideItems);
        }
    },

    hideItems : function()
    {
        $$('.drop_down_menu').each(function(object) {
            $(object).hide();
            $$('body')[0].stopObserving('click', DropDownObj.hideItems);
        });
    }

    // --------------------------------
}
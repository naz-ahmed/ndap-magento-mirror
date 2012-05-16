AttributeSetsHandlers = Class.create();
AttributeSetsHandlers.prototype = {

    //----------------------------------

    initialize: function(selectId) {
        this.selectId = selectId || 'attribute_sets';

        Validation.add('M2ePro-validate-attribute-sets', M2ePro.text.attribute_set_not_selected_error, function(value)
        {
            var confirmedAttributeSets = AttributeSetsHandlersObj.getConfirmedAttributeSets();

            if (!confirmedAttributeSets.length) {
                return false;
            }

            var selectedAttributeSets = AttributeSetsHandlersObj.getSelectedAttributeSets();
            
            if (confirmedAttributeSets != selectedAttributeSets) {
                return false;
            }
            return true;
        });
    },

    //----------------------------------

    isAttributeSetsConfirmed: function()
    {
        var self = AttributeSetsHandlersObj;
        var confirmedAttributeSets = self.getConfirmedAttributeSets(true);

        if (!confirmedAttributeSets.length) {
            return false;
        }
        return true;
    },

    getConfirmedAttributeSets: function(returnAsArray)
    {
        returnAsArray = returnAsArray || false;

        var self = AttributeSetsHandlersObj;
        var confirmedAttributeSets = $(self.selectId).readAttribute('confirmed-attribute-sets');

        if (confirmedAttributeSets === null || !confirmedAttributeSets.length) {
            if (returnAsArray) {
                return new Array();
            }
            return '';
        }

        if (returnAsArray) {
            return confirmedAttributeSets.split(',');
        }

        return confirmedAttributeSets;
    },

    getSelectedAttributeSets: function(returnAsArray)
    {
        returnAsArray = returnAsArray || false;

        var self = AttributeSetsHandlersObj;
        var selectedAttributeSets = [];

        if (!$$('select#' + AttributeSetsHandlersObj.selectId)[0]) {
            //template is locked
            selectedAttributeSets = $(AttributeSetsHandlersObj.selectId).value.split(',');
        } else {
            $$('select#' + AttributeSetsHandlersObj.selectId + ' option').each(function(obj) {
                if (obj.selected) {
                    selectedAttributeSets.push(obj.value);
                }
            });
        }

        if (returnAsArray) {
            return selectedAttributeSets;
        }
        return selectedAttributeSets.join(',');
    },

    //----------------------------------

    changeAttributeSets: function()
    {
        var self = AttributeSetsHandlersObj;

        CommonHandlersObj.hideEmptyOption(self.selectId);
        self.showConfirmButton();
    },

    confirmAttributeSets: function()
    {
        var self = AttributeSetsHandlersObj;
        var selectedAttributeSets = self.getSelectedAttributeSets();

        if (selectedAttributeSets.length) {
            $(self.selectId).writeAttribute('confirmed-attribute-sets', selectedAttributeSets);
            self.prepareAttributes(selectedAttributeSets);
            self.hideConfirmButton();
        } else {
            alert(M2ePro.text.attribute_set_not_selected_error);
        }
    },

    selectAllAttributeSets: function()
    {
        var self = AttributeSetsHandlersObj;

        $$('#' + self.selectId + ' option').each(function(obj) {
            if (obj.value != '') {
                obj.selected = true;
            }
        });

        self.changeAttributeSets();
    },

    //----------------------------------

    showConfirmButton: function()
    {
        $(this.selectId + '_confirm_button').show();
    },

    hideConfirmButton: function()
    {
        $(this.selectId + '_confirm_button').hide();
    },

    //----------------------------------

    checkAttributeSetSelection: function()
    {
        if (!this.isAttributeSetsConfirmed()) {
            alert(M2ePro.text.attribute_set_not_selected_error);
            return false;
        }

        return true;
    },

    //----------------------------------

    appendToText: function(ddId, targetId)
    {
        var suffix = ' #' + $(ddId).value + '#';
        $(targetId).value = $(targetId).value + suffix;
    },

    appendToTextarea: function(v)
    {
        if (wysiwygtext.isEnabled()) {
            var data = tinyMCE.get('description_template').getContent();
            tinyMCE.get('description_template').setContent(data + ' ' + v);
        } else {
            var data = $('description_template').value + ' ' + v;
            $('description_template').value = data;
        }
    },

    //----------------------------------

    checkAttributesSelect: function (id, value)
    {
        if ($(id)) {
            if (typeof M2ePro.formData[id] != 'undefined') {
                $(id).value = M2ePro.formData[id];
            }
            if (value) {
                $(id).value = value;
            }
        }
    },

    prepareAttributes: function(attributeSets)
    {
        var self = this;

        if (attributeSets instanceof Array) {
            attributeSets = attributeSets.join(',');
        }

        new Ajax.Request( M2ePro.url.magentoGetAttributesByAttributeSets + 'attribute_sets/' + attributeSets + '/',
        {
            method: 'post',
            asynchronous : false,
            onSuccess: function (transport)
            {
                var data = transport.responseText.evalJSON(true);

                var cachedOptions = '';
                data.each(function(v) {
                    cachedOptions += '<option value="' + v.code + '">' + v.label + '</option>\n';
                });

                self.attrData = cachedOptions;
            }
        });
	},

    //----------------------------------

	renderAttributes: function (name, insertTo, value, width)
	{
		var style = width ? ' style="width: ' + width + 'px;"' : '';
		var txt = '<select name="' + name + '" id="' + name + '"' + style + '>\n';

		txt += this.attrData;
		txt += '</select>';

		$(insertTo).innerHTML = txt;
		this.checkAttributesSelect(name, value);
	},

    renderAttributesWithEmptyOption: function(name, insertTo, value, notRequiried)
    {
        className = notRequiried ? '' : ' class="M2ePro-required-when-visible"';
        var txt = '<select name="' + name + '" id="' + name + '" ' + className + '>\n';

        txt += '<option class="empty"></option>\n';
        txt += this.attrData;
        txt += '</select>';

        if ($(insertTo + '_note') != null && $$('#' + insertTo + '_note').length != 0) {
            $(insertTo).innerHTML = txt + $(insertTo + '_note').innerHTML;
        } else {
            $(insertTo).innerHTML = txt;
        }

        this.checkAttributesSelect(name, value);
    }

    //----------------------------------
}
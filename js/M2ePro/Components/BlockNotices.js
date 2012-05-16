BlockNotices = Class.create();
BlockNotices.prototype = {

    // --------------------------------

    initialize: function(type)
    {
        this.type = type;
    },

    // --------------------------------

    show: function(id)
    {
        id = id || '';
        if (id == '') {
            return false;
        }
        $(id).show();
        return true;
    },

    hide: function(id)
    {
        id = id || '';
        if (id == '') {
            return false;
        }
        $(id).hide();
        return true;
    },

    // --------------------------------

    showContent: function(id)
    {
        var self = this;
        
        id = id || '';
        if (id == '') {
            return false;
        }

        $$('#'+id+' div.block_notices_content').each(function(object) {
            Effect.SlideDown(object,{duration:0.7});
        });
        $$('#'+id+' div.block_notices_header div.block_notices_header_left span.arrow').each(function(object) {
            object.innerHTML = '&uarr;';
        });
        $$('#'+id+' div.block_notices_header div.block_notices_header_left a').each(function(object) {
            object.writeAttribute("onclick",self.type+'NoticesObj.hideContent(\'' + id + '\')');
        });

        deleteCookie(id+'_closed_content', '/', '');

        return true;
    },

    hideContent: function(id)
    {
        var self = this;

        id = id || '';
        if (id == '') {
            return false;
        }

        $$('#'+id+' div.block_notices_content').each(function(object) {
            Effect.SlideUp(object,{duration:0.7});
        });
        $$('#'+id+' div.block_notices_header div.block_notices_header_left span.arrow').each(function(object) {
            object.innerHTML = '&darr;';
        });
        $$('#'+id+' div.block_notices_header div.block_notices_header_left a').each(function(object) {
            object.writeAttribute("onclick",self.type+'NoticesObj.showContent(\'' + id + '\')');
        });

        setCookie( id+'_closed_content' , 1 , 3*365 , '/' );

        return true;
    },

    // --------------------------------

    showBlock: function(id)
    {
        id = id || '';
        if (id == '') {
            return false;
        }
        $(id).show();
        deleteCookie(id+'_hide_block', '/', '');
        return true;
    },

    hideBlock: function(id)
    {
        if (!confirm(CONFIRM)) {
            return;
        }

        id = id || '';
        if (id == '') {
            return false;
        }
        $(id).remove();
        setCookie( id+'_hide_block' , 1 , 3*365 , '/' );
        return true;
    },

    // --------------------------------

    remove: function(id)
    {
        id = id || '';
        if (id == '') {
            return false;
        }
        $(id).remove();
        return true;
    },

    clear: function(id)
    {
        id = id || '';
        if (id == '') {
            return false;
        }
        $(id).innerHTML = '';
        return true;
    },

    // --------------------------------

    getPreparedId: function(object)
    {
        var id = object.readAttribute('id');
        if (typeof id != 'string') {
            id = 'block_notice_md5_' + md5(object.innerHTML.replace(/[^A-Za-z]/g,''));
            object.writeAttribute('id',id);
        }
        return id;
    },

    getPreparedTitle: function(object)
    {
        var title = object.readAttribute('title');
        if (typeof title != 'string') {
            title = '';
        }
        object.writeAttribute('title','');
        return title;
    },

    getPreparedSubTitle: function(object)
    {
        var subtitle = object.readAttribute('subtitle');
        if (typeof subtitle != 'string') {
            subtitle = '['+HELP+']';
        }
        object.writeAttribute('subtitle','');
        return subtitle;
    },

    getPreparedContent: function(object)
    {
        var content = object.readAttribute('content');
        if (typeof content != 'string') {
            content = '';
        }
        object.writeAttribute('content','');
        return content;
    },

    getPreparedCollapseable: function(object)
    {
        var collapseable = object.readAttribute('collapseable');
        if (typeof collapseable != 'string') {
            collapseable = true;
        } else {
            if (collapseable == 'no') {
                collapseable = false;
            } else {
                collapseable = true;
            }
        }
        object.writeAttribute('collapseable','');
        return collapseable;
    },

    getPreparedHideBlock: function(object)
    {
        var hideblock = object.readAttribute('hideblock');
        if (typeof hideblock != 'string') {
            hideblock = true;
        } else {
            if (hideblock == 'no') {
                hideblock = false;
            } else {
                hideblock = true;
            }
        }
        object.writeAttribute('hideblock','');
        return hideblock;
    },

    getPreparedAlwaysShow: function(object)
    {
        var alwaysShow = object.readAttribute('always_show');
        if (typeof alwaysShow != 'string') {
            alwaysShow = false;
        } else {
            if (alwaysShow == 'no') {
                alwaysShow = false;
            } else {
                alwaysShow = true;
            }
        }
        object.writeAttribute('always_show','');
        return alwaysShow;
    },

    // --------------------------------

    getHeaderHtml: function(id,title,subtitle,collapseable,hideblock)
    {
        var isClosedContent = getCookie(id+'_closed_content');

        var titleHtml = '';
        if (title != '') {
            titleHtml = '<span class="title">'+title+'</span>';
        }

        var subtitleHtml = '';
        if (subtitle != '') {
            subtitleHtml = '<span class="subtitle">'+subtitle+'</span>';
        }

        var arrowHtml = '';
        if (collapseable) {
            if (isClosedContent == '1') {
                arrowHtml = '<span class="arrow">&darr;</span>';
            } else {
                arrowHtml = '<span class="arrow">&uarr;</span>';
            }
        }

        var hideBlockHtml = '';
        if (hideblock) {
            var tempOnClick = this.type+'NoticesObj.hideBlock(\'' + id + '\')';
            hideBlockHtml = '<a href="javascript:void(0);" onclick="' + tempOnClick + '" title="'+HIDE_BLOCK+'"><span class="hideblock">&times;</span></a>';
        }

        if (titleHtml == '' && subtitleHtml == '' && arrowHtml == '' && hideBlockHtml == '') {
            return '';
        }

        var leftHtml = titleHtml + '&nbsp;&nbsp;' + subtitleHtml + '&nbsp;&nbsp;' + arrowHtml;
        if (collapseable) {
            var tempOnClick = this.type+'NoticesObj.hideContent(\'' + id + '\')';
            if (isClosedContent == '1') {
                tempOnClick = this.type+'NoticesObj.showContent(\'' + id + '\')';
            }
            leftHtml = '<a href="javascript:void(0);" onclick="' + tempOnClick + '">' + leftHtml + '</a>';
        }

        var rightHtml = hideBlockHtml;
        
        var html = '<div class="block_notices_header">' +
                        '<div class="block_notices_header_left">' +
                            leftHtml +
                        '</div>' +
                        '<div class="block_notices_header_right">' +
                            rightHtml +
                        '</div>' +
                        '<div style="clear: both;"></div>' +
                    '</div>';

        return html;
    },

    getContentHtml: function(id,content,collapseable)
    {
        var isClosedContent = getCookie(id+'_closed_content');
        
        var contentHtml = '';
        if (collapseable && isClosedContent == '1') {
            contentHtml = '<div class="block_notices_content" style="display: none;">';
        } else {
            contentHtml = '<div class="block_notices_content">';
        }
        contentHtml = contentHtml + '<div>' + content + '</div></div>';

        return contentHtml;
    },

    getFinalHtml: function(headerHtml,contentHtml)
    {
        if (headerHtml == '') {
            return contentHtml;
        }

        var search = '<div class="block_notices_content" style="';
        var replace = '<div class="block_notices_content" style="margin-top: 5px;';

        var tempBefore = contentHtml;
        contentHtml = contentHtml.replace(search,replace)
        var tempAfter = contentHtml;

        if (tempBefore == tempAfter) {
            var search = '<div class="block_notices_content"';
            var replace = '<div class="block_notices_content" style="margin-top: 5px;"';
            contentHtml = contentHtml.replace(search,replace)
        }

        return headerHtml + '<div style="clear: both;"></div>' + contentHtml;
    },

    // --------------------------------

    observeModulePrepareStart: function(object)
    {
        var id = this.getPreparedId(object);
        var title = this.getPreparedTitle(object);
        var subtitle = this.getPreparedSubTitle(object);
        var collapseable = this.getPreparedCollapseable(object);
        var hideblock = this.getPreparedHideBlock(object);
        var alwaysShow = this.getPreparedAlwaysShow(object);

        if (!alwaysShow) {
            if (!BLOCK_NOTICES_SHOW || (hideblock && getCookie(id+'_hide_block') == '1')) {
                object.remove(); return;
            }
        }
        
        var headerHtml = this.getHeaderHtml(id,title,subtitle,collapseable,hideblock);
        var contentHtml = this.getContentHtml(id,object.innerHTML,collapseable);
        object.innerHTML = this.getFinalHtml(headerHtml,contentHtml);

        object.removeClassName("block_notices_module");
        object.addClassName("block_notices");
    },

    // --------------------------------

    observeServerPrepareStart: function(object)
    {
        var id = this.getPreparedId(object);
        var title = this.getPreparedTitle(object);
        var subtitle = this.getPreparedSubTitle(object);
        var content = this.getPreparedContent(object);
        var collapseable = this.getPreparedCollapseable(object);
        var hideblock = this.getPreparedHideBlock(object);
        var alwaysShow = this.getPreparedAlwaysShow(object);

        if (!alwaysShow) {
            if (!BLOCK_NOTICES_SHOW || (hideblock && getCookie(id+'_hide_block') == '1')) {
                object.remove(); return;
            }
        }

        var headerHtml = '';
        var realContent = object.innerHTML;

        var self = this;

        if (title != '') {
            new Ajax.Request( BLOCK_NOTICES_GET_DATA_URL ,
            {
                method:'get',
                parameters: {key: title},
                onSuccess: function(transport)
                {
                    if (transport.responseText != '') {
                        title = transport.responseText;
                    }

                    headerHtml = self.getHeaderHtml(id,title,subtitle,collapseable,hideblock);
                    self.observeServerPrepareContent(object,id,headerHtml,realContent,content,collapseable);
                }
            });
        } else {
            headerHtml = self.getHeaderHtml(id,title,subtitle,collapseable,hideblock);
            self.observeServerPrepareContent(object,id,headerHtml,realContent,content,collapseable);
        }
    },

    observeServerPrepareContent: function(object,id,headerHtml,realContent,content,collapseable)
    {
        var self = this;
        
        if (realContent != '') {
            contentHtml = self.getContentHtml(id,realContent,collapseable);
            self.observeServerPrepareFinal(object,headerHtml,contentHtml);
        } else {

            var contentHtml = '';
            if (content != '') {
                new Ajax.Request( BLOCK_NOTICES_GET_DATA_URL ,
                {
                    method:'get',
                    parameters: {key: content},
                    onSuccess: function(transport)
                    {
                        if (transport.responseText != '') {
                            contentHtml = transport.responseText;
                        } else {
                            contentHtml = content;
                        }

                        contentHtml = self.getContentHtml(id,contentHtml,collapseable);
                        self.observeServerPrepareFinal(object,headerHtml,contentHtml);
                    }
                });
            }
            else {
                contentHtml = self.getContentHtml(id,contentHtml,collapseable);
                self.observeServerPrepareFinal(object,headerHtml,contentHtml);
            }
        }
    },

    observeServerPrepareFinal: function(object,headerHtml,contentHtml)
    {
        object.innerHTML = this.getFinalHtml(headerHtml,contentHtml);

        object.removeClassName("block_notices_server");
        object.addClassName("block_notices");
    }

    // --------------------------------
}
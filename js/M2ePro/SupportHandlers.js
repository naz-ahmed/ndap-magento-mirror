SupportHandlers = Class.create();
SupportHandlers.prototype = Object.extend(new CommonHandlers(), {

    //----------------------------------

    initialize: function() {},

    //----------------------------------

    searchUserVoiceArticles: function()
    {
        var self = SupportHandlersObj,
            query = $('user_voice_search_query').value;

        self.getUserVoiceArticles(query);
    },

    //----------------------------------

    renderUserVoiceArticles: function(html)
    {
        var self = SupportHandlersObj;

        if (html != '') {
            $('magento_block_support_faq_data').innerHTML = html;
            $('magento_block_support_faq_container').show();
            $('magento_block_support_general_container').addClassName('support-box-right');
        }
    },

    //----------------------------------

    getUserVoiceArticlesHtml: function(data)
    {
        var self = SupportHandlersObj,
            html = '';

        if (data && data.articles.length) {
            data.articles.each(function(article) {

                html += '<li class="faq">';

                html += '<a href="javascript:void(0);" onclick="SupportHandlersObj.toggleAnswer(\'' + article.id + '\');">';
                html += '<h4>' + article.question + '</h4>';
                html += '</a>';

                html += '<div class="answer" id="article_answer_' + article.id + '" style="display: none;">' + article.answer_html + '</div>';

                html += '</li>';

            });
            html = '<ul>' + html + '</ul>';
        } else {
            html = '<div class="no-articles">'+M2ePro.text.nothing_was_found_text+'</div>';
        }

        return html;
    },

    //----------------------------------

    getUserVoiceArticles: function(query)
    {
        query = query ? 'search=' + query : '';

        var self = SupportHandlersObj;

        new Ajax.Request( M2ePro.url.getUserVoiceData + '?' + query ,
        {
            method: 'get',
            asynchronous: true,
            onSuccess: function(transport)
            {
                var html = self.getUserVoiceArticlesHtml(transport.responseText.evalJSON(true));
                self.renderUserVoiceArticles(html);
            }
        });
    },

    //----------------------------------

    toggleAnswer: function(answerId)
    {
        var answerBlock = $('article_answer_' + answerId);

        if (answerBlock.style.display == 'none') {
            Effect.Appear(answerBlock,{duration:0.5});
        } else {
            Effect.Fade(answerBlock,{duration:0.3});
        }
    },

    //----------------------------------

    moreAttachments: function()
    {
        var emptyField = false;

        $$('#more input').each(function(obj) {
            if (obj.value == '') {
                emptyField = true;
            }
        });

        if (emptyField) {
            return;
        }
        $('more').insert('<input type="file" name="files[]" /><br />');
    }

    //----------------------------------
});
/**
 * Nooku Platform - http://www.nooku.org/platform
 *
 * @copyright      Copyright (C) 2011 Johan Janssens and Timble CVBA. (http://www.timble.net)
 * @license        GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link           https://github.com/nooku/nooku-platform for the canonical source repository
 */

(function ($)
{
    Attachments = {
        getInstance: function(config)
        {
            var my = {
                init: function (config)
                {
                    my.template = config.template ? $(config.template) : null;
                    my.selector = $(config.selector);
                    my.url = config.url;
                    my.csrf_token = config.csrf_token;

                    if (my.template)  {
                        my.template.text(my.templateCleanup(my.template.text()));
                    }
                },
                templateCleanup: function(content)
                {
                    return content.replace(/(href|src)=".+?\[%=/g, "$1=\"[%=");
                },
                render: function(attachment, template)
                {
                    var output = '';

                    if (template)
                    {
                        template = $(template);
                        template.text(this.templateCleanup(template.text()));
                    } else {
                        template = this.template;
                    }

                    if (template)
                    {
                        attachment.name = this.escape(attachment.name);
                        attachment.thumbnail = attachment.thumbnail || null;

                        output = new EJS({element: template.get(0)}).render(attachment);
                    }

                    return output;
                },
                escape: function(string)
                {
                    var entityMap = {
                        "&": "&amp;",
                        "<": "&lt;",
                        ">": "&gt;",
                        '"': '&quot;',
                        "'": '&#39;',
                        "/": '&#x2F;'
                    };

                    return String(string).replace(/[&<>"'\/]/g, function (s) {return entityMap[s]});
                },
                attach: function(attachment)
                {
                    var context = {
                        data: {
                            csrf_token: this.csrf_token,
                            _action: 'attach'
                        },
                        url: this.url,
                        attachment: attachment
                    };

                    this.selector.trigger('before.attach', context);

                    $.ajax({
                        url: context.url,
                        method: 'POST',
                        data: context.data,
                        success: function(data)
                        {
                            context.result = data;
                            my.selector.trigger('after.attach', context);
                        }
                    });
                },
                detach: function(attachment)
                {
                    var context = {
                        data: {
                            csrf_token: this.csrf_token,
                            _action: 'detach'
                        },
                        url: this.url,
                        attachment: attachment
                    };

                    this.selector.trigger('before.detach', context);

                    $.ajax({
                        url: context.url,
                        method: 'POST',
                        data: context.data,
                        success: function(data)
                        {
                            context.result = data;
                            my.selector.trigger('after.detach', context);
                        }
                    });
                },
                replace: function(text, params)
                {
                    $.each(params, function(key, value) {

                        var search = '%7B' + key + '%7D';

                        if (text.search(search) === -1) {
                            search = '{' + key + '}';
                        }

                        text = text.replace(search, my.escape(value));
                    });

                    return text;
                },
                bind: function(event, handler)
                {
                    this.selector.on(event, handler);
                }
            };

            my.init(config);

            return my;
        }
    }
})(kQuery)
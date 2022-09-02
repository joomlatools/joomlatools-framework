/**
 * Joomlatools Framework - https://www.joomlatools.com/developer/framework/
 *
 * @copyright	Copyright (C) 2011 Johan Janssens and Timble CVBA. (http://www.timble.net)
 * @license		GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link		http://github.com/joomlatools/joomlatools-framework-files for the canonical source repository
 */

if (!Files.Attachments) Files.Attachments = {};

Files.Attachments.App = new Class({
    Extends: Files.App,
    Implements: [Events, Options],
    cookie: false,
    attachments: {},
    options: {
        url: null,
        pathway: false,
        persistent: false,
        types: ['file', 'image'],
        preview:  'files-preview',
        state: {
            defaults: {
                limit: 0,
                offset: 0
            }
        },
        grid: {
            cookie: false,
            layout: 'attachments',
            element: 'attachments-container'
        },
        history: {
            enabled: false
        },
        uploader_dialog: false,
        folder_dialog: false,
        copy_dialog: false,
        move_dialog: false,
        onAfterNavigate: function(path) {
            // Do nothing
        }
    },
    initialize: function(options) {
        this.url = options.url;

        this.parent(options);

        this.preview = document.id(this.options.preview);

        var app = this;

        if (callback = options.callback)
        {
            if (typeof window.parent[callback] == 'function') {
                window.parent[callback](app);
            }
        }
    },
    navigate: function(path, type, revalidate_cache, response) {
        this.fireEvent('beforeNavigate', [path, type]);

        var that = this;

        if (this.url)
        {
            var url = this.url;

            url += '&_' + Date.now();

            that.grid.reset(); // Flush current content.

            this.grid.spin();

            new Request.JSON({
                url: url,
                method: 'get',
                onSuccess: function(response)
                {
                    that.grid.insertRows(response.entities);
                    that.grid.unspin();
                }
            }).send();
        }

        this.fireEvent('afterNavigate', [path, type]);
    },
    setPaginator: function() {
    },
    setGrid: function() {
        var opts = this.options.grid;
        var that = this;

        Object.append(opts, {
            'onClickAttachment': function (e) {
                var target = document.id(e.target),
                    node = target.getParent('.files-node-shadow') || target.getParent('.files-node');

                node.getParent().getChildren().removeClass('active');
                node.addClass('active');
                var row = node.retrieve('row');
                var copy = Object.append({}, row);
                copy.template = 'details_attachment';

                that.preview.empty();

                copy.render('attachments').inject(that.preview);

                if (copy.file.type == 'image')
                {
                    if (copy.file.thumbnail) {
                        that.preview.getElement('img').set('src', Files.sitebase + '/' + row.encodePath(copy.file.thumbnail.relative_path, Files.urlEncoder)).setStyle('display', 'block');
                    } else {
                        that.preview.getElement('img').set('src', that.createRoute({view: 'file', format: 'html', name: copy.file.name, routed: 1}));
                    }
                }

                that.grid.selected = row.name;
            }
        });

        this.grid = new Files.Attachments.Grid(this.options.grid.element, opts);

    }
});

Files.Attachments.Grid = new Class({
    Extends: Files.Grid,
    select: function(node) {
        if (typeof node === 'string') {
            node = this.nodes.get(node);
        }

        var handler = 'click' + node.type.capitalize();

        this.fireEvent(handler, {target: node.element.getElement('a.navigate')});
    },
    unselect: function() {
        this.container.getElements('.files-node').removeClass('active');
        this.selected = null;
    },
    attachEvents: function() {
        var that = this;
        this.container.addEvent('click:relay(.files-attachment a.navigate)', function(e) {
            e.stop();
            that.fireEvent('clickAttachment', arguments);
        });
    },
    /**
     * Insert multiple rows, possibly coming from a JSON request
     */
    insertRows: function(rows) {
        this.fireEvent('beforeInsertRows', {rows: rows});

        Object.each(rows, function(row) {
            var item = new Files.Attachment(row);
            this.insert(item, 'last');
        }.bind(this));

        if (this.options.icon_size) {
            this.setIconSize(this.options.icon_size);
        }

        this.fireEvent('afterInsertRows', {rows: rows});
    },
});

Files.Template.Attachments = new Class({
    initialize: function(html)
    {
        var el = new Element('div', {html: html}).getFirst();

        if (el.getElement('.template-item'))  {
            el = el.getElement('.template-item').getFirst();
        }


        return el;
    }
});

Files.Attachment = new Class({
    Extends: Files.Row,

    type: 'attachment',
    template: 'attachment',
    initialize: function(object, options) {
        this.parent(object, options);

        this.size = new Files.Filesize(this.file.metadata.size);
        this.filetype = Files.getFileType(this.file.metadata.extension);
    },
    delete: function(success, failure) {
        // Do nothing, just call the success event handler.
        if (typeof success == 'function') {
            success();
        }
    },
    getAttachedDate: function(formatted) {
        if (this.attached_on_timestamp) {
            var date = new Date();
            date.setTime(this.attached_on_timestamp*1000);
            if (formatted) {
                return date.toLocaleString('default', { year: 'numeric', month: 'short', day: 'numeric' });
            } else {
                return date;
            }
        }

        return null;
    }
});
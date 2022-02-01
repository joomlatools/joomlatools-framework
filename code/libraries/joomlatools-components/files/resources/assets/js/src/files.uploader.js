/**
 * Joomlatools Framework - https://www.joomlatools.com/developer/framework/
 *
 * @copyright    Copyright (C) 2011 Johan Janssens and Timble CVBA. (http://www.timble.net)
 * @license        GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        http://github.com/joomlatools/joomlatools-framework-files for the canonical source repository
 */

if (!Files) var Files = {};

(function($) {

    Files.createUploader = function (options) {
        options = $.extend({}, {
            element: '#files-upload-multi',
            container: Files.app.container,
            multi_selection: true,
            url: Files.app.createRoute({
                view: 'file',
                plupload: 1,
                thumbnails: Files.app.options.thumbnails
            }),
            multipart_params: {
                _action: 'add',
                csrf_token: Files.token,
                folder: Files.app.getPath()
            },
            check_duplicates:true,
            chunking:true,
            autostart:true,
            uploaded: function(event, data) {
                var item, row, path,
                    json = data.result.response;

                if (json.status && typeof Files.app !== 'undefined') {
                    item = json.entities[0];
                    path = (item.folder ? item.folder+'/' : '') + item.name;

                    if (typeof Files.app.grid.nodes[path] === 'undefined') {
                        var cls = Files[item.type.capitalize()];
                        row = new cls(item);
                        Files.app.grid.insert(row);
                    } else {
                        row = Files.app.grid.nodes[path];

                        if (item.metadata) {
                            row.metadata = item.metadata;
                            row.size = new Files.Filesize(row.metadata.size);
                        }
                    }

                    if (row.type == 'image')
                    {
                        var image = row.element.getElement('img');

                        if (image) {

                            var setThumbnail = function(row)
                            {
                                var source = Files.blank_image;

                                if (row.thumbnail) {
                                    source = Files.sitebase + '/' + row.encodePath(row.thumbnail.relative_path, Files.urlEncoder);
                                } else if (row.download_link) {
                                    source = row.download_link;
                                }

                                image.set('src', source).addClass('loaded').removeClass('loading');

                                /* @TODO We probably do not need this anymore? Layouts have changed and these elements/classes no longer exist */
                                var element = row.element.getElement('.files-node');

                                if (element) {
                                    element.addClass('loaded').removeClass('loading');
                                }
                            };

                            setThumbnail(row);

                            /* @TODO Test if this is necessary: This is for the thumb margins to recalculate */
                            window.fireEvent('resize');
                        }
                    }
                    Files.app.fireEvent('uploadFile', [row]);
                }
            }

        }, options);

        var element = kQuery(options.element);

        delete options.element;

        if (element.length === 0) {
            return;
        }

        element.uploader(options);

        Files.app.uploader = element.uploader('instance').getUploader();

        Files.app.addEvent('afterNavigate', function(path, type) {
            if (typeof Files.app.uploader !== 'undefined') {
                Files.app.uploader.settings.multipart_params.folder = Files.app.getPath();
            }
        });
    };

})(kQuery);


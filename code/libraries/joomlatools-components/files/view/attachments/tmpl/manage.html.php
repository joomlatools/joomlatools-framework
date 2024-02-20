<?php
/**
 * Joomlatools Framework - https://www.joomlatools.com/developer/framework/
 *
 * @copyright	Copyright (C) 2011 Johan Janssens and Timble CVBA. (http://www.timble.net)
 * @license		GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link		http://github.com/joomlatools/joomlatools-framework-files for the canonical source repository
 */
defined('KOOWA') or die;

$can_attach       = isset(parameters()->config['can_attach']) ? parameters()->config['can_attach'] : true;
$can_detach       = isset(parameters()->config['can_detach']) ? parameters()->config['can_detach'] : true;
$check_duplicates = $container->getParameters()->check_duplicates ? $container->getParameters()->check_duplicates : 'unique';

$query = url()->getQuery(true);

$table     = $query['table'];
$row       = $query['row'];
$component = isset($component) ? $component : substr($query['option'], 4);
$callback  = isset($query['callback']) ? $query['callback'] : null;
$itemless  = empty($query['itemless']) ? 0 : 1;
?>

<?= helper('ui.load', array('wrapper_class' => array('com_files--attachments'))); ?>

<?= import('com:files.files.scripts.html'); ?>

<ktml:script src="media://koowa/com_files/js/attachments.js"/>
<ktml:style src="media://koowa/com_files/css/files.css"/>


<div class="k-dynamic-content-holder">
    <script>
        Files.sitebase = '<?= $sitebase; ?>';
        Files.token = '<?= $token; ?>';

        kQuery(function($)
        {
            var encodeUrlComponent = function (str)
            {
                return encodeURIComponent(str).replace(/[!'()*]/g, function(c) {
                    return '%' + c.charCodeAt(0).toString(16);
                });
            }

            var config = <?= json_encode(KObjectConfig::unbox(parameters()->config)); ?>,
                options = {
                    cookie: {path: '<?=object('request')->getSiteUrl()?>'},
                    callback: <?= json_encode(isset($callback) ? $callback : '') ?>,
                    url:  "<?= route('component='. urlencode($component) .'&view=attachments&itemless=1&format=json&table=' . $table . '&row=' . $row, true, false) ?>",
                    root_text: <?= json_encode(translate('Root folder')) ?>,
                    editor: <?= json_encode(parameters()->editor); ?>,
                    types: <?= json_encode(KObjectConfig::unbox(parameters()->types)); ?>,
                    container: <?= json_encode($container->toArray()) ?>
                };
            options = Object.append(options, config);

            Files.app = new Files.Attachments.App(options);

            var app = Files.app;

            var updateGridCount = function() {
                $('#document_list .count').html('(' + this.getCount() + ')');
            }.bind(app.grid);

            // Update attachements label count.
            app.grid.addEvent('afterInsertRows', function() {
                updateGridCount();
            });

            // Update attachements label count.
            app.grid.addEvent('afterDeleteNode', function() {
                updateGridCount();
            });

            app.grid.addEvent('afterRenderObject', function(object, position)
            {
                var that = this;

                $(object.object.element).find('span').click(function()
                {
                    var attachment = object.object.name;
                    that.select(attachment);

                    if (confirm(<?= json_encode(translate('You are about to remove this attachment. Would you like to proceed?')) ?>)) {
                        that.detach(attachment);
                    }
                });
            }.bind(app.grid));

            app.grid.addEvent('afterInsertNode', function(data)
            {
                this.select(data.node); // Auto-select attached file after attach.

            }.bind(app.grid));

            Attachments = Attachments.getInstance(
                {
                    url: "<?= route('component=' . urlencode($component) . '&view=attachment&itemless=1&format=json&container=' . urlencode($container->slug), true, false) ?>",
                    selector: '#attachments-container',
                    csrf_token: <?= json_encode(object('user')->getSession()->getToken()) ?>
                }
            );

            $('.attachments-uploader').on('uploader:uploaded', function (event, data)
            {
                var response = data.result.response;

                if (typeof response.entities !== 'undefined') {
                    app.grid.attach(data.file.name);
                }
            }).on('uploader:create', function() {
                $(this).addClass('k-upload--boxed-top');
            });

            // Attach action implementation
            app.grid.attach = function (attachment)
            {
                this.fireEvent('beforeAttachAttachment', {attachment: attachment});
                Attachments.attach(attachment);
            }.bind(app.grid);

            // After attach logic.
            Attachments.bind('after.attach', function (event, context)
            {
                var url = "<?= route('component=' . urlencode($component) . '&view=attachments&itemless=1&container=' . urlencode($container->slug) . '&format=json&name={name}&table={table}&row={row}', true, false) ?>";

                url = Attachments.replace(url, {
                    name: encodeUrlComponent(context.attachment),
                    table: <?= json_encode($table) ?>,
                    row: <?= json_encode($row) ?>
                });

                var that = this;

                $.ajax({
                    url: url,
                    method: 'get',
                    success: function (data) {
                        that.insertRows(data.entities);
                        that.fireEvent('afterAttachAttachment', {attachment: {name: context.attachment, entity: data.entities.pop()}});
                    },
                    error: function() {
                        that.fireEvent('afterAttachAttachment', {attachment: {name: context.attachment}});
                    }
                });
            }.bind(app.grid));

            var setContext = function (context) {
                context.url += (context.url.search(/\?/) ? '&' : '?');
                context.url += 'name=' + encodeUrlComponent(context.attachment);

                context.data.table = <?= json_encode($table) ?>;
                context.data.row = <?= json_encode($row) ?>;
            };

            Attachments.bind('before.attach', function (event, context) {
                setContext(context)
            });

            // Detach grid implementation.
            app.grid.detach = function (attachment)
            {
                node = this.nodes.get(attachment);

                if (node) {
                    this.fireEvent('beforeDetachAttachment', {node: node});

                    Attachments.detach(attachment);
                }
            }.bind(app.grid);

            Attachments.bind('before.detach', function (event, context) {
                setContext(context)
            });

            Attachments.bind('after.detach', function (event, context)
            {
                this.erase(context.attachment);

                $('#files-preview').empty();

                this.fireEvent('afterDetachAttachment', {node: node});
            }.bind(app.grid));

            $('.attachments-uploader').on('uploader:checkduplicates', function(e, config)
            {
                e.preventDefault();

                var response = config.response, uploader = config.uploader, subject = e.subject;

                if (typeof response.entities === 'object' && response.entities.length)
                {
                    uploader.settings.multipart_params.overwrite = 0;

                    var existing = subject.getNamesFromArray(response.entities),
                        that = this,
                        promises = [];

                    function getConfirmationMessage(files) {
                        var message = '';

                        if (files.length === 1) {
                            message = Koowa.translate('An attachment with the same name already exists. Would you like to re-use it?');
                        } else if (files.length > 1) {
                            message = Koowa.translate('The following attachments already exist. Would you like to re-use them? {names}', {
                                names: "\n" + files.join("\n")
                            });
                        }

                        return message;
                    };

                    if (!confirm(getConfirmationMessage(existing)))
                    {
                        // Overwrite

                        $.each(uploader.files, function (i, file) {

                            if ($.inArray(file.name, existing) !== -1) {
                                var url = uploader.settings.url,
                                    promise;

                                url = subject.updateUrlParameter(url, 'view', 'files');
                                url = subject.updateUrlParameter(url, 'format', 'json');
                                url = subject.updateUrlParameter(url, 'limit', '100');
                                url = subject.updateUrlParameter(url, 'folder', uploader.settings.multipart_params.folder);
                                url = subject.updateUrlParameter(url, 'search', file.name.substr(0, file.name.lastIndexOf('.')) + ' (');

                                promise = $.ajax({
                                    type: 'GET',
                                    url: url
                                }).done(function (response) {
                                    return subject.makeUnique(file, response, uploader)
                                });

                                promises.push(promise);
                            }
                        });

                        if (promises) {
                            $.when.apply(kQuery, promises).then(function () {
                                uploader.start();
                            });
                        }
                    }
                    else
                    {
                        // Re-use

                        $.each([...uploader.files], function (i, file) {

                            if ($.inArray(file.name, existing) !== -1)
                            {
                                uploader.removeFile(file);

                                app.grid.attach(file.name);
                            }
                        });

                        uploader.start();
                    }
                }
                else {
                    uploader.start();
                }
            });
        });
    </script>

    <?= import('com:files.files.templates_compact.html');?>
    <?= import('com:files.attachments.templates_manage.html', array('can_detach' => $can_detach));?>
</div>


<!-- Wrapper -->
<div class="k-wrapper k-js-wrapper">

    <!-- Titlebar -->
    <div class="k-title-bar k-title-bar--mobile k-js-title-bar">
        <div class="k-title-bar__heading"><?= translate('Attachments'); ?></div>
    </div><!-- .k-titlebar -->

    <!-- Overview -->
    <div class="k-content-wrapper">

        <!-- Content -->
        <div class="k-content k-js-content">

            <!-- Component wrapper -->
            <div class="k-component-wrapper">

                <!-- Component -->
                <div class="k-component k-js-component">

                    <!-- Uploader -->
                    <? if ($can_attach): ?>
                        <div class="attachments-upload">
                            <?= helper('uploader.container', array(
                                'container' => $container->slug,
                                'element' => '.attachments-uploader',
                                'options'   => array(
                                    'multi_selection' => true,
                                    'duplicate_mode' => $check_duplicates,
                                    'url' => route('component=' . urlencode($component) . '&view=file&plupload=1&routed=1&format=json&container=' .
                                                   (isset($container) ? $container->slug : '') . '&itemless=1', false, false)
                                )
                            )) ?>
                        </div>
                    <? endif ?>

                    <!-- Attachments list -->
                    <div class="k-table-container">
                        <div class="k-table" id="attachments-container"></div><!-- .k-table -->
                        <div class="k-loader-container">
                            <span class="k-loader k-loader--large"><?= translate('Loading') ?></span>
                        </div>
                    </div><!-- .k-table-container -->

                </div><!-- .k-component -->

                <!-- Sidebar -->
                <div class="k-sidebar-right k-js-sidebar-right">

                    <div class="k-sidebar-item">

                        <div class="k-sidebar-item__header">
                            <?= translate('Selected attachment info'); ?>
                        </div>

                        <div class="k-sidebar-item__content" id="properties">

                            <div id="attachments-preview">
                                <div id="files-preview"></div>
                            </div>

                        </div><!-- .k-sidebar__content -->

                    </div><!-- .k-sidebar__item -->

                </div><!-- .k-sidebar-right -->

            </div><!-- .k-component-wrapper -->

        </div><!-- k-content -->

    </div><!-- .k-content-wrapper -->

</div><!-- .k-wrapper -->
<?php
/**
 * Joomlatools Framework - https://www.joomlatools.com/developer/framework/
 *
 * @copyright	Copyright (C) 2011 Johan Janssens and Timble CVBA. (http://www.timble.net)
 * @license		GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link		http://github.com/joomlatools/joomlatools-framework-files for the canonical source repository
 */
defined('KOOWA') or die;

$can_upload = isset(parameters()->config['can_upload']) ? parameters()->config['can_upload'] : true;
?>

<?= import('com:files.files.scripts.html'); ?>
<?= import('templates_compact.html');?>


<script>
Files.sitebase = '<?= $sitebase; ?>';
Files.token = '<?= $token; ?>';

window.addEvent('domready', function() {
    var config = <?= json_encode(KObjectConfig::unbox(parameters()->config)); ?>,
        options = {
            cookie: {
                path: '<?=object('request')->getSiteUrl()?>'
            },
            root_text: <?= json_encode(translate('Root folder')) ?>,
            editor: <?= json_encode(parameters()->editor); ?>,
            types: <?= json_encode(KObjectConfig::unbox(parameters()->types)); ?>,
            container: <?= json_encode($container ? $container->toArray() : null); ?>,
            tree: {
                dataFilter: function(response){

                    kQuery('.koowa_dialog__file_dialog_categories').css('display', 'block');
                    kQuery('.koowa_dialog--file_dialog').removeClass('koowa_dialog--no_categories');

                    return Files.app.tree.filterData(response);
                }
            },
            thumbnails: <?= json_encode($thumbnails ?: ($container->getParameters()->thumbnails ?: true)) ?>
        },
        app = new Class({
            Extends: Files.Compact.App
            /*fetch: function() {
                this.grid.unspin();
                return kQuery.Deferred();
            }*/
        });
    options = Object.append(options, config);

    Files.app = new app(options);

    <? if ($can_upload): ?>
    $('files-new-folder-create').addEvent('click', function(e){
        e.stop();

        var element = $('files-new-folder-input'),
            value = element.get('value');

        if (value.length > 0) {
            var folder = new Files.Folder({name: value, folder: Files.app.getPath()});

            folder.add(function(response, responseText) {
                if (response.status === false) {
                    return alert(response.error);
                }
                var el = response.entities[0];
                var cls = Files[el.type.capitalize()];
                var row = new cls(el);

                element.set('value', '');
                $('files-new-folder-create').removeClass('valid').setProperty('disabled', 'disabled');

                Files.app.tree.appendNode({
                    id: row.path,
                    label: row.name
                });
            });
        }
    });
    var validate = function(){
            if(this.value.trim()) {
                $('files-new-folder-create').addClass('valid').removeProperty('disabled');
            } else {
                $('files-new-folder-create').removeClass('valid').setProperty('disabled', 'disabled');
            }
        },
        input = $('files-new-folder-input');

    input.addEvent('change', validate);

    if (window.addEventListener) {
        input.addEventListener('input', validate);
    } else {
        input.addEvent('keyup', validate);
    }
    <? endif; ?>
});
</script>


<!-- Wrapper -->
<div class="k-wrapper k-js-wrapper">

    <!-- Titlebar -->
    <div class="k-title-bar k-title-bar--mobile k-js-title-bar">
        <div class="k-title-bar__heading"><?= translate('Insert / Upload file'); ?></div>
    </div><!-- .k-titlebar -->

    <!-- Content wrapper -->
    <div class="k-content-wrapper">

        <!-- Sidebar -->
        <?= import('compact_sidebar.html'); ?>

        <!-- Content -->
        <div class="k-content k-js-content">

          <!-- Component wrapper -->
            <div class="k-component-wrapper">

                <!-- Component -->
                <div class="k-component k-js-component">

                    <div class="k-breadcrumb" id="files-pathway"></div>

                    <?= import('compact_upload.html'); ?>

                    <?= import('compact_select.html'); ?>

                </div><!-- .k-component -->

                <!-- Sidebar -->
                <?= import('compact_sidebar_right.html'); ?>

            </div><!-- .k-component-wrapper -->

        </div><!-- k-content -->

    </div><!-- .k-content-wrapper -->

</div><!-- .k-wrapper -->

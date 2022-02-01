<?php
/**
 * @package     Joomlatools Migrator
 * @copyright   Copyright (C) 2016 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        http://www.joomlatools.com
 */
defined('KOOWA') or die; ?>

<?= helper('behavior.bootstrap', array('javascript' => true, 'css' => true)) ?>
<?= helper('ui.load', array('styles' => false)) ?>

<ktml:script src="media://koowa/com_migrator/js/uploader.min.js" />
<ktml:script src="media://koowa/com_migrator/js/migrator.js" />
<ktml:script src="media://koowa/com_migrator/js/import.js" />

<ktml:style src="media://koowa/com_migrator/css/migrator.css" />

<script type="text/javascript">

    if (typeof JoomlatoolsMigrator === 'undefined') {
        JoomlatoolsMigrator = {};
    }

    JoomlatoolsMigrator.token    = '<?= $token; ?>';
    JoomlatoolsMigrator.base_url = '<?= route('view=import&format=json', true, false); ?>';
    JoomlatoolsMigrator.max_file_size = '<?= $server_upload_limit ?>';
</script>

<div class="k-migrator-container">
    <div class="migrator" id="migrator-container">
        <div class="migrator__header">
            <img class="joomlatools_logo" src="media://koowa/com_migrator/img/joomlatools_logo_80px.png" alt="Joomlatools logo" />
            <?= translate('Joomlatools importer') ?>
        </div>
        <div class="migrator__steps">
            <ul class="migrator__steps__list">
                <li class="migrator__steps__list__item item--active"><?= translate('Start') ?></li>
                <li class="migrator__steps__list__item"><?= translate('Import') ?></li>
                <li class="migrator__steps__list__item"><?= translate('Completed') ?></li>
            </ul>
        </div>
        <div class="migrator__wrapper migrator--step1">
            <h1><?= translate('Start import') ?></h1>
            <?
            if (KObjectConfig::unbox($missing_dependencies)): ?>
                <div class="alert alert-error">
                    <h3><?= translate('Missing Requirements') ?></h3>
                    <ul>
                        <? foreach ($missing_dependencies as $key => $error): ?>
                            <li><?= $error; ?></li>
                        <? endforeach; ?>
                    </ul>
                </div>
            <? else: ?>
            <div class="migrator__content">
                <p><?= translate('Select the export file that you have generated using the Joomlatools exporter') ?></p>
                <p>
                    <a id="pickfiles" class="migrator_button" href="#"><?= translate('Select export file') ?></a>
                </p>

            </div>
            <div class="migrator__content">
                <p><a class="migrator_go_to" href="<?= $go_back ?>">
                        <?= translate('Go back') ?></a></p>
            </div>
            <? endif; ?>

        </div>
        <div class="migrator__wrapper migrator--step2" style="display: none">
            <h1><?= translate('Importing') ?></h1>
            <div class="migrator_alert">
                <?= translate('Do not close this page or use the back button!') ?>
            </div>
            <div class="migrator__content">
                <h3><?= translate('Uploading export file') ?></h3>
                <div class="progress progress-striped active">
                    <div class="bar" style="width: 0" id="progress-bar-upload"></div>
                </div>
            </div>
        </div>
        <div class="migrator__wrapper migrator--step3" style="display: none">
            <h1><?= translate('Import completed') ?></h1>
            <div class="migrator_success">
                <?= translate('The import process has successfully completed!') ?>
            </div>
            <div class="migrator__content">
                <p><a class="migrator_button migrator_go_to" href="<?= $go_back ?>">
                    <?= translate('Go back') ?></a></p>
            </div>
        </div>
    </div>
</div>
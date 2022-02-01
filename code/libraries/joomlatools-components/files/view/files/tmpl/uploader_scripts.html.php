<?php
/**
 * Joomlatools Framework - https://www.joomlatools.com/developer/framework/
 *
 * @copyright	Copyright (C) 2011 Johan Janssens and Timble CVBA. (http://www.timble.net)
 * @license		GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link		http://github.com/joomlatools/joomlatools-framework-files for the canonical source repository
 */
defined('KOOWA') or die;

$server_limit = ComFilesModelEntityContainer::getServerUploadLimit();
?>


<?= helper('translator.script', array('strings' => array(
    // file size
    "tb", "mb", "kb", "gb", "b", "N/A",

    // file status
    'done', 'failed', 'delete', 'uploading',

    // koowa.uploader.overwritable.js
    'A file with the same name already exists. Click OK to overwrite and Cancel to create a new version.',
    'Following files already exist. Would you like to overwrite them? {names}',

    // errors
    "Init error.",
    "HTTP Error.",
    "Duplicate file error.",
    "File size error.",
    "File: %s",
    "File: %s, size: %d, max file size: %d",
    "%s already present in the queue.",
    "Upload element accepts only %d file(s) at a time. Extra files were stripped.",
    "Image format either wrong or not supported.",
    "File count error.",
    "Runtime ran out of available memory.",
    "Upload URL might be wrong or doesn't exist.",
    "File extension error."
))); ?>

<?= helper('behavior.bootstrap', array(
    'javascript' => true,
    'css' => false
)); ?>
<?= helper('behavior.koowa'); ?>
<?= helper('behavior.jquery'); ?>

<ktml:style src="assets://files/css/uploader.css" />

<ktml:script src="assets://files/js/uploader<?= !empty($debug) ? '' : '.min' ?>.js" />

<script>
kQuery.koowa.uploader.server_limit = <?= json_encode($server_limit) ?>;
</script>


<div class="k-dynamic-content-holder">

    <!-- Uploader content box -->
    <script data-inline type="text/html" class="js-uploader-template" data-name="content-box">
        <div class="k-upload__body-default">
            <div class="k-upload__content-wrapper">
                <div class="k-upload__buttons">
                    <button type="button" class="k-upload__button js-choose-files"
                            data-caption-update="<?= escape(translate('Update')) ?>">
                        <?= translate('Upload') ?>
                    </button>
                    <button type="button" class="k-upload__button k-upload__button--upload js-start-upload">
                        <?= translate('Start') ?>
                    </button>
                    <button class="k-upload__button js-stop-upload disabled">
                        <?= translate('Stop') ?>
                    </button>
                    <span class="k-upload__divider">/</span>
                </div>
                <div class="k-upload__content">
                    <div class="js-content"></div>
                </div>
            </div>
        </div>
    </script>

    <!-- Error box -->
    <script data-inline type="text/html" class="js-uploader-template" data-name="error-box">
        <div class="k-upload__body-message">
            <div class="k-upload__message k-upload__message--error">
                <div class="k-upload__message__content">
                    <div class="k-upload__message__body js-message-body"></div>
                    <div class="k-upload__message__button"><button type="button" class="k-upload__button js-close-error"><?= translate('OK') ?></button></div>
                </div>
            </div>
        </div>
    </script>

    <!-- Extra info -->
    <script data-inline type="text/html" class="js-uploader-template" data-name="info-box">
        <div class="k-upload__body-info">
            <div class="k-upload__info">
                <div class="k-upload__info__content">
                    <div class="k-upload__info__body">
                        <table>
                            <thead>
                            <tr>
                                <th class="k-upload__clear-queue" width="1%" style="width: 1%;"><a href="#" class="k-uploader__clear-queue js-clear-queue"><?= translate('Clear queue') ?></a></th width="1">
                                <th width="1%" style="width: 1%;"></th>
                                <th width="1%" style="width: 1%;"><?= translate('Size') ?></th>
                                <th width="99%" style="width: 99%;"><?= translate('Title') ?></th>
                            </tr>
                            </thead>
                            <tbody class="js-filelist-multiple"></tbody>
                        </table>
                    </div>
                    <div class="k-upload__info__button">
                    </div>
                </div>
            </div>
        </div>
    </script>

    <!-- Progress bar -->
    <script data-inline type="text/html" class="js-uploader-template" data-name="progress-bar">
        <div class="k-upload__progress progress progress-striped">
            <div class="k-upload__progress__bar bar" style="width: 0"></div>
        </div>
    </script>

    <script data-inline type="text/html" class="js-uploader-template" data-name="single-file">
        <div class="k-upload__files">
            <div class="k-upload__file-list">
                <div id="{{=it.id}}" class="js-uploader-file">
                    <span class="js-file-name-container">{{=it.name}}</span>
                    {{?it.size}}
                    , <span class="js-file-size-container">{{=it.size}}</span>
                    {{?}}
                </div>
            </div>
        </div>
        <div class="k-upload__drop-message">
            <?= translate('Drop another file to update') ?>
        </div>

    </script>

    <script data-inline type="text/html" class="js-uploader-template" data-name="multiple-files">
        <tr id="{{=it.id}}" class="js-uploader-file">
            <td class="k-upload__file-status-wrapper">
                <span class="k-upload__file-status js-file-status is-in-queue"><?= translate('in queue') ?></span>
            </td>
            <td>
                <a class="k-upload__remove-button js-remove-file">x</a>
            </td>
            <td>
                <span class="js-file-size-container">{{=it.size}}</span>
            </td>
            <td class="k-upload__overflow">
                <span class="js-file-name-container">{{=it.name}}</span>
            </td>
        </tr>
    </script>

    <script data-inline type="text/html" class="js-uploader-template" data-name="upload-pending">
        <div>
            <?= translate('{total} files in the queue', array(
                'total' => '{{=it.total}}'
            )) ?>
            <a class="k-upload__details-button js-open-info">
                <span class="k-upload__details-button__view"><?= translate('show queue') ?></span>
                <span class="k-upload__details-button__close"><?= translate('hide queue') ?></span>
            </a>
        </div>
        <div class="k-upload__drop-message">
            <?= translate('Drop files to add to the queue') ?>
        </div>
    </script>

    <script data-inline type="text/html" class="js-uploader-template" data-name="uploading">
        <div>
            <?= translate('Uploading {total} files, {remaining} to go', array(
                'total' => '{{=it.total}}',
                'remaining' => '{{=it.remaining}}'
            ), '</span>') ?>
            {{? it.failed > 0 }}
            -
            <?= translate('{failed} errors', array(
                'failed' => '{{=it.failed}}'
            )) ?>
            {{?}}
            <a class="k-upload__details-button js-open-info">
                <span class="k-upload__details-button__view"><?= translate('show queue') ?></span>
                <span class="k-upload__details-button__close"><?= translate('hide queue') ?></span>
            </a>
        </div>
    </script>

    <script data-inline type="text/html" class="js-uploader-template" data-name="upload-finished">
        <div>
            <?= translate('Uploaded {total} files', array(
                'total' => '{{=it.total}}'
            )) ?>
            {{? it.failed > 0 }}
            -
            <?= translate('{failed} errors', array(
                'failed' => '{{=it.failed}}'
            )) ?>
            {{?}}
            <a class="k-upload__details-button js-open-info">
                <span class="k-upload__details-button__view"><?= translate('show queue') ?></span>
                <span class="k-upload__details-button__close"><?= translate('hide queue') ?></span>
            </a>
        </div>
        <div class="k-upload__drop-message">
            <?= translate('Drop more files here') ?>
        </div>
    </script>

    <script data-inline type="text/html" class="js-uploader-template" data-name="empty-single">
        <div class="k-upload__drop-message">
            <?= translate('Drop a file here') ?>
        </div>
    </script>

    <script data-inline type="text/html" class="js-uploader-template" data-name="empty-multiple">
        <div class="k-upload__drop-message">
            <?= translate('Drop files here') ?>
        </div>
        <div class="k-upload__select-message">
            <?= translate('Select files to upload') ?>
        </div>
    </script>

    <script data-inline type="text/html" class="js-uploader-template" data-name="drop-message-single">
        <div class="k-uploader-drop-visual">
            <span><?= translate('Drop a file here') ?></span>
        </div>
    </script>

    <script data-inline type="text/html" class="js-uploader-template" data-name="drop-message-multiple">
        <div class="k-uploader-drop-visual">
            <span><?= translate('Drop files here') ?></span>
        </div>
    </script>

</div>

<?php
/**
 * Joomlatools Framework - https://www.joomlatools.com/developer/framework/
 *
 * @copyright	Copyright (C) 2011 Johan Janssens and Timble CVBA. (http://www.timble.net)
 * @license		GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link		https://github.com/joomlatools/joomlatools-framework-files for the canonical source repository
 */
defined('KOOWA') or die;

$can_upload = isset(parameters()->config['can_upload']) ? parameters()->config['can_upload'] : true;
?>


<div class="k-sidebar-left k-js-sidebar-left">

    <div class="k-sidebar-item k-sidebar-item--flex">
        <div class="k-sidebar-item__header">
            <?= translate('Select a folder'); ?>
        </div>
        <div class="k-tree" id="files-tree">
            <div class="k-sidebar-item__content k-sidebar-item__content--horizontal">
                <?= translate('Loading') ?>
            </div>
        </div>
    </div>

    <? if ($can_upload): ?>
        <div class="k-sidebar-item">
            <div class="k-sidebar-item__header">
                <?= translate('Create a new folder'); ?>
            </div>
            <div class="k-sidebar-item__content">
                <div class="k-input-group" id="files-new-folder-modal">
                    <input type="text" class="k-form-control" id="files-new-folder-input" />
                    <div class="k-input-group__button">
                        <button id="files-new-folder-create" class="k-button k-button--default" disabled>
                            <span class="k-icon-plus" aria-hidden="true"></span>
                            <span class="k-visually-hidden"><?= translate('Add folder'); ?></span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    <? endif; ?>

</div><!-- .k-sidebar-left -->

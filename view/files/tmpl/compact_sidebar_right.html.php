<?php
/**
 * Nooku Framework - http://nooku.org/framework
 *
 * @copyright	Copyright (C) 2011 - 2014 Johan Janssens and Timble CVBA. (http://www.timble.net)
 * @license		GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link		http://github.com/nooku/nooku-files for the canonical source repository
 */
defined('KOOWA') or die;
?>

<div id="k-sidebar-right" class="k-sidebar-right">

    <div class="k-sidebar__item">
        <div class="k-sidebar__header">
            <?= translate('Selected file info'); ?>
        </div>
        <? // @TODO: @Ercan: add message to select a file via the select tab; ?>
        <? // @TODO: @Robin: Create image placeholder file with a fixed width height so the insert button won't jump on selection; ?>
        <div class="k-sidebar__content ercan-todo">
            <div class="koowa_dialog__wrapper__child koowa_dialog__file_dialog_insert">
                <div class="koowa_dialog__child__content">
                    <div class="koowa_dialog__child__content__box">
                        <div id="files-preview"></div>
                        <div id="insert-button-container"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div><!-- .k-sidebar-right -->
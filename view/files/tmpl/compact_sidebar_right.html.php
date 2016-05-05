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
        <div class="k-sidebar__content">
            <div id="files-preview">
                <?= translate('Select a file from the list'); ?>
            </div>
            <div id="insert-button-container" class="k-button-container"></div>
        </div>
    </div>
</div><!-- .k-sidebar-right -->
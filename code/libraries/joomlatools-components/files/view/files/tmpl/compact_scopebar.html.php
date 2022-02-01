<?php
/**
 * Joomlatools Framework - https://www.joomlatools.com/developer/framework/
 *
 * @copyright	Copyright (C) 2011 Johan Janssens and Timble CVBA. (http://www.timble.net)
 * @license		GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link		https://github.com/joomlatools/joomlatools-framework-files for the canonical source repository
 */
defined('KOOWA') or die;
?>


<!-- Scopebar -->
<div class="k-scopebar k-js-scopebar">

    <!-- Search -->
    <div class="k-scopebar__item k-scopebar__item--search">
        <?= helper('grid.search', array(
            'submit_on_clear' => false,
            'placeholder' => translate('Find by file or folder name&hellip;')
        )) ?>
    </div>

</div><!-- .k-scopebar -->

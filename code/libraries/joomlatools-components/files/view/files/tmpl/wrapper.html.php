<?php
/**
 * Joomlatools Framework - https://www.joomlatools.com/developer/framework/
 *
 * @copyright	Copyright (C) 2011 Johan Janssens and Timble CVBA. (http://www.timble.net)
 * @license		GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link		https://github.com/joomlatools/joomlatools-framework-files for the canonical source repository
 */

/*
 * Wraps around the default views adding toolbars, Bootstrap and CSS files to the page
 */
defined('KOOWA') or die; ?>


<?= helper('ui.load'); ?>

<ktml:module position="submenu">
    <ktml:toolbar type="menubar">
</ktml:module>

<ktml:module position="toolbar">
    <ktml:toolbar type="actionbar" title="Files">
</ktml:module>

<ktml:style src="assets://files/css/files.css" />

<ktml:content>
<?php
/**
 * Joomlatools Framework - https://www.joomlatools.com/developer/framework/
 *
 * @copyright	Copyright (C) 2011 Johan Janssens and Timble CVBA. (http://www.timble.net)
 * @license		GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link		http://github.com/joomlatools/joomlatools-framework-files for the canonical source repository
 */
defined('KOOWA') or die( 'Restricted access' );
?>

<ktml:script src="assets://files/js/mootools<?= !empty($debug) ? '' : '.min' ?>.js" />

<?= helper('behavior.koowa'); ?>
<?= helper('behavior.local_dates'); ?>
<?= helper('behavior.keepalive'); ?>
<?= helper('behavior.tooltip'); ?>
<?= helper('behavior.modal'); ?>
<?= helper('behavior.tree'); ?>

<?= helper('translator.script', array('strings' => array(
    'B', 'KB', 'MB', 'GB', 'TB', 'PB',
    'You are deleting {item}. Are you sure?',
    'You are deleting {items}. Are you sure?',
    '{count} files and folders',
    '{count} folders',
    '{count} files',
    'All Files',
    'An error occurred during request',
    'An error occurred with status code: {code}',
    'An error occurred: {error}',
    'Unknown error',
    'Insert'
))); ?>

<ktml:script src="assets://files/js/files<?= !empty($debug) ? '' : '.min' ?>.js" />

<?= helper('icon.icon_map'); ?>

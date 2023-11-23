<?php
/**
 * Joomlatools Framework - https://www.joomlatools.com/developer/framework/
 *
 * @copyright   Copyright (C) 2007 Johan Janssens and Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://github.com/joomlatools/joomlatools-framework for the canonical source repository
 */

defined('KOOWA') or die;

$app             = JFactory::getApplication();
$doc             = JFactory::getDocument();
$language  = $doc->language;
$direction = $doc->direction;

?>
<!DOCTYPE html>
<html class="k-ui-page" xmlns="http://www.w3.org/1999/xhtml" lang="<?php echo $language; ?>" dir="<?php echo $direction; ?>">
<head>
    <base href="<?= url(); ?>" />
    <title><?= title() ?></title>
    <meta content="text/html; charset=utf-8" http-equiv="content-type"  />
    <meta content="chrome=1" http-equiv="X-UA-Compatible" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <ktml:title>
    <ktml:meta>
    <ktml:link>
    <ktml:style>
    <ktml:importmap>
    <ktml:script>
</head>
<body>
    <div class="k-ui-namespace"><ktml:messages></div>
    <ktml:content>
</body>
</html>
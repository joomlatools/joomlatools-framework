<?php
/**
 * @package     Joomlatools Migrator
 * @copyright   Copyright (C) 2016 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        http://www.joomlatools.com
 */
defined('KOOWA') or die; ?>

<ktml:style src="media://koowa/com_migrator/css/migrator.css" />

<div class="k-ui-namespace">
    <div class="migrator">
        <div class="migrator__header">
            <img class="joomlatools_logo" src="media://koowa/com_migrator/img/joomlatools_logo_80px.png" alt="Joomlatools logo" />
            <?= translate('Joomlatools exporter') ?>
        </div>
        <div class="migrator__wrapper migrator--step1">
            <div class="alert alert-error">
                <h3><?= translate('A supported extension was not found in the system') ?></h3>
            </div>
            <div class="migrator__content">
                <p style="display:block;"><a class="migrator_button"
                     href="<?= $go_back ?>"><?= translate('Go back') ?></a></p>
            </div>
            <div class="migrator__content">
                <p>
                    <?= translate('If you run into any problems please let us know on our <a href="{url}">forums</a>.', array(
                        'url' => 'http://support.joomlatools.com/forums'
                    )) ?>
                </p>
            </div>
        </div>
    </div>
</div>

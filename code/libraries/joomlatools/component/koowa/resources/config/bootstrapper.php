<?php
/**
 * Joomlatools Framework - https://www.joomlatools.com/developer/framework/
 *
 * @copyright   Copyright (C) 2007 Johan Janssens and Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://github.com/joomlatools/joomlatools-framework for the canonical source repository
 */

return array(

    'aliases'  => array(
        'request'           => 'com:koowa.dispatcher.request',
        'response'          => 'com:koowa.dispatcher.response',
        'translator'        => 'com:koowa.translator',
        'user'              => 'com:koowa.user',
        'filter.factory'    => 'com:koowa.filter.factory',
        'exception.handler' => 'com:koowa.exception.handler',
        'date'              => 'com:koowa.date',
        'event.publisher'   => 'com:koowa.event.publisher',
        'user.provider'     => 'com:koowa.user.provider',
        'database'          => 'com:koowa.database',
        'joomla'            => 'com:koowa.joomla',

        'lib:database.adapter.mysqli'      => 'com:koowa.database.adapter.mysqli',
        'lib:dispatcher.router.route'      => 'com:koowa.dispatcher.router.route',
        'lib:filesystem.stream.buffer'     => 'com:koowa.filesystem.stream.buffer',
        'lib:template.locator.component'   => 'com:koowa.template.locator.component',
        'lib:template.locator.file'        => 'com:koowa.template.locator.file',
        'lib:translator.locator.component' => 'com:koowa.translator.locator.component',
        'lib:translator.locator.file'      => 'com:koowa.translator.locator.file',
    ),

    'identifiers' => array(

        'template.engine.factory' => array(
            'cache'        => JDEBUG ? false : true,
            'debug'        => JDEBUG ? true  : false,
            'cache_path'   => JPATH_ADMINISTRATOR.'/cache/koowa.templates'
        ),

        'template.locator.factory' => array(
            'locators' => array('com:koowa.template.locator.module')
        ),

        'translator.locator.factory' => array(
            'locators' => array(
                'com:koowa.translator.locator.plugin',
                'com:koowa.translator.locator.module'
            )
        ),

        'event.subscriber.factory' => array(
            'subscribers' => array(
                'com:koowa.event.subscriber.application',
                'com:koowa.event.subscriber.user',
                'com:koowa.event.subscriber.exception'
            )
        )
    )
);
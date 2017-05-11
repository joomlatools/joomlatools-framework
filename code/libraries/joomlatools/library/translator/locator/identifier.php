<?php
/**
 * Joomlatools Framework - https://www.joomlatools.com/developer/framework/
 *
 * @copyright   Copyright (C) 2007 Johan Janssens and Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://github.com/joomlatools/joomlatools-framework for the canonical source repository
 */

/**
 * Identifier Translator Locator
 *
 * @author  Johan Janssens <http://github.com/johanjanssens>
 * @package Koowa\Library\Translator\Locator
 */
abstract class KTranslatorLocatorIdentifier extends KTranslatorLocatorAbstract
{
    /**
     * Locate the translation based on a physical path
     *
     * @param  string $url       The translation url
     * @return string  The real file path for the translation
     */
    public function locate($url)
    {
        $result     = array();
        $identifier = $this->getIdentifier($url);

        $info   = array(
            'url'     => $identifier,
            'locale'  => $this->getLocale(),
            'path'    => '',
            'domain'  => $identifier->getDomain(),
            'package' => $identifier->getPackage(),
        );

        return $this->find($info);
    }
}

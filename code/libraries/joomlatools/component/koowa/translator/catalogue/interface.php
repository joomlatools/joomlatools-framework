<?php
/**
 * Joomlatools Framework - https://www.joomlatools.com/developer/framework/
 *
 * @copyright   Copyright (C) 2007 Johan Janssens and Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://github.com/joomlatools/joomlatools-framework for the canonical source repository
 */

/**
 * Translator Catalogue
 *
 * @author  Ercan Ozkaya <https://github.com/ercanozkaya>
 * @package Koowa\Component\Koowa\Translator\Catalogue
 */
interface ComKoowaTranslatorCatalogueInterface extends KTranslatorCatalogueInterface
{
    /**
     * Generates a translation key that is safe for INI format
     *
     * @param  string $string
     * @return string
     */
    public function generateKey($string);

    /**
     * Return the language key prefix
     *
     * @return string
     */
    public function getPrefix();

    /**
     * Set the language key prefix
     *
     * @param string $prefix
     * @return ComKoowaTranslatorCatalogueInterface
     */
    public function setPrefix($prefix);
}

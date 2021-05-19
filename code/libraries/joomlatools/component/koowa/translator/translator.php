<?php
/**
 * Joomlatools Framework - https://www.joomlatools.com/developer/framework/
 *
 * @copyright   Copyright (C) 2007 Johan Janssens and Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://github.com/joomlatools/joomlatools-framework for the canonical source repository
 */

/**
 * Translator
 *
 * @author  Ercan Ozkaya <https://github.com/ercanozkaya>
 * @package Koowa\Component\Koowa\Translator
 */
class ComKoowaTranslator extends KTranslator
{
    /**
     * Associative array containing the list of loaded translations.
     *
     * @var array
     */
    static protected $_paths;

    /**
     * Initializes the options for the object
     *
     * Called from {@link __construct()} as a first step of object instantiation.
     *
     * @param   KObjectConfig $config Configuration options.
     * @return  void
     */
    protected function _initialize(KObjectConfig $config)
    {
        $config->append(array(
            'locale'  => JFactory::getConfig()->get('language'),
        ));

        parent::_initialize($config);
    }

    /**
     * Prevent caching
     *
     * Do not decorate the translator with the cache.
     *
     * @param   KObjectConfigInterface  $config   A ObjectConfig object with configuration options
     * @param   KObjectManagerInterface	$manager  A ObjectInterface object
     * @return  $this
     * @see KFilterTraversable
     */
    public static function getInstance(KObjectConfigInterface $config, KObjectManagerInterface $manager)
    {
        $class = $manager->getClass($config->object_identifier);
        return new $class($config);
    }

    /**
     * Loads translations from a url
     *
     * @param string $url      The translation url
     * @param bool   $override If TRUE override previously loaded translations. Default FALSE.
     * @return bool TRUE if translations are loaded, FALSE otherwise
     */
    public function load($url, $override = false)
    {
        $loaded = array();

        if (!$this->isLoaded($url))
        {
            $current  = $this->getLocale();
            $fallback = $this->getLocaleFallback();

            $locales   = array($current);

            if ($parts = explode('-', $current, 2))
            {
                if (count($parts) === 2 && $parts[0] !== $parts[1]) {
                    array_unshift($locales, $parts[0].'-'.$parts[0]);
                }
            }

            if ($current !== $fallback) {
                array_unshift($locales, $fallback);
            }

            foreach($this->find($url) as $extension => $base)
            {
                foreach ($locales as $locale)
                {
                    if (!JFactory::getLanguage()->load($extension, $base, $locale, true, false))
                    {
                        $file = glob(sprintf('%1$s/language/%2$s/%2$s.*', $base, $locale));

                        if ($file) {
                            $loaded[] = $this->_loadFile(current($file), $extension, $this);
                        }
                    }
                    else $loaded[] = true;
                }
            }

            $this->setLoaded($url);
        }

        return in_array(true, $loaded);
    }

    /**
     * Adds file translations to the JLanguage catalogue.
     *
     * @param string               $file       The file containing translations.
     * @param string               $extension  The name of the extension containing the file.
     * @param KTranslatorInterface $translator The Translator object.
     *
     * @return bool True if translations where loaded, false otherwise.
     */
    protected function _loadFile($file, $extension, KTranslatorInterface $translator)
    {
        $lang     = JFactory::getLanguage();
        $result   = false;

        if (!isset(static::$_paths[$extension][$file]))
        {
            $strings = $this->_parseFile($file, $translator);

            $closure = Closure::bind(function() use ($strings, $extension, &$result)
            {
                if (count($strings))
                {
                    ksort($strings, SORT_STRING);

                    $this->strings = array_merge($this->strings, $strings);

                    if (!empty($this->override)) {
                        $this->strings = array_merge($this->strings, $this->override);
                    }

                    $result = true;
                }

                // Record the result of loading the extension's file.
                if (!isset($this->paths[$extension])) {
                    $this->paths[$extension] = array();
                }

            }, $lang, $lang);

            $closure();

            self::$_paths[$extension][$file] = $result;
        }

        return $result;
    }

    /**
     * Parses a translations file and returns an array of key/values entries.
     *
     * @param string               $file       The file to parse.
     * @param KTranslatorInterface $translator The translator object.
     * @return array The parse result.
     */
    protected function _parseFile($file, KTranslatorInterface $translator)
    {
        $strings   = array();
        $catalogue = $translator->getCatalogue();

        // Catch exceptions if any.
        try {
            $translations = $translator->getObject('object.config.factory')->fromFile($file);
        }  catch (Exception $e) {
            $translations = array();
        }

        foreach ($translations as $key => $value) {
            $strings[$catalogue->getPrefix() . $catalogue->generateKey($key)] = $value;
        }

        return $strings;
    }

    /**
     * Sets the locale
     *
     * @param string $locale
     * @return KTranslatorAbstract
     */
    public function setLocale($locale)
    {
        if($this->_locale != $locale)
        {
            parent::setLocale($locale);

            //Load the koowa translations
            $this->load('com:koowa');
        }

        return $this;
    }
}
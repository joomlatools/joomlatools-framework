<?php
/**
 * Joomlatools Framework - https://www.joomlatools.com/developer/framework/
 *
 * @copyright   Copyright (C) 2011 Johan Janssens and Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        http://github.com/joomlatools/joomlatools-framework-activities for the canonical source repository
 */

/**
 * Activity Translator.
 *
 * @author  Arunas Mazeika <https://github.com/amazeika>
 * @package Koowa\Component\Activities
 */
class ComActivitiesActivityTranslator extends KObjectDecorator implements ComActivitiesActivityTranslatorInterface, KTranslatorInterface
{
    /**
     * Associative array containing previously calculated overrides.
     *
     * @var array
     */
    protected $_overrides = array();

    /**
     * Fallback catalogue.
     *
     * @var mixed
     */
    protected $_fallback_catalogue;

    /**
     * Holds de locale of each activity format that has been translated.
     *
     * @var array
     */
    protected $_locales = array();

    public function __construct(KObjectConfig $config)
    {
        parent::__construct($config);

        $this->_fallback_catalogue = $config->fallback_catalogue;
    }

    protected function _initialize(KObjectConfig $config)
    {
        $config->append(array('fallback_catalogue' => 'com:activities.activity.translator.catalogue'));
        parent::_initialize($config);
    }

    public function onDecorate($delegate)
    {
        parent::onDecorate($delegate);

        $urls = $delegate->getLoaded();

        // Load previosly loaded files on decorator catalogue.
        if ($urls)
        {
            $this->_switchCatalogues();

            foreach ($urls as $url)
            {
                $translations = array();

                foreach($this->find($url) as $file)
                {
                    try {
                        $loaded = $this->getObject('object.config.factory')->fromFile($file)->toArray();
                    } catch (Exception $e) {
                        return false;
                        break;
                    }

                    $translations = array_merge($translations, $loaded);
                }

                $this->getCatalogue()->add($translations);

                $this->setLoaded($url);
            }

            $this->_switchCatalogues();
        }
    }

    /**
     * Translates a string and handles parameter replacements
     *
     * Parameters are wrapped in curly braces. So {foo} would be replaced with bar given that $parameters['foo'] = 'bar'
     *
     * @param string $string String to translate
     * @param array  $parameters An array of parameters
     * @return string Translated string
     */
    public function translate($string, array $parameters = array())
    {
        return $this->getDelegate()->translate($string, $parameters);
    }

    /**
     * Translates a string based on the number parameter passed
     *
     * @param array   $strings Strings to choose from
     * @param integer $number The umber of items
     * @param array   $parameters An array of parameters
     * @throws InvalidArgumentException
     * @return string Translated string
     */
    public function choose(array $strings, $number, array $parameters = array())
    {
        return $this->getDelegate()->choose($strings, $number, $parameters);
    }

    /**
     * Find translations from a url
     *
     * @param string $url      The translation url
     * @return array An array with physical file paths
     */
    public function find($url)
    {
        $catalogue = $this->getCatalogue();

        if ($catalogue instanceof ComActivitiesActivityTranslatorCatalogueInterface)
        {
            // Locate files using fallback locale
            $locale  = $this->getLocaleFallback();
            $locator = $this->getObject('translator.locator.factory')->createLocator($url);
            $result  = $locator->setLocale($locale)->locate($url, $locale);
        }
        else $result = $this->getDelegate()->find($url);

        return $result;
    }

    /**
     * Sets the locale
     *
     * @param string $locale
     * @return KTranslatorInterface
     */
    public function setLocale($locale)
    {
        return $this->getDelegate()->setLocale($locale);
    }

    /**
     * Gets the locale
     *
     * @return string|null
     */
    public function getLocale()
    {
        return $this->getDelegate()->getLocale();
    }

    /**
     * Set the fallback locale
     *
     * @param string $locale The fallback locale.
     * @return KTranslatorInterface
     */
    public function setLocaleFallback($locale)
    {
        return $this->getDelegate()->setLocaleFallback($locale);
    }

    /**
     * Get the fallback locale
     *
     * @return string The fallback locale.
     */
    public function getLocaleFallback()
    {
        return $this->getDelegate()->getLocaleFallback();
    }

    /**
     * Get a catalogue
     *
     * @throws  UnexpectedValueException    If the catalogue doesn't implement the TranslatorCatalogueInterface
     * @return KTranslatorCatalogueInterface The translator catalogue.
     */
    public function getCatalogue()
    {
        return $this->getDelegate()->getCatalogue();
    }

    /**
     * Set a catalogue
     *
     * @param   mixed   $catalogue An object that implements KObjectInterface, KObjectIdentifier object
     *                             or valid identifier string
     * @return KTranslatorInterface
     */
    public function setCatalogue($catalogue)
    {
        return $this->getDelegate()->setCatalogue($catalogue);
    }

    /**
     * Checks if the translator can translate a string
     *
     * @param $string String to check
     * @return bool
     */
    public function isTranslatable($string)
    {
        return $this->getDelegate()->isTranslatable($string);
    }

    /**
     * Checks if translations from a given url are already loaded.
     *
     * @param mixed $url The url to check
     * @return bool TRUE if loaded, FALSE otherwise.
     */
    public function isLoaded($url)
    {
        return $this->getDelegate()->isLoaded($url);
    }

    /**
     * Sets a url as loaded.
     *
     * @param mixed $url The url.
     * @return KTranslatorInterface
     */
    public function setLoaded($url)
    {
        return $this->getDelegate()->setLoaded($url);
    }

    /**
     * Returns a list of loaded urls.
     *
     * @return array The loaded urls.
     */
    public function getLoaded()
    {
        return $this->getDelegate()->getLoaded();
    }

    /**
     * Translates an activity format.
     *
     * @param string $string The activity format to translate.
     * @return string The translated activity format.
     */
    public function translateActivityFormat(ComActivitiesActivityInterface $activity)
    {
        $tokens = $this->getActivityTokens($activity);
        $format = $activity->getActivityFormat();

        $parameters = array();

        foreach ($tokens as $key => $value)
        {
            if ($value instanceof ComActivitiesActivityObjectInterface && $value->getObjectName()) {
                $value = $value->getObjectName();
            }

            if (is_scalar($value)) {
                $parameters[$key] = $value;
            }
        }

        $formats = array();

        for ($i = 0; $i < 2; $i++)
        {
            $catalogue = $this->getCatalogue();

            if ($length = $catalogue->getConfig()->key_length) {
                $catalogue->getConfig()->key_length = false;
            }

            $formats[] = $this->translate($this->_getOverride($format, $parameters), array());

            if ($length) {
                $catalogue->getConfig()->key_length = $length;
            }

            if ($i == 0 && isset($this->_locales[$formats[0]])) {
                break; // Format already localized, no need to compare
            } else {
                $this->_switchCatalogues();
            }
        }

        if (count($formats) > 1)
        {
            list($format, $decorator_format) = $formats;
            $this->_locales[$format] = ($format == $decorator_format) ? $this->getLocaleFallback() : $this->getLocale();
        }
        else $format = $formats[0];

        // Set the activity locale.
        $activity->setLocale($this->_locales[$format]);

        return $format;
    }

    /**
     * Translates an activity token.
     *
     * @param string|ComActivitiesActivityObjectInterface $token    The activity token.
     * @param ComActivitiesActivityInterface              $activity The activity object.
     * @return string The translated token.
     */
    public function translateActivityToken($token, ComActivitiesActivityInterface $activity)
    {
        if (is_string($token))
        {
            $tokens = $this->getActivityTokens($activity);

            if (isset($tokens[$token])) {
                $token = $tokens[$token];
            }
        }

        if (!$token instanceof ComActivitiesActivityObjectInterface) {
            throw new RuntimeException('Invalid token');
        }

        $result = $token->getDisplayName();

        if ($token->isTranslatable())
        {
            if ($activity->getLocale() == $this->getLocaleFallback())
            {
                // Use decorator catalogue instead
                $this->_switchCatalogues();
                $result = $this->translate($result);
                $this->_switchCatalogues();
            } else $result = $this->translate($result);
        }

        return $result;
    }

    /**
     * Fallback catalogue setter.
     *
     * @param KTranslatorCatalogueInterface $catalogue The decorator catalogue.
     * @return ComActivitiesActivityTranslatorInterface
     */
    protected function _setFallbackCatalogue(KTranslatorCatalogueInterface $catalogue)
    {
        $this->_fallback_catalogue = $catalogue;
        return $this;
    }

    /**
     * Decorator catalogue getter.
     *
     * @return KTranslatorCatalogueInterface The decorator catalogue.
     */
    protected function _getFallbackCatalogue()
    {
        if (!$this->_fallback_catalogue instanceof KTranslatorCatalogueInterface) {
            $this->_setFallbackCatalogue($this->getObject($this->_fallback_catalogue));
        }

        return $this->_fallback_catalogue;
    }

    /**
     * Get an activity format override.
     *
     * @param  string $format     The activity format.
     * @param  array  $parameters Associative array containing parameters.
     * @return string The activity format override. If an override was not found, the original activity format is
     *                returned instead.
     */
    protected function _getOverride($format, $parameters = array())
    {
        $override = $format;

        $locale = $this->getLocale();

        if (!isset($this->_overrides[$locale])) {
            $this->_overrides[$locale] = array();
        }

        if ($parameters)
        {
            $key = $this->_getOverrideKey($format, $parameters);

            if (!isset($this->_overrides[$locale][$key]))
            {
                foreach ($this->_getOverrides($format, $parameters) as $candidate)
                {
                    // Check if the override is translatable.
                    if ($this->isTranslatable($candidate))
                    {
                        $override = $candidate;
                        break;
                    }
                }

                $this->_overrides[$locale][$key] = $override;
            }
            else $override = $this->_overrides[$locale][$key];
        }

        return $override;
    }

    /**
     * Get an activity format override key.
     *
     * @param  string $format     The activity format.
     * @param  array  $parameters Associative array containing parameters.
     * @return string The activity format override key.
     */
    protected function _getOverrideKey($format, $parameters = array())
    {
        $result = $format;

        foreach ($parameters as $key => $value) {
            $result = str_replace(sprintf('{%s}', $key), $value, $result);
        }

        return $result;
    }

    /**
     * Returns a list of activity format overrides.
     *
     * @param  string $format     The activity format.
     * @param  array  $parameters Associative array containing parameters.
     * @return array A list of activity format overrides.
     */
    protected function _getOverrides($format, $parameters = array())
    {
        $overrides = array();

        if (!empty($parameters))
        {
            // Get the power set of the set of parameters and construct a list of string overrides from it.
            foreach ($this->_getPowerSet(array_keys($parameters)) as $subset)
            {
                $override = $format;

                foreach ($subset as $key) {
                    $override = str_replace(sprintf('{%s}', $key), $parameters[$key], $override);
                }

                $overrides[] = $override;
            }
        }

        return $overrides;
    }

    /**
     * Returns the power set of a set represented by the elements contained in an array.
     *
     * For convenience, the elements are ordered from size (subsets with more elements first).
     *
     * @param     array $set        The set to get the power set from.
     * @param     int   $min_length The minimum amount of elements that a subset from the power set may contain.
     * @return array The power set represented by an array of arrays containing elements from the provided set.
     */
    protected function _getPowerSet(array $set = array(), $min_length = 1)
    {
        $elements = count($set);
        $size     = pow(2, $elements);
        $members  = array();

        for ($i = 0; $i < $size; $i++)
        {
            $b      = sprintf("%0" . $elements . "b", $i);
            $member = array();
            for ($j = 0; $j < $elements; $j++) {
                if ($b[$j] == '1') $member[] = $set[$j];
            }

            if (count($member) >= $min_length)
            {
                if (!isset($members[count($member)])) {
                    $members[count($member)] = array();
                }

                // Group members by number of elements they contain.
                $members[count($member)][] = $member;
            }
        }

        // Sort members by number of elements (key value).
        ksort($members, SORT_NUMERIC);

        $power = array();

        // We want members with greater amount of elements first.
        foreach (array_reverse($members) as $subsets) {
            $power = array_merge($power, $subsets);
        }

        return $power;
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
        if (!$this->isLoaded($url))
        {
            for ($i = 0; $i < 2; $i++)
            {
                $translations = array();

                foreach($this->find($url) as $file)
                {
                    try {
                        $loaded = $this->getObject('object.config.factory')->fromFile($file)->toArray();
                    } catch (Exception $e) {
                        return false;
                        break;
                    }

                    $translations = array_merge($translations, $loaded);
                }

                $this->getCatalogue()->add($translations, $override);

                // Switch catalogue for loading translations on fallback locale.
                $this->_switchCatalogues();
            }

            $this->setLoaded($url);
        }

        return true;
    }

    /**
     * Switch translator catalogues.
     *
     * The translator and fallback catalogues are switched.
     *
     * @return ComActivitiesActivityTranslatorInterface
     */
    protected function _switchCatalogues()
    {
        // Switch Catalogues
        $catalogue = $this->_getFallbackCatalogue();
        $this->_setFallbackCatalogue($this->getCatalogue());
        $this->setCatalogue($catalogue);

        return $this;
    }

    /**
     * Activities token
     *
     * Tokens are activity objects being referenced in the activity format. They represent variables contained
     * in an activity message. A token is represented in an activity format with a label.
     *
     * @param ComActivitiesActivityInterface $activity
     * @return array A list containing ComActivitiesActivityObjectInterface objects.
     */
    public function getActivityTokens(ComActivitiesActivityInterface $activity)
    {
        $format = $activity->getActivityFormat();

        $tokens = array();

        if (preg_match_all('/\{(.+?)\}/',$format, $labels))
        {
            $objects = $activity->getActivityObjects();

            foreach ($labels[1] as $label)
            {
                $parts = explode(':', $label);

                if (count($parts) > 1) {
                    $label = $parts[0];
                }

                $object = null;
                $parts  = explode('.', $label);

                if (count($parts) > 1)
                {
                    $name = array_shift($parts);

                    if (isset($objects[$name]))
                    {
                        $object = $objects[$name];

                        foreach ($parts as $property)
                        {
                            $object = $object->{$property};
                            if (is_null($object)) break;
                        }
                    }
                }
                else
                {
                    if (isset($objects[$label])) {
                        $object = $objects[$label];
                    }
                }

                if ($object instanceof ComActivitiesActivityObjectInterface) {
                    $tokens[$label] = $object;
                }
            }
        }

        return $tokens;
    }
}
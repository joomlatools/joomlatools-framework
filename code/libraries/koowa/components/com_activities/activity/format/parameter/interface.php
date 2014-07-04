<?php
/**
 * Koowa Framework - http://developer.joomlatools.com/koowa
 *
 * @copyright      Copyright (C) 2011 - 2014 Johan Janssens and Timble CVBA. (http://www.timble.net)
 * @license        GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link           http://github.com/joomlatools/koowa-activities for the canonical source repository
 */

/**
 * Activity Format Parameter Interface
 *
 * @author  Arunas Mazeika <https://github.com/amazeika>
 * @package Koowa\Component\Activities
 */
interface ComActivitiesActivityFormatParameterInterface
{
    /**
     * Constructor.
     *
     * @param    string             $name   The parameter name
     * @param   array|KObjectConfig $config An associative array of configuration settings or a KObjectConfig instance.
     */
    public function __construct($name, $config = array());

    /**
     * Get the parameter name
     *
     * A name uniquely identifies a parameter.
     *
     * @return string The parameter name
     */
    public function getName();

    /**
     * Get the parameter value
     *
     * @param mixed $value The parameter value.
     * @return ComActivitiesActivityFormatParameterInterface
     */
    public function setValue($value);

    /**
     * Set the parameter value
     *
     * @return string The parameter value.
     */
    public function getValue();

    /**
     * Set the parameter attributes
     *
     * @param array $attributes The parameter attributes.
     * @return ComActivitiesActivityFormatParameterInterface
     */
    public function setAttributes($attributes);

    /**
     * Get the parameter attributes
     *
     * @return array The parameter attributes.
     */
    public function getAttributes();

    /**
     * Set the link attributes
     *
     * @param array $attributes The link attributes.
     * @return ComActivitiesActivityFormatParameterInterface
     */
    public function setLink($attributes);

    /**
     * Get the link attributes
     *
     * @return array The link attributes
     */
    public function getLink();

    /**
     * Tells if the parameter is linkable or not.
     *
     * @return bool
     */
    public function isLinkable();

    /**
     * Tells if the parameter is translatable or not.
     *
     * @return bool
     */
    public function isTranslatable();

    /**
     * Sets the translatable status of the parameter.
     *
     * @param bool $status True for setting it as translatable, false otherwise.
     *
     * @return ComActivitiesActivityFormatParameterInterface
     */
    public function translate($status = true);
}
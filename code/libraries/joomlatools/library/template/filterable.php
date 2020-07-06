<?php
/**
 * Joomlatools Framework - https://www.joomlatools.com/developer/framework/
 *
 * @copyright   Copyright (C) 2007 Johan Janssens and Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://github.com/joomlatools/joomlatools-framework for the canonical source repository
 */

/**
 * Template Filterable Interface
 *
 * @author  Johan Janssens <http://github.com/johanjanssens>
 * @package Koowa\Library\Template
 */
interface KTemplateFilterable
{
    /**
     * Filter template content
     *
     * @return string The filtered template source
     */
    public function filter();

    /**
     * Add template filters
     *
     * @param  array $filters A mixed array of template filters
     * @return KTemplateInterface
     */
    public function addFilters($filters);

    /**
     * Add a filter for template transformation
     *
     * @param   mixed  $filter An object that implements KObjectInterface, KObjectIdentifier object
     *                         or valid identifier string
     * @param   array $config  An optional associative array of configuration settings
     * @return KTemplateInterface
     */
    public function addFilter($filter, $config = array());

    /**
     * Check if a filter exists
     *
     * @param   mixed $filter An object that implements ObjectInterface, ObjectIdentifier object
     *                         or valid identifier string
     * @return  boolean	TRUE if the filter exists, FALSE otherwise
     */
    public function hasFilter($filter);

    /**
     * Create a filter by identifier
     *
     * @param   mixed $filter An object that implements KObjectInterface, KObjectIdentifier object
     *                        or valid identifier string
     * @return KTemplateFilterInterface|null
     */
    public function getFilter($filter);
}
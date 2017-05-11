<?php
/**
 * Joomlatools Framework - https://www.joomlatools.com/developer/framework/
 *
 * @copyright   Copyright (C) 2007 Johan Janssens and Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://github.com/joomlatools/joomlatools-framework for the canonical source repository
 */

/**
 * Toolbar Template Helper
 *
 * Extended by each specific toolbar renderer
 *
 * @author  Johan Janssens <https://github.com/johanjanssens>
 * @package Koowa\Library\Template\Helper
 */
abstract class KTemplateHelperToolbar extends KTemplateHelperAbstract
{
    /**
     * Returns the type of toolbar this helper can render
     *
     * @return string
     */
    public function getToolbarType()
    {
        return $this->getIdentifier()->getName();
    }
}
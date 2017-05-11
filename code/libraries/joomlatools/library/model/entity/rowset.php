<?php
/**
 * Joomlatools Framework - https://www.joomlatools.com/developer/framework/
 *
 * @copyright   Copyright (C) 2007 Johan Janssens and Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://github.com/joomlatools/joomlatools-framework for the canonical source repository
 */

/**
 * Model Rowset Entity
 *
 * @author  Johan Janssens <https://github.com/johanjanssens>
 * @package Koowa\Library\Model\Entity
 */
class KModelEntityRowset extends KDatabaseRowsetAbstract implements KModelEntityInterface
{
    /**
     * Get the entity key
     *
     * @return string
     */
    public function getIdentityKey()
    {
        return parent::getIdentityColumn();
    }
}
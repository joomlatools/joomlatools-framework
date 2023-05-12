<?php
/**
 * Joomlatools Framework - https://www.joomlatools.com/developer/framework/
 *
 * @copyright   Copyright (C) 2007 Johan Janssens and Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://github.com/joomlatools/joomlatools-framework for the canonical source repository
 */

/**
 * Abstract Controller Permission
 *
 * @author  Johan Janssens <https://github.com/johanjanssens>
 * @package Koowa\Component\Koowa\Controller\Permission
 */
abstract class ComKoowaControllerPermissionAbstract extends KControllerPermissionAbstract
{
    public function getMixableMethods($exclude = array())
    {
        if(!$this->_mixable_methods)
        {
            $methods = parent::getMixableMethods($exclude);

            $mixer = $this->getMixer();

            $overridden = [];

            foreach ($methods as $name => $method)
            {
                $overridden[$name] = function(...$arguments) use ($mixer, $name, $method)
                {
                    $is_restricted = $mixer->isRestrictable() && $mixer->isRestricted() && $mixer->isRestrictedAction($name);

                    return $is_restricted ? false : $method->{$name}(...$arguments);
                };
            }

            $this->_mixable_methods = array_merge($this->_mixable_methods, $overridden);
        }

        return $this->_mixable_methods;
    }

    /**
     * {@inheritdoc}
     */
    public function canAdd()
    {
        $component = $this->getIdentifier()->package;

        return (parent::canAdd() && $this->getObject('user')->authorise('core.create', 'com_'.$component) === true);
    }

    /**
     * {@inheritdoc}
     */
    public function canEdit()
    {
        $component = $this->getIdentifier()->package;

        return (parent::canEdit() && $this->getObject('user')->authorise('core.edit', 'com_'.$component) === true);
    }

    /**
     * {@inheritdoc}
     */
    public function canDelete()
    {
        $component = $this->getIdentifier()->package;

        return (parent::canDelete() && $this->getObject('user')->authorise('core.delete', 'com_'.$component) === true);
    }

    /**
     * Check if user can perform administrative tasks such as changing configuration options
     *
     * @return  boolean  Can return both true or false.
     */
    public function canAdmin()
    {
        $component = $this->getIdentifier()->package;

        return $this->getObject('user')->authorise('core.admin', 'com_'.$component) === true;
    }

    /**
     * Check if user can can access a component in the administrator backend
     *
     * @return  boolean  Can return both true or false.
     */
    public function canManage()
    {
        $component = $this->getIdentifier()->package;

        return $this->getObject('user')->authorise('core.manage', 'com_'.$component) === true;
    }
}
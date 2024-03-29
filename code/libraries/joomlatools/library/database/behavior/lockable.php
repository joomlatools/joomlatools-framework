<?php
/**
 * Joomlatools Framework - https://www.joomlatools.com/developer/framework/
 *
 * @copyright   Copyright (C) 2007 Johan Janssens and Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://github.com/joomlatools/joomlatools-framework for the canonical source repository
 */

/**
 * Lockable Database Behavior
 *
 * @author  Johan Janssens <https://github.com/johanjanssens>
 * @package Koowa\Library\Database\Behavior
 */
class KDatabaseBehaviorLockable extends KDatabaseBehaviorAbstract
{
    /**
     * The lock lifetime
     *
     * @var integer
     */
    protected $_lifetime;

    /**
     * Initializes the options for the object
     *
     * Called from {@link __construct()} as a first step of object instantiation.
     *
     * @param   KObjectConfig $config Configuration options
     * @return void
     */
    protected function _initialize(KObjectConfig $config)
    {
        $config->append(array(
            'priority'   => self::PRIORITY_HIGH,
            'lifetime'   =>  $this->getObject('user')->getSession()->getLifetime(),
        ));

        $this->_lifetime = $config->lifetime;

        parent::_initialize($config);
    }

    /**
     * Get the user that owns the lock on the resource
     *
     * @return KUserInterface|null Returns a User object or NULL if no user could be found
     */
    public function getLocker()
    {
        $user = null;

        if($this->hasProperty('locked_by') && !empty($this->locked_by)) {
            $user = $this->getObject('user.provider')->load($this->locked_by);
        }

        return $user;
    }

    /**
     * Check if the behavior is supported
     *
     * Behavior requires a 'locked_by' or 'locked_on' row property
     *
     * @return  boolean  True on success, false otherwise
     */
    public function isSupported()
    {
        $table = $this->getMixer();

        //Only check if we are connected with a table object, otherwise just return true.
        if($table instanceof KDatabaseTableInterface)
        {
            if(!$table->hasColumn('locked_by') && !$table->hasColumn('locked_on')) {
                return false;
            }
        }

        return true;
    }

    /**
     * Lock a row
     *
     * Requires an 'locked_on' and 'locked_by' column
     *
     * @return boolean	If successful return TRUE, otherwise FALSE
     */
    public function lock()
    {
        //Prevent lock take over, only an saved and unlocked row and be locked
        if(!$this->isNew() && !$this->isLocked())
        {
            $this->locked_by = (int) $this->getObject('user')->getId();
            $this->locked_on = gmdate('Y-m-d H:i:s');
            $this->save();
        }

        return true;
    }

    /**
     * Unlock a row
     *
     * Requires an locked_on and locked_by column to be present in the table
     *
     * @return boolean	If successful return TRUE, otherwise FALSE
     */
    public function unlock()
    {
        $userid = $this->getObject('user')->getId();

        //Only an saved row can be unlocked by the user who locked it
        if(!$this->isNew() && $this->locked_by != 0 && $this->locked_by == $userid)
        {
            $this->locked_by = 0;
            $this->locked_on = 0;

            $this->save();
        }

        return true;
    }

    /**
     * Checks if a row is locked
     *
     * @return boolean	If the row is locked TRUE, otherwise FALSE
     */
    public function isLocked()
    {
        $result = false;
        if(!$this->isNew())
        {
            if(isset($this->locked_on) && isset($this->locked_by))
            {
                $locked  = strtotime($this->locked_on ?: '');
                $current = strtotime(gmdate('Y-m-d H:i:s'));

                //Check if the lock has gone stale
                if($current - $locked < $this->_lifetime)
                {
                    $userid = $this->getObject('user')->getId();
                    if($this->locked_by != 0 && $this->locked_by != $userid) {
                        $result= true;
                    }
                }
            }
        }

        return $result;
    }

    /**
     * Checks if a row can be updated
     *
     * This function determines if a row can be updated based on it's locked_by information. If a row is locked, and
     * not by the logged in user, the function will return false, otherwise it will return true
     *
     * @param  KDatabaseContextInterface $context
     * @return boolean         True if row can be updated, false otherwise
     */
    protected function _beforeUpdate(KDatabaseContextInterface $context)
    {
        return (bool) !$this->isLocked();
    }

    /**
     * Checks if a row can be deleted
     *
     * This function determines if a row can be deleted based on it's locked_by information. If a row is locked, and
     * not by the logged in user, the function will return false, otherwise it will return true
     *
     * @param  KDatabaseContextInterface $context
     * @return boolean         True if row can be deleted, false otherwise
     */
    protected function _beforeDelete(KDatabaseContextInterface $context)
    {
        return (bool) !$this->isLocked();
    }
}

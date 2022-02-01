<?php
/**
 * Joomlatools Framework - https://www.joomlatools.com/developer/framework/
 *
 * @copyright	Copyright (C) 2011 Johan Janssens and Timble CVBA. (http://www.timble.net)
 * @license		GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link		http://github.com/joomlatools/joomlatools-framework-files for the canonical source repository
 */

/**
 * Folders Entity
 *
 * @author  Ercan Ozkaya <https://github.com/ercanozkaya>
 * @package Koowa\Component\Files
 */
class ComFilesModelEntityFolders extends ComFilesModelEntityNodes
{
    protected function _initialize(KObjectConfig $config)
    {
        $config->append(array(
            'identity_key' => 'path'
        ));

        parent::_initialize($config);
    }

	/**
     * Adds the rows as a hierachical tree of nodes.
     *
     * {@inheritdoc}
     */
    public function create(array $properties = array(), $status = null)
    {
        $entity = parent::create($properties, $status);

        $hierarchy = $entity->hierarchy;

        if(!empty($hierarchy) && is_array($entity->hierarchy))
        {
            // We are gonna add it as a child of another node
            $this->remove($entity);

            $nodes   = $this;
            $node    = null;

            foreach($hierarchy as $key => $parent)
            {
                $path = implode('/', array_slice($hierarchy, 0, $key+1));

                if ($node) {
                    $nodes = $node->getChildren();
                }

                $node = $nodes->find($path);
            }

            if (!$node) {
                $this->insert($entity);
            } else {
                $node->insertChild($entity);
            }
        }

        $entity->removeProperty('hierarchy');

        return $entity;
    }

    /**
     * Defined by IteratorAggregate
     *
     * @return \RecursiveArrayIterator
     */
    public function getIterator()
    {
        return new \RecursiveArrayIterator($this->_data);
    }
}

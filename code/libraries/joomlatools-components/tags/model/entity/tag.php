<?php
/**
 * Tagging Component for Joomlatools Framework - http://developer.joomlatools.com/framework
 *
 * @copyright   Copyright (C) 2016 Johan Janssens and Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        http://github.com/joomlatools/joomlatools-framework-tags for the canonical source repository
 */


/**
 * Tag Model Entity
 *
 * @author  Johan Janssens <http://github.com/johanjanssens>
 * @package Koowa\Component\Tags
 */
class ComTagsModelEntityTag extends KModelEntityRow
{
    /**
     * Save the tag in the database.
     *
     * If the tag already exists, only add the relationship.
     *
     * @return bool
     */
    public function save()
    {
        $result = true;

        if($this->row)
        {
            $tag = $this->getTable()->select(array('title' => $this->title), KDatabase::FETCH_ROW);

            //Create the tag
            if($this->isNew() && $tag->isNew())
            {
                //Unset the row property
                $properties = $this->getProperties();
                unset($properties['row']);

                $result = $tag->setProperties($properties)->save();
            }

            //Create the tag relation
            if($result && !$tag->isNew())
            {
                $data = array(
                    'tag_id' => $tag->id,
                    'row'    => $this->row,
                );

                $name     = $this->getTable()->getName().'_relations';
                $table    = $this->getObject('com:tags.database.table.relations', array('name' => $name));

                if (!$table->count($data)) {
                    $relation = $table->createRow(array('data' => $data));

                    $result = $table->insert($relation);
                }
            }
        }
        else $result = parent::save();

        return $result;
    }

    /**
     * Deletes the tag and it's relations form the database.
     *
     * @return bool
     */
    public function delete()
    {
        $result = true;

        $name   = $this->getTable()->getName().'_relations';
        $table  = $this->getObject('com:tags.database.table.relations', array('name' => $name));

        if($this->row) {
            $query = array('tag_id' => $this->id, 'row' => $this->row);
        } else {
            $query = array('tag_id' => $this->id);
        }

        $rowset = $table->select($query);

        //Delete the relations
        if($rowset->count()) {
            $result = $rowset->delete();
        }

        //Delete the tag
        if(!$this->row) {
            $result = parent::delete();
        }

        return $result;
    }
}

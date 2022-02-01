<?php
/**
 * Joomlatools Framework - https://www.joomlatools.com/developer/framework/
 *
 * @copyright	Copyright (C) 2011 Johan Janssens and Timble CVBA. (http://www.timble.net)
 * @license		GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link		http://github.com/joomlatools/joomlatools-framework-files for the canonical source repository
 */

/**
 * Attachment Model Entity
 *
 * @author  Arunas Mazeika <https://github.com/amazeika>
 * @package Koowa\Component\Files
 */
class ComFilesModelEntityAttachment extends KModelEntityRow
{
    /**
     * Attachment file getter.
     *
     * @return KModelEntityInterface
     */
    public function getPropertyFile()
    {
        return $this->getObject('com:files.model.files')
                    ->container($this->container_slug)
                    ->name($this->name)
                    ->thumbnails(true)
                    ->fetch()
                    ->getIterator()
                    ->current();
    }

    public function delete()
    {
        if ($result = parent::delete())
        {
            $file = $this->file;

            if (!$file->isNew()) $file->delete();
        }

        return $result;
    }

    public function toArray()
    {
        $data = parent::toArray();

        $file = $this->file;

        if (!$file->isNew()) {
            $data['file'] = $file->toArray();
        } else {
            unset($data['file']);
        }

        $data['created_on_timestamp']  = strtotime($this->created_on);
        $data['attached_on_timestamp'] = strtotime($this->attached_on);

        return $data;
    }
}
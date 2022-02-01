<?php
/**
 * Nooku Framework - http://nooku.org/framework
 *
 * @copyright	Copyright (C) 2011 - 2015 Johan Janssens and Timble CVBA. (http://www.timble.net)
 * @license		GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link		http://github.com/joomlatools/joomlatools-framework-files for the canonical source repository
 */

/**
 * Thumbnailable Database Behavior
 *
 * @author  Arunas Mazeika <https://github.com/amazeika>
 * @package Koowa\Component\Files
 */
class ComFilesDatabaseBehaviorThumbnailable extends KDatabaseBehaviorAbstract
{
    /**
     * @var array A list of files extensions for which thumbnails may be generated
     */
    protected $_thumbnailable_extensions;

    public function __construct(KObjectConfig $config)
    {
        parent::__construct($config);

        $this->_thumbnailable_extensions = KObjectConfig::unbox($config->thumbnailable_extensions);
    }

    protected function _initialize(KObjectConfig $config)
    {
        $config->append(array('thumbnailable_extensions' => ComFilesModelEntityFile::$extension_type_map['image']));

        parent::_initialize($config);
    }

    /**
     * Tells if a thumbnail should be generated for the file
     *
     * @param array $dimension An array specifying the dimension of the thumbnail in pixels
     *
     * @return bool true if it can, false otherwise
     */
    public function canHaveThumbnail($dimension = null)
    {
        $result = false;
        $mixer  = $this->getMixer();

        if ($mixer instanceof ComFilesModelEntityFile && !$mixer->isNew()) {
            $result = in_array($mixer->extension, $this->_thumbnailable_extensions);
        }

        if ($result && is_array($dimension))
        {
            // Check source size against thumbnail size (local sources only)
            if ($mixer->isLocal() && ($size = $mixer->adapter->getImageSize()))
            {
                if (isset($dimension['width']) && isset($dimension['height']))
                {
                    if ($size['width'] <= $dimension['width'] && $size['height'] <= $dimension['height']) $result = false;
                }
                elseif (isset($dimension['width']))
                {
                    if ($size['width'] <= $dimension['width']) $result = false;
                }
                elseif (isset($dimension['height']))
                {
                    if ($size['height'] <= $dimension['height']) $result = false;
                }
            }
        }

        return $result;
    }

    /**
     * Thumbnail getter
     *
     * @param string $version The version model state value
     *
     * @return ComFilesModelEntityThumbnails|false The thumbnails entity object, false if an empty entity is returned or
     *                                             if the model could not be fetched
     */
    public function getThumbnail($version =  null)
    {
        $thumbnail = false;

        if ($container = $this->thumbnails_container_slug)
        {
            $model = $this->getObject('com:files.model.thumbnails')->container($container)->source($this->uri);

            if ($version) {
                $model->version($version);
            }

            $thumbnail = $model->fetch();

            if ($thumbnail->isNew()) {
                $thumbnail = false;
            }
        }

        return $thumbnail;
    }
}
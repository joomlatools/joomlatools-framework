<?php
/**
 * Joomlatools Framework - https://www.joomlatools.com/developer/framework/
 *
 * @copyright	Copyright (C) 2011 Johan Janssens and Timble CVBA. (http://www.timble.net)
 * @license		GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link		http://github.com/joomlatools/joomlatools-framework-files for the canonical source repository
 */

/**
 * Thumbnail Entity
 *
 * @author  Ercan Ozkaya <https://github.com/ercanozkaya>
 * @package Koowa\Component\Files
 */
class ComFilesModelEntityThumbnail extends ComFilesModelEntityFile
{
    /**
     * First calls the parent to finish object constructing before setting data.
     *
     * This is because setProperties calls setAdapter which might call save to generate a thumbnail.
     * However in the normal construction flow command mixin is not mixed in yet.
     * So invokeCommand method does not exist.
     *
     * @param KObjectConfig $config
     */
    public function __construct(KObjectConfig $config)
    {
        $data = $config->data;
        unset($config->data);

        parent::__construct($config);

        if (isset($data)) {
            $this->setProperties($data->toArray(), $this->isNew());
        }
    }

    protected function _initialize(KObjectConfig $config)
    {
        $config->append(array(
            'validator' => 'com:files.database.validator.file',
            'data'      => array('crop' => false, 'dimension' => array('width' => 200, 'height' => 150))
        ));

        parent::_initialize($config);
    }

    public function getHandle()
    {
        if ($version = $this->version) {
            $handle = $version;
        } else {
            $handle = parent::getHandle();
        }

        return $handle;
    }

    public function setAdapter()
    {
        $path = '/' . ($this->folder ? $this->folder . '/' : '') . $this->name;

        if ($container = $this->getContainer()) {
            $path = $container->fullpath . $path;
        } else {
            $path = $this->uri ?: $path;
        }

        $this->_adapter = $this->getObject('com:files.adapter.file', array('path' => $path));

        $this->_regenerate();

        unset($this->_data['fullpath']);
        unset($this->_data['metadata']);

        return $this;
    }

    protected function _regenerate()
    {
        $result = false;

        $source = $this->source;

        // Only regenerate local sources ...we don't want to calculate dimensions on external sources.
        if ($this->_adapter && $this->_adapter->exists() && $source && $source->isImage() && $source->isLocal())
        {
            $current_size = @getimagesize($this->fullpath);

            $dimension = $this->getDimension();

            // Compare dimensions
            if ($current_size && ($current_size[0] != $dimension['width'] || $current_size[1] != $dimension['height'])) {
                $result = $this->save();
            }
        }

        return $result;
    }

    public function generate($in_place = false)
    {
        $memory_limit = ini_get('memory_limit');

        if ($memory_limit < '256M') {
            @ini_set('memory_limit', '256M');
        }

    	if ($this->_canGenerate())
		{
            try
            {
                $source = $this->source;

                $imagine = new \Imagine\Gd\Imagine();
                $image   = $imagine->open($source->fullpath);

                $dimension = $this->getDimension();

                if ($dimension['width'] && $dimension['height']) {
                    $size = new \Imagine\Image\Box($dimension['width'], $dimension['height']);
                }
                else
                {
                    $image_size = $image->getSize();
                    $larger     = max($image_size->getWidth(), $image_size->getHeight());
                    $scale      = max($dimension['width'], $dimension['height']);

                    $size       = $image_size->scale(1/($larger/$scale));
                }

                $mode = ($this->crop) ? \Imagine\Image\ImageInterface::THUMBNAIL_OUTBOUND : \Imagine\Image\ImageInterface::THUMBNAIL_INSET;

                if ($in_place) {
                    return $image->thumbnail($size, $mode)->save($this->fullpath);
                } else {
                    return (string) $image->thumbnail($size, $mode);
                }
			}
			catch (Exception $e) {
				return false;
			}
		}

		return false;
    }

    public function save()
    {
        $context = $this->getContext();
        $context->result = false;

        $is_new = $this->isNew();

        if ($this->invokeCommand('before.save', $context) !== false)
        {
            if ($source = $this->source)
            {
                if ($str = $this->generate())
                {
                    $path = '/' . ($this->folder ? $this->folder . '/' : '');

                    if ($container = $this->getContainer()) {
                        $path = $container->fullpath . $path;
                    }

                    $folder = $this->getObject('com:files.adapter.folder', array('path' => $path));

                    if (!$folder->exists()) {
                        $folder->create();
                    }

                    $context->result = $this->_adapter->write($str);

                    $this->invokeCommand('after.save', $context);
                }
            }
        }

        if ($context->result === false) {
            $this->setStatus(KDatabase::STATUS_FAILED);
        } else {
            $this->setStatus($is_new ? KDatabase::STATUS_CREATED : KDatabase::STATUS_UPDATED);
        }

        return $context->result;
    }

    public function toArray()
    {
        $data = parent::toArray();

        $data['relative_path'] = $this->relative_path;

        if ($version = $this->version) {
            $data['version'] = $version;
        }

		unset($data['_thumbnail_size']);
		unset($data['source']);

        return $data;
    }

    public function setPropertySource($value)
    {
        if ($value instanceof ComFilesModelEntityFiles) {
            $value = $value->top();
        }

        if (!$value instanceof ComFilesModelEntityFile) {
            throw new RuntimeException('Wrong type for source');
        }

        if ($value->isNew()) {
            throw new RuntimeException('Source cannot be a new entity');
        }

        return $value;
    }

    public function setPropertyCrop($value)
    {
        return (bool) $value;
    }

    /**
     * Thumbnail dimension setter.
     *
     * The dimension of a thumbnail consists on a width and height pair.
     *
     * @param  string|array $dimension
     * @throws BadMethodCallException
     * @return $this
     */
    public function setPropertyDimension($value)
    {
        if (!(isset($value['width']) || isset($value['height']))) {
            throw new BadMethodCallException('The provided dimension is invalid');
        }

        return $value;
    }

    /**
     * Thumbnail dimension getter.
     *
     * The dimension of a thumbnail consists on a width and height pair. This method will return the resulting
     * thumbnail dimension depending on: the provided width/height pair, the source image ratio and whether or not
     * cropping is enabled.
     *
     * Ratio will always be preserved unless crop is enabled and both components are provided. Otherwise the resulting
     * dimensions will consist on a box that's guaranteed to not exceed the dimension property components while
     * preserving the source ratio. If one of the component is missing in the dimension property, the other will get
     * calculated based on the ratio of the source.
     *
     * @throws RuntimeException If no source or its dimension cannot be determined.
     *
     * @return array Associative array containing the thumbnail width and height.
     */
    public function getDimension()
    {
        $dimension = $this->dimension;

        if ($dimension && empty($dimension['width']) || empty($dimension['height']) || !$this->crop)
        {
            $source = $this->source;

            if (!($source && ($info = @getimagesize($source->fullpath)))) {
                throw new RuntimeException('Unable to get source size');
            }

            $ratio = $info[0] / $info[1];

            if (!$this->crop && !empty($dimension['height']) && !empty($dimension['width']))
            {
                $dimension_ratio = $dimension['height'] / $dimension['width'];

                // Decide which dimension component to keep, the other will get re-calculated below to preserve ratio.
                if ($ratio > $dimension_ratio) {
                    unset($dimension['height']);
                } elseif ($ratio < $dimension_ratio) {
                    unset($dimension['width']);
                }
            }

            if (!empty($dimension['width']))
            {
                if ($dimension['width'] > $info[0]) {
                    $dimension['width'] = $info[0]; // Thumbnails cannot be bigger than source
                }

                $dimension['height'] = round($dimension['width'] / $ratio);
            }
            else
            {
                if ($dimension['height'] > $info[1]) {
                    $dimension['height'] = $info[1]; // Thumbnails cannot be bigger than source
                }

                $dimension['width'] = round($dimension['height'] * $ratio);
            }
        }

        return $dimension;
    }

    /**
     * Checks if a thumbnail for the current source and provided dimension can be generated given the
     * amount of memory that's available.
     *
     * @return bool True if the thumbnail can be "safely" processed, false otherwise.
     */
    protected function _canGenerate()
    {
        $result = true;

        // Multiplier to take into account memory consumed by the Image Processing Library.
        $tweak_factor  = 6;

        if (($source = $this->source) && ($info = @getimagesize($source->fullpath)))
        {
            $channels      = isset($info['channels']) ? $info['channels'] : 4;
            $bits          = isset($info['bits']) ? $info['bits'] : 8;
            $source_memory = ceil($info[0] * $info[1] * $bits * $channels / 8 * $tweak_factor);

            $dimension = $this->getDimension();

            // We assume the same amount of bits and channels as source.
            $thumb_memory = ceil($dimension['width'] * $dimension ['height'] * $bits * $channels / 8 * $tweak_factor);

            $limit = ini_get('memory_limit');

            // Check if memory is limited (-1 => Unlimited)
            if ($limit != '-1')
            {
                $limit = self::convertToBytes($limit);
                $available_memory = $limit - memory_get_usage();

                // Leave 16 megs for the rest of the request
                $available_memory -= 16777216;

                if ($source_memory + $thumb_memory > $available_memory) {
                    $result = false;
                }
            }
        }

        return $result;
    }

    public static function convertToBytes($value)
    {
        $keys = array('k', 'm', 'g');
        $last_char = strtolower(substr($value, -1));
        $value = (int) $value;

        if (in_array($last_char, $keys)) {
            $value *= pow(1024, array_search($last_char, $keys)+1);
        }

        return $value;
    }
}

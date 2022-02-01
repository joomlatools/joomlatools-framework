<?php
/**
 * Joomlatools Framework - https://www.joomlatools.com/developer/framework/
 *
 * @copyright   Copyright (C) 2011 Johan Janssens and Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        http://github.com/joomlatools/joomlatools-framework-activities for the canonical source repository
 */

/**
 * Activity Stream Media Link.
 *
 * @link     http://activitystrea.ms/specs/json/1.0/#media-link
 *
 * @author   Arunas Mazeika <https://github.com/amazeika>
 * @package  Koowa\Component\Activities
 */
class ComActivitiesActivityMedialink extends KObjectArray implements ComActivitiesActivityMedialinkInterface
{
    /**
     * Get Duration.
     *
     * @link http://activitystrea.ms/specs/json/1.0/#media-link See duration property.
     *
     * @return int|null The duration, null if the media link does not have a duration property.
     */
    public function getDuration()
    {
        return $this->duration;
    }

    /**
     * Set Duration.
     *
     * @link http://activitystrea.ms/specs/json/1.0/#media-link See duration property.
     *
     * @param int $duration The duration.
     * @return ComActivitiesActivityMedialink
     */
    public function setDuration($duration)
    {
        $this->duration = (int) $duration;
        return $this;
    }

    /**
     * Get Height.
     *
     * @link http://activitystrea.ms/specs/json/1.0/#media-link See height property.
     *
     * @return int|null The height, null if the media link does not have a height property.
     */
    public function getHeight()
    {
        return $this->height;
    }

    /**
     * Set Height.
     *
     * @link http://activitystrea.ms/specs/json/1.0/#media-link See height property.
     *
     * @param int $height The height.
     * @return ComActivitiesActivityMedialink
     */
    public function setHeight($height)
    {
        $this->height = (int) $height;
        return $this;
    }

    /**
     * Get Url.
     *
     * @link http://activitystrea.ms/specs/json/1.0/#media-link See Url property.
     *
     * @return KHttpUrlInterface The url.
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * Set Url.
     *
     * @link http://activitystrea.ms/specs/json/1.0/#media-link See Url property.
     *
     * @param KHttpUrlInterface $url The url.
     * @return ComActivitiesActivityMedialink
     */
    public function setUrl(KHttpUrl $url)
    {
        $this->url = $url;
        return $this;
    }

    /**
     * Get Width.
     *
     * @link http://activitystrea.ms/specs/json/1.0/#media-link See width property.
     *
     * @return int|null The width, null if the media link does not have a width property.
     */
    public function getWidth()
    {
        return $this->width;
    }

    /**
     * Set Width.
     *
     * @link http://activitystrea.ms/specs/json/1.0/#media-link See width property.
     *
     * @param int $width The width.
     * @return ComActivitiesActivityMedialink
     */
    public function setWidth($width)
    {
        $this->width = (int) $width;
        return $this;
    }
}
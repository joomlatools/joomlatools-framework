<?php
/**
 * Joomlatools Framework - https://www.joomlatools.com/developer/framework/
 *
 * @copyright   Copyright (C) 2011 Johan Janssens and Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        http://github.com/joomlatools/joomlatools-framework-activities for the canonical source repository
 */

/**
 * Activity Stream Media Link Interface.
 *
 * @see     http://activitystrea.ms/specs/json/1.0/#media-link
 *
 * @author  Arunas Mazeika <https://github.com/amazeika>
 * @package Koowa\Component\Activities
 */
interface ComActivitiesActivityMedialinkInterface
{
    /**
     * Get Duration.
     *
     * @return int|null The duration, null if the media link does not have a duration property.
     */
    public function getDuration();

    /**
     * Set Duration.
     *
     * @param int $duration The duration.
     * @return ComActivitiesActivityMedialinkInterface
     */
    public function setDuration($duration);

    /**
     * Get Height.
     *
     * @return int|null The height, null if the media link does not have a height property.
     */
    public function getHeight();

    /**
     * Set Height.
     *
     * @param int $height The height.
     * @return ComActivitiesActivityMedialinkInterface
     */
    public function setHeight($height);

    /**
     * Get Url.
     *
     * @return KHttpUrl The url.
     */
    public function getUrl();

    /**
     * Set Url.
     *
     * @param KHttpUrl $url The url.
     * @return ComActivitiesActivityMedialinkInterface
     */
    public function setUrl(KHttpUrl $url);

    /**
     * Get Width.
     *
     * @return int|null The width, null if the media link does not have a width property.
     */
    public function getWidth();

    /**
     * Set Width.
     *
     * @param int $width The width.
     * @return ComActivitiesActivityMedialinkInterface
     */
    public function setWidth($width);
}
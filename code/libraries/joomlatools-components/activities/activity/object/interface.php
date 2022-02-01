<?php
/**
 * Joomlatools Framework - https://www.joomlatools.com/developer/framework/
 *
 * @copyright   Copyright (C) 2011 Johan Janssens and Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        http://github.com/joomlatools/joomlatools-framework-activities for the canonical source repository
 */

/**
 * Activity Object Interface.
 *
 * @link    http://activitystrea.ms/specs/json/1.0/#object
 *
 * @author  Arunas Mazeika <https://github.com/amazeika>
 * @package Koowa\Component\Activities
 */
interface ComActivitiesActivityObjectInterface
{
    /**
     * Get the activity object name.
     *
     * The object name identifies the object using a human-readable and plain-text string. HTML markup MUST NOT be
     * included.
     *
     * @return string|null The activity object name, null if the object does not have a name.
     */
    public function getObjectName();

    /**
     * Set the activity object name.
     *
     * @see ComActivitiesActivityObjectInterface::getObjectName
     *
     * @param string|null $name The activity object name.
     * @return ComActivitiesActivityObjectInterface
     */
    public function setObjectName($name);

    /**
     * Get the display name.
     *
     * @link http://activitystrea.ms/specs/json/1.0/#object See displayName property.
     *
     * @return string|null The display name, null if the object does not have a display name property.
     */
    public function getDisplayName();

    /**
     * Set the display name.
     *
     * @link http://activitystrea.ms/specs/json/1.0/#object See displayName property.
     *
     * @param string|null $name The display name.
     * @return ComActivitiesActivityObjectInterface
     */
    public function setDisplayName($name);

    /**
     * Get the attachments.
     *
     * @link http://activitystrea.ms/specs/json/1.0/#object See attachments property.
     * @return array An array of {@link ComActivitiesActivityObjectInterface} objects.
     */
    public function getAttachments();

    /**
     * Set the attachments.
     *
     * @link http://activitystrea.ms/specs/json/1.0/#object See attachments property.
     *
     * @param array $attachments An array of {@link ComActivitiesActivityObjectInterface} objects.
     * @param bool  $merge       Tells if attachments should be replaced or merged with current existing attachments.
     * @return ComActivitiesActivityObjectInterface
     */
    public function setAttachments(array $attachments, $merge = true);

    /**
     * Get the author.
     *
     * @link http://activitystrea.ms/specs/json/1.0/#object See author property.
     *
     * @return ComActivitiesActivityObjectInterface|null The author, null if the object does not have an actor property.
     */
    public function getAuthor();

    /**
     * Set the author.
     *
     * @link http://activitystrea.ms/specs/json/1.0/#object See author property.
     *
     * @param ComActivitiesActivityObjectInterface|null $author The author.
     * @return ComActivitiesActivityObjectInterface
     */
    public function setAuthor($author);

    /**
     * Get the content.
     *
     * @link http://activitystrea.ms/specs/json/1.0/#object See content property.
     *
     * @return string|null The content, null if the object does not have a content property.
     */
    public function getContent();

    /**
     * Set the content.
     *
     * @link http://activitystrea.ms/specs/json/1.0/#object See content property.
     *
     * @param string|null $content The content.
     * @return ComActivitiesActivityObjectInterface
     */
    public function setContent($content);

    /**
     * Get the downstream duplicates.
     *
     * @link http://activitystrea.ms/specs/json/1.0/#object See downstreamDuplicates property.
     *
     * @return array An array of {@link ComActivitiesActivityObjectInterface} objects.
     */
    public function getDownstreamDuplicates();

    /**
     * Set the downstream duplicates.
     *
     * @link http://activitystrea.ms/specs/json/1.0/#object See downstreamDuplicates property.
     *
     * @param array $duplicates An array of {@link ComActivitiesActivityObjectInterface} objects.
     * @param bool  $merge      Tells if downstream duplicates should be replaced or merged with current existing
     *                          downstream duplicates.
     *
     * @return ComActivitiesActivityObjectInterface
     */
    public function setDownstreamDuplicates(array $duplicates, $merge = true);

    /**
     * Get the Id.
     *
     * @link http://activitystrea.ms/specs/json/1.0/#object See id property.
     *
     * @return string|null The id, null if the object does not have an id property.
     */
    public function getId();

    /**
     * Set the Id.
     *
     * @link http://activitystrea.ms/specs/json/1.0/#object See id property.
     *
     * @param string|null $id The Id.
     * @return ComActivitiesActivityObjectInterface
     */
    public function setId($id);

    /**
     * Get the Universally Unique Identifier.
     *
     * @see http://en.wikipedia.org/wiki/UUID
     * @return string|null The Universally Unique Identifier, null if the object does not have one.
     */
    public function getUuid();

    /**
     * Set the Universally Unique Identifier.
     *
     * @param string|null $uuid The Universally Unique Identifier.
     * @return ComActivitiesActivityObjectInterface
     */
    public function setUuid($uuid);

    /**
     * Get the image.
     *
     * @link http://activitystrea.ms/specs/json/1.0/#object See image property.
     *
     * @return ComActivitiesActivityMedialinkInterface|null The image, null if the object does not have an image
     *                                                      property.
     */
    public function getImage();

    /**
     * Set the image.
     *
     * @link http://activitystrea.ms/specs/json/1.0/#object See image property.
     *
     * @param ComActivitiesActivityMedialinkInterface|null $image The image.
     * @return ComActivitiesActivityObjectInterface
     */
    public function setImage($image);

    /**
     * Get the object type.
     *
     * @link http://activitystrea.ms/specs/json/1.0/#object See objectType property.
     *
     * @return string|null The object type, null if the object does not have an object type property.
     */
    public function getObjectType();

    /**
     * Set the object type.
     *
     * @link http://activitystrea.ms/specs/json/1.0/#object See objectType property.
     *
     * @param string|null $type The object type.
     * @return ComActivitiesActivityObjectInterface
     */
    public function setObjectType($type);

    /**
     * Get the published date.
     *
     * @return KDateInterface|null The published date, null if the object does not have a published property.
     *
     * @link http://activitystrea.ms/specs/json/1.0/#object See published property.
     */
    public function getPublished();

    /**
     * Set the published date.
     *
     * @link http://activitystrea.ms/specs/json/1.0/#object See published property.
     *
     * @param KDateInterface|null $date The published date.
     * @return ComActivitiesActivityObjectInterface
     */
    public function setPublished($date);

    /**
     * Get the summary.
     *
     * @link http://activitystrea.ms/specs/json/1.0/#object See summary property.
     *
     * @return string|null The summary, null if the object does not have a summary property.
     */
    public function getSummary();

    /**
     * Set the summary.
     *
     * @link http://activitystrea.ms/specs/json/1.0/#object See summary property.
     *
     * @param mixed $summary The summary.
     * @return ComActivitiesActivityObjectInterface
     */
    public function setSummary($summary);

    /**
     * Get the updated date.
     *
     * @link http://activitystrea.ms/specs/json/1.0/#object See updated property.
     *
     * @return KDateInterface|null The updated date, null if the object does not have an updated date property.
     */
    public function getUpdated();

    /**
     * Set the updated date.
     *
     * @link http://activitystrea.ms/specs/json/1.0/#object See updated property.
     *
     * @param KDateInterface|null $date The updated date.
     * @return ComActivitiesActivityObjectInterface
     */
    public function setUpdated($date);

    /**
     * Get the upstream duplicates.
     *
     * @link http://activitystrea.ms/specs/json/1.0/#object See upstreamDuplicates property.
     *
     * @return array An array of {@link ComActivitiesActivityObjectInterface} objects.
     */
    public function getUpstreamDuplicates();

    /**
     * Set the upstream duplicates.
     *
     * @link http://activitystrea.ms/specs/json/1.0/#object See upstreamDuplicates property.
     *
     * @param array $duplicates An array of {@link ComActivitiesActivityObjectInterface} objects.
     * @param bool $merge Tells if upstream duplicates should be replaced or merged with current existing upstream
     *                    duplicates.
     * @return ComActivitiesActivityObjectInterface
     */
    public function setUpstreamDuplicates(array $duplicates, $merge = true);

    /**
     * Get the url.
     *
     * @link http://activitystrea.ms/specs/json/1.0/#object See url property.
     *
     * @return KHttpUrlInterface|null The url, null if the object does not have a url property.
     */
    public function getUrl();

    /**
     * Set the url.
     *
     * @link http://activitystrea.ms/specs/json/1.0/#object See url property.
     *
     * @param KHttpUrlInterface|null $url The url.
     * @return ComActivitiesActivityObjectInterface
     */
    public function setUrl($url);

    /**
     * Get the attributes.
     *
     * @return array The attributes.
     */
    public function getAttributes();

    /**
     * Set the attributes.
     *
     * @param array $attributes The attributes.
     * @param bool  $merge      Tells if attributes should be replaced or merged with current existing attributes.
     * @return ComActivitiesActivityObjectInterface
     */
    public function setAttributes(array $attribs = array(), $merge = true);

    /**
     * Set the deleted state.
     *
     * @param bool $state The deleted state.
     * @return ComActivitiesActivityObjectInterface
     */
    public function setDeleted($state);

    /**
     * Tells if the object has been deleted, i.e. no longer reachable or persisted.
     *
     * @return bool True if the object has been deleted, false otherwise.
     */
    public function isDeleted();

    /**
     * Set the translateable state.
     *
     * @param bool $state The translateable state.
     * @return ComActivitiesActivityObjectInterface
     */
    public function setTranslatable($state);

    /**
     * Tells if the object should be translated when rendered.
     *
     * @return bool True if the object is translatable, false otherwise.
     */
    public function isTranslatable();
}
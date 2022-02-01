<?php
/**
 * Joomlatools Framework - https://www.joomlatools.com/developer/framework/
 *
 * @copyright   Copyright (C) 2011 Johan Janssens and Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        http://github.com/joomlatools/joomlatools-framework-activities for the canonical source repository
 */

/**
 * Activity Translator Interface.
 *
 * @author  Arunas Mazeika <https://github.com/amazeika>
 * @package Koowa\Component\Activities
 */
interface ComActivitiesActivityTranslatorInterface
{
    /**
     * Translates an activity format.
     *
     * @param string $string The activity format to translate.
     * @return string The translated activity format.
     */
    public function translateActivityFormat(ComActivitiesActivityInterface $activity);

    /**
     * Translates an activity token.
     *
     * @param string|ComActivitiesActivityObjectInterface $token    The activity token.
     * @param ComActivitiesActivityInterface              $activity The activity object.
     * @return string The translated token.
     */
    public function translateActivityToken($token, ComActivitiesActivityInterface $activity);

    /**
     * Activities token
     *
     * Tokens are activity objects being referenced in the activity format. They represent variables contained
     * in an activity message.
     *
     * @param ComActivitiesActivityInterface $activity
     * @return array A list containing ComActivitiesActivityObjectInterface objects.
     */
    public function getActivityTokens(ComActivitiesActivityInterface $activity);
}
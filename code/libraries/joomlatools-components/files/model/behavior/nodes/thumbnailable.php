<?php
/**
 * Nooku Framework - http://nooku.org/framework
 *
 * @copyright	Copyright (C) 2011 - 2015 Johan Janssens and Timble CVBA. (http://www.timble.net)
 * @license		GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link		http://github.com/joomlatools/joomlatools-framework-files for the canonical source repository
 */

/**
 * Nodes Thumbnailable Model behavior
 *
 * Handles Nodes thumbnailable requests by adding the thumbnails state.
 *
 * @author  Arunas Mazeika <https://github.com/amazeika>
 * @package Koowa\Component\Files
 */
class ComFilesModelBehaviorNodesThumbnailable extends ComFilesModelBehaviorThumbnailable
{
    protected function _afterFetch(KModelContextInterface $context)
    {
        // Do nothing ... Nodes model fetches files from the files model which is also thumbnailable
        // The thumbnails state is forwarded to files model at this time
    }
}
<?php
/**
 * Joomlatools Framework - https://www.joomlatools.com/developer/framework/
 *
 * @copyright	Copyright (C) 2011 Johan Janssens and Timble CVBA. (http://www.timble.net)
 * @license		GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link		http://github.com/joomlatools/joomlatools-framework-files for the canonical source repository
 */

/**
 * Player Template Helper
 *
 * @author  Rastin Mehr <https://github.com/rmdstudio>
 * @package Koowa\Component\Files
 */
class ComFilesTemplateHelperPlayer extends KTemplateHelperAbstract
{
    /**
     * Array which holds a list of loaded Javascript libraries
     *
     * @type array
     */
    protected static $_loaded = array();

    /**
     * Marks the resource as loaded
     *
     * @param      $key
     * @param bool $value
     */
    public static function setLoaded($key, $value = true)
    {
        static::$_loaded[$key] = $value;
    }

    /**
     * Checks if the resource is loaded
     *
     * @param $key
     * @return bool
     */
    public static function isLoaded($key)
    {
        return !empty(static::$_loaded[$key]);
    }

    protected static $_SUPPORTED_FORMATS = array(
        'audio' => array('aac', 'mp3', 'ogg', 'flac','x-flac', 'wave', 'wav', 'x-wav', 'x-pn-wav'),
        'video' => array('mp4', 'webm', 'ogg')
    );

    public function load($config = array())
    {
        $config = new KObjectConfigJson($config);
        $config->append(array(
            'download' => false
        ))->append(array(
            'options' => [
                'play-large',   // The large play button in the center
                'play',         // Play/pause playback
                'progress',     // The progress bar and scrubber for playback and buffering
                'current-time', // The current time of playback
                'mute',         // Toggle mute
                'volume',       // Volume control
                'fullscreen'    // Toggle fullscreen
            ]
            ));

        if ($config->download) {
            $config->options->append(['download']); // Show a download button with a link to either the current source or a custom URL you specify in your options
        }

        $html = '';

        if (!static::isLoaded('plyr'))
        {
            $html = $this->getObject('template.default')
                ->addFilter('lib:template.filter.style')
                ->addFilter('lib:template.filter.script')
                ->addFilter('com:koowa.template.filter.asset')
                ->loadString('
                    <ktml:style src="assets://files/css/plyr.css" />
                    <ktml:script src="assets://files/js/plyr.js" />
                    <ktml:script src="assets://files/js/files.plyr.js" />
                    <script>
                    kQuery(function($){
                        var controls = ' . json_encode(KObjectConfig::unbox($config->options)) . ';
                        new Files.Plyr({ controls });
                    });
                    </script>
                ', 'php')
                ->render();

                static::setLoaded('plyr');
        }

        return $html;
    }
}
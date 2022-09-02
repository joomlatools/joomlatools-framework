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
        ))->append([
            'options' => [
                'download' => $config->download
            ]
        ]);

        $html = $this->getTemplate()->createHelper('behavior')->koowa();

        if (!static::isLoaded('plyr'))
        {
            $options = (string) $config->options;
            $html = '<ktml:style src="assets://files/css/plyr.css" />
                    <ktml:script src="assets://files/js/plyr.js" />
                    <script>
                        kQuery(function(){
                            new Files.Plyr('.$options.');
                        });
                    </script>
                ';

            static::setLoaded('plyr');
        }

        return $html;
    }
}
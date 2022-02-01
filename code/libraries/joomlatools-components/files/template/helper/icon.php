<?php
/**
 * @package     FILEman
 * @copyright   Copyright (C) 2011 Timble CVBA (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        http://www.joomlatools.com
 */

class ComFilesTemplateHelperIcon extends KTemplateHelperAbstract
{
    public static $icon_extension_map = array(
        'archive'     => array('7z','gz','rar','tar','zip'),
        'audio'       => array('aif','aiff','alac','amr','flac','ogg','m3u','m4a','mid','mp3','mpa','wav','wma'),
        'document'    => array('doc','docx','rtf','txt','ppt','pptx','pps','xml'),
        'image'       => array('bmp','gif','jpg','jpeg','png','psd','tif','tiff'),
        'pdf'         => array('pdf'),
        'spreadsheet' => array('xls', 'xlsx', 'ods'),
        'video'       => array('3gp','avi','flv','mkv','mov','mp4','mpg','mpeg','rm','swf','vob','wmv')
    );

    public static function getIconExtensionMap()
    {
        return self::$icon_extension_map;
    }

    public function icon_map($config = array())
    {
        $icon_map = json_encode(self::getIconExtensionMap());

        $html = "
            <script>
            if (typeof Files === 'undefined') Files = {};

            Files.icon_map = $icon_map;
            </script>";

        return $html;
    }

    /**
     * Gets the icon for the given extension
     *
     * @param array|KObjectConfig $config
     * @return string Icon class, "default" if the extension doesn't exist in the map
     */
    public function icon($config = array())
    {
        $config = new KObjectConfigJson($config);
        $config->append(array(
            'extension' => null
        ));

        $icon = 'default';

        if ($config->extension)
        {
            $extension = strtolower($config->extension);

            foreach (self::$icon_extension_map as $type => $extensions)
            {
                if (in_array($extension, $extensions))
                {
                    $icon = $type;
                    break;
                }
            }
        }

        return $icon;
    }
}
<?php
/**
 * Joomlatools Framework - https://www.joomlatools.com/developer/framework/
 *
 * @copyright	Copyright (C) 2011 Johan Janssens and Timble CVBA. (http://www.timble.net)
 * @license		GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link		http://github.com/joomlatools/joomlatools-framework-files for the canonical source repository
 */

/**
 * File Database Row
 *
 * @author  Ercan Ozkaya <https://github.com/ercanozkaya>
 * @package Koowa\Component\Files
 */
class ComFilesModelEntityFile extends ComFilesModelEntityNode implements KCommandCallbackDelegate
{
    public static $extension_type_map = array(
        'archive'     => array('7z','gz','rar','tar','zip'),
        'audio'       => array('mp3', '3gp', 'act', 'aiff', 'aac', 'amr', 'au', 'awb', 'dct', 'dss', 'dvf', 'flac', 'gsm', 'm4a', 'm4p', 'ogg', 'oga', 'ra', 'rm', 'raw', 'tta', 'vox', 'wav', 'wma', 'wv', 'webm'),
        'document'    => array('pdf', 'csv', 'doc','docx','odc','odg','odp','ods', 'odt', 'otc','otg', 'otp','ott', 'rtf','txt','ppt','pptx','pps','tsv', 'tab','xls', 'xlsx','xml'),
        'image'       => array('ai','bmp','cr2','crw','eps','erf','gif','jpg','jpeg','nef','orf','png','pbm','pgm', 'ppm','psd','svg','tif','tiff','x3f','xbm'),
        'video'       => array('webm','mkv','flv','vob','ogv','ogg','avi','rm','rmvb','mp4','m4p','m4v','asf','mpg','mpeg','mpv','mpe','3gp','3g2','roq','nsv'),
        'executable'  => array('cmd', 'exe','bat','bin','apk','msi', 'dmg')
    );

	public function __construct(KObjectConfig $config)
    {
        parent::__construct($config);

        $this->addBehavior('com:files.database.behavior.thumbnailable');
        $this->addCommandCallback('after.save', '_fixOrientation');
        $this->addCommandCallback('after.save', '_downsizeImage');
    }

    public function save()
	{
		$context = $this->getContext();
		$context->result = false;

		$is_new = $this->isNew();

		if ($this->invokeCommand('before.save', $context) !== false)
		{
			$context->result = $this->_adapter->write(!empty($this->contents) ? $this->contents : $this->file);
			$this->invokeCommand('after.save', $context);
        }

		if ($context->result === false) {
			$this->setStatus(KDatabase::STATUS_FAILED);
		} else {
            $this->setStatus($is_new ? KDatabase::STATUS_CREATED : KDatabase::STATUS_UPDATED);
        }

		return $context->result;
	}

    protected function _fixOrientation(KDatabaseContext $context)
    {
        if ($this->isImage() && in_array($this->extension, ['jpeg', 'jpg'])) {
            $exif = $this->_adapter->readExifData();

            if (is_array($exif) && !empty($exif['Orientation']) && in_array($exif['Orientation'], [3,6,8])) {
                $path  = $this->_adapter->getRealPath();
                $image = imagecreatefromjpeg($path);

                switch ($exif['Orientation']) {
                    case 3:
                        $image = imagerotate($image, 180, 0);
                        break;

                    case 6:
                        $image = imagerotate($image, -90, 0);
                        break;

                    case 8:
                        $image = imagerotate($image, 90, 0);
                        break;
                }

                imagejpeg($image, $path);
            }
        }
    }

	protected function _downsizeImage(KDatabaseContext $context)
    {
        if ($container = $this->getContainer())
        {
            $parameters = $container->getParameters();

            if ($size = $parameters['maximum_image_size']) {
                $this->resize($size);
            }
        }
    }

    public function resize($width)
    {
        $valid_extensions = array('jpg', 'jpeg', 'gif', 'png');

        if ($this->isImage()
            && $this->getContainer()->getParameters()->maximum_image_size
            && in_array(strtolower($this->extension), $valid_extensions))
        {
            if (!empty($width))
            {
                $current_size = @getimagesize($this->fullpath);

                if ($current_size && $current_size[0] > $width || $current_size[1] > $width)
                {
                    $thumbnail = $this->getObject('com:files.model.entity.thumbnail',
                        array(
                            'data' => array(
                                'overwrite' => true,
                                'dimension' => array('width' => $width, 'height' => $width),
                                'name'      => $this->name,
                                'folder'    => $this->folder,
                                'container' => $this->getContainer()->slug,
                                'source'    => $this
                            )
                        ));

                    $thumbnail->save();
                }
            }
        }
    }


    public function getPropertyFilename()
    {
        return \Koowa\pathinfo($this->name, PATHINFO_FILENAME);
    }

    public function getPropertySize()
    {
        if($metadata = $this->_adapter->getMetadata())
        {
            if(isset($metadata['size'])) {
                return $metadata['size'];
            }
        }

        return false;
    }

    public function getPropertyExtension()
    {
        if($metadata = $this->_adapter->getMetadata())
        {
            if(isset($metadata['extension'])) {
                return $metadata['extension'];
            }
        }

        return false;
    }

    public function getPropertyModifiedDate()
    {
        if($metadata = $this->_adapter->getMetadata())
        {
            if(isset($metadata['modified_date'])) {
                return $metadata['modified_date'];
            }
        }

        return false;
    }

    public function getPropertyMimetype()
    {
        if($metadata = $this->_adapter->getMetadata())
        {
            if(isset($metadata['mimetype'])) {
                return $metadata['mimetype'];
            }
        }

        return false;
    }

    public function getPropertyWidth()
    {
        if($this->isImage())
        {
            $size = $this->_adapter->getImageSize();

            if ($size !== false) {
                return $size['width'];
            }
        }

        return false;
    }

    public function getPropertyHeight()
    {
        if($this->isImage())
        {
            $size = $this->_adapter->getImageSize();

            if ($size !== false) {
                return $size['height'];
            }
        }

        return false;
    }

    public function getPropertyMetadata()
    {
        return $this->_adapter->getMetadata();
    }

    public function getPropertyExifComment()
    {
            if(!$this->isImage()){
                 return false;
            }

            $exif = $this->_adapter->readExifData();

            return isset($exif['COMMENT']) ? implode(' ', $exif['COMMENT']) : array();
    }

    public function toArray()
    {
        $data = parent::toArray();

        unset($data['file']);
		unset($data['contents']);

		$data['metadata'] = $this->metadata;

		if ($this->isImage()) {
			$data['type'] = 'image';
		}

        return $data;
    }

    public function isImage()
    {
        return in_array(strtolower($this->extension), static::$extension_type_map['image']);
    }
    
    public function isVideo()
    {
        return in_array(strtolower($this->extension), static::$extension_type_map['video']);
    }

    /**
     * Invoke a command handler
     *
     * @param string             $method    The name of the method to be executed
     * @param KCommandInterface  $command   The command
     * @return mixed Return the result of the handler.
     */
    public function invokeCommandCallback($method, KCommandInterface $command)
    {
        return $this->$method($command);
    }
}

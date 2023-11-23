<?php
/**
 * Joomlatools Framework - https://www.joomlatools.com/developer/framework/
 *
 * @copyright	Copyright (C) 2011 Johan Janssens and Timble CVBA. (http://www.timble.net)
 * @license		GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link		http://github.com/joomlatools/joomlatools-framework-files for the canonical source repository
 */

/**
 * File Local Adapter
 *
 * @author  Ercan Ozkaya <https://github.com/ercanozkaya>
 * @package Koowa\Component\Files
 */
class ComFilesAdapterFile extends ComFilesAdapterAbstract
{
    public static $image_extensions = array('jpg', 'jpeg', 'gif', 'png', 'tiff', 'tif', 'xbm', 'bmp');

    protected $_metadata;

	public function getMetadata()
	{
		if ($this->_handle && empty($this->_metadata))
        {
			$this->_metadata = array(
				'extension' => strtolower(pathinfo($this->_handle->getFilename(), PATHINFO_EXTENSION)),
				'mimetype'  => $this->getObject('com:files.mixin.mimetype')->getMimetype($this->_path)
			);

            try
            {
                if ($this->_handle->isReadable())
                {
                    $this->_metadata += array(
                        'size'          => $this->_handle->getSize(),
                        'modified_date' => $this->_handle->getMTime()
                    );

                    if (in_array($this->_metadata['extension'], self::$image_extensions) && $image_size = $this->getImageSize()) {
                        $this->_metadata['image'] = $image_size;
                    }
                }
			}
            catch (RunTimeException $e) {}
		}

		return $this->_metadata;
	}

	public function getImageSize()
	{
	    if ($this->isLocal()) {
            $result = @getimagesize($this->_path);
        } else {
	        $result = $this->_getimagesize();
        }

		if ($result)
		{
            if (count($result) > 2) {
                $result = array_slice($result, 0, 2);
            }

            $result = array('width' => $result[0], 'height' => $result[1]);
		}

		return $result;
	}

    /**
     * Alternative method to GD getimagesize for getting image size without having to download
     * the whole file
     *
     * @see https://mtekk.us/archives/guides/check-image-dimensions-without-getimagesize/
     * @see http://php.net/manual/fr/function.getimagesize.php#88793
     * @see http://php.net/manual/fr/function.getimagesize.php#122015
     *
     * @return array|bool An array containing the image size array($width, $height) on success,
     * false otherwise
     */
	protected function _getimagesize()
    {
        $result = false;

        if ($extension = strtolower(pathinfo($this->_handle->getFilename(), PATHINFO_EXTENSION)))
        {
            switch($extension)
            {
                case 'png':
                    $result = $this->_getPngSize();
                    break;
                case 'jpeg':
                case 'jpg':
                    $result = $this->_getJpegSize();
                    break;
                case 'gif':
                    $result = $this->_getGifSize();
                    break;
                default:
                    break;
            }
        }


        return $result;
    }

    /**
     * PNG image size getter
     *
     * @return array|bool An array containing the image size, false if method is unable to determine it
     */
    protected function _getPngSize()
    {
        $size = false;

        $handle = fopen($this->_path, "rb", false, $this->_getFileStreamOptions());

        if ($handle && !feof($handle))
        {
            $block = fread($handle, 24);

            if ($block[0] == "\x89" &&
                $block[1] == "\x50" &&
                $block[2] == "\x4E" &&
                $block[3] == "\x47" &&
                $block[4] == "\x0D" &&
                $block[5] == "\x0A" &&
                $block[6] == "\x1A" &&
                $block[7] == "\x0A")
            {
                if ($block[12] . $block[13] . $block[14] . $block[15] === "\x49\x48\x44\x52")
                {
                    $width  = unpack('H*', $block[16] . $block[17] . $block[18] . $block[19]);
                    $width  = hexdec($width[1]);

                    $height = unpack('H*', $block[20] . $block[21] . $block[22] . $block[23]);
                    $height = hexdec($height[1]);

                    $size = array($width, $height);
                }
            }
        }

        return $size;
    }

    /**
     * GIF image size getter
     *
     * @return array|bool An array containing the image size, false if method is unable to determine it
     */
    protected function _getGifSize()
    {
        $size = false;

        $handle = fopen($this->_path, "rb", false, $this->_getFileStreamOptions());

        if ($handle && !feof($handle))
        {
            $block = fread($handle, 10);

            if ($block[0] == "\x47" &&
                $block[1] == "\x49" &&
                $block[2] == "\x46" &&
                $block[3] == "\x38" &&
                (($block[4] == "\x37" && $block[5] == "\x61") || ($block[4] == "\x39" && $block[5] == "\x61"))
            )
            {
                $width  = unpack('H*', $block[7] . $block[6]);
                $width  = hexdec($width[1]);

                $height = unpack('H*', $block[9] . $block[8]);
                $height = hexdec($height[1]);

                $size = array($width, $height);
            }
        }

        return $size;
    }

    /**
     * JPEG image size getter
     *
     * @see https://www.media.mit.edu/pia/Research/deepview/exif.html
     *
     * @return array|bool An array containing the image size, false if method is unable to determine it
     */
    protected function _getJpegSize()
    {
        $size = false;

        $handle = fopen($this->_path, 'rb', false, $this->_getFileStreamOptions());

        $block = NULL;

        $length = 8192;

        if ($handle && !feof($handle))
        {
            $block = fread($handle, $length);

            $i = 0;

            if ($block[$i] == "\xFF" && $block[$i + 1] == "\xD8" && $block[$i + 2] == "\xFF" && ($block[$i + 3] == "\xE0" || $block[$i + 3] == "\xE1"))
            {
                if ($block[$i + 3] == "\xE0"){
                    $marker = "\x4A\x46\x49\x46\x00"; // JFIF format
                } else {
                    $marker = "\x45\x78\x69\x66\x00\x00"; // EXIF format
                }

                while($i < 5)
                {
                    // Look for APP0 marker
                    $pos = strpos($block,$marker);

                    if ($pos === false)
                    {
                        // Keep trying

                        $block .= fread($handle, $length);

                        $i++;
                    }
                    else break;
                }
                
                if ($pos !== false)
                {
                    $block_size = unpack("H*", $block[$pos - 2] . $block[$pos - 1]);
                    $block_size = hexdec($block_size[1]);

                    $pos -= 2;

                    while (!feof($handle))
                    {
                        while (strlen($block) - $pos < $block_size) {
                            $block .= fread($handle, $length); // Make sure we have read enough for the next block test
                        }

                        $pos += $block_size;

                        if ($block[$pos] == "\xFF")
                        {
                            // New block detected, check for SOF marker
                            $sof_marker = array(
                                "\xC0",
                                "\xC1",
                                "\xC2",
                                "\xC3",
                                "\xC5",
                                "\xC6",
                                "\xC7",
                                "\xC8",
                                "\xC9",
                                "\xCA",
                                "\xCB",
                                "\xCD",
                                "\xCE",
                                "\xCF"
                            );

                            if (in_array($block[$pos + 1], $sof_marker))
                            {
                                // SOF marker detected. Width and height information is contained in bytes 4-7 after this byte.
                                $size_data = $block[$pos + 2] . $block[$pos + 3] . $block[$pos + 4] . $block[$pos + 5] . $block[$pos + 6] . $block[$pos + 7] . $block[$pos + 8];

                                $unpacked = unpack("H*", $size_data);
                                $unpacked = $unpacked[1];

                                $height = hexdec($unpacked[6] . $unpacked[7] . $unpacked[8] . $unpacked[9]);
                                $width  = hexdec($unpacked[10] . $unpacked[11] . $unpacked[12] . $unpacked[13]);

                                $size = array($width, $height);

                                break;
                            }
                            else
                            {
                                // Skip block marker and read block size
                                $pos += 2;

                                $block_size = unpack("H*", $block[$pos] . $block[$pos + 1]);
                                $block_size = hexdec($block_size[1]);
                            }
                        }
                        else break;
                    }
                }
            }
        }

        return $size;
    }

	public function move($target)
	{
		$result = false;
		$dir = dirname($target);

		if (!is_dir($dir)) {
			$result = mkdir($dir, 0755, true);
		}

		if (is_dir($dir) && is_writable($dir)) {
			$result = rename($this->_path, $target);
		}

		if ($result) {
			$this->setPath($target);
			clearstatcache();
		}

		return (bool) $result;
	}

	public function copy($target)
	{
		$result = false;
		$dir = dirname($target);

		if (!is_dir($dir)) {
			$result = mkdir($dir, 0755, true);
		}

		if (is_dir($dir) && is_writable($dir)) {
			$result = copy($this->_path, $target);
		}

		if ($result) {
			$this->setPath($target);
			clearstatcache();
		}

		return (bool) $result;
	}


	public function create()
	{
		$result = true;

		if (!is_file($this->_path)) {
			$result = touch($this->_path);
		}

		return $result;
	}

	public function delete()
	{
		$return = false;

		if (is_file($this->_path)) {
			$return = unlink($this->_path);
		}

		if ($return) {
			$this->_handle = null;
		}

		return $return;
	}

	public function read()
	{
		return @file_get_contents($this->_path);
	}

	public function write($data)
	{
		$result = false;

        $path = $this->getObject('com:files.adapter.folder', array('path' => dirname($this->_path)));

        if (!$path->exists()) {
            $path->create();
        }

		if (is_writable(dirname($this->_path)))
		{
			if ($data instanceof SplFileObject)
            {
			    if ($source = $data->getRealPath())
                {
                    $target = $this->_path;

                    $this->setPath($source);

                    $result = $this->copy($target);
                }
                else
                {
                    $handle = @fopen($this->_path, 'w');

                    if ($handle)
                    {
                        foreach ($data as $line) {
                            $result = (fwrite($handle, $line) !== false);
                        }

                        fclose($handle);
                    }
                }
			}
			elseif (is_uploaded_file(str_replace(chr(0), '', $data)))
            {
                $result = move_uploaded_file($data, $this->_path);
            }
            elseif (is_string($data))
            {
                $result = file_put_contents($this->_path, $data);
            }
		}

		if ($result)
        {
			$this->_metadata = null;
			clearstatcache();
		}

		return (bool) $result;
	}

  public function readExifData()
	{
	    $result = null;

	    if (function_exists('exif_read_data')) {
            $result = @exif_read_data($this->_path, null);
        }

  		return $result;
	}

	public function exists()
	{
	    if ($this->isLocal()) {
            $result = is_file($this->_path); 
        } else {
            $result = @fopen($this->_path, 'r', false, $this->_getFileStreamOptions()) !== false; 
        }
        
		return $result;
    }
    
    /**
     * Get file stream options
     * Turn off ssl verification in dev machines
     * 
     * @return resource
     */
    protected function _getFileStreamOptions()
    {
        $result = stream_context_create([]);

        if (in_array($_SERVER['REMOTE_ADDR'], ['33.33.33.1', '127.0.0.1']))
        {
            $options = array(
                "ssl" => array(
                    "verify_peer" => false,
                    "verify_peer_name" => false
                )
            );
    
            $result = stream_context_create($options);
        }

        return $result;
    }
}

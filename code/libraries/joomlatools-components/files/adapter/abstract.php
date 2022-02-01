<?php
/**
 * Joomlatools Framework - https://www.joomlatools.com/developer/framework/
 *
 * @copyright	Copyright (C) 2011 Johan Janssens and Timble CVBA. (http://www.timble.net)
 * @license		GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link		http://github.com/joomlatools/joomlatools-framework-files for the canonical source repository
 */

/**
 * Abstract Local Adapter
 *
 * @author  Ercan Ozkaya <https://github.com/ercanozkaya>
 * @package Koowa\Component\Files
 */
abstract class ComFilesAdapterAbstract extends KObject
{
	/**
	 * Path to the node
	 */
	protected $_path = null;

	/**
	 * A pointer for the FileInfo object
	 */
	protected $_handle = null;

    /**
     * @var bool Tells if the adapter points to a local resource
     */
	protected $_local;

	public function __construct(KObjectConfig $config)
	{
		parent::__construct($config);

        $this->setPath($config->path);
	}

	protected function _initialize(KObjectConfig $config)
	{
        $config->append(array('path' => ''));

		parent::_initialize($config);
	}

	public function isLocal()
    {
        return (bool) $this->_local;
    }

	public function setPath($path)
	{
		$path = $this->normalize($path);

		$this->_path = $path;
		$this->_handle = new SplFileInfo($path);

		$this->_metadata = null;

        $parts = parse_url($this->_path);

        $this->_local = true;

        if (isset($parts['scheme']))
        {
            $scheme = $parts['scheme'];

            if ($scheme === 'file') {
                $this->_path = str_replace('file://', '', $this->_path);
            } else {
                $this->_local = false;
            }
        }

		return $this;
	}

	public function getName()
	{
        $path = $this->_handle->getBasename();

		return $this->normalize(\Koowa\basename($path));
	}

	public function getPath()
	{
		return $this->normalize($this->_handle->getPathname());
	}

	public function getDirname()
	{
		return $this->normalize(dirname($this->_handle->getPathname()));
	}

	public function getRealPath()
	{
		return $this->_path;
	}

	public function normalize($string)
	{
		return str_replace('\\', '/', $string);
	}
}

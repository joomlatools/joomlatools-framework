<?php
/**
 * Joomlatools Framework - https://www.joomlatools.com/developer/framework/
 *
 * @copyright	Copyright (C) 2011 Johan Janssens and Timble CVBA. (http://www.timble.net)
 * @license		GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link		http://github.com/joomlatools/joomlatools-framework-files for the canonical source repository
 */
/**
 * File Uploadable Filter
 *
 * @author  Ercan Ozkaya <https://github.com/ercanozkaya>
 * @package Koowa\Component\Files
 */
class ComFilesFilterFileUploadable extends KFilterChain
{
	public function __construct(KObjectConfig $config)
	{
		parent::__construct($config);

		$this->addFilter($this->getObject('com:files.filter.file.name'), self::PRIORITY_HIGH);

		$this->addFilter($this->getObject('com:files.filter.file.extension'));
		$this->addFilter($this->getObject('com:files.filter.file.mimetype'));
		$this->addFilter($this->getObject('com:files.filter.file.size'));
	}
}

<?php
/**
 * @package     Joomlatools Migrator
 * @copyright   Copyright (C) 2016 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        http://www.joomlatools.com
 */

class ComMigratorControllerMigrator extends KControllerView implements KControllerModellable
{
    /**
     * The time in seconds before the working folder of an abandoned run gets removed.
     *
     * @var int
     */
    const FOLDER_LIFETIME = 86400;

    /**
     * Model object or identifier (com://APP/COMPONENT.model.NAME)
     *
     * @var	string|object
     */
    protected $_model;

    /**
     * Temporary folder for migration files
     * @var string
     */
    protected $_temporary_folder;

    /**
     * Cache of extension versions
     *
     * @var array
     */
    protected $_versions = array();

    public function __construct(KObjectConfig $config)
    {
        parent::__construct($config);

        // Set the model identifier
        $this->_model = $config->model;
    }

    protected function _initialize(KObjectConfig $config)
    {
        $config->append(array(
            'formats'       => array('json', 'binary'),
            'model'         => 'model.empty',
            'folder_prefix' => 'migrator'
        ));

        parent::_initialize($config);
    }

    /**
     * Returns the working folder of the current run.
     *
     * A run spans multiple requests, the session keeps track of its folder in between.
     *
     * @return string|null The folder path, null if there's no run going on.
     */
    public function getTemporaryFolder()
    {
        if (!$this->_temporary_folder) {
            $this->_temporary_folder = $this->getObject('user')->get($this->_getSessionContainer(), null);
        }

        return $this->_temporary_folder;
    }

    /**
     * @param string $temporary_folder
     * @return ComMigratorControllerMigrator
     */
    public function setTemporaryFolder($temporary_folder)
    {
        $this->_temporary_folder = $temporary_folder;

        return $this;
    }

    /**
     * Creates the working folder for a new run.
     *
     * Each run gets its own folder so that runs do not interfere with each other. This is also when folders that are
     * no longer in use get removed.
     *
     * @throws RuntimeException If the folder cannot be created.
     * @return string The folder path.
     */
    public function createTemporaryFolder()
    {
        $this->deleteTemporaryFolder();

        $root = $this->_getTemporaryRoot();

        $this->_deleteExpiredFolders($root);

        $folder = sprintf('%s/%s_%s', $root, $this->getConfig()->folder_prefix, bin2hex(random_bytes(16)));

        if (!mkdir($folder, 0700, true)) {
            throw new RuntimeException('The working folder could not be created');
        }

        $this->getObject('user')->set($this->_getSessionContainer(), $folder);
        $this->setTemporaryFolder($folder);

        return $folder;
    }

    /**
     * Deletes the working folder of the current run.
     *
     * @return $this
     */
    public function deleteTemporaryFolder()
    {
        if ($folder = $this->getTemporaryFolder())
        {
            $this->_forgetTemporaryFolder();

            if ($this->_isTemporaryFolder($folder)) {
                $this->_deleteFolder($folder);
            }
        }

        return $this;
    }

    /**
     * Returns the working folder of a run that must already be going on.
     *
     * @throws RuntimeException If the run has lost track of its folder.
     * @return string The folder path.
     */
    protected function _getCurrentTemporaryFolder()
    {
        $folder = $this->getTemporaryFolder();

        if (!$folder || !is_dir($folder)) {
            throw new RuntimeException('The working folder could not be found, please start over');
        }

        return $folder;
    }

    /**
     * Makes the controller lose track of the working folder, leaving the folder itself in place.
     *
     * @return $this
     */
    protected function _forgetTemporaryFolder()
    {
        $this->getObject('user')->set($this->_getSessionContainer(), null);
        $this->setTemporaryFolder(null);

        return $this;
    }

    /**
     * Returns the folder holding the working folders.
     *
     * @throws RuntimeException If no writable folder is found.
     * @return string The folder path.
     */
    protected function _getTemporaryRoot()
    {
        $candidates = array(JFactory::getConfig()->get('tmp_path'), JPATH_ROOT.'/tmp');

        foreach ($candidates as $folder)
        {
            if ($folder && @is_dir($folder) && is_writable($folder)) {
                return rtrim($folder, '\\/');
            }
        }

        throw new RuntimeException('Please make sure your Joomla tmp directory is writable');
    }

    /**
     * Tells if a path is a working folder of this controller.
     *
     * @param string $folder The folder path.
     * @return bool
     */
    protected function _isTemporaryFolder($folder)
    {
        $pattern = sprintf('/^%s_[a-f0-9]{32}$/', preg_quote($this->getConfig()->folder_prefix, '/'));

        return is_dir($folder) && !is_link($folder) && preg_match($pattern, basename($folder));
    }

    /**
     * Deletes the working folders of runs that were abandoned.
     *
     * @param string $root The folder holding the working folders.
     * @return $this
     */
    protected function _deleteExpiredFolders($root)
    {
        $folders = glob(sprintf('%s/%s_*', $root, $this->getConfig()->folder_prefix), GLOB_ONLYDIR | GLOB_NOSORT);

        if ($folders)
        {
            foreach ($folders as $folder)
            {
                $modified = @filemtime($folder);

                if ($modified && $modified < (time() - self::FOLDER_LIFETIME) && $this->_isTemporaryFolder($folder)) {
                    $this->_deleteFolder($folder);
                }
            }
        }

        return $this;
    }

    /**
     * Recursively deletes a directory with all of its contents.
     *
     * @param string $folder The folder to delete.
     *
     * @return bool True is successful, false otherwise.
     */
    protected function _deleteFolder($folder)
    {
        $files = array_diff(scandir($folder), array('.', '..'));

        foreach ($files as $file)
        {
            $node = sprintf('%s/%s', $folder, $file);

            if (is_dir($node) && !is_link($node)) {
                $this->_deleteFolder($node);
            }
            else unlink($node);
        }

        return rmdir($folder);
    }

    /**
     * Session container getter.
     *
     * Provides a session variable for storing the location of the working folder.
     *
     * @return string
     */
    protected function _getSessionContainer()
    {
        return 'session.'.$this->getIdentifier().'.folder';
    }

    /**
     * Extension version getter.
     *
     * @param  string $extension Extension name (without com_ prefix)
     * @return string|null The version number, null if the extension wasn't found.
     */
    public function getVersion($extension)
    {
        if (!isset($this->_versions[$extension]))
        {
            $version = null;

            $manifests = array(sprintf('%s.xml', $extension), 'manifest.xml');

            foreach ($manifests as $manifest)
            {
                $file = sprintf('%s/administrator/components/com_%s/%s', JPATH_ROOT, $extension, $manifest);

                if (file_exists($file))
                {
                    $manifest = simplexml_load_file($file);

                    if ($manifest->version) {
                        $version = (string) $manifest->version;
                    }

                    break;
                }
            }

            $this->_versions[$extension] = $version;
        }

        return $this->_versions[$extension];
    }

    public function getView()
    {
        $view = parent::getView();

        //Set the model in the view
        $view->setModel($this->getModel());

        return $view;
    }

    /**
     * Get the model object attached to the controller
     *
     * @throws	\UnexpectedValueException	If the model doesn't implement the ModelInterface
     * @return	KModelInterface
     */
    public function getModel()
    {
        if(!$this->_model instanceof KModelInterface)
        {
            //Make sure we have a model identifier
            if(!($this->_model instanceof KObjectIdentifier)) {
                $this->setModel($this->_model);
            }

            $this->_model = $this->getObject($this->_model);

            if(!$this->_model instanceof KModelInterface)
            {
                throw new UnexpectedValueException(
                    'Model: '.get_class($this->_model).' does not implement KModelInterface'
                );
            }

            //Inject the request into the model state
            $this->_model->getState()->insert('status', 'cmd');
            $this->_model->setState($this->getRequest()->query->toArray());
        }

        return $this->_model;
    }

    /**
     * Method to set a model object attached to the controller
     *
     * @param	mixed	$model An object that implements KObjectInterface, KObjectIdentifier object
     * 					       or valid identifier string
     * @return	KControllerView
     */
    public function setModel($model)
    {
        if(!($model instanceof KModelInterface))
        {
            if(is_string($model) && strpos($model, '.') === false )
            {
                // Model names are always plural
                if(KStringInflector::isSingular($model)) {
                    $model = KStringInflector::pluralize($model);
                }

                $identifier			= $this->getIdentifier()->toArray();
                $identifier['path']	= array('model');
                $identifier['name']	= $model;

                $identifier = $this->getIdentifier($identifier);
            }
            else $identifier = $this->getIdentifier($model);

            $model = $identifier;
        }

        $this->_model = $model;

        return $this->_model;
    }
}
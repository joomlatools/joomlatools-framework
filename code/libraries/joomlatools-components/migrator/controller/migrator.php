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

        $this->setTemporaryFolder($config->folder);
    }

    protected function _initialize(KObjectConfig $config)
    {
        $config->append(array(
            'formats'   => array('json', 'binary'),
            'model'     => 'model.empty'
        ));

        parent::_initialize($config);
    }

    /**
     * @return string
     */
    public function getTemporaryFolder()
    {
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
<?php
/**
 * Tagging Component for Joomlatools Framework - http://developer.joomlatools.com/framework
 *
 * @copyright   Copyright (C) 2016 Johan Janssens and Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        http://github.com/joomlatools/joomlatools-framework-tags for the canonical source repository
 */

/**
 * Tag Controller
 *
 * @author  Johan Janssens <http://github.com/johanjanssens>
 * @package Koowa\Component\Tags
 */
class ComTagsControllerTag extends KControllerModel
{
    /**
     * Constructor.
     *
     * @param KObjectConfig $config Configuration options.
     */
    protected function _initialize(KObjectConfig $config)
    {
        $config->append(array(
            'model' => 'com:tags.model.tags'
        ));

        parent::_initialize($config);
    }

    /**
     * Get the model object attached to the controller
     *
     * This method will set the model table name to [component]_tags
     *
     * @throws  \UnexpectedValueException   If the model doesn't implement the ModelInterface
     * @return  ComTagsModelTags
     */
    public function getModel()
    {
        if(!$this->_model instanceof KModelInterface)
        {
            $package = $this->getIdentifier()->package;
            $this->_model = $this->getObject($this->_model, array('table' => $package.'_tags'));

            //Inject the request into the model state
            $this->_model->setState($this->getRequest()->query->toArray());
        }

        return $this->_model;
    }
}

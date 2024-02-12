<?php
/**
 * Joomlatools Framework - https://www.joomlatools.com/developer/framework/
 *
 * @copyright   Copyright (C) 2007 Johan Janssens and Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://github.com/joomlatools/joomlatools-framework for the canonical source repository
 */

/**
 * Listbox Template Helper
 *
 * @author  Johan Janssens <https://github.com/johanjanssens>
 * @package Koowa\Library\Template\Helper
 */
class KTemplateHelperListbox extends KTemplateHelperSelect
{
    /**
     * Adds the option to enhance the select box using Select2
     *
     * @param array|KObjectConfig $config
     * @return string
     */
    public function optionlist($config = array())
    {
        $translator = $this->getObject('translator');

        $config = new KObjectConfigJson($config);
        $config->append(array(
            'prompt'    => '- '.$translator->translate('Select').' -',
            'deselect'  => false,
            'options'   => array(),
            'select2'   => false,
            'attribs'   => array(),
        ));

        if ($config->deselect && !$config->attribs->multiple)
        {
            $deselect = $this->option(array('value' => '', 'label' => $config->prompt));
            $options  = $config->options->toArray();
            array_unshift($options, $deselect);
            $config->options = $options;
        }

        if ($config->attribs->multiple && $config->name && substr($config->name, -2) !== '[]') {
            $config->name .= '[]';
        }

        $html = '';

        if ($config->select2)
        {
            if (!$config->name) {
                $config->attribs->append(array(
                    'id' => 'select2-element-'.mt_rand(1000, 100000)
                ));
            }

            if ($config->deselect) {
                $config->attribs->append(array(
                    'data-placeholder' => $config->prompt
                ));
            }

            $config->append(array(
                'select2_options' => array(
                    'element' => $config->attribs->id ? 'select[id=\"'.$config->attribs->id.'\"]' : 'select[name=\"'.$config->name.'\"]',
                    'options' => array(
                        'allowClear'  => $config->deselect
                    )

                )
            ));

            $html .= $this->getTemplate()->createHelper('behavior')->select2($config->select2_options);
        }

        $html .= parent::optionlist($config);

        return $html;
    }

    /**
     * Generates an HTML enabled listbox
     *
     * @param   array   $config An optional array with configuration options
     * @return  string  Html
     */
    public function enabled( $config = array())
    {
        $config = new KObjectConfigJson($config);
        $config->append(array(
            'name'      => 'enabled',
            'attribs'   => array(),
            'deselect'  => true,
        ))->append(array(
            'selected'  => $config->{$config->name}
        ));

        $translator = $this->getObject('translator');
        $options    = array();

        $options[] = $this->option(array('label' => $translator->translate( 'Enabled' ) , 'value' => 1 ));
        $options[] = $this->option(array('label' => $translator->translate( 'Disabled' ), 'value' => 0 ));

        //Add the options to the config object
        $config->options = $options;

        return $this->optionlist($config);
    }

    /**
     * Generates an HTML published listbox
     *
     * @param   array   $config An optional array with configuration options
     * @return  string  Html
     */
    public function published($config = array())
    {
        $config = new KObjectConfigJson($config);
        $config->append(array(
            'name'      => 'enabled',
            'attribs'   => array(),
            'deselect'  => true,
        ))->append(array(
            'selected'  => $config->{$config->name}
        ));

        $translator = $this->getObject('translator');
        $options    = array();

        $options[] = $this->option(array('label' => $translator->translate('Published'), 'value' => 1 ));
        $options[] = $this->option(array('label' => $translator->translate('Unpublished') , 'value' => 0 ));

        //Add the options to the config object
        $config->options = $options;

        return $this->optionlist($config);
    }

    /**
     * Generates an HTML optionlist based on the distinct data from a model column.
     *
     * The column used will be defined by the name -> value => column options in cascading order.
     *
     * If no 'model' name is specified the model identifier will be created using the helper identifier. The model name
     * will be the pluralised package name.
     *
     * If no 'value' option is specified the 'name' option will be used instead. If no 'text'  option is specified the
     * 'value' option will be used instead.
     *
     * @param 	array 	$config An optional array with configuration options
     * @return	string	Html
     * @see __call()
     */
    protected function _render($config = array())
    {
        $config = new KObjectConfig($config);
        $config->append(array(
            'autocomplete' => false
        ));

        if($config->autocomplete) {
            $result = $this->_autocomplete($config);
        } else {
            $result = $this->_listbox($config);
        }

        return $result;
    }

    /**
     * Generates an HTML optionlist based on the distinct data from a model column.
     *
     * The column used will be defined by the name -> value => column options in
     * cascading order.
     *
     * If no 'model' name is specified the model identifier will be created using
     * the helper identifier. The model name will be the pluralised package name.
     *
     * If no 'value' option is specified the 'name' option will be used instead.
     * If no 'label' option is specified the 'value' option will be used instead.
     *
     * @param 	array|KObjectConfig 	$config An optional array with configuration options
     * @return	string	Html
     * @see __call()
     */
    protected function _listbox($config = array())
    {
        $config = new KObjectConfigJson($config);
        $config->append(array(
            'name'       => '',
            'attribs'    => array(),
            'model'      => KStringInflector::pluralize($this->getIdentifier()->package),
            'deselect'   => true
        ))->append(array(
            'value'      => $config->name,
            'selected'   => $config->{$config->name},
        ))->append(array(
            'label'      => $config->value,
        ))->append(array(
            'filter'     => array('sort' => $config->label),
        ));

        //Get the model object
        if(!$config->model instanceof KModelInterface)
        {
            $config->append(array(
                'identifier' => 'com://'.$this->getIdentifier()->domain.'/'.$this->getIdentifier()->package.'.model.'.$config->model
            ));

            $model = $this->getObject($config->identifier);
        }
        else $model = $config->model;

        $options = array();
        $state   = KObjectConfig::unbox($config->filter);
        $count   = $model->setState($state)->count();
        $offset  = 0;
        $limit   = 100;

        /*
         * We fetch data gradually here and convert it directly into options
         * This only loads 100 entities into memory at once so that
         * we do not run into memory limit issues
         */
        while ($offset < $count)
        {
            $entities = $model->setState($state)->limit($limit)->offset($offset)->fetch();

            foreach ($entities as $entity) {
                $options[] = $this->option(array('label' => $entity->{$config->label}, 'value' => $entity->{$config->value}));
            }

            $offset += $limit;
        }

        //Compose the selected array
        if($config->selected instanceof KModelEntityInterface)
        {
            $selected = array();
            foreach($config->selected as $entity) {
                $selected[] = $entity->{$config->value};
            }

            $config->selected = $selected;
        }

        //Add the options to the config object
        $config->options = $options;

        return $this->optionlist($config);
    }

    /**
     * Renders a listbox with autocomplete behavior
     *
     * @see    ComKoowaTemplateHelperBehavior::_listbox
     *
     * @param  array|KObjectConfig    $config
     * @return string	The html output
     */
    protected function _autocomplete($config = array())
    {
        $config = new KObjectConfigJson($config);
        $config->append(array(
            'name'     => '',
            'attribs'  => array(
                'id' => 'select2-element-'.mt_rand(1000, 100000)
            ),
            'model'    => KStringInflector::pluralize($this->getIdentifier()->package),
            'validate' => true,
            'prompt'   => '- '.$this->getObject('translator')->translate('Select').' -',
            'deselect' => true,
        ))->append(array(
            'element'    => '#'.$config->attribs->id,
            'options'    => array('multiple' => (bool) $config->attribs->multiple),
            'value'      => $config->name,
            'selected'   => $config->{$config->name},
        ))->append(array(
            'label'      => $config->value,
        ))->append(array(
            'text'       => $config->label,
            'filter'     => array('sort' => $config->label),
        ));

        //Add name to attribs
        $config->attribs->name = $config->name;

        //Get the model object
        if(!$config->model instanceof KModelInterface)
        {
            $config->append(array(
                'identifier' => 'com://'.$this->getIdentifier()->domain.'/'.$this->getIdentifier()->package.'.model.'.$config->model
            ));

            $model = $this->getObject($config->identifier);
        }
        else $model = $config->model;

        //Get the autocomplete url
        if (!$config->url)
        {
            $identifier = $this->getIdentifier($model);
            $parts      = array(
                'component' => $identifier->package,
                'view'      => $identifier->name,
                'format'    => 'json'
            );

            if ($config->filter) {
                $parts = array_merge($parts, KObjectConfig::unbox($config->filter));
            }

            $config->url = $this->getTemplate()->route($parts, false, false);
        }

        $html = '';
        $html .= $this->getTemplate()->createHelper('behavior')->autocomplete($config);

        //Get the selected items
        $options = array();
        if ((is_scalar($config->selected) || is_null($config->selected)) ? !empty($config->selected) : count($config->selected))
        {
            $selected = $config->selected;

            if(!$selected instanceof KModelEntityInterface)
            {
                $selected = $model->setState(KObjectConfig::unbox($config->filter))
                                  ->setState(array($config->value => KObjectConfig::unbox($selected)))
                                  ->fetch();
            }

            foreach($selected as $entity)
            {
                $options[]  = $this->option(array(
                    'value' => $entity->{$config->value},
                    'label' => $entity->{$config->label},
                    'attribs' => array('selected' => true)
                ));
            }
        }

        $html .= $this->optionlist(array(
            'name'     => $config->name,
            'id'       => $config->id,
            'options' => $options,
            'deselect' => false,
            'select2'  => false,
            'attribs'  => $config->attribs
        ));

        return $html;
    }

    /**
     * Search the mixin method map and call the method or trigger an error
     *
     * This function check to see if the method exists in the mixing map if not it will call the 'listbox' function.
     * The method name will become the 'name' in the config array.
     *
     * This can be used to auto-magically create select filters based on the function name.
     *
     * @param  string   $method The function name
     * @param  array    $arguments The function arguments
     * @throws BadMethodCallException   If method could not be found
     * @return mixed The result of the function
     */
    public function __call($method, $arguments)
    {
        if(!in_array($method, $this->getMethods()))
        {
            $config = $arguments[0];
            if(!isset($config['name'])) {
                $config['name']  = KStringInflector::singularize(strtolower($method));
            }

            return $this->_render($config);
        }

        return parent::__call($method, $arguments);
    }
}

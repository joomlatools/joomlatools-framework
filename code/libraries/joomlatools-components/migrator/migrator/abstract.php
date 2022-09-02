<?php
/**
 * @package     Joomlatools Migrator
 * @copyright   Copyright (C) 2016 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        http://www.joomlatools.com
 */

/**
 * Abstract Migrator Class.
 *
 * @method mixed run(string $job)
 */
abstract class ComMigratorMigratorAbstract extends KControllerAbstract implements ComMigratorMigratorInterface
{
    /**
     * A list of jobs.
     *
     * @var KObjectArray
     */
    protected $_jobs;

    /**
     * Human readable label to display in forms
     *
     * @var string
     */
    protected $_label;

    /**
     * Constructor.
     *
     * @param KObjectConfig $config The configuration object.
     */
    public function __construct(KObjectConfig $config)
    {
        parent::__construct($config);

        $this->_jobs = $this->getObject('object.array');

        if (!$config->label) {
            $config->label = $this->getIdentifier()->name;
        }

        if (!$config->extension) {
            $config->extension = $this->getIdentifier()->name;
        }

        $this->setLabel($config->label);

        $this->addJobs($config->jobs);
    }

    /**
     * Initializes the options for the object
     *
     * Called from {@link __construct()} as a first step of object instantiation.
     *
     * @param   KObjectConfig $config Configuration options.
     */
    protected function _initialize(KObjectConfig $config)
    {
        $config->append(array(
            'jobs'      => array(),
            'label'     => '',
            'extension' => ''
        ));

        parent::_initialize($config);

        $behaviors = $config->behaviors->toArray();

        if (in_array('permissible', $behaviors))
        {
            $key = array_search('permissible', $behaviors);
            unset($behaviors[$key]);

            $config->behaviors = $behaviors;
        }
    }

    /**
     * @return string
     */
    public function getLabel()
    {
        return $this->_label;
    }

    /**
     * @param string $label
     *
     * @return $this
     */
    public function setLabel($label)
    {
        $this->_label = $label;

        return $this;
    }

    /**
     * Runs a job
     *
     * @param ComMigratorMigratorContext $context
     * @return int
     */
    protected function _actionRun(ComMigratorMigratorContext $context)
    {
        $context->result = null;
        $job     = $context->param;

        if ($job && $this->hasJob($job))
        {
            $job     = $this->getJob($job);
            $request = $context->getRequest();

            // Push POST data from the request to the job object.
            if ($request->isPost() && $request->getData()->has('data'))
            {
                $job->append(array(
                    'data' =>  $request->getData()->data
                ));
            }

            $action = $job->action;

            if (!in_array($action, $this->getActions())) {
                $this->addBehavior($job->behavior);
            }

            $job->append($this->getConfig());

            $context = $this->getContext();
            $context->setJob($job);

            $context->result = $this->execute($action, $context);

            if (is_bool($context->result) && $context->result == false) {
                $context->response->setStatus(500);
            }

            $response = array('result' => KObjectConfig::unbox($context->result));

            if ($error = $context->getError()) {
                $response['error'] = $error;
            }

            $context->response->setContent(json_encode($response));
        }
        else {
            throw new RuntimeException('Invalid job');
        }

        return $context->result;
    }

    /**
     * Jobs setter.
     *
     * @param array $jobs A list of jobs.
     *
     * @return $this
     */
    public function addJobs($jobs)
    {
        foreach ($jobs as $key => $config) {
            $this->addJob($key, $config);
        }

        return $this;
    }

    /**
     * Adds a job to the queue.
     *
     * @param      string $name   The job name.
     * @param      mixed  $config The job parameters.
     *
     * @return $this
     */
    public function addJob($name, $config)
    {
        $config = new KObjectConfig($config);
        $config->append(array(
            'name'     => $name,
            'action'   => $name,
            'behavior' => $config->action,
            'label'    => ucfirst($name)
        ));

        $this->_jobs->$name = $config;

        return $this;
    }

    /**
     * Removes a job from the list
     *
     * @param string $name Job name
     * @return $this
     */
    public function removeJob($name)
    {
        if ($this->hasJob($name)) {
            unset($this->_jobs->$name);
        }

        return $this;
    }

    /**
     * Returns an ordered iterator for job list
     *
     * @return SplQueue
     */
    public function getIterator()
    {
        $list = array();
        $jobs = $this->_jobs;

        // First pass, set priorities
        $i = 1.00;
        foreach ($jobs as $key => $config) {
            $list[$key] = $i;

            $i++;
        }

        // Second pass, move jobs to the correct order
        foreach ($jobs as $key => $config)
        {
            if ($config->after) {
                $list[$key] = $list[$config->after] + 0.01;
            }
        }

        asort($list);

        $queue = new SplQueue();

        foreach ($list as $job => $priority) {
            $queue->push($this->getJob($job));
        }

        return $queue;
    }

    /**
     * Job getter.
     *
     * @param string $name The job name.
     *
     * @return mixed The job.
     */
    public function getJob($name)
    {
        $job = null;

        if ($this->hasJob($name)) {
            $job = $this->_jobs->$name;
        }

        return $job;
    }

    /**
     * Checks if a job is supported by the exporter.
     *
     * @param string $name The job name.
     *
     * @return bool True if it is, false otherwise.
     */
    public function hasJob($name)
    {
        return (bool) isset($this->_jobs->$name);
    }

    /**
     * Gets the job context
     *
     * @return ComMigratorMigratorContext
     */
    public function getContext()
    {
        $context = new ComMigratorMigratorContext();
        $context->setSubject($this);
        $context->setRequest($this->getRequest());
        $context->setResponse($this->getResponse());
        $context->setUser($this->getUser());

        return $context;
    }

    /**
     * Returns extension name
     * @return string
     */
    public function getExtension()
    {
        return $this->getConfig()->extension;
    }
}
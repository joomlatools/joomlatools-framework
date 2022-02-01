<?php
/**
 * Joomlatools Framework - https://www.joomlatools.com/developer/framework/
 *
 * @copyright   Copyright (C) 2016 Johan Janssens and Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        http://github.com/joomlatools/joomlatools-framework-scheduler for the canonical source repository
 */

/**
 * Job behavior
 *
 * @author Ercan Ozkaya <https://github.com/ercanozkaya>
 * @package Koowa\Component\Scheduler
 *
 * @method void run(ComSchedulerJobInterface $job)
 */
class ComSchedulerControllerDispatcher extends KControllerAbstract implements ComSchedulerControllerDispatcherInterface
{
    /**
     * Model object or identifier (com://APP/COMPONENT.model.NAME)
     *
     * @var	string|object
     */
    protected $_model;

    /**
     * @param KObjectConfig $config
     */
    public function __construct(KObjectConfig $config)
    {
        parent::__construct($config);

        @set_time_limit(60);
        @ini_set('memory_limit', '256M');
        @ignore_user_abort(true);

        // Set the model identifier
        $this->_model = $config->model;
    }

    /**
     * Initializes the default configuration for the object
     *
     * Called from {@link __construct()} as a first step of object instantiation.
     *
     * @param   KObjectConfig $config Configuration options
     * @return void
     */
    protected function _initialize(KObjectConfig $config)
    {
        $config->append(array(
            'model'	=> 'com:scheduler.model.jobs',
            'jobs'  => array()
        ));

        parent::_initialize($config);
    }

    /**
     * Gets the job context
     *
     * @return ComSchedulerJobContextInterface
     */
    public function getContext()
    {
        $context = new ComSchedulerJobContext();
        $context->setSubject($this);
        $context->setRequest($this->getRequest());
        $context->setResponse($this->getResponse());
        $context->setUser($this->getUser());

        return $context;
    }

    /**
     * Runs a job
     *
     * @param ComSchedulerJobContextInterface $context
     * @return int
     */
    protected function _actionRun(ComSchedulerJobContextInterface $context)
    {
        if (!$context->param instanceof ComSchedulerJobInterface) {
            throw new UnexpectedValueException('Invalid job passed. Expecting ComSchedulerJobInterface');
        }

        /** @var ComSchedulerJobInterface $job */
        $job = $context->param;

        try {
            $context->log('Running '.$job->getIdentifier());

            $context->result = $job->run($context);
        }
        catch (Exception $e)
        {
            $context->log('Exception thrown: '.$e->getMessage());

            $context->result = ComSchedulerJobInterface::JOB_FAIL;
        }

        $context->log(sprintf('Ran %s with the result %s', $job->getIdentifier(), $context->result));

        return $context->result;
    }

    /**
     * Dispatches the next job in line
     *
     * @param ComSchedulerJobContextInterface $context
     * @return bool
     */
    protected function _actionDispatch(ComSchedulerJobContextInterface $context)
    {
        $start = microtime(true);

        if (($entity = $context->job) || ($entity = $this->getNextJob()))
        {
            // Set to running
            $entity->status = 1;
            $entity->save();

            try
            {
                $context->setTimeLimit(time()+15);
                $context->setState($entity->getState());

                $context->param  = $this->getObject($entity->id);

                $this->execute('run', $context);

                /*
                complete:
                    high priority: put it on the top of low priority queue
                    low priority:  put it on the bottom of low priority queue
                suspend:
                    high priority: put it on the top of high priority queue
                    low priority:  put it on the bottom of high priority queue
                */

                $entity->ordering = $context->param->isPrioritized() ? -PHP_INT_MAX : PHP_INT_MAX;

                if ($context->result === ComSchedulerJobInterface::JOB_SUSPEND) {
                    $entity->queue = 1;
                }
                else {
                    $entity->completed_on = gmdate('Y-m-d H:i:s');
                    $entity->queue = 0;
                }
            }
            catch (Exception $e) {}

            if ($context->result === ComSchedulerJobInterface::JOB_COMPLETE && !$this->_getNextRun($entity)) {
                $entity->delete();
            }
            else {
                // Stop the job
                $entity->status = 0;
                $entity->save();
            }
        }

        $context->setJobDuration(microtime(true) - $start);

        return $context->result;
    }

    /**
     * Syncs the jobs passed into the object config to the database
     *
     * Automatically creates the database table if necessary
     * Also handles job frequency updates
     *
     * @param ComSchedulerJobContextInterface $context
     */
    protected function _actionSynchronize(ComSchedulerJobContextInterface $context)
    {
        $model    = $this->getModel();
        $jobs     = $this->getConfig()->jobs->toArray();
        $current  = array();
        $existing = $model->fetch();

        // Add new jobs and update frequencies if needed
        foreach ($jobs as $identifier => $config)
        {
            if (is_numeric($identifier)) {
                $identifier = $config;
                $config = array();
            }

            $current[] = $identifier;

            $entity = $existing->find($identifier);

            try
            {
                if (!$entity)
                {
                    $entity = $model->create();
                    $entity->id = $identifier;
                    $entity->package = $this->getIdentifier($identifier)->getPackage();
                }

                $frequency = $this->getObject($identifier, $config)->getFrequency();

                if ($frequency !== $entity->frequency)
                {
                    $entity->frequency = $frequency;
                    $entity->save();
                }
            }
            catch (Exception $e) {}
        }

        foreach ($existing as $entity)
        {
            if (!in_array($entity->id, $current)) {
                $entity->delete();
            }
        }
    }

    /**
     * Picks the next job to run based on priority
     *
     * @return null|KDatabaseRowInterface
     */
    public function getNextJob()
    {
        $this->_quitStaleJobs();

        if ($this->getModel()->status(1)->count() === 0)
        {
            $high_priority = $this->getModel()->status(0)->sort('ordering')->queue(1)->fetch();

            if (count($high_priority) === 0)
            {
                $low_priority = $this->getModel()->status(0)->sort('ordering')->queue(0)->fetch();

                foreach ($low_priority as $job)
                {
                    if ($this->_isDue($job))
                    {
                        $job->queue = 1;

                        if ($job->save()) {
                            return $job;
                        }
                    }
                }
            }
            else
            {
                foreach ($high_priority as $job)
                {
                    if ($this->_isDue($job)) {
                        return $job;
                    }
                }
            }
        }

        return null;
    }

    protected function _afterDispatch(KControllerContextInterface $context)
    {
        $context->log(sprintf('Job took %.2f seconds', $context->getJobDuration()));

        $sleep_until = gmdate('Y-m-d H:i:s', $this->getNextRun());
        $last_run    = gmdate('Y-m-d H:i:s');

        $adapter = $this->getObject('database.adapter.mysqli');

        $query = $this->getObject('database.query.insert')
            ->replace()
            ->table('scheduler_metadata')
            ->values(['type' => 'metadata', 'sleep_until' => $sleep_until, 'last_run' => $last_run]);

        $adapter->execute($query);

        $context->sleep_until = $sleep_until;
    }

    public function getNextRun()
    {
        $this->_quitStaleJobs();

        $adapter = $this->getObject('database.adapter.mysqli');
        $query   = $this->getObject('database.query.select')->table('scheduler_jobs');

        $q1 = clone $query;
        $q1->columns('COUNT(*)')->where('status = 1');

        // There is a running job, keep hitting to make sure it finishes first
        if (!$adapter->select($q1, KDatabase::FETCH_FIELD))
        {
            $next_run = time() + (4 * 60 * 60); // at worst, run once every 4 hours for housekeeping

            $q2 = clone $query;
            $q2->table('scheduler_jobs')
                ->columns(array('completed_on', 'frequency'))
                ->where('status = 0')
                ->order('queue DESC, completed_on');

            $jobs = $adapter->select($q2, KDatabase::FETCH_OBJECT_LIST);

            foreach ($jobs as $job)
            {
                if ($job->completed_on === null) {
                    $next_run = time();
                    break; // next run is now
                }

                try
                {
                    if ($this->_isDue($job)) {
                        $next_run = time();
                        break;
                    }

                    $result = $this->_getNextRun($job);

                    if ($result)
                    {
                        $result = $result->getTimestamp();

                        if ($result < $next_run) {
                            $next_run = $result;
                        }
                    }
                }
                catch (Exception $e) {}
            }
        }
        else $next_run = time();

        return $next_run;
    }

    protected function _isDue($job)
    {
        $result = true;

        if ($job->completed_on !== null)
        {
            try {
                $cron = Cron\CronExpression::factory($job->frequency);
                $next = $cron->getNextRunDate(new DateTime($job->completed_on, new DateTimeZone('UTC')));
                $now  = new DateTime('now', new DateTimeZone('UTC'));
                $result = $next < $now;
            }
            catch (RuntimeException $e) {
                $result = true; // last run and it'll be deleted
            }
            catch (Exception $e) {
                $result = false;
            }
        }

        return $result;
    }

    protected function _getNextRun($job)
    {
        $result = false;

        try {
            $cron   = Cron\CronExpression::factory($job->frequency);
            $result = $cron->getNextRunDate(new DateTime('now', new DateTimeZone('UTC')));
        }
        catch (RuntimeException $e) {
            // never gonna run again :(
        }

        return $result;
    }

    /**
     * Quits jobs that are running for more than 5 minutes
     *
     * Uses a direct database query for speed
     */
    protected function _quitStaleJobs()
    {
        $table = $this->getModel()->getTable();
        $query = $this->getObject('database.query.update');

        $query
            ->table($table->getName())
            ->values(array('status = 0', 'modified_on = :now'))
            ->where('status = 1 AND :now > DATE_ADD(modified_on, INTERVAL 5 MINUTE)')
            ->bind(['now' => gmdate('Y-m-d H:i:s')]);

        $table->getAdapter()->update($query);
    }

    /**
     * Get the model object attached to the controller
     *
     * @throws  \UnexpectedValueException   If the model doesn't implement the ModelInterface
     * @return  KModelInterface
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
            $this->_model->setState($this->getRequest()->query->toArray());
        }

        $this->_model->getState()->reset();

        return $this->_model;
    }

    /**
     * Method to set a model object attached to the controller
     *
     * @param   mixed   $model An object that implements KObjectInterface, KObjectIdentifier object
     *                         or valid identifier string
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

                $identifier         = $this->getIdentifier()->toArray();
                $identifier['path'] = array('model');
                $identifier['name'] = $model;

                $identifier = $this->getIdentifier($identifier);
            }
            else $identifier = $this->getIdentifier($model);

            $model = $identifier;
        }

        $this->_model = $model;

        return $this->_model;
    }
}
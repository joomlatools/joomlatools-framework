<?php
/**
 * Joomlatools Framework - https://www.joomlatools.com/developer/framework/
 *
 * @copyright   Copyright (C) 2007 Johan Janssens and Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://github.com/joomlatools/joomlatools-framework for the canonical source repository
 */

/**
 * Event dispatcher subscriber
 * 
 * Lets users add a PHP file to `joomlatools-config/events/` folder that returns an array of event listeners.
 * 
 * Event names starting with onBefore/onAfter are treated as Koowa events while all others are registered on Joomla dispatcher.
 * 
 * For example the below file can be put into `joomlatools-config/events/example.php`:
 * ```php
 * <?php
 * 
 * return [
 *    'onAfterFilesFileControllerRead' => function(KEventInterface $event) {
 *         $file = $event->result;
 *         // do something with file
 *     },
 *     'onContentPrepare' => function($context, $article) {
 *         $article->title = 'foo';
 *     },
 * ];
 * ```
 *
 * @author  Johan Janssens <https://github.com/johanjanssens>
 * @package Koowa\Component\Koowa\Event\Subscriber
 */
class ComKoowaEventSubscriberEventdispatcher extends KEventSubscriberAbstract
{
    use ComKoowaEventTrait;

    public function onAfterApplicationInitialise(KEventInterface $event)
    {
        $path = Koowa::getInstance()->getRootPath().'/joomlatools-config/events';

        if(is_dir($path))
        {
            if($files = glob($path.'/*.php'))
            {
                foreach ($files as $file)
                {
                    try 
                    {
                        $listeners = include $file;

                        if (is_array($listeners)) 
                        {
                            foreach ($listeners as $event => $listener) 
                            {
                                if (is_callable($listener)) 
                                {
                                    if (str_starts_with($event, 'onBefore') || str_starts_with($event, 'onAfter')) {
                                        $this->getObject('event.publisher')->addListener($event, $listener);
                                    } 
                                    else $this->attachEventHandler($event, $listener);
                                }
                            }
                        }
                    } catch (\Exception $e) {
                        if (Koowa::isDebug()) throw $e;
                    }
                }
            }
        }
    }
}

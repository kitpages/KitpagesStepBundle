<?php
// test

namespace <<proxyNameSpace>>;

use Kitpages\StepBundle\Proxy\ProxyInterface;
use Kitpages\StepBundle\Step\StepEvent;
use Kitpages\StepBundle\KitpagesStepEvents;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Stopwatch\Stopwatch;

/**
 * This class is a proxy around a step method.
 * This proxy adds the following methods :
 * -
 *
 * @example
 */
class <<shortClassName>>
    extends <<originalClassName>>
    implements ProxyInterface
{


    ////
    // overidden methods
    ////
    public function execute(StepEvent $event = null)
    {
        if ($event == null) {
            $event = new StepEvent();
        }
        $event->setStep($this);
        $event->setPreviousReturnValue($event->getReturnValue());
        $event->setReturnValue(null);

        if ($this->__stepProxyStopwatch) { $this->__stepProxyStopwatch->start('KitpagesStep: '.$this->getParameter('__stopwatch_step_name')); }
        $this->__stepProxyEventDispatcher->dispatch(KitpagesStepEvents::ON_STEP_EXECUTE, $event);
        if ($this->__stepProxyStopwatch) { $this->__stepProxyStopwatch->stop('KitpagesStep: '.$this->getParameter('__stopwatch_step_name')); }

        if (!$event->isDefaultPrevented()) {
            if ($this->__stepProxyStopwatch) { $this->__stepProxyStopwatch->start('KitpagesStep: '.$this->getParameter('__stopwatch_step_name')); }
            $event->setReturnValue(parent::execute($event));
            if ($this->__stepProxyStopwatch) { $this->__stepProxyStopwatch->stop('KitpagesStep: '.$this->getParameter('__stopwatch_step_name')); }
        }
        if ($this->__stepProxyStopwatch) { $this->__stepProxyStopwatch->start('KitpagesStep: '.$this->getParameter('__stopwatch_step_name')); }
        $this->__stepProxyEventDispatcher->dispatch(KitpagesStepEvents::AFTER_STEP_EXECUTE, $event);
        if ($this->__stepProxyStopwatch) { $this->__stepProxyStopwatch->stop('KitpagesStep: '.$this->getParameter('__stopwatch_step_name')); }
        return $event->getReturnValue();
    }

    ////
    // added methods
    ////
    private $__stepProxyEventDispatcher = null;
    public function __stepProxySetEventDispatcher(EventDispatcherInterface $dispatcher)
    {
        $this->__stepProxyEventDispatcher = $dispatcher;
    }
    private $__stepProxyStopwatch = null;
    public function __stepProxySetStopwatch($stopwatch)
    {
        $this->__stepProxyStopwatch = $stopwatch;
    }
}

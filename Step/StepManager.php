<?php
namespace Kitpages\StepBundle\Step;

use Kitpages\StepBundle\StepException;
use Kitpages\StepBundle\Step\StepInterface;
use Kitpages\StepBundle\Proxy\ProxyGenerator;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Stopwatch\Stopwatch;

class StepManager
{
    /** @var array of Step */
    protected $stepList = null;
    /** @var ContainerInterface  */
    protected $container = null;
    /** @var EventDispatcherInterface  */
    protected $eventDispatcher = null;
    /** @var Stopwatch */
    protected $stopwatch = null;

    public function __construct(
        $stepList,
        ContainerInterface $container,
        EventDispatcherInterface $eventDispatcher,
        Stopwatch $stopwatch = null
    )
    {
        $this->stepList = $stepList;
        $this->container = $container;
        $this->eventDispatcher = $eventDispatcher;
        $this->stopwatch = $stopwatch;
    }

    public function getStep($stepName, $stepConfig = array())
    {
        $step = null;

        $stepFinalConfig = $this->getResultingConfig($stepName);

        $stepFinalConfig = $this->customMerge($stepFinalConfig, $stepConfig);
        $stepFinalConfig = $this->normalizeStepConfig($stepFinalConfig);

        // step name is only defined in config given in parameters
        if (!isset($stepFinalConfig['class'])) {
            throw new StepException("unknown stepName and class undefined in config");
        }
        $className = $stepFinalConfig['class'];

        if (!class_exists($className)) {
            throw new StepException("class ".$className." doesn't exists");
        }

        // generate step
        $proxyGenerator = new ProxyGenerator();
        $step = $proxyGenerator->generateProcessProxy($className);
        $step->__stepProxySetEventDispatcher($this->eventDispatcher);
        $step->__stepProxySetStopwatch($this->stopwatch);

        if (! $step instanceof StepInterface) {
            throw new StepException("Step class ".$className." doesn't implements StepInterface");
        }

        // inject Services
        if (isset($stepFinalConfig['service_list']) && is_array($stepFinalConfig['service_list'])) {
            foreach($stepFinalConfig['service_list'] as $key => $serviceName) {
                $service = $this->container->get($serviceName);
                $step->setService($key, $service);
            }
        }

        // set parameters
        if (isset($stepFinalConfig['parameter_list']) && is_array($stepFinalConfig['parameter_list'])) {
            foreach($stepFinalConfig['parameter_list'] as $key => $val) {
                $step->setParameter($key, $val);
            }
        }

        return $step;
    }

    public function getResultingConfig($stepName)
    {
        $stepConfigStack = $this->getStepConfigStack($stepName);

        // build final stepConfig by merging steps in the right order
        $stepFinalConfig = array();
        while($stepConfig = array_pop($stepConfigStack)) {
            $stepFinalConfig = $this->customMerge($stepFinalConfig, $stepConfig);
        }

        // erase help and put only last level help if present
        // because help is never inherited
        if (isset($this->stepList[$stepName])) {
            $help = array();
            $originalStepConfig = $this->stepList[$stepName];
            if (isset($originalStepConfig['help'])) {
                $help = $originalStepConfig['help'];
            }
            $stepFinalConfig["help"] = $help;
        }

        return $this->normalizeStepConfig($stepFinalConfig);
    }
    
    public function normalizeStepConfig($stepConfig)
    {
        // defines default values for help
        if (!isset($stepConfig["help"]["short"])) {
            $stepConfig["help"]["short"] = "no help";
        }
        if (!isset($stepConfig["help"]["complete"])) {
            $stepConfig["help"]["complete"] = "no description";
        }
        if (!isset($stepConfig["help"]["private"])) {
            $stepConfig["help"]["private"] = false;
        }
        if (!isset($stepConfig["parameter_list"])) {
            $stepConfig["parameter_list"] = array();
        }
        // normalize classname
        if (isset($stepConfig['class']) && $stepConfig['class']) {
            $stepConfig['class'] = '\\'.ltrim($stepConfig['class'], '\\');
        }
        return $stepConfig;
    }
    public function getStepConfigStack($stepName)
    {
        $stepConfigStack = array();
        $runningStepName = $stepName;
        // register list of steps inherited
        while (isset($this->stepList[$runningStepName])) {
            $stepConfig = $this->stepList[$runningStepName];
            $stepConfigStack[$runningStepName] = $stepConfig;
            $runningStepName = null;
            if (isset($stepConfig["parent_shared_step"])) {
                $runningStepName = $stepConfig["parent_shared_step"];
            }
        }
        return $stepConfigStack;
    }

    protected function customMerge($tab1, $tab2)
    {
        $res = $tab1;
        foreach ($tab2 as $key => $val) {
            if (isset($res[$key]) && is_array($res[$key]) && is_array($val)) {
                $res[$key] = $this->customMerge($res[$key], $val);
                continue;
            }
            $res[$key] = $val;
        }
        return $res;
    }

}

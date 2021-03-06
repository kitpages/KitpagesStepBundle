<?php
namespace Kitpages\StepBundle\Tests\Sample;

use Kitpages\StepBundle\Step\StepInterface;
use Kitpages\StepBundle\Step\StepEvent;
use Symfony\Component\DependencyInjection\ContainerInterface;

class StepSample2 implements StepInterface
{

    public $parameterList = array('return' => "originalSample2");

    public function execute(StepEvent $event = null) {
        return $this->parameterList['return'];
    }

    public function setParameter($parameter, $value) {
        $this->parameterList[$parameter] = $value;
        return $this;
    }

    public function setContainer(ContainerInterface $container)
    {
    }

    public function setService($key, $service)
    {
        $this->serviceList[$key] = $service;
        return $this;
    }

    public function getService($key)
    {
        if (!isset($this->serviceList[$key])) {
            return null;
        }
        return $this->serviceList[$key];
    }

}

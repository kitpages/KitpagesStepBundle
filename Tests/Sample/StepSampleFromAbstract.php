<?php
namespace Kitpages\StepBundle\Tests\Sample;

use Kitpages\StepBundle\Step\StepAbstract;
use Kitpages\StepBundle\Step\StepEvent;

class StepSampleFromAbstract
    extends StepAbstract
{
    public function execute(StepEvent $event = null)
    {
        return "executed";
    }
}

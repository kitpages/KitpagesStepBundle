<?php
namespace Kitpages\StepBundle\Tests\Step;

use Kitpages\StepBundle\StepException;
use Kitpages\StepBundle\Step\StepManager;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\EventDispatcher\EventDispatcher;

class StepManagerTest extends WebTestCase
{
    public function setUp()
    {
        $this->container = $this->getMock('Symfony\Component\DependencyInjection\Container');
        $this->container
            ->expects($this->any())
            ->method('get')
            ->will($this->returnArgument(0));
        $this->container
            ->expects($this->any())
            ->method('getParameter')
            ->with(
                $this->logicalOr(
                    $this->equalTo('kernel.debug'),
                    $this->equalTo('kernel.cache_dir')
                )
            )
            ->will(
                $this->returnCallback(
                    function ($param) {

                        if ($param == 'kernel.cache_dir') {
                            return __DIR__.'../app/cache/test';
                        }

                        return true;
                    }
                )
            );

        $this->eventDispatcher = $this->getMock('Symfony\Component\EventDispatcher\EventDispatcher');
    }

    public function testSimpleStep()
    {
        $stepListConfig = array(
            'stepTest' => array(
                'class' => '\Kitpages\StepBundle\Tests\Sample\StepSample'
            )
        );

        $stepManager = new StepManager($stepListConfig, $this->container, $this->eventDispatcher);

        $stepTest = $stepManager->getStep('stepTest');
        $resultExecute = $stepTest->execute();
        $this->assertEquals($resultExecute, "original");
    }

    public function testStepWithParameter()
    {

        $stepListConfig = array(
            'stepTest' => array(
                'class' => '\Kitpages\StepBundle\Tests\Sample\StepSample',
                'parameter_list' => array(
                    'return' => "changed"
                )
            )
        );

        $stepManager = new StepManager($stepListConfig, $this->container, $this->eventDispatcher);

        $stepTest = $stepManager->getStep('stepTest');
        $resultExecute = $stepTest->execute();
        $this->assertEquals($resultExecute, "changed");
    }

    public function testStepExceptions()
    {
        $stepListConfig = array(
            'StepThatDoesNotExist' => array(
                'class' => '\Kitpages\StepBundle\Tests\Sample\StepThatDoesNotExist'
            ),
            'StepWithoutInterface' => array(
                'class' => '\Kitpages\StepBundle\KitpagesStepBundle'
            )
        );
        $stepManager = new StepManager($stepListConfig, $this->container, $this->eventDispatcher);

        try {
            $stepTest = $stepManager->getStep('StepThatDoesNotExist');
            $this->fail('No exception raised for StepThatDoesNotExist');
        } catch (StepException $e) {
            $this->assertTrue(true);
        }

        try {
            $stepTest = $stepManager->getStep('StepWithoutInterface');
            $this->fail('No exception raised for StepWithoutInterface');
        } catch (StepException $e) {
            $this->assertTrue(true);
        }
    }

    public function testExtraStepListConfig()
    {
        $stepListConfig = array();

        $extraStepListConfig = array(
            'StepThatDoesNotExist' => array(
                'class' => '\Kitpages\StepBundle\Tests\Sample\StepThatDoesNotExist'
            ),
            'StepWithoutInterface' => array(
                'class' => '\Kitpages\StepBundle\KitpagesStepBundle'
            )
        );
        $stepManager = new StepManager($stepListConfig, $this->container, $this->eventDispatcher);

        try {
            $stepTest = $stepManager->getStep('StepNotDefined', array());
            $this->fail('No exception raised for StepThatDoesNotExist');
        } catch (StepException $e) {
            $this->assertTrue(true);
        }

        try {
            $stepTest = $stepManager->getStep('StepThatDoesNotExist', $extraStepListConfig['StepThatDoesNotExist']);
            $this->fail('No exception raised for StepThatDoesNotExist');
        } catch (StepException $e) {
            $this->assertTrue(true);
        }

        try {
            $stepTest = $stepManager->getStep('StepWithoutInterface', $extraStepListConfig['StepWithoutInterface']);
            $this->fail('No exception raised for StepWithoutInterface');
        } catch (StepException $e) {
            $this->assertTrue(true);
        }
    }

    public function testStepWithManualyChangedParameter()
    {

        $stepListConfig = array(
            'stepTest' => array(
                'class' => '\Kitpages\StepBundle\Tests\Sample\StepSample',
                'parameter_list' => array(
                    'return' => "changed"
                )
            )
        );

        $stepManager = new StepManager($stepListConfig, $this->container, $this->eventDispatcher);

        $stepTest = $stepManager->getStep('stepTest');
        $stepTest->setParameter('return', "changed2");
        $resultExecute = $stepTest->execute();
        $this->assertEquals($resultExecute, "changed2");
    }

    public function testStepWithConfigChangedParameter()
    {

        $stepListConfig = array(
            'stepTest' => array(
                'class' => '\Kitpages\StepBundle\Tests\Sample\StepSample',
                'parameter_list' => array(
                    'return' => "changed"
                )
            )
        );
        $customChangedConfig = array(
            'class' => '\Kitpages\StepBundle\Tests\Sample\StepSample2',
            'parameter_list' => array(
                'return' => "configChanged"
            )
        );

        $stepManager = new StepManager($stepListConfig, $this->container, $this->eventDispatcher);

        $stepTest = $stepManager->getStep('stepTest', $customChangedConfig);
        $resultExecute = $stepTest->execute();
        $this->assertEquals($resultExecute, "configChanged");
    }

    public function testBasicInheritanceStep()
    {
        $stepListConfig = array(
            'stepTest' => array(
                'class' => '\Kitpages\StepBundle\Tests\Sample\StepSample'
            ),
            'childStep' => array(
                "parent_shared_step" => "stepTest"
            )
        );

        $stepManager = new StepManager($stepListConfig, $this->container, $this->eventDispatcher);

        $stepTest = $stepManager->getStep('childStep');
        $resultExecute = $stepTest->execute();
        $this->assertEquals($resultExecute, "original");
    }

    public function testExtendedInheritanceStep()
    {

        $stepListConfig = array(
            'stepTest' => array(
                'class' => '\Kitpages\StepBundle\Tests\Sample\StepSample',
                'parameter_list' => array(
                    'return' => "changed"
                ),
                'service_list' => array(
                    'logger' => 'loggerresult'
                )
            ),
            'childStep1' => array(
                "parent_shared_step" => "stepTest"
            ),
            'childStep2' => array(
                "parent_shared_step" => "childStep1",
                'service_list' => array(
                    'logger' => 'loggerresult-child2'
                )
            ),
            'childStep3' => array(
                "parent_shared_step" => "childStep2",
                'parameter_list' => array(
                    'return' => "childStep3"
                )
            )
        );

        $stepManager = new StepManager($stepListConfig, $this->container, $this->eventDispatcher);

        $stepTest = $stepManager->getStep('stepTest');
        $this->assertEquals("loggerresult", $stepTest->getService("logger"));

        $stepTest = $stepManager->getStep('childStep1');
        $resultExecute = $stepTest->execute();
        $this->assertEquals("loggerresult", $stepTest->getService("logger"));
        $this->assertEquals($resultExecute, "changed");

        $stepTest = $stepManager->getStep('childStep2');
        $resultExecute = $stepTest->execute();
        $this->assertEquals("loggerresult-child2", $stepTest->getService("logger"));
        $this->assertEquals($resultExecute, "changed");

        $stepTest = $stepManager->getStep('childStep3');
        $resultExecute = $stepTest->execute();
        $this->assertEquals($resultExecute, "childStep3");
    }

}

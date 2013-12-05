<?php
namespace Kitpages\StepBundle\Tests\Console;

use Kitpages\StepBundle\Tests\TestUtil\CommandTestCase;
use Kitpages\StepBundle\StepException;

class ConsoleTest extends CommandTestCase
{
    public function testRunCommandSimple()
    {
        $client = self::createClient();

        $output = $this->runCommand($client, "kitpages:step:run-step StepSample");
        $this->assertContains('changedByStepConfig1', $output);

        $output = $this->runCommand($client, "kitpages:step:run-step StepSampleOriginal");
        $this->assertContains('original', $output);
    }
    public function testInheritanceStep()
    {
        $client = self::createClient();

        $output = $this->runCommand($client, "kitpages:step:run-step childStep");
        $this->assertContains('changedByStepConfig1', $output);
    }
    public function testRunCommandWithParameters()
    {
        $client = self::createClient();

        $output = $this->runCommand($client, "kitpages:step:run-step StepSample --p=return:titi");
        $this->assertContains('titi', $output);
    }

    public function testPreventDefault()
    {
        $client = self::createClient();

        $output = $this->runCommand($client, "kitpages:step:run-step CustomPreventDefault");
        $this->assertContains("unit test exception", $output);

        $output = $this->runCommand($client, "kitpages:step:run-step CustomPreventDefault --p=isDefaultPrevented:true");
        $this->assertContains("output=null", $output);
    }

    public function testStepHelp()
    {
        $client = self::createClient();

        $output = $this->runCommand($client, "kitpages:step:help-step");
        $this->assertContains("StepSample : step sample displaying a string", $output);
        $this->assertContains("CustomPreventDefault : no help", $output);
        var_dump($output);

        $output = $this->runCommand($client, "kitpages:step:help-step childStep");
        $this->assertContains("@param string return string returned by the step", $output);
        $this->assertContains("@event:returnValue string", $output);
        $this->assertContains("childStep <- parentStep", $output);
        var_dump($output);
    }

    public function testStepHelpPrivate()
    {
        $client = self::createClient();

        $output = $this->runCommand($client, "kitpages:step:help-step");
        $this->assertNotContains("private step", $output);
        $this->assertNotContains("StepSampleOriginal", $output);
    }

}
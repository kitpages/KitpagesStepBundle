<?php
namespace Kitpages\StepBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;

class executeStepCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('kitpages:step:run-step')
            ->addArgument('stepName', InputArgument::REQUIRED, 'step name')
            ->addOption('p', null, InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY, 'If set, no display message')
            ->setHelp(<<<EOT
The <info>kitpages:step:run-step</info> execute a step  with as parameter --p=nameParameter:valueParameter
EOT
            )
            ->setDescription('run a kitpagesStepBundle step')
            ;
    }
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $stepName = $input->getArgument('stepName');
        $parameterStringList = $input->getOption('p');
        $parameterList = array();
        foreach($parameterStringList as $parameterString) {
            $parameterStringArray = explode(':', $parameterString);
            $parameterList[$parameterStringArray[0]] = $parameterStringArray[1];
        }
        $stepConfig = array(
            'parameter_list' => $parameterList
        );
        $stepManager = $this->getContainer()->get('kitpages_step.step');
        $step = $stepManager->getStep($stepName, $stepConfig);
        $ret = $step->execute();
        $output->writeln("StepName: $stepName; output=".($ret?$ret:"null")."\n");
    }



}
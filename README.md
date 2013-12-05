KitpagesStepBundle
===================

[![Build Status](https://travis-ci.org/kitpages/KitpagesStepBundle.png?branch=master)](https://travis-ci.org/kitpages/KitpagesStepBundle)

This bundle provides a step system for a future workflow system

## Versions
05/12/2013 : major refactor and separation between chain and steps
04/23/2013 : v1.4.0 help system steps
04/18/2013 : v1.3.0 step inheritance with the optional
02/19/2013 : v1.2.0 step parameter template rendering
02/18/2013 : v1.1.0 steps are not container aware anymore. Services are injected in config.yml
02/18/2013 : v1.0.0 first stable version

## Actual state

This bundle is stable, tested and under travis-ci.

## Installation

Add KitpagesStepBundle in your composer.json

```js
{
    "require": {
        "kitpages/step-bundle": "*"
    }
}
```

Now tell composer to download the bundle by running the step:

``` bash
$ php composer.phar update kitpages/step-bundle
```

AppKernel.php

``` php
$bundles = array(
    ...
    new Kitpages\StepBundle\KitpagesStepBundle(),
);
```


## create a step

Each step must implements StepInterface or extend StepAbstract. The DIC
is injected to the step with the method setContainer.

```php
<?php
namespace Kitpages\StepBundle\Tests\Sample;

use Kitpages\StepBundle\Step\StepAbstract;

class StepSample extends StepAbstract
{
    public function execute() {
        // do whatever you want
        return $whatever;
    }
}
```

## Configuration example

The following configuration defines 2 steps :

* kitpagesMep : a production start
* kitpagesCms : instantiate a KitpagesCms

Let's see the configuration in config.yml

``` yaml
kitpages_step:
    shared_step_list:
        CodeCopy:
            class: '\Kitpages\StepBundle\Step\CodeCopy'
            parameter_list:
                src_dir: '/home/webadmin/htdocs/dev/www.kitpages.com'
                dest_dir: '/home/webadmin/htdocs/prod/www.kitpages.com'
            help:
                short: copy a directory to another
                complete: |
                    This step copies a directory to another
                    @param string return string returned by the step
                    @service listener service used for xxx
                    @event:returnValue string
                    @return boolean true if ok or false

        CodeCopyPreProd:
            parent_shared_step: CodeCopy
            parameter_list:
                dest_dir: '/home/webadmin/htdocs/pre-prod/www.kitpages.com'
        GitKitpages:
            class: '\Kitpages\StepBundle\Step\GitKitpages'
            parameter_list:
                url: git.kitpages.com
            service_list:
                logger: logger
```

## using app/console
### run a step with app/console

``` bash
# run a step with parameters defined in config.yml
php app/console kitpages:step:run-step CodeCopy

# run a step with custom parameters
php app/console kitpages:step:run-step CodeCopy --p=src_dir:'/home/webadmin/src' --p=dest_dir:'/tmp/destDir'
```

### run a step with PHP

``` php
$stepKitpages = $this->get("kitpages_step.step");
$codeCopyStepKitpages = $stepKitpages->getStep('CodeCopy');
$codeCopyStepKitpages->setParameter('src_dir', '/home/webadmin/htdocs/dev/cms2.kitpages.com');

$codeCopyStepKitpages->execute();
```

## Using events

With events, you can alter the way each step is executed. You can :

* prevent the step from running the execute() method. $event->preventDefault()
* alter the step before or after the execution
* change return value
* ...

Create a listener :

```php
namespace Foo\Bar;
class StepListener
{
    public function onStepExecute(StepEvent $event)
    {
        $step = $event->getStep();
        // do whatever you want with the current step
        // $event->preventDefault();
        // $event->stopPropagation();
        // log something ?
    }
}
```

register listener :

```yaml
services:
    stepListener:
        class: Foo\Bar\StepListener
        tags:
            - { name: kernel.event_listener, event: kitpages_step.on_step_execute, method: onStepExecute }
```

``` php
use Kitpages\StepBundle\Step\StepEvent;
[...]

$event = new StepEvent();

$stepKitpages = $this->get("kitpages_step.step");
$codeCopyStepKitpages = $stepKitpages->getStep('CodeCopy');
$codeCopyStepKitpages->setParameter('src_dir', '/home/webadmin/htdocs/dev/cms2.kitpages.com');

$codeCopyStepKitpages->execute($event);
```


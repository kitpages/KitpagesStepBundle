<?php
namespace Kitpages\StepBundle\Tests\Proxy;

use Kitpages\StepBundle\Tests\Sample\StepSample;
use Kitpages\StepBundle\Proxy\ProxyGenerator;
use Kitpages\StepBundle\Proxy\ProxyInterface;

class ProxyGeneratorTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
    }
    public function testProxyInfo()
    {
        $originalClassName = '\Kitpages\StepBundle\Tests\Sample\StepSample';
        $proxyGenerator = new ProxyGenerator($originalClassName, true, __DIR__.'../app/cache/test');

        $this->assertEquals(
            $proxyGenerator->getProxyClass(),
            '\Kitpages\StepBundle\Proxy\Kitpages\StepBundle\Tests\Sample\StepSample'
        );
    }

    public function testProxyClassGeneration()
    {
        $originalClassName = '\Kitpages\StepBundle\Tests\Sample\StepSample';
        $proxyGenerator = new ProxyGenerator($originalClassName, true, __DIR__.'../app/cache/test');
        $proxyClass = $proxyGenerator->getProxyClass();

        $proxyGenerator->loadProxyClass();

        $this->assertTrue(
            class_exists($proxyClass)
        );
    }

    public function testProxyGeneration()
    {
        $originalClassName = '\Kitpages\StepBundle\Tests\Sample\StepSample';
        $proxyGenerator = new ProxyGenerator($originalClassName, true, __DIR__.'../app/cache/test');

        $proxy = $proxyGenerator->generateProcessProxy();

        $this->assertTrue($proxy instanceof StepSample);
        $this->assertTrue($proxy instanceof ProxyInterface);
    }

    public function testProxyGenerationTwice()
    {
        $originalClassName = '\Kitpages\StepBundle\Tests\Sample\StepSample';
        $proxyGenerator = new ProxyGenerator($originalClassName, true, __DIR__.'../app/cache/test');

        $proxy = $proxyGenerator->generateProcessProxy();

        $this->assertTrue($proxy instanceof StepSample);
        $this->assertTrue($proxy instanceof ProxyInterface);

        $proxy = $proxyGenerator->generateProcessProxy();
        $this->assertTrue($proxy instanceof ProxyInterface);
    }

}

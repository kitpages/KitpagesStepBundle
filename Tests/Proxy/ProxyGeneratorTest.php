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
        $proxyGenerator = new ProxyGenerator();
        $originalClassName = '\Kitpages\StepBundle\Tests\Sample\StepSample';

        $this->assertEquals(
            $proxyGenerator->getProxyNameSpace($originalClassName),
            'Kitpages\StepBundle\Proxy\Kitpages\StepBundle\Tests\Sample'
        );
        $this->assertEquals(
            $proxyGenerator->getProxyClassName($originalClassName),
            '\Kitpages\StepBundle\Proxy\Kitpages\StepBundle\Tests\Sample\StepSample'
        );
    }

    public function testProxyClassGeneration()
    {
        $proxyGenerator = new ProxyGenerator();
        $originalClassName = '\Kitpages\StepBundle\Tests\Sample\StepSample';

        // this test is used to check if proxy class generated in the previous test
        // is generated again or not.
        $className = $proxyGenerator->generateProxyClass(
            $originalClassName,
            'class <<proxyClassName>> {}',
            array("proxyClassName"=>'\Kitpages\StepBundle\Proxy\Kitpages\StepBundle\Tests\Sample\StepSample')
        );

        $this->assertEquals(
            $className,
            '\Kitpages\StepBundle\Proxy\Kitpages\StepBundle\Tests\Sample\StepSample'
        );
    }

    public function testProxyGeneration()
    {
        $proxyGenerator = new ProxyGenerator();
        $originalClassName = '\Kitpages\StepBundle\Tests\Sample\StepSample';

        $proxyClassName = $proxyGenerator->generateProcessProxyClass($originalClassName);

        $proxy = new $proxyClassName();

        $this->assertTrue($proxy instanceof StepSample);
        $this->assertTrue($proxy instanceof ProxyInterface);
    }

    public function testProxyGenerationTwice()
    {
        $proxyGenerator = new ProxyGenerator();
        $originalClassName = '\Kitpages\StepBundle\Tests\Sample\StepSample';

        $proxyClassName = $proxyGenerator->generateProcessProxyClass($originalClassName);
        $proxy = new $proxyClassName();

        $this->assertTrue($proxy instanceof StepSample);
        $this->assertTrue($proxy instanceof ProxyInterface);

        $proxyClassName = $proxyGenerator->generateProcessProxyClass($originalClassName);
        $proxy = new $proxyClassName();
        $this->assertTrue($proxy instanceof ProxyInterface);
    }

}
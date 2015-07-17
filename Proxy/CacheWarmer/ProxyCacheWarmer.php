<?php

namespace Kitpages\StepBundle\Proxy\CacheWarmer;

use Kitpages\StepBundle\Proxy\ProxyGenerator;
use Kitpages\StepBundle\Step\StepManager;
use Kitpages\StepBundle\StepException;
use Symfony\Component\HttpKernel\CacheWarmer\CacheWarmerInterface;

/**
 * Removes the proxy cache file when the cache is cleared.
 *
 * @author Hugues Maignol <hugues.maignol@kitpages.fr>
 */
class ProxyCacheWarmer implements CacheWarmerInterface
{
    /**
     * @var StepManager
     */
    private $stepManager;

    /**
     * ProxyCacheClearer constructor.
     *
     * @param StepManager $stepManager
     */
    public function __construct(StepManager $stepManager)
    {
        $this->stepManager = $stepManager;
    }

    /**
     * We need the workflow proxies in the cache.
     *
     * @return bool false
     */
    public function isOptional()
    {
        return false;
    }

    /**
     * Writes the workflow proxy cache file.
     *
     * @param string $cacheDir The cache directory
     */
    public function warmUp($cacheDir)
    {
        $stepList = $this->stepManager->getStepList();

        if (null === $stepList) {
            return;
        }

        foreach ($stepList as $stepName => $step) {

            $stepFinalConfig = $this->stepManager->getResultingConfig($stepName);
            $stepFinalConfig = $this->stepManager->normalizeStepConfig($stepFinalConfig);

            // step name is only defined in config given in parameters
            if (!isset($stepFinalConfig['class'])) {
                throw new StepException("unknown stepName and class undefined in config");
            }
            $className = $stepFinalConfig['class'];

            if (!class_exists($className)) {
                throw new StepException("class ".$className." doesn't exists");
            }

            $proxyGenerator = new ProxyGenerator($className, true, $cacheDir);
            $proxyGenerator->writeProxyClassCache();
        }
    }

}

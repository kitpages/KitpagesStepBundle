<?php

namespace Kitpages\StepBundle\Proxy\CacheClearer;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpKernel\CacheClearer\CacheClearerInterface;

/**
 * Removes the proxy cache file when the cache is cleared.
 *
 * @author Hugues Maignol <hugues.maignol@kitpages.fr>
 */
class ProxyCacheClearer implements CacheClearerInterface
{
    /**
     * Clears any caches necessary.
     *
     * @param string $cacheDir The cache directory.
     */
    public function clear($cacheDir)
    {
        $fs = new Filesystem();
        $proxyCacheDir = $cacheDir.'/kitpages_proxy';
        if ($fs->exists($proxyCacheDir)) {
            die('here');
            $fs->remove($proxyCacheDir);
        }
    }
}

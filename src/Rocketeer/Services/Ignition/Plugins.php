<?php

/*
 * This file is part of Rocketeer
 *
 * (c) Maxime Fabre <ehtnam6@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */

namespace Rocketeer\Services\Ignition;

use Illuminate\Support\Arr;
use Rocketeer\Traits\ContainerAwareTrait;

/**
 * Publishes the plugin's configurations in user-land.
 *
 * @author Maxime Fabre <ehtnam6@gmail.com>
 */
class Plugins
{
    use ContainerAwareTrait;

    /**
     * Publishes a package's configuration.
     *
     * @param string $package
     *
     * @return bool|string|null
     */
    public function publish($package)
    {
        // Find the plugin's configuration
        $paths = $this->findPackageConfiguration($package);

        // Cancel if no valid paths
        $paths = array_filter($paths, [$this->files, 'isDirectory']);
        $paths = array_values($paths);
        if (empty($paths)) {
            return $this->explainer->error('No configuration found for '.$package);
        }

        return $this->publishConfiguration($paths[0]);
    }

    /**
     * Find all the possible locations for a package's configuration.
     *
     * @param string $package
     *
     * @return string[]
     */
    public function findPackageConfiguration($package)
    {
        $paths = [
            $this->paths->getBasePath().'vendor/%s/src/config',
            $this->paths->getBasePath().'vendor/%s/config',
            $this->paths->getUserHomeFolder().'/.rocketeer/vendor/%s/src/config',
            $this->paths->getUserHomeFolder().'/.rocketeer/vendor/%s/config',
            $this->paths->getUserHomeFolder().'/.composer/vendor/%s/src/config',
            $this->paths->getUserHomeFolder().'/.composer/vendor/%s/config',
        ];

        // Check for the first configuration path that exists
        $paths = array_map(function ($path) use ($package) {
            return sprintf($path, $package);
        }, $paths);

        return $paths;
    }

    /**
     * Publishes a configuration within a classic application.
     *
     * @param string $path
     *
     * @return bool
     */
    protected function publishConfiguration($path)
    {
        // Get the vendor and package
        preg_match('/vendor\/([^\/]+)\/([^\/]+)/', $path, $handle);
        $handle = (array) $handle;
        $package = Arr::get($handle, 2);

        // Compute and create the destination foldser
        $destination = $this->paths->getConfigurationPath().'/plugins/'.$package;
        if (!$this->files->has($destination)) {
            $this->files->createDir($destination);
        }

        // Display success
        $this->explainer->success('Publishing configuration from '.$path.' to '.$destination);
        $this->files->copyDir($path, $destination);
    }
}

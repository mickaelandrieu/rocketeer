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

namespace Rocketeer\Tasks\Subtasks;

use Rocketeer\Tasks\AbstractTask;

class PrepareRelease extends AbstractTask
{
    /**
     * @var string
     */
    protected $description = 'Prepares the release for use';

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $this->steps()->executeTask('Dependencies');
        if ($this->getOption('tests')) {
            $this->steps()->executeTask('Test');
        }

        // Create release and set permissions
        $this->steps()->setApplicationPermissions();

        // Run migrations
        if ($this->getOption('migrate') || $this->getOption('seed')) {
            $this->steps()->executeTask('Migrate');
        }

        // Synchronize shared folders and files
        $this->steps()->syncSharedFolders();

        // Swap symlink if that wasn't already done
        $release = $this->releasesManager->getCurrentRelease();
        $alreadyDeployed = $this->releasesManager->checkReleaseState($release);
        if (!$alreadyDeployed) {
            if ($this->getOption('coordinated', true)) {
                $this->steps()->coordinator->whenAllServersReadyTo('symlink', 'SwapSymlink');
            } else {
                $this->steps()->executeTask('SwapSymlink');
            }
        }

        return $this->runSteps();
    }
}

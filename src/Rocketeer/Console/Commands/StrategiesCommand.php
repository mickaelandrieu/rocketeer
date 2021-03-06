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

namespace Rocketeer\Console\Commands;

/**
 * Lists the available options for each strategy.
 *
 * @author Maxime Fabre <ehtnam6@gmail.com>
 */
class StrategiesCommand extends AbstractCommand
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'strategies';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Lists the available options for each strategy';

    /**
     * {@inheritdoc}
     */
    public function fire()
    {
        $strategies = [
            'check' => ['Php', 'Ruby', 'Node', 'Polyglot'],
            'create-release' => ['Clone', 'Copy'],
            'deploy' => ['Rolling', 'Sync', 'Upload'],
            'test' => ['Phpunit'],
            'migrate' => ['Artisan'],
            'dependencies' => ['Composer', 'Bundler', 'Npm', 'Bower', 'Polyglot'],
        ];

        $rows = [];
        foreach ($strategies as $strategy => $implementations) {
            foreach ($implementations as $implementation) {
                $instance = $this->builder->buildStrategy($strategy, $implementation);
                if ($instance) {
                    $rows[] = [$strategy, $implementation, $instance->getDescription()];
                }
            }
        }

        $this->table(['Strategy', 'Implementation', 'Description'], $rows);
    }
}

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

namespace Rocketeer\Strategies\Check;

use Prophecy\Argument;
use Rocketeer\TestCases\RocketeerTestCase;

class PhpStrategyTest extends RocketeerTestCase
{
    /**
     * @var \Rocketeer\Strategies\Check\PhpStrategy
     */
    protected $strategy;

    public function setUp()
    {
        parent::setUp();

        $this->strategy = $this->builder->buildStrategy('Check', 'Php');
    }

    public function testCanCheckPhpVersion()
    {
        $this->bindDummyConnection([
            'which php' => 'php',
            'which composer' => 'composer',
            'php -r "print defined(\'HHVM_VERSION\');"' => false,
            'php -r "print PHP_VERSION;"' => '5.6.0',
        ]);

        $prophecy = $this->bindFilesystemProphecy(true);
        $prophecy->put()->willReturn();
        $prophecy->has(Argument::cetera())->willReturn(true);
        $prophecy->read(Argument::cetera())->willReturn('{"require":{"php":">=5.6.0"}}');

        $this->assertTrue($this->strategy->language());

        // This is is going to come bite me in the ass in 10 years
        $prophecy = $this->bindFilesystemProphecy(true);
        $prophecy->put()->willReturn();
        $prophecy->has(Argument::cetera())->willReturn(true);
        $prophecy->read(Argument::cetera())->willReturn('{"require":{"php":">=12.9.0"}}');

        $this->assertFalse($this->strategy->language());
    }

    public function testCanCheckPhpExtensions()
    {
        $this->mockHhvm(false, [
            'which composer' => 'composer',
            'php -m' => 'sqlite',
        ]);

        $this->strategy->extensions();

        $this->assertHistory(['php -m']);
    }

    public function testCanCheckForHhvmExtensions()
    {
        $this->mockHhvm();
        $exists = $this->strategy->checkPhpExtension('_hhvm');

        $this->assertTrue($exists);
    }
}

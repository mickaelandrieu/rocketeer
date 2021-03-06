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

namespace Rocketeer\Scm;

use Rocketeer\Binaries\Scm\Svn;
use Rocketeer\TestCases\RocketeerTestCase;

class SvnTest extends RocketeerTestCase
{
    /**
     * The current SCM instance.
     *
     * @var Svn
     */
    protected $scm;

    public function setUp()
    {
        parent::setUp();

        $this->scm = new Svn($this->container);
    }

    ////////////////////////////////////////////////////////////////////
    //////////////////////////////// TESTS /////////////////////////////
    ////////////////////////////////////////////////////////////////////

    public function testCanGetCheck()
    {
        $command = $this->scm->check();

        $this->assertEquals('svn --version', $command);
    }

    public function testCanGetCurrentState()
    {
        $command = $this->scm->currentState();

        $this->assertEquals('svn info | grep "Revision"', $command);
    }

    public function testCanGetCurrentBranch()
    {
        $command = $this->scm->currentBranch();

        $this->assertEquals('echo trunk', $command);
    }

    public function testCanGetCheckout()
    {
        $this->swapScmConfiguration([
            'username' => 'foo',
            'password' => 'bar',
            'repository' => 'http://github.com/my/repository',
            'branch' => 'develop',
        ]);

        $command = $this->scm->checkout($this->server);

        $this->assertEquals('svn co http://github.com/my/repository/develop '.$this->server.' --non-interactive --username="foo" --password="bar"', $command);
    }

    public function testCanGetDeepClone()
    {
        $this->swapScmConfiguration([
            'username' => 'foo',
            'password' => 'bar',
            'repository' => 'http://github.com/my/repository',
            'branch' => 'develop',
        ]);

        $command = $this->scm->checkout($this->server);

        $this->assertEquals('svn co http://github.com/my/repository/develop '.$this->server.' --non-interactive --username="foo" --password="bar"', $command);
    }

    public function testDoesntDuplicateCredentials()
    {
        $this->swapScmConfiguration([
            'username' => 'foo',
            'password' => 'bar',
            'repository' => 'http://foo:bar@github.com/my/repository',
            'branch' => 'develop',
        ]);

        $command = $this->scm->checkout($this->server);

        $this->assertEquals('svn co http://github.com/my/repository/develop '.$this->server.' --non-interactive --username="foo" --password="bar"', $command);

        $this->swapScmConfiguration([
            'username' => 'foo',
            'password' => null,
            'repository' => 'http://foo@github.com/my/repository',
            'branch' => 'develop',
        ]);

        $command = $this->scm->checkout($this->server);

        $this->assertEquals('svn co http://github.com/my/repository/develop '.$this->server.' --non-interactive --username="foo"', $command);
    }

    public function testDoesntStripRevisionFromUrl()
    {
        $this->swapScmConfiguration([
            'username' => 'foo',
            'password' => 'bar',
            'repository' => 'url://user:login@example.com/test',
            'branch' => 'trunk@1234',
        ]);

        $command = $this->scm->checkout($this->server);

        $this->assertEquals('svn co url://example.com/test/trunk@1234 '.$this->server.' --non-interactive --username="foo" --password="bar"', $command);
    }

    public function testCanGetReset()
    {
        $command = $this->scm->reset();

        $this->assertEquals("svn status -q | grep -v '^[~XI ]' | awk '{print $2;}' | xargs --no-run-if-empty svn revert", $command);
    }

    public function testCanGetUpdate()
    {
        $command = $this->scm->update();

        $this->assertEquals('svn up --non-interactive', $command);
    }

    public function testCanGetSubmodules()
    {
        $command = $this->scm->submodules();

        $this->assertEmpty($command);
    }
}

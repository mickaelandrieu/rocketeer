<?php
namespace Rocketeer\Services\Storages;

use Rocketeer\TestCases\RocketeerTestCase;

class LocalStorageTest extends RocketeerTestCase
{
	////////////////////////////////////////////////////////////////////
	//////////////////////////////// TESTS /////////////////////////////
	////////////////////////////////////////////////////////////////////

	public function testCanCreateDeploymentsFileAnywhere()
	{
		$this->app['path.storage'] = null;
		$this->app->offsetUnset('path.storage');

		new LocalStorage($this->app);

		$storage = $this->rocketeer->getRocketeerConfigFolder();
		$exists  = file_exists($storage);
		$this->app['files']->deleteDirectory($storage);
		$this->assertTrue($exists);
	}

	public function testCanGetValueFromDeploymentsFile()
	{
		$this->assertEquals('bar', $this->app['rocketeer.storage.local']->getValue('foo'));
	}

	public function testCanSetValueInDeploymentsFile()
	{
		$this->app['rocketeer.storage.local']->setValue('foo', 'baz');

		$this->assertEquals('baz', $this->app['rocketeer.storage.local']->getValue('foo'));
	}

	public function testCandeleteRepository()
	{
		$this->app['rocketeer.storage.local']->deleteRepository();

		$this->assertFalse($this->app['files']->exists(__DIR__.'/_meta/deployments.json'));
	}

	public function testCanFallbackIfFileDoesntExist()
	{
		$this->app['rocketeer.storage.local']->deleteRepository();

		$this->assertEquals(null, $this->app['rocketeer.storage.local']->getValue('foo'));
	}

	public function testCanGetLineEndings()
	{
		$this->app['rocketeer.storage.local']->deleteRepository();

		$this->assertEquals(PHP_EOL, $this->app['rocketeer.storage.local']->getLineEndings());
	}

	public function testCanGetSeparators()
	{
		$this->app['rocketeer.storage.local']->deleteRepository();

		$this->assertEquals(DIRECTORY_SEPARATOR, $this->app['rocketeer.storage.local']->getSeparator());
	}

	public function testCanComputeHashAccordingToContentsOfFiles()
	{
		$this->mock('files', 'Filesystem', function ($mock) {
			return $mock
				->shouldReceive('put')->once()
				->shouldReceive('exists')->twice()->andReturn(false)
				->shouldReceive('glob')->once()->andReturn(array('foo', 'bar'))
				->shouldReceive('getRequire')->once()->with('foo')->andReturn(array('foo'))
				->shouldReceive('getRequire')->once()->with('bar')->andReturn(array('bar'));
		});

		$hash = $this->app['rocketeer.storage.local']->getHash();

		$this->assertEquals(md5('["foo"]["bar"]'), $hash);
	}

	public function testCanCheckIfComposerIsNeeded()
	{
		$this->usesComposer(true);
		$this->assertTrue($this->app['rocketeer.storage.local']->usesComposer());

		$this->usesComposer(false);
		$this->assertFalse($this->app['rocketeer.storage.local']->usesComposer());
	}
}

<?php
namespace AntonioPrimera\TestScenarios\Tests;

use AntonioPrimera\TestScenarios\TestScenarioServiceProvider;
use Mockery;

class TestCase extends \Orchestra\Testbench\TestCase
{
	protected function getPackageProviders($app)
	{
		return [
			TestScenarioServiceProvider::class,
		];
	}

	protected function tearDown(): void
	{
		Mockery::close();
		parent::tearDown();
	}
}

<?php
namespace AntonioPrimera\TestScenarios\Tests\Unit;

use AntonioPrimera\TestScenarios\ScenarioFactory;
use AntonioPrimera\TestScenarios\Tests\Context\SimpleScenario;
use AntonioPrimera\TestScenarios\Tests\TestCase;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\Test;

class ScenarioFactoryTest extends TestCase
{
	#[Test]
	public function it_can_resolve_scenarios_via_alias()
	{
		config(['test-scenarios.aliases.blog' => SimpleScenario::class]);

		$scenario = $this->app->make(ScenarioFactory::class)->make('blog', ['foo' => 'bar']);

		$this->assertInstanceOf(SimpleScenario::class, $scenario);
		$this->assertNull($scenario->getTestCase());
	}

	#[Test]
	public function it_fails_when_the_class_is_missing()
	{
		$this->expectException(InvalidArgumentException::class);

		$this->app->make(ScenarioFactory::class)->resolveClass('MissingScenarioClass');
	}
}

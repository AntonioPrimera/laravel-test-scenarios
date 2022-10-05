<?php
namespace AntonioPrimera\TestScenarios\Tests\Unit;

use AntonioPrimera\TestScenarios\HasScenarios;
use AntonioPrimera\TestScenarios\TestContext;
use AntonioPrimera\TestScenarios\Tests\Context\AppContext;
use AntonioPrimera\TestScenarios\Tests\Context\ComplexScenario;
use AntonioPrimera\TestScenarios\Tests\Context\SimpleScenario;
use AntonioPrimera\TestScenarios\Tests\Context\TestModels\Comment;
use AntonioPrimera\TestScenarios\Tests\Context\TestModels\Product;
use AntonioPrimera\TestScenarios\Tests\TestCase;

class AutomaticScenarioCreationTest extends TestCase
{
	use HasScenarios;
	
	protected SimpleScenario $simpleScenario;
	protected ComplexScenario $complexScenario;
	
	protected function setUp(): void
	{
		parent::setUp();
		$this->setupScenarios();	//this should instantiate the two scenario properties automatically
	}
	
	/** @test */
	public function it_automatically_instantiated_all_scenario_properties()
	{
		$this->assertInstanceOf(SimpleScenario::class, $this->simpleScenario);
		$this->assertInstanceOf(ComplexScenario::class, $this->complexScenario);
	}
}
<?php
namespace AntonioPrimera\TestScenarios\Tests\Unit;

use AntonioPrimera\TestScenarios\TestContext;
use AntonioPrimera\TestScenarios\Tests\Context\AppContext;
use AntonioPrimera\TestScenarios\Tests\Context\ComplexScenario;
use AntonioPrimera\TestScenarios\Tests\Context\SimpleScenario;
use AntonioPrimera\TestScenarios\Tests\Context\TestModels\Comment;
use AntonioPrimera\TestScenarios\Tests\Context\TestModels\Product;
use AntonioPrimera\TestScenarios\Tests\TestCase;
use AntonioPrimera\TestScenarios\TestScenario;

class ScenarioCreationTest extends TestCase
{
	
	/** @test */
	public function it_can_create_a_simple_scenario()
	{
		$scenario = new SimpleScenario($this);
		$this->assertInstanceOf(TestScenario::class, $scenario);
		$this->assertInstanceOf(AppContext::class, $scenario->getContext());
		$this->assertSame($this, $scenario->getTestCase());
	}
	
	/** @test */
	public function it_can_retrieve_attributes_directly_from_the_context()
	{
		$simpleScenario = new SimpleScenario($this);
		
		$this->assertInstanceOf(Product::class, $simpleScenario->prod_1);
		$this->assertSame($simpleScenario->prod_1, $simpleScenario->getContext()->prod_1);
	}
	
	/** @test */
	public function it_can_run_several_different_scenarios_at_the_same_time()
	{
		$simpleScenario = new SimpleScenario($this);
		$complexScenario = new ComplexScenario($this);
		
		$this->assertInstanceOf(Product::class, $simpleScenario->prod_1);
		$this->assertNull($simpleScenario->prod_2);
		$this->assertInstanceOf(Product::class, $complexScenario->prod_1);
		$this->assertInstanceOf(Product::class, $complexScenario->prod_2);
	}
	
	/** @test */
	public function it_can_forward_assertions_to_its_test_case()
	{
		$simpleScenario = new SimpleScenario($this);
		
		$simpleScenario->assertNotNull($simpleScenario->prod_1);
		$this->assertSame($simpleScenario->prod_1, $simpleScenario->getContext()->prod_1);
	}
	
	/** @test */
	public function it_can_assert_it_has_a_specific_model_under_a_specific_attribute_name()
	{
		$simpleScenario = new SimpleScenario($this);
		
		$simpleScenario->assertInstanceOf(Comment::class, 'comment_1');
		$simpleScenario->assertHas(Product::class, 'prod_1');
	}
	
}
<?php

namespace AntonioPrimera\TestScenarios;

use AntonioPrimera\TestScenarios\Traits\Assertions;
use PHPUnit\Framework\TestCase;

abstract class TestScenario
{
	use Assertions;
	
	protected TestContext $context;
	protected TestCase $testCase;
	
	public function __construct(TestCase $testCase, mixed $setupData = null)
	{
		$this->testCase = $testCase;
		$this->context = $this->createTestContext($testCase);
		
		$this->setup($setupData);
	}
	
	/**
	 * Set up the test scenario by creating all necessary models
	 * and data, and adding them to the $testContext
	 */
	abstract public function setup(mixed $data = null);
	
	/**
	 * Create a new TestContext instance, using your project's
	 * TestContext class (with all traits and stuff)
	 */
	abstract protected function createTestContext(TestCase $testCase): TestContext;
	
	//--- Getters -----------------------------------------------------------------------------------------------------
	
	public function getContext(): TestContext
	{
		return $this->context;
	}
	
	public function getTestCase(): TestCase
	{
		return $this->testCase;
	}
	
	//--- Magic stuff -------------------------------------------------------------------------------------------------
	
	public function __get(string $name)
	{
		return $this->context->$name;
	}
	
	public function __set(string $name, $value): void
	{
		$this->context->$name = $value;
	}
	
	public function __call(string $name, array $arguments)
	{
		//run assertions on the TestCase instance
		if (str_starts_with($name, 'assert'))
			return $this->testCase->$name(...$arguments);
		
		//forward everything else to the context
		return $this->context->$name(...$arguments);
	}
}
<?php

namespace AntonioPrimera\TestScenarios\Tests\Context;

use AntonioPrimera\TestScenarios\TestContext;
use AntonioPrimera\TestScenarios\TestScenario;
use PHPUnit\Framework\TestCase;

/**
 * Seeds a single product and comment for smoke tests.
 */
class SimpleScenario extends TestScenario
{
	
	public function setup(mixed $data = null)
	{
		$context = $this->context;
		/* @var AppContext $context */
		
		$context->createProduct('prod_1', ['name' => 'SP1']);
		$context->createComment('comment_1', 'prod_1');
	}
	
	protected function createTestContext(?TestCase $testCase): TestContext
	{
		return new AppContext($testCase);
	}
}

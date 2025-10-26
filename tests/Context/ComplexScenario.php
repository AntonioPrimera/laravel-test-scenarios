<?php

namespace AntonioPrimera\TestScenarios\Tests\Context;

use AntonioPrimera\TestScenarios\TestContext;
use AntonioPrimera\TestScenarios\TestScenario;
use PHPUnit\Framework\TestCase;

class ComplexScenario extends TestScenario
{
	
	public function setup(mixed $data = null)
	{
		$context = $this->context;
		/* @var AppContext $context */
		
		$context->createProduct('prod_1', ['name' => 'CP1']);
		$context->createProduct('prod_2');
		$context->createComment('comment_11', 'prod_1');
		$context->createComment('comment_21', 'prod_2');
		$context->createComment('comment_22', 'prod_2');
	}
	
	protected function createTestContext(?TestCase $testCase): TestContext
	{
		return new AppContext($testCase);
	}
}

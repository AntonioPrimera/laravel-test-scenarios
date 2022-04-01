<?php
namespace AntonioPrimera\TestScenarios\Tests\Unit;

use AntonioPrimera\TestScenarios\TestContext;
use AntonioPrimera\TestScenarios\Tests\Context\AppContext;
use AntonioPrimera\TestScenarios\Tests\Context\TestModels\Comment;
use AntonioPrimera\TestScenarios\Tests\Context\TestModels\Product;
use AntonioPrimera\TestScenarios\Tests\TestCase;

class ContextCreationTest extends TestCase
{
	/** @test */
	public function it_can_create_a_simple_test_context()
	{
		$context = new AppContext($this);
		$this->assertInstanceOf(TestContext::class, $context);
	}
	
	/** @test */
	public function it_can_create_and_retrieve_a_simple_product()
	{
		$context = new AppContext($this);
		$product = $context->createProduct('prod_1', ['name' => 'My Product']);
		
		$this->assertInstanceOf(Product::class, $product);
		$this->assertInstanceOf(Product::class, $context->prod_1);
		$this->assertTrue($product === $context->prod_1);
	}
	
	/** @test */
	public function it_can_create_2_related_models()
	{
		$context = new AppContext($this);
		$product = $context->createProduct('prod_1', ['name' => 'My Product']);
		$comment1 = $context->createComment('comment_1', $product, ['body' => 'Bla']);
		$comment2 = $context->createComment('comment_2', 'prod_1', ['body' => 'Bla Bla']);
		
		$this->assertInstanceOf(Product::class, $context->prod_1);
		$this->assertInstanceOf(Comment::class, $context->comment_1);
		$this->assertInstanceOf(Comment::class, $context->comment_2);
		
		$this->assertTrue($product === $context->comment_1->product);
		$this->assertTrue($product === $context->comment_2->product);
	}
	
	
}
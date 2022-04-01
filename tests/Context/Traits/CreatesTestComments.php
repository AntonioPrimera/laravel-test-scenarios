<?php

namespace AntonioPrimera\TestScenarios\Tests\Context\Traits;

use AntonioPrimera\TestScenarios\Tests\Context\TestModels\Comment;
use AntonioPrimera\TestScenarios\Tests\Context\TestModels\Product;
use Illuminate\Support\Str;

trait CreatesTestComments
{
	
	public function createComment(string $attribute, $product, array $data = [])
	{
		$productInstance = $this->getInstance(Product::class, $product, true);
		/* @var Product $productInstance */
		
		$comment = new Comment($data);
		$comment->product()->associate($productInstance);
		
		return $this->set($attribute, $comment);
	}
	
	public function createAnonymousComment($product)
	{
		$productInstance = $this->getInstance(Product::class, $product, true);
		/* @var Product $productInstance */
		
		$comment = new Comment(['body' => 'lorem ipsum...']);
		$comment->product()->associate($productInstance);
		
		return $this->set('Comment:' . Str::random(4), $comment);
	}
}
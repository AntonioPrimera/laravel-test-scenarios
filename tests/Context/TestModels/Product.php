<?php

namespace AntonioPrimera\TestScenarios\Tests\Context\TestModels;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
	protected $guarded = [];
	
	public function comments()
	{
		return $this->hasMany(Comment::class);
	}
}
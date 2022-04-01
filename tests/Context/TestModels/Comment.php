<?php

namespace AntonioPrimera\TestScenarios\Tests\Context\TestModels;

use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
	protected $guarded = [];
	
	public function product()
	{
		return $this->belongsTo(Product::class);
	}
}
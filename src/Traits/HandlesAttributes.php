<?php
namespace AntonioPrimera\TestScenarios\Traits;

use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

trait HandlesAttributes
{
    protected array $attributes = [];
    
    //--- Attribute handling ------------------------------------------------------------------------------------------
    
    public function all(): array
    {
        return $this->attributes;
    }
    
    public function set(?string $attribute, mixed $value): mixed
    {
    	//if the attribute is empty, save the item with a random generated key
    	$key = $attribute ?: class_basename($value) . ':' . Str::random(4);
    	
    	Arr::set($this->attributes, $key, $value);
    	
        return $value;
    }
    
    public function get(string $attribute): mixed
    {
        return Arr::get($this->attributes, $attribute);
    }
    
    /**
     * Goes through all models stored in the current
     * context and gets fresh copies from the DB
     */
    public function refreshModels(): static
    {
        foreach ($this->attributes as $item)
            if ($item instanceof Model)
            	$item->refresh();
            
		return $this;
    }
    
    /**
	 * This method retrieves an object instance from the context.
	 * It requires the expected class and the name assigned
	 * to the instance when added to the context
     */
    public function getInstance(string $expectedClass, mixed $attributeOrInstance, bool $required = false): mixed
    {
        //if the attribute is required, it can not be empty
        if ($required && !$attributeOrInstance)
            throw new Exception("Given parameter is required and must be an instance of {$expectedClass} or reference a context attribute containing an instance of this class.");
            
        if (!$attributeOrInstance)
            return null;
        
        $val = is_string($attributeOrInstance)
            ? $this->get($attributeOrInstance)
            : $attributeOrInstance;
        
        //a list of possible expected classes can be given at least one must match
		//(useful for parents of polymorphic models)
		foreach (Arr::wrap($expectedClass) as $expectedClassName) {
			if ($val instanceof $expectedClassName)
				return $val;
		}
	
		throw new Exception("Given parameter must be an instance of {$expectedClass} or reference a context attribute containing an instance of this class.");
    }
    
    //--- Magic stuff -------------------------------------------------------------------------------------------------
    
    public function __set($attribute, $value)
    {
        $this->set($attribute, $value);
    }
    
    public function __get($attribute)
    {
        return $this->get($attribute);
    }
}
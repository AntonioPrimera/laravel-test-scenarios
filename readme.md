# Laravel Test Scenarios

This package allows you to easily create and reuse test scenarios in your project, and even publish
test scenarios in your packages.

## Overview

Test Scenarios allow you to create a set of different models once, and reuse or even create 
other scenarios by extending existing ones.

For example, if you created a BlogScenario for a blog project with 2 users (editor, reader)
and 2 posts (publishedPost and unpublishedPost), you could easily do something like this:

```php
/** @test */
public function a_reader_can_only_read_published_posts()
{
    $scenario = new BlogScenario($this);
    $scenario->login($scenario->reader);
    
    $this->get(route('posts', ['slug' => $this->scenario->publishedPost->slug]))
        ->assertSuccessful();
    
    $this->get(route('posts', ['slug' => $this->scenario->unpublishedPost->slug]))
        ->assertStatus(404);
}
```

## Installation

You can install this package to your project, in your dev dependencies via composer:

`composer require --dev antonioprimera/laravel-test-contexts`

## Creating Scenarios

Test Contexts are a sort of model factories for Test Scenarios, and because each project has
a different set of models and model relations, each project should have its own TestContext
class.

Each TestScenario class uses under its hood a specific TestContext class - preferably the one
you created for your project. You can have any number of TestScenario classes using the same
TestContext class.

### Step 1: create the TestContext class for your project

Create your TestContext class, in your 'tests/TestContext' folder, by inheriting
`AntonioPrimera\TestScenarios\TestContext`.

### Step 2: create your model factory methods

A TestContext will have various methods, used as factories, allowing you to create models. For
example, a TestContext for a Blog project would have methods like:

```php
public function createPost(string $key, $author, $data = [])
{
    //allow the user to provide the author instance or its context key
    $authorInstance = $this->getInstance(User::class, $author, true);
    $post = $authorInstance->posts()->create($data);
    
    return $this->set($key, $post);
}

public function createComment(string $key, $parentPost, $author, $data = [])
{
    //allow the user to provide the post instance or its context key
    $postInstance = $this->getInstance(Post::class, $parentPost, true);
    
    //allow the user to provide the author instance or its context key
    $authorInstance = $this->getInstance(User::class, $author, true);
    
    $comment = $postInstance
        ->comments()
        ->create(array_merge($data, ['author_id' => $authorInstance->id]));
        
    return $this->set($key, $comment);
}
```

You should enable your methods to use either model instances or the context keys of models
already created in the context.

You can add these methods directly to your project's TestContext file, but this can get very
complicated, very fast. I recommend you to create several Traits, one for each model, and
include these traits to your TestContext class. For the example above you would create two
traits `tests/Context/Traits/CreatesPosts.php` and `tests/Context/Traits/CreatesComments.php`.

### Step 3: create your TestScenarios

You can create a TestScenario, by extending the abstract class
`AntonioPrimera\TestScenarios\TestScenario` and implementing the 2 abstract methods **setup**
and **createTestContext**.

The **createTestContext** must return a TestContext instance, usually the one created in your
project.

The **setup** method should use the created TestContext to create all models for the scenario
and assign them to corresponding keys (so they can be easily retrieved later).

Here's an example:

```php
class SimpleBlogScenario extends TestScenario
{

    public function setup(mixed $data = null)
    {
        $context = $this->context;
        /* @var AppContext $context */
        
        //create all necessary models for this scenario
        $context->createUser('editor', ['role' => 'editor']);
        $context->createUser('reader', ['role' => 'reader']);
        $context->createPost('publishedPost', 'editor', ['published' => true]);
        $context->createPost('unpublishedPost', 'editor', ['published' => false]);
        $context->createComment('readerComment', 'publishedPost', 'reader', ['body' => 'Nice post']);
    }
    
    protected function createTestContext(TestCase $testCase): TestContext
    {
        //the test context created previously for your own project
        return new BlogContext($testCase);
    }
}
```

## Using Scenarios

The models are created using the TestContext within the TestScenario and are handled by the
TestContext. The TestScenario is a wrapper for the TestContext and forwards all attributes
and methods via magic getter, magic setters and magic method calls to the TestContext.

In other words, the TestScenario exposes all created models and TestContext methods.

### Accessing the models from a TestScenario

When you create a model inside your TestScenario, you should use the TestContext
`set($key, $valueOrModel)` method. This assigns the model to a key.

You can access your created models via the keys you assigned them in the TestScenario setup
method. For example, if you created an admin user and assigned it to the `admin` key in your
TestContext (during the TestScenario setup), you can access it via `$scenario->admin`.

Here's an example, continuing the examples above:

```php
/** @test */
public function a_reader_can_only_read_published_posts()
{
    $scenario = new BlogScenario($this);
    $scenario->login($scenario->reader);
    
    $this->get(route('posts', ['slug' => $this->scenario->publishedPost->slug]))
        ->assertSuccessful();
    
    $this->get(route('posts', ['slug' => $this->scenario->unpublishedPost->slug]))
        ->assertStatus(404);
}
```

### Accessing TestContext methods from a TestScenario

Any public methods you create in your TestContext, will be accessible on your TestScenario
instance.

### Login and Logout of TestScenario Users (actors)

Two methods, which are built into your TestContext and TestScenario are `login($actor)` and
`logout()`. These allow you to easily log in an actor (User) you created on your TestScenario,
by providing either the User model instance or its TestContext key.

For example, to log in the `reader` user from the above examples, you can do something like this:

```php
$scenario->login($scenario->reader);
//OR
$scenario->login('reader');
```

### Handling TestContext models and data

#### set(?string $key, mixed $value)

This method assigns a value (usually a model instance) to the TestContext, with the given key.

You can create and assign 'anonymous' models, by providing `null` for the key. This will assign
the model to a randomly generated key.

#### get(string $key)

This method retrieves the TestContext value assigned to the given key, or null if no value was
assigned to that key.

#### getInstance(string|iterable $expectedClass, mixed $keyOrInstance, bool $required = false)

This method can be used whenever you want to get an object of a specific class, but you want
to be able to accept either the model or its context key.

If the model was given as the second argument ($attributeOrInstance) it is just returned if
its class matches the expected class. If a context key was given (as a string), the object
from the context is retrieved and returned if its class matches the expected class.

In some cases, you might allow the model to have one of many classes, so you can provide a list
of acceptable classes as the first argument. This is an edge case, but there were some valid
use cases where this was handy.

#### refreshModels()

This method refreshes all model instances, by re-fetching them from the DB (using their
`refresh()` method). In some cases, this is needed, because applications are stateless and
your real life application uses fresh models.
# Laravel Test Scenarios

This package allows you to easily create and reuse test scenarios in your project, and even publish
test scenarios in your packages.

## Overview

Test Scenarios allow you to create a set of models which resemble a live application, and reuse these
scenarios in several tests. You can create any number of test scenarios, with different sets of models
and application setup (e.g. application settings, configuration etc.).

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

`composer require --dev antonioprimera/laravel-test-scenarios`

## Overview and basic concepts

In order to create and use scenarios, you have to first create the "TestContext". The TestContext is
nothing more than a fancy model factory, which should be smart enough to create models and adjust the
settings and configuration of the application. In most cases, one TestContext is enough for an application.

After the TestContext is created, you can create any number of scenarios, using this context. Inside
a TestScenario, in the Scenario Setup method, you will use the TestContext methods to create the models
and adjust the configuration and application settings.

After you created the TestScenarios, you just instantiate them in your tests, and you can use the models
created inside the scenarios and you can easily create more models, using the scenario context.

## Creating the Scenarios

### Step 1: create a TestContext for your project

Create your TestContext class, in your `tests/Context` folder, by inheriting
`AntonioPrimera\TestScenarios\TestContext`.

### Step 2: create your model factory methods

A TestContext will have various public methods, used as factories, allowing you to create models. For
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

In order to keep your TestContext class clean, you should group these methods in traits and just include
them in your TestContext class. For the example above you would create two
traits `tests/Context/Traits/CreatesPosts.php` and `tests/Context/Traits/CreatesComments.php`.

### Step 3: create your TestScenarios

You can create a TestScenario, in folder `tests/Scenarios`, by extending the abstract class
`AntonioPrimera\TestScenarios\TestScenario` and implementing the 2 abstract methods **setup**
and **createTestContext**.

The **createTestContext** must return the TestContext instance, which will be used to create the scenario
models and data.

The **setup** method should use the previously instantiated Context to create all models for the scenario
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

### Instantiating scenarios

In order to use a scenario, you must instantiate it, by providing the current test instance to its
constructor.

```php
$scenario = new SimpleBlogScenario($this);
```

Or you can instantiate it in your TestCase setup method.

```php
protected SimpleBlogScenario $scenario;

protected function setUp(): void
{
    parent::setUp();
    $this->scenario = new SimpleBlogScenario($this);  //then just use $this->scenario in your tests
}
```

Or you can use a trait in your base TestCase, which automatically instantiates any typed scenario
property in any of the inheriting TestCases, so you don't have to do anything other than declare
the scenario property.

Here is how your base TestCase class could look like:

```php
namespace Tests;

use AntonioPrimera\TestScenarios\HasScenarios;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication,
        HasScenarios;               //1. add this trait

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->setupScenarios();    //2. call this method to magically instantiate scenarios
        
        //... other setup stuff
    }
}
```

Here is how one of your application TestCase could look like:

```php
namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Scenarios\SimpleBlogScenario;
use Tests\TestCase;

class PostTest extends TestCase
{
    use RefreshDatabase;

    protected SimpleBlogScenario $scenario;     //1. declare your scenarios with the proper type

    /** @test */
    public function a_moderator_can_edit_any_post()
    {
        $this->scenario->login('moderator');    //2. just use the scenario (it was magically set up)
        $this->patch(
            route('posts.update', ['post-id' => $this->scenario->post->id]),
            ['title' => 'Updated Title']
        );
        
        $this->assertEquals('Updated Title', $this->scenario->post->fresh()->title);
    }
}
```

### Accessing the models from a TestScenario

When you create a model inside your TestScenario, you can create a property for each model created
inside the Scenario class, but you can also use the TestContext `set($key, $valueOrModel)` method.
This assigns a model or some piece of data to a key in the Context, which can be easily (magically)
accessed on the Scenario instance.

You can then magically access your created models via the keys you assigned them in the TestScenario
setup method. For example, if you created an admin user and assigned it to the `admin` key, you can
access it via `$scenario->admin`.

Here's a test example, continuing the blog example above:

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

Any public methods you create in your TestContext, will be accessible on your TestScenario instance.
For linting support from your IDE, you can also directly access the context instance on your scenario.

```php
$scenario->createPost('somePost', ['title' => 'Some Title', 'body' => 'Some Body']);
//is equivalent to
$scenario->context->createPost('somePost', ['title' => 'Some Title', 'body' => 'Some Body']);

//then, to access the post you can do:
$post = $scenario->somePost;                    //this is the magic version
//or
$post = $scenario->context->get('somePost');    //this actually happens under the hood
```

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
your real life application uses fresh models in subsequent requests.
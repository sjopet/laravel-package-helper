<?php

use Illuminate\View\Factory;
use Orchestra\Testbench\TestCase as OrchestraTestCase;

class TestCase extends OrchestraTestCase
{
    public function getMockForTrait(
        $traitName,
        array $arguments = array(),
        $mockClassName = '',
        $callOriginalConstructor = true,
        $callOriginalClone = true,
        $callAutoload = true,
        $mockedMethods = array(),
        $cloneArguments = false
    ) {
        $mock = parent::getMockForTrait($traitName, $arguments, $mockClassName, $callOriginalConstructor,
            $callOriginalClone, $callAutoload, $mockedMethods, $cloneArguments);

        $mock->expects($this->any())->method('path')->willReturn('path/to/package');
        $mock->expects($this->any())->method('name')->willReturn('ac_me');

        return $mock;
    }

    public function assetsTest($providerClass)
    {
        $this->assertPublishes($providerClass, 'ac-me-assets', 'resources/assets', public_path('vendor/ac-me'));
    }

    public function publicAssetsTest($providerClass)
    {
        $this->assertPublishes($providerClass, 'ac-me-public', 'public', public_path('vendor/ac-me'));
    }

    public function bladeDirectiveTest()
    {
        $this->assertEquals('hello, default', $this->renderView('test'));
        $this->assertEquals('hello, Jeroen', $this->renderView('params'));
    }

    public function configTest($providerClass)
    {
        $this->assertPublishes($providerClass, 'ac-me-config', 'config/ac_me.php', config_path('ac_me.php'));

        $this->assertEquals('Hello!', config('ac_me.hi'));
    }

    public function migrationsTest($providerClass)
    {
        $this->assertPublishes($providerClass, 'ac-me-migrations', 'database/migrations', database_path('migrations'));
    }

    public function translationsTest($providerClass)
    {
        $this->assertPublishes(
            $providerClass,
            'ac-me-translations',
            'resources/lang',
            resource_path('lang/vendor/ac_me')
        );

        $this->assertEquals(trans('ac_me::test.hi'), 'Hello dude!');
    }

    public function viewsTest($providerClass)
    {
        $this->assertPublishes(
            $providerClass,
            'ac-me-views',
            'resources/views',
            resource_path('views/vendor/ac_me')
        );

        $this->assertEquals(view('ac_me::test')->render(), 'Yes!');
    }

    private function getPackagePath($path)
    {
        return __DIR__ . "/stubs/package/$path";
    }

    private function assertPublishes($providerClass, $tag, $packagePath, $appPath)
    {
        $provider = $this->app->getProvider($providerClass);

        $paths = $provider->pathsToPublish($providerClass, $tag);

        $this->assertEquals($appPath, $paths[$this->getPackagePath($packagePath)]);
    }

    private function renderView($view)
    {
        /** @var Factory $viewFactory */
        $viewFactory = $this->app->make(Factory::class);

        $viewFactory->addLocation(__DIR__ . '/stubs');

        return $viewFactory->make($view)->render();
    }
}

class DirectiveStub
{
    private $injected;

    public function __construct(Injected $injected)
    {
        $this->injected = $injected;
    }

    public function test($a = 'default')
    {
        return $this->injected->message . $a;
    }
}

class Injected
{
    public $message = 'hello, ';
}
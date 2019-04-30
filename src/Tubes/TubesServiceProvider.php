<?php namespace Steenbag\Tubes;

use Steenbag\Tubes\Auth\Middleware;
use Steenbag\Tubes\Certificate\RsaZipCertStore;
use Steenbag\Tubes\Illuminate\Container;
use Steenbag\Tubes\Illuminate\FileSystem;
use Steenbag\Tubes\Illuminate\Repository;
use Steenbag\Tubes\Illuminate\Router;
use Steenbag\Tubes\Illuminate\Request;
use Steenbag\Tubes\Illuminate\Authenticator;
use Steenbag\Tubes\Keys\Ardent\ApiKeyProvider;
use Illuminate\Support\ServiceProvider;

class TubesServiceProvider extends ServiceProvider
{

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->registerBindings();
        $this->registerApiManager();
        $this->registerCommands();
    }

    protected function registerApiManager()
    {
        $this->app['tubes.api-manager'] = $this->app->share(function ($app) {
            $manager = $app->make('Steenbag\Tubes\Manager\ApiManager');
            $manager->initServices(config('steenbag/tubes::rpc-services'));

            return $manager;
        });
    }

    protected function registerBindings()
    {
        $this->app->bind('Steenbag\Tubes\Contract\Container', function ($app) {
            return new Container($app);
        }, true);
        $this->app->bind('Steenbag\Tubes\Contract\Repository', function ($app) {
            return new Repository($app['config']);
        }, true);
        $this->app->bind('Steenbag\Tubes\Contract\Request', function ($app) {
            return new Request($app['request']);
        }, true);
        $this->app->bind('Steenbag\Tubes\Contract\FileSystem', function ($app) {
            return new FileSystem($app['files']);
        }, true);
        $this->app->bind('Steenbag\Tubes\Contract\Authenticator', function ($app) {
            return new NullImpl\Authenticator();
        }, true);
        $this->app->bind('Steenbag\Tubes\Contract\ApiKeyProvider', function ($app) {
            return new ApiKeyProvider();
        }, true);
        $this->app->bind('Steenbag\Tubes\Contract\CertStore', function ($app) {
            $certStore = new RsaZipCertStore($app['Steenbag\Tubes\Contract\FileSystem']);

            $certStore->setBasePath($app['config']->get('steenbag/tubes::key-storage-path'), storage_path('api-keys/'));

            return $certStore;
        }, true);
        $this->app->bind('Steenbag\Tubes\Keys\ApiKeyProviderInterface', 'Steenbag\Tubes\Keys\Ardent\ApiKeyProvider');
    }

    protected function registerCommands()
    {
        $commands = [
            'command.thrift.gen' => 'Steenbag\Tubes\Console\NgThriftServiceCommand'
        ];
        // Register our commands.
        foreach ($commands as $binding => $command) {
            $this->app[$binding] = $this->app->share(function ($app) use ($command) {
                return $app->make($command);
            });
        }

        $this->commands(array_keys($commands));
    }

    /**
     * Bootstrap the application events.
     *
     * @return void
     */
    public function boot()
    {
        $this->mergeConfigFrom(__DIR__ . '/../../config/config.php', 'steenbag/tubes');
    }

}

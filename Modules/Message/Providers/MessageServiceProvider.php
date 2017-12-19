<?php

namespace Modules\Message\Providers;

use Illuminate\Foundation\AliasLoader;
use Illuminate\Support\ServiceProvider;
use Illuminate\Database\Eloquent\Factory;
use Modules\Message\Services\MessageService;
use Modules\Message\Services\SmsService;
use Modules\Message\Services\MailService;
use Modules\Message\Services\SystemService;
use Modules\Message\Services\PushService;

class MessageServiceProvider extends ServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

    /**
     * Boot the application events.
     *
     * @return void
     */
    public function boot()
    {
        $this->addFacade();
        $this->registerTranslations();
        $this->registerConfig();
        $this->registerViews();
        $this->registerFactories();
    }

    /**
     * 门面注册
     */
    public function addFacade()
    {
        $loader = AliasLoader::getInstance();
        $loader->alias('MessageService',\Modules\Message\Facades\MessageFacade::class);
        $loader->alias('SmsService',\Modules\Message\Facades\SmsFacade::class);
        $loader->alias('MailService',\Modules\Message\Facades\MailFacade::class);
        $loader->alias('SystemService',\Modules\Message\Facades\SystemFacade::class);
        $loader->alias('PushService',\Modules\Message\Facades\PushFacade::class);
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('MessageService',function(){
            return new MessageService();
        });
        $this->app->singleton('SmsService',function(){
            return new SmsService();
        });
        $this->app->singleton('MailService',function(){
            return new MailService();
        });
        $this->app->singleton('SystemService',function(){
            return new SystemService();
        });
        $this->app->singleton('PushService',function(){
            return new PushService();
        });
    }

    /**
     * Register config.
     *
     * @return void
     */
    protected function registerConfig()
    {
        $this->publishes([
            __DIR__.'/../Config/config.php' => config_path('message.php'),
        ], 'config');
        $this->mergeConfigFrom(
            __DIR__.'/../Config/config.php', 'message'
        );
    }

    /**
     * Register views.
     *
     * @return void
     */
    public function registerViews()
    {
        $viewPath = resource_path('views/modules/message');

        $sourcePath = __DIR__.'/../Resources/views';

        $this->publishes([
            $sourcePath => $viewPath
        ]);

        $this->loadViewsFrom(array_merge(array_map(function ($path) {
            return $path . '/modules/message';
        }, \Config::get('view.paths')), [$sourcePath]), 'message');
    }

    /**
     * Register translations.
     *
     * @return void
     */
    public function registerTranslations()
    {
        $langPath = resource_path('lang/modules/message');

        if (is_dir($langPath)) {
            $this->loadTranslationsFrom($langPath, 'message');
        } else {
            $this->loadTranslationsFrom(__DIR__ .'/../Resources/lang', 'message');
        }
    }

    /**
     * Register an additional directory of factories.
     * @source https://github.com/sebastiaanluca/laravel-resource-flow/blob/develop/src/Modules/ModuleServiceProvider.php#L66
     */
    public function registerFactories()
    {
        if (! app()->environment('production')) {
            app(Factory::class)->load(__DIR__ . '/Database/factories');
        }
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [];
    }
}

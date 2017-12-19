<?php

namespace App\Providers;

use Illuminate\Foundation\AliasLoader;
use Illuminate\Support\ServiceProvider;

class ThirdServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        //
        $this->addFacade();
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->registerOss();
        $this->registerWeChat();
        $this->registerAlipay();
        $this->registerThirdValidator();
        $this->registerJpush();
    }

    public function registerOss()
    {
        $this->app->bind('third.oss', 'Libraries\Service\OssService');
    }

    public function registerWeChat()
    {
        //$this->app->bind('third.wechat', 'Libraries\Services\WechatService');
    }

    public function registerAlipay()
    {
        //$this->app->bind('third.alipay', 'Libraries\Services\AlipayService');
    }
    public function registerThirdValidator()
    {
        $this->app->bind('third.thirdValidator','Libraries\Service\ThirdValidatorService');
    }
    public function registerJpush()
    {
        $this->app->bind('third.jpush','Libraries\Service\JpushService');
    }

    public function addFacade()
    {

        $loader = AliasLoader::getInstance();

        $loader->alias('Oss',  \App\Facades\OssFacade::class);
        $loader->alias('thirdValidator',  \App\Facades\ThirdValidatorFacade::class);
        $loader->alias('jpush',\App\Facades\JpushFacade::class);
    }

}

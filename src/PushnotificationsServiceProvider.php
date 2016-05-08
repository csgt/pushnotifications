<?php namespace Csgt\Pushnotifications;

use Illuminate\Support\ServiceProvider;
use Illuminate\Foundation\AliasLoader;

class PushnotificationsServiceProvider extends ServiceProvider {

	protected $defer = false;

	public function boot() {
		$this->mergeConfigFrom(__DIR__ . '/config/csgtpushnotifications.php', 'csgtpushnotifications');
		AliasLoader::getInstance()->alias('Pushnotifications','Csgt\Pushnotifications\Pushnotifications');

		$this->publishes([
      __DIR__.'/config/csgtpushnotifications.php' => config_path('csgtpushnotifications.php'),
    ], 'config');
	}

	public function register() {
		$this->app['pushnotifications'] = $this->app->share(function($app) {
    	return new Pushnotifications;
  	});
	}

	public function provides() {
		return ['pushnotifications'];
	}
}
<?php namespace Csgt\Pushnotifications\Facades;
 
use Illuminate\Support\Facades\Facade;
 
class Pushnotifications extends Facade {
  protected static function getFacadeAccessor() { return 'pushnotifications'; }
}
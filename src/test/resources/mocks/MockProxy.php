<?php


class MockProxy {
  public static function setStaticExpectations($mock) {
    static::$mock = $mock;
  }
  
  public static function __callStatic($name, $args) {
    return call_user_func_array(
        array(static::$mock,$name),
        $args
    );
  }
}
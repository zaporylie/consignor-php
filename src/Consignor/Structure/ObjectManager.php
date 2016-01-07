<?php

namespace Consignor\Structure;

/**
 * Class ObjectManager
 * @package Consignor
 */
class ObjectManager {

  /**
   * @param null $object
   */
  public function __construct($object = NULL) {
    if (is_array($object) || is_object($object)) {
      foreach ($object as $key => $value) {
        try {
          $this->setValue($key, $value);
        }
        catch (\Exception $e) {
          continue;
        }
      }
    }
  }

  /**
   * @param $key
   * @param $value
   *
   * @throws \Exception
   */
  public function setValue($key, $value) {
    if (!property_exists($this, $key)) {
      throw new \Exception("Key $key does not exist.");
    }
    if (is_array($this->{$key}) && is_object($value) && $class = $this->nestedClass($key)) {
      $this->{$key}[] = new $class($value);
    }
    elseif (is_array($this->{$key}) && !is_array($value)) {
      $this->addValue($key, $value);
    }
    elseif (is_array($this->{$key}) && is_array($value)) {
      foreach ($value as $v) {
        $this->setValue($key, $v);
      }
    }
    else {
      $this->{$key} = $value;
    }
  }

  /**
   * @param $key
   * @param $value
   *
   * @throws \Exception
   */
  public function addValue($key, $value) {
    if (!property_exists($this, $key)) {
      throw new \Exception("Key $key does not exist.");
    }
    if (!is_array($this->{$key})) {
      throw new \Exception("Key $key is not an array.");
    }
    $this->{$key}[] = $value;
  }

  /**
   * @param $key
   * @param null $index
   *
   * @return array
   * @throws \Exception
   */
  public function getValue($key, $index = NULL) {
    if (!property_exists($this, $key)) {
      throw new \Exception("Key $key does not exist.");
    }
    if (is_array($this->{$key}) && isset($index)) {
      return $this->{$key}[$index];
    }
    return $this->{$key};
  }

  /**
   * @param $key
   *
   * @return bool
   */
  protected function nestedClass($key) {
    return FALSE;
  }

  /**
   * @return array
   */
  public function jsonSerialize() {
    return get_object_vars($this);
  }

  /**
   * @return array
   */
  public function toArray() {
    $params = get_object_vars($this);
    foreach ($params as $key => $param) {
      if (is_array($param)) {
        foreach ($param as $k => $item) {
          if (is_object($item) && method_exists(get_class($item), 'toArray')) {
            $params[$key][$k] = $item->toArray();
          }
          else {
            $params[$key][$k] = get_object_vars($this);
          }
        }
      }
      elseif (is_object($param) && method_exists(get_class($param), 'toArray')) {
        $params[$key] = $param->toArray();
      }
    }
    return $params;
  }
}

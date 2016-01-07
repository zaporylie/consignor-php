<?php

namespace Consignor\Structure;

/**
 * Class Line
 * @package Consignor
 */
class Line extends ObjectManager {
  protected $Number;
  protected $PkgWeight;
  protected $Pkgs = array();

  /**
   * @param $key
   * @return bool|string
   */
  protected function nestedClass($key) {
    switch ($key) {
      case 'Pkgs':
        return 'Consignor\Structure\Package';

      default:
        return FALSE;
    }
  }
}

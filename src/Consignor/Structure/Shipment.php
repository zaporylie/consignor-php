<?php

namespace Consignor\Structure;

/**
 * Class Shipment
 * @package Consignor
 */
class Shipment extends ObjectManager {

  protected $ShpCSID;
  protected $Kind = CONSIGNOR_SHIPMENT_KIND_ESKNORMAL;
  protected $ActorCSID = CONSIGNOR_TEST_ACTOR;
  protected $OrderNo;
  protected $ProdConceptID;
  protected $Addresses = array();
  protected $Lines = array();

  /**
   * @param $key
   * @return bool|string
   */
  protected function nestedClass($key) {
    switch ($key) {
      case 'Addresses':
        return 'Consignor\Structure\Address';

      case 'Lines':
        return 'Consignor\Structure\Line';

      default:
        return FALSE;
    }
  }
}

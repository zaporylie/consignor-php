<?php

/**
 * @file
 *
 */

namespace Consignor;

define('CONSIGNOR_ADDRESS_KIND_ESAKRECEIVER', 1);
define('CONSIGNOR_ADDRESS_KIND_ESAKSENDER', 2);
define('CONSIGNOR_SHIPMENT_KIND_ESKNORMAL', 1);
define('CONSIGNOR_TEST_ACTOR', 63);
define('CONSIGNOR_TEST_KEY', 'sample');

/**
 * Class ConsignorServer
 * @package Consignor
 */
class ConsignorServer extends Consignor {

  protected $ServerRequestType = 'JSON';
  protected $ServerURL = 'http://consignorsupport.no/ship/ShipmentServerModule.dll';
  protected $ServerRequestHeaders = array(
    'Content-type: multipart/form-data',
  );
  protected $Request = array(
    'actor' => CONSIGNOR_TEST_ACTOR,
    'key' => CONSIGNOR_TEST_KEY,
  );

  /**
   * @param null $Request
   */
  public function __construct($Request = NULL) {
    if (isset($Request)) {
      $this->Request = $Request;
    }
  }

  /**
   * @return bool
   */
  public function getProducts() {
    $request = $this->Request;
    $request['command'] = 'GetProducts';
    $data = $this->postRequest($request);
    $data = $this->deserializeData($data);
    if (isset($data->Carriers)) {
      return $data->Carriers;
    }
    return FALSE;
  }
}

/**
 * Class Shipment
 * @package Consignor
 */
class Shipment extends ObjectManager {

  protected $ShpCSID;
  protected $Kind = CONSIGNOR_SHIPMENT_KIND_ESKNORMAL;
  protected $ActorCSID = CONSIGNOR_TEST_ACTOR;
  protected $ProdConceptID;
  protected $Addresses = array();
  protected $Lines = array();

  /**
   * @param $Key
   * @return bool|string
   */
  protected function NestedClass($Key) {
    switch ($Key) {
      case 'Addresses':
        return 'Consignor\Address';

      case 'Lines':
        return 'Consignor\Line';

      default:
        return FALSE;
    }
  }
}

/**
 * Class Address
 * @package Consignor
 */
class Address extends ObjectManager {
  public $Kind;
  public $Name1;
  public $Street1;
  public $PostCode;
  public $City;
  public $CountryCode;
}

/**
 * Class Line
 * @package Consignor
 */
class Line extends ObjectManager {
  protected $Number;
  protected $PkgWeight;
  protected $Pkgs = array();

  /**
   * @param $Key
   * @return bool|string
   */
  protected function NestedClass($Key) {
    switch ($Key) {
      case 'Pkgs':
        return 'Consignor\Package';

      default:
        return FALSE;
    }
  }
}

/**
 * Class Package
 * @package Consignor
 */
class Package extends ObjectManager {
  protected $ItemNo;
  protected $PkgNo;
  protected $Barcode1;
  protected $Barcode2;
}

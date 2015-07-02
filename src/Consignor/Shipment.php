<?php

/**
 * @file
 *
 */

namespace Consignor;

class ShipmentAPI extends ConsignorServer {

  public $Shipment;
  public $Options = array(
    'Labels' => 'PNG',
  );

  public function __construct($ShpCSID = NULL, $Request = NULL) {
    if (isset($ShpCSID)) {
      $this->loadShipment($ShpCSID);
    }
    else {
      $this->Shipment = new Shipment();
    }
    parent::__construct($Request);
  }

  public function loadShipment($ShpCSID) {
    // @todo Request

  }

  public function createShipment($data = NULL) {
    if (empty($data)) {
      $data = $this->Shipment->getData();
    }
    $request = $this->Request;
    $request['command'] = 'SubmitShipment';
    $request['data'] = $this->serializeRequestData($data);
    $request['Options'] = $this->serializeRequestData($this->Options);
    $response = $this->postRequest($request);
    $this->Shipment->setValue($this->deserializeData($response));
    return $this->Shipment;
  }

  public function saveShipment() {

  }

  public function deleteShipment() {

  }

  public function getLabel() {

  }
}

class Shipment {

  protected $Shipment;

  public function __construct($Shipment = array()) {
    $this->Shipment = $Shipment;
  }

  public function setValue($data) {
    $this->Shipment = $data;
  }

  public function getData() {
    return $this->Shipment;
  }
}

//
//class Shipment {
//  public $ShpCSID;
//  public $Kind;
//  public $ActorCSID;
//  public $ProdConceptID;
//  public $Addresses;
//  public $Lines;
//
//  public function __construct($Kind, $ActorCSID, $ProdConceptID, $Addresses, $Lines) {
//    $this->Kind = $Kind;
//    $this->ActorCSID = $ActorCSID;
//    $this->ProdConceptID = $ProdConceptID;
//    $this->Addresses = $Addresses;
//    $this->Lines = $Lines;
//  }
//}
//
//class Address {
//  public $Kind;
//  public $Name1;
//  public $Street1;
//  public $PostCode;
//  public $City;
//  public $CountryCode;
//
//  public function __construct($Kind, $Name1, $Street1, $PostCode, $City, $CountryCode) {
//    $this->$Kind = $Kind;
//    $this->$Name1 = $Name1;
//    $this->$Street1 = $Street1;
//    $this->$PostCode = $PostCode;
//    $this->$City = $City;
//    $this->$CountryCode = $CountryCode;
//  }
//}
//
//class Line {
//  public $PkgWeight;
//  public $Pkgs;
//
//  public function __construct($PkgWeight, $Pkgs) {
//    $this->$PkgWeight = $PkgWeight;
//    $this->$Pkgs = $Pkgs;
//  }
//}
//
//class Package {
//  public $ItemNo;
//
//  public function __construct($ItemNo) {
//    $this->$ItemNo = $ItemNo;
//  }
//}
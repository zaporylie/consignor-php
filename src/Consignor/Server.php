<?php

/**
 * @file
 *
 */

namespace Consignor;

class ConsignorServer extends Consignor {

  protected $ServerRequestType = 'JSON';
  protected $ServerURL = 'http://consignorsupport.no/ship/ShipmentServerModule.dll';
  protected $ServerRequestHeaders = array(
    'Content-type: multipart/form-data',
  );
  protected $Request = array(
    'actor' => 63,
    'key' => 'sample',
  );

  public function __construct($Request = NULL) {
    if (isset($Request)) {
      $this->Request = $Request;
    }
  }

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
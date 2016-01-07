<?php

/**
 * @file
 * Shipment API.
 *
 * @package Consignor
 */

namespace Consignor;

use Consignor\Structure\Shipment;

/**
 * Class ShipmentAPI
 */
class ShipmentAPI extends ConsignorServer {

  public $Shipment;
  public $Options = array(
    'Labels' => 'PNG',
  );

  /**
   * @param null $ShpCSID
   * @param null $Request
   */
  public function __construct($ShpCSID = NULL, $Request = NULL) {
    if (isset($ShpCSID)) {
      $this->loadShipment($ShpCSID);
    }
    else {
      $this->Shipment = new Shipment();
    }
    parent::__construct($Request);
  }

  /**
   * @param $ShpCSID
   * @return \Consignor\Structure\Shipment
   */
  public function loadShipment($ShpCSID) {
    $request = $this->Request;
    $request['command'] = 'GetShipment';
    $request['data'] = $this->serializeRequestData(array('ShpCSID' => $ShpCSID));
    $request['Options'] = $this->serializeRequestData($this->Options);
    $response = $this->postRequest($request);
    $this->Shipment = new Shipment($this->deserializeData($response));
    return $this->Shipment;
  }

  /**
   * @param null $data
   * @return \Consignor\Structure\Shipment;
   */
  public function createShipment($data = NULL) {
    if (empty($data)) {
      $data = $this->Shipment;
    }
    $request = $this->Request;
    $request['command'] = 'SubmitShipment';
    $request['data'] = $this->serializeRequestData($data);
    $request['Options'] = $this->serializeRequestData($this->Options);
    $response = $this->postRequest($request);
    $this->Shipment = new Shipment($this->deserializeData($response));
    return $this->Shipment;
  }

  /**
   * @return mixed
   */
  public function getLabel() {
    $request = $this->Request;
    $request['command'] = 'ReprintLabels';
    $request['data'] = $this->serializeRequestData($this->Shipment);
    $request['Options'] = $this->serializeRequestData($this->Options);
    $response = $this->postRequest($request);
    $response = $this->deserializeData($response);
    return $response->Labels[0]->Content;
  }

  /**
   * @return mixed
   */
  public function getTrackingURL() {
    $request = $this->Request;
    $request['command'] = 'GetTrackingURL';
    $request['data'] = $this->serializeRequestData($this->Shipment);
    $request['Options'] = $this->serializeRequestData($this->Options);
    $response = $this->postRequest($request);
    $response = $this->deserializeData($response);
    return $response->TrackingURL;
  }
}

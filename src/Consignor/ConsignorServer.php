<?php

/**
 * @file
 *
 */

namespace Consignor;

/**
 * Class ConsignorServer
 * @package Consignor
 */
class ConsignorServer extends Consignor {

  /**
   *
   */
  const CONSIGNOR_ADDRESS_KIND_ESAKRECEIVER = 1;

  /**
   *
   */
  const CONSIGNOR_ADDRESS_KIND_ESAKSENDER = 2;

  /**
   *
   */
  const CONSIGNOR_SHIPMENT_KIND_ESKNORMAL = 1;

  /**
   *
   */
  const CONSIGNOR_TEST_SERVER = 'http://consignorsupport.no/ship/ShipmentServerModule.dll';

  /**
   *
   */
  const CONSIGNOR_TEST_ACTOR = 63;

  /**
   *
   */
  const CONSIGNOR_TEST_KEY = 'sample';

  /**
   * @var
   */
  protected $ServerAPIKey;

  /**
   * @var string
   */
  protected $ServerRequestType = 'JSON';

  /**
   * @var string
   */
  protected $ServerURL = self::CONSIGNOR_TEST_SERVER;

  /**
   * @var array
   */
  protected $ServerRequestHeaders = array(
    'Content-type: multipart/form-data',
  );

  /**
   * @var array|null
   */
  protected $Request = array(
    'actor' => self::CONSIGNOR_TEST_ACTOR,
    'key' => self::CONSIGNOR_TEST_KEY,
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
   *
   * @todo throw an Exception if no Carriers provided.
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

  /**
   * @param array $data
   *
   * @return mixed
   * @throws \Exception
   */
  protected function getRequest($data = array()) {
    return $this->httpRequest('GET', $data);
  }

  /**
   * @param array $data
   *
   * @return mixed
   * @throws \Exception
   */
  protected function postRequest($data = array()) {
    return $this->httpRequest('POST', $data);
  }

  /**
   * @param $type
   * @param $data
   *
   * @return mixed
   * @throws \Exception
   */
  private function httpRequest($type, $data) {
    $ch = curl_init();
    switch ($type) {
      case 'GET':
        curl_setopt($ch, CURLOPT_URL, $this->ServerURL . '?' . $data);
        break;

      case 'POST':
        curl_setopt($ch, CURLOPT_URL, $this->ServerURL);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        break;
    }
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
    curl_setopt($ch, CURLINFO_HEADER_OUT, TRUE);

    $data = curl_exec($ch);
    $info = curl_getinfo($ch);

    $error = $this->deserializeData($data);
    if ($info['http_code'] != 200 || isset($error->ErrorMessages)) {
      $error_message = '';
      if (isset($error->ErrorMessages)) {
        $error_message .= implode(', ', $error->ErrorMessages);
      }
      else {
        $error_message = 'Error when getting ' . $this->ServerURL . '. Message: "';
        $error_message .= strip_tags($error);
        $error_message .= '" (' . $info['http_code'] . ')';
      }
      throw new \Exception($error_message);
    }
    return $data;
  }

  /**
   * @param array $data
   * @param bool $method
   *
   * @return array|string
   */
  protected function serializeRequestData($data = array(), $method = FALSE) {
    if (!$method) {
      $method = $this->ServerRequestType;
    }
    switch ($method) {
      case 'JSON':
        if (is_object($data) && method_exists(get_class($data), 'toArray')) {
          $data = $data->toArray();
        }
        return json_encode($data);

      case 'QUERY':
        return http_build_query($data);

      default:
        return $data;
    }
  }

  /**
   * @param $data
   * @param bool $method
   *
   * @return mixed
   */
  protected function deserializeData($data, $method = FALSE) {
    if (!$method) {
      $method = $this->ServerRequestType;
    }
    switch ($method) {
      case 'JSON':
        return json_decode($data);

      default:
        return $data;
    }
  }
}

<?php

/**
 * @file
 *
 */

namespace Consignor;

class Consignor {

  protected $ServerRequestType;
  protected $ServerURL;
  protected $ServerAPIKey;
  protected $ServerRequestHeaders;

  protected function getRequest($data = array()) {
    return $this->httpRequest('GET', $data);
  }

  protected function postRequest($data = array()) {
    return $this->httpRequest('POST', $data);
  }

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

    if ($info['http_code'] != 200) {
      $data = $this->deserializeData($data);
      $error_message = '';
      if (isset($data->ErrorMessages)) {
        $error_message .= implode(', ', $data->ErrorMessages);
      }
      else {
        $error_message = 'Error when getting ' . $this->ServerURL . '. Message: "';
        $error_message .= strip_tags($data);
        $error_message .= '" (' . $info['http_code'] . ')';
      }
      throw new \Exception($error_message);
    }
    return $data;
  }

  protected function serializeRequestData($data = array(), $method = FALSE) {
    if (!$method) {
      $method = $this->ServerRequestType;
    }
    switch ($method) {
      case 'JSON':
        return json_encode($data);

      case 'QUERY':
        return http_build_query($data);

      default:
        return $data;
    }
  }

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
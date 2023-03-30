<?php

namespace App\Libraries;

class ApiResponse {
  public static function send($statusCode, $message, $data = null) {
    http_response_code($statusCode);

    $response = [
      'success' => $statusCode >= 200 && $statusCode < 300,
      'status' => $statusCode,
      'message' => $message
    ];

    if (is_array($data) && empty($data)) {
      $response['data'] = array();
    }

    if ($data != NULL) {
      $response['data'] = $data;
    }

    echo json_encode($response);

    die;
  }
}

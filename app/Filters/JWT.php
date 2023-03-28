<?php

namespace App\Filters;

use CodeIgniter\API\ResponseTrait;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Filters\FilterInterface;
use Firebase\JWT\JWT;

class JWT implements FilterInterface {
  use ResponseTrait;

  public function before(RequestInterface $request, $arguments = null) {
    // check for JWT token in headers
    $authHeader = $request->getServer('HTTP_AUTHORIZATION');
    if (!$authHeader) {
      return $this->failUnauthorized('JWT token is missing');
    }

    // extract token from headers
    $token = sscanf($authHeader, 'Bearer %s')[0];
    if (!$token) {
      return $this->failUnauthorized('Invalid token');
    }

    try {
      // verify JWT token
      $payload = JWT::decode($token, getenv('JWT_SECRET'), ['HS256']);
    } catch (\Exception $e) {
      return $this->failUnauthorized('Invalid token');
    }

    // set authenticated user to request
    $request->user = $payload;

    return $request;
  }

  public function after(RequestInterface $request, ResponseInterface $response, $arguments = null) {
    // Do nothing
  }
}

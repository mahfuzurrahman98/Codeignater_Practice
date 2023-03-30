<?php

namespace App\Libraries;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class AuthToken {
  private static $issuer = 'localhost';
  private static $audience = 'localhost';

  public static function create($user) {
    $payload = [
      "iss" => self::$issuer,
      "iat" => time(),
      "nbf" => time() + 10,
      "exp" => time() + 3600,
      "aud" => self::$audience,
      "user" => $user
    ];

    $header = [
      "alg" => "HS256",
      "typ" => "JWT",
      "kid" => getenv('JWT_KID') // Add the "kid" claim to the header
    ];

    return JWT::encode($payload, getenv('JWT_SECRET'), "HS256", null, $header);
  }

  public static function verify($token) {
    $decoded = JWT::decode($token, new Key(getenv('JWT_SECRET'), 'HS256'));
    return (array)$decoded;
  }
}

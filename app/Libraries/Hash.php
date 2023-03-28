<?php

namespace App\Libraries;

class Hash {
  public static function make($plainTextPassword) {
    return password_hash($plainTextPassword, PASSWORD_BCRYPT);
  }

  public static function verify($plainTextPassword, $hashedPassword) {
    return password_verify($plainTextPassword, $hashedPassword);
  }
}

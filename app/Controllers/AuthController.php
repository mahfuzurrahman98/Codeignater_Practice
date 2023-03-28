<?php

namespace App\Controllers;

use App\Models\UserModel;
use CodeIgniter\RESTful\ResourceController;
use Firebase\JWT\JWT;

class AuthController extends ResourceController {
  protected $format = 'json';

  public function register() {
    // validate user input
    $validation =  \Config\Services::validation();
    $validation->setRules([
      'name' => 'required',
      'email' => 'required|valid_email|is_unique[users.email]',
      'password' => 'required'
    ]);
    if (!$validation->run($this->request->getPost())) {
      return $this->respond(['error' => $validation->getErrors()], 400);
    }

    // save user to database
    $user = new UserModel();
    $user->fill($this->request->getPost());
    $user->password = password_hash($this->request->getPost('password'), PASSWORD_DEFAULT);
    $user->save();

    // generate JWT token
    $token = JWT::encode(['email' => $user->email], getenv('JWT_SECRET'));

    return $this->respond(['token' => $token]);
  }

  public function login() {
    // validate user input
    $validation =  \Config\Services::validation();
    $validation->setRules([
      'email' => 'required|valid_email',
      'password' => 'required'
    ]);
    if (!$validation->run($this->request->getPost())) {
      return $this->respond(['error' => $validation->getErrors()], 400);
    }

    // authenticate user
    $user = new UserModel();
    $user = $user->where('email', $this->request->getPost('email'))->first();
    if (!$user || !password_verify($this->request->getPost('password'), $user->password)) {
      return $this->respond(['error' => 'Invalid email or password'], 401);
    }

    // generate JWT token
    $token = JWT::encode(['email' => $user->email], getenv('JWT_SECRET'));

    return $this->respond(['token' => $token]);
  }

  public function logout() {
    // invalidate JWT token
    return $this->respond(['message' => 'Logged out']);
  }
}

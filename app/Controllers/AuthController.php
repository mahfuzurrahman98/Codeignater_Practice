<?php

namespace App\Controllers;

use App\Libraries\Hash;
use App\Models\UserModel;
use App\Controllers\BaseController;
use App\Libraries\ApiResponse;
use App\Libraries\AuthToken;
use CodeIgniter\API\ResponseTrait;


class AuthController extends BaseController {
  // use ResponseTrait;

  protected $validator;
  protected $model;
  protected $issuer = 'http://localhost:8080';
  protected $audience = 'http://localhost:8080';

  public function __construct() {
    $this->validator = \Config\Services::validation();
    $this->model = new UserModel();
  }

  public function register() {
    try {
      $data = (array)$this->request->getJSON();

      $this->validator->setRules([
        'name' => 'required',
        'email' => 'required|valid_email|is_unique[users.email]',
        'password' => 'required'
      ]);

      if (!$this->validator->run($data)) {
        return $this->respond(
          [
            'success' => false,
            'status' => 400,
            'message' => $this->validator->getErrors()
          ],
          400
        );
      }

      $data['password'] = Hash::make($data['password']);
      $this->model->save($data);

      $userData = [
        'id' => $this->model->getInsertID(),
        'name' => $data['name'],
        'email' => $data['email']
      ];
      ApiResponse::send(201, 'User created successfully', $userData);
    } catch (\Exception $e) {
      ApiResponse::send(500, $e->getMessage());
    }
  }

  public function login() {
    try {
      $data = (array) $this->request->getJSON();

      $this->validator->setRules([
        'email' => 'required|valid_email',
        'password' => 'required'
      ]);

      if (!$this->validator->run($data)) {
        ApiResponse::send(400, $this->validator->getErrors());
      }

      $user = $this->model->where('email', $data['email'])->first();

      if (!$user || !Hash::verify($data['password'], $user['password'])) {
        ApiResponse::send(401, 'Invalid email or password');
      }

      ApiResponse::send(200, 'User logged in successfully', [
        'token' => AuthToken::create([
          'id' => $user['id'],
          'name' => $user['name'],
          'email' => $user['email']
        ]),
        'user' => [
          'id' => $user['id'],
          'name' => $user['name'],
          'email' => $user['email']
        ]
      ]);
    } catch (\Exception $e) {
      ApiResponse::send(500, $e->getMessage());
    }
  }

  public function profile() {
    try {
      $user = (array) $this->request->user;
      ApiResponse::send(200, 'User profile', $user);
    } catch (\Exception $e) {
      ApiResponse::send(500, $e->getMessage());
    }
  }

  // public function logout() {
  //   // invalidate JWT token
  //   return $this->respond(['message' => 'Logged out']);
  // }
}

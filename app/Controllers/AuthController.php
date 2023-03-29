<?php

namespace App\Controllers;

use App\Libraries\Hash;
use App\Models\UserModel;
use App\Controllers\BaseController;
use App\Libraries\AuthToken;
use CodeIgniter\API\ResponseTrait;


class AuthController extends BaseController {
  use ResponseTrait;

  protected $validator;
  protected $model;
  protected $issuer = 'http://localhost:8080';
  protected $audience = 'http://localhost:8080';

  public function __construct() {
    $this->validator = \Config\Services::validation();
    $this->model = new UserModel();
  }

  public function register() {
    $data = (array)$this->request->getJSON();

    try {
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

      return $this->respond(
        [
          'success' => true,
          'status' => 201,
          'message' => 'User created successfully',
          'data' => [
            'id' => $this->model->getInsertID(),
            'name' => $data['name'],
            'email' => $data['email']
          ]
        ],
        201
      );
    } catch (\Exception $e) {
      return $this->respond(
        [
          'success' => false,
          'status' => 500,
          'message' => $e->getMessage()
        ],
        500
      );
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
        return $this->respond(
          [
            'success' => false,
            'status' => 400,
            'message' => $this->validator->getErrors()
          ],
          400
        );
      }

      $user = $this->model->where('email', $data['email'])->first();

      if (!$user || !Hash::verify($data['password'], $user['password'])) {
        return $this->respond(
          [
            'success' => false,
            'status' => 401,
            'message' => 'Invalid email or password'
          ],
          401
        );
      }

      return $this->respond(
        [
          'success' => true,
          'status' => 200,
          'message' => 'User logged in successfully',
          'token' => AuthToken::create($user),
          'data' => [
            'id' => $user['id'],
            'name' => $user['name'],
            'email' => $user['email']
          ]
        ],
        200
      );
    } catch (\Exception $e) {
      return $this->respond(
        [
          'success' => false,
          'status' => 500,
          'message' => $e->getMessage()
        ],
        500
      );
    }
  }

  public function profile() {
    $user = $this->request->user;

    return $this->respond(
      [
        'success' => true,
        'status' => 200,
        'message' => 'User profile',
        'data' => [
          'id' => $user['id'],
          'name' => $user['name'],
          'email' => $user['email']
        ]
      ],
      200
    );
  }

  // public function logout() {
  //   // invalidate JWT token
  //   return $this->respond(['message' => 'Logged out']);
  // }
}

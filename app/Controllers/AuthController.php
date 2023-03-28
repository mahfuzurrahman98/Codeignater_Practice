<?php

namespace App\Controllers;

use App\Libraries\Hash;
use Firebase\JWT\JWT;
use App\Models\UserModel;
use CodeIgniter\Controller;
use CodeIgniter\API\ResponseTrait;


class AuthController extends Controller {
  use ResponseTrait;

  public function formatResponse($statusCode, $message, $data = NULL) {

    $response = [
      'status' => $statusCode,
      'success' => $statusCode / 100 == 2 ? true : false,
      'message' => $message
    ];

    if (is_array($data) && empty($data)) {
      $response['data'] = array();
    }

    if ($data != NULL) {
      $response['data'] = $data;
    }

    return [$response, $statusCode];
  }

  public function register() {
    $data =  $this->request->getJSON();

    try {
      $validation = \Config\Services::validation();

      $validation->setRules([
        'name' => 'required',
        'email' => 'required|valid_email|is_unique[users.email]',
        'password' => 'required'
      ]);

      if (!$validation->run((array)$data)) {
        return $this->respond(
          [
            'success' => false,
            'status' => 400,
            'message' => $validation->getErrors()
          ],
          400
        );
      }

      $userModel = new UserModel();

      $data->password = Hash::make($data->password);
      $userModel->save($data);

      return $this->respond(
        [
          'success' => true,
          'status' => 201,
          'message' => 'User created successfully',
          'data' => [
            'id' => $userModel->getInsertID(),
            'name' => $data->name,
            'email' => $data->email
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

  // public function login() {
  //   // validate user input
  //   $validation =  \Config\Services::validation();
  //   $validation->setRules([
  //     'email' => 'required|valid_email',
  //     'password' => 'required'
  //   ]);

  //   if (!$validation->run($this->request->getPost())) {
  //     return $this->respond(['error' => $validation->getErrors()], 400);
  //   }

  //   // authenticate user
  //   $user = new UserModel();
  //   $user = $user->where('email', $this->request->getPost('email'))->first();
  //   if (!$user || !password_verify($this->request->getPost('password'), $user->password)) {
  //     return $this->respond(['error' => 'Invalid email or password'], 401);
  //   }

  //   // generate JWT token
  //   $token = JWT::encode(['email' => $user->email], getenv('JWT_SECRET'), 'HS256');

  //   return $this->respond(['token' => $token]);
  // }

  // public function logout() {
  //   // invalidate JWT token
  //   return $this->respond(['message' => 'Logged out']);
  // }
}

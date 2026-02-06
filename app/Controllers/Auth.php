<?php

namespace App\Controllers;

use App\Models\UserModel;

class Auth extends BaseController
{
    public function login()
    {
        // Check if already logged in
        if (session()->get('logged_in')) {
            return redirect()->to(site_url('dashboard'));
        }

        return view('auth/login');
    }

    public function authenticate()
    {
        $session = session();
        $userModel = new UserModel();

        $username = $this->request->getPost('username');
        $password = $this->request->getPost('password');

        $user = $userModel->verifyPassword($username, $password);

        if ($user) {
            $session->set([
                'user_id' => $user['id'],
                'username' => $user['username'],
                'logged_in' => true
            ]);
            return redirect()->to(site_url('dashboard'));
        } else {
            $session->setFlashdata('error', 'Invalid username or password');
            return redirect()->to(site_url('login'));
        }
    }

    public function logout()
    {
        session()->destroy();
        return redirect()->to(site_url('login'));
    }

    public function checkSession()
    {
        // This endpoint checks if the user's session is still valid
        if (session()->get('logged_in')) {
            return $this->response->setJSON([
                'success' => true,
                'message' => 'Session is active'
            ]);
        } else {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Session expired'
            ]);
        }
    }

    public function getVersion()
    {
        // Return application version information
        return $this->response->setJSON([
            'success' => true,
            'name' => env('app.name', 'CertMaster'),
            'version' => env('app.version', '1.0.0'),
            'description' => env('app.description', 'SSL Certificate Management System')
        ]);
    }
}

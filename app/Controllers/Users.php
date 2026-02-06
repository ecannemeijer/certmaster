<?php

namespace App\Controllers;

use App\Models\UserModel;

class Users extends BaseController
{
    protected $userModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
    }

    /**
     * Display list of all users
     */
    public function index()
    {
        $users = \Config\Database::connect()
            ->table('users')
            ->select('id, username, created_at, updated_at')
            ->orderBy('id', 'DESC')
            ->get()
            ->getResultArray();

        return view('users/index', ['users' => $users]);
    }

    /**
     * Show create user form
     */
    public function create()
    {
        return view('users/create');
    }

    /**
     * Store new user
     */
    public function store()
    {
        $username = $this->request->getPost('username');
        $password = $this->request->getPost('password');
        $password_confirm = $this->request->getPost('password_confirm');

        // Validate input
        if (!$username) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Username is required'
            ]);
        }

        if (!$password) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Password is required'
            ]);
        }

        if ($password !== $password_confirm) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Passwords do not match'
            ]);
        }

        if (strlen($password) < 6) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Password must be at least 6 characters'
            ]);
        }

        // Check if username already exists
        $existing = \Config\Database::connect()
            ->table('users')
            ->where('username', $username)
            ->get()
            ->getRow();

        if ($existing) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Username already exists'
            ]);
        }

        // Hash password and insert
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        $result = \Config\Database::connect()
            ->table('users')
            ->insert([
                'username' => $username,
                'password' => $hashedPassword,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ]);

        if ($result) {
            return $this->response->setJSON([
                'success' => true,
                'message' => 'User created successfully'
            ]);
        } else {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Failed to create user'
            ]);
        }
    }

    /**
     * Show edit user form
     */
    public function edit($id)
    {
        $user = \Config\Database::connect()
            ->table('users')
            ->where('id', $id)
            ->get()
            ->getRow();

        if (!$user) {
            return redirect()->to(site_url('users'))->with('error', 'User not found');
        }

        return view('users/edit', ['user' => $user]);
    }

    /**
     * Update user
     */
    public function update($id)
    {
        $username = $this->request->getPost('username');

        // Validate input
        if (!$username) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Username is required'
            ]);
        }

        // Check if username already exists (excluding current user)
        $existing = \Config\Database::connect()
            ->table('users')
            ->where('username', $username)
            ->where('id !=', $id)
            ->get()
            ->getRow();

        if ($existing) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Username already exists'
            ]);
        }

        // Update user
        $result = \Config\Database::connect()
            ->table('users')
            ->where('id', $id)
            ->update([
                'username' => $username,
                'updated_at' => date('Y-m-d H:i:s')
            ]);

        if ($result !== false) {
            return $this->response->setJSON([
                'success' => true,
                'message' => 'User updated successfully'
            ]);
        } else {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Failed to update user'
            ]);
        }
    }

    /**
     * Delete user
     */
    public function delete($id)
    {
        // Prevent deleting the current logged-in user
        if (session()->get('user_id') == $id) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Cannot delete your own user account'
            ]);
        }

        $result = \Config\Database::connect()
            ->table('users')
            ->where('id', $id)
            ->delete();

        if ($result !== false) {
            return $this->response->setJSON([
                'success' => true,
                'message' => 'User deleted successfully'
            ]);
        } else {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Failed to delete user'
            ]);
        }
    }

    /**
     * Show change password form
     */
    public function changePassword($id)
    {
        $user = \Config\Database::connect()
            ->table('users')
            ->where('id', $id)
            ->get()
            ->getRow();

        if (!$user) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'User not found'
            ]);
        }

        return view('users/change_password', ['user' => $user]);
    }

    /**
     * Update password
     */
    public function updatePassword($id)
    {
        $new_password = $this->request->getPost('new_password');
        $new_password_confirm = $this->request->getPost('new_password_confirm');

        // Validate input
        if (!$new_password) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'New password is required'
            ]);
        }

        if ($new_password !== $new_password_confirm) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'New passwords do not match'
            ]);
        }

        if (strlen($new_password) < 6) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Password must be at least 6 characters'
            ]);
        }

        // Verify user exists
        $user = \Config\Database::connect()
            ->table('users')
            ->where('id', $id)
            ->get()
            ->getRow();

        if (!$user) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'User not found'
            ]);
        }

        // Update password
        $hashedPassword = password_hash($new_password, PASSWORD_DEFAULT);

        $result = \Config\Database::connect()
            ->table('users')
            ->where('id', $id)
            ->update([
                'password' => $hashedPassword,
                'updated_at' => date('Y-m-d H:i:s')
            ]);

        if ($result !== false) {
            return $this->response->setJSON([
                'success' => true,
                'message' => 'Password changed successfully'
            ]);
        } else {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Failed to change password'
            ]);
        }
    }
}

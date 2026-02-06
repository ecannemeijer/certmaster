<?php

namespace App\Controllers;

use App\Models\ServerModel;
use App\Models\SshKeyModel;
use App\Models\CertificateModel;

class Servers extends BaseController
{
    protected $serverModel;
    protected $sshKeyModel;
    protected $certificateModel;

    public function __construct()
    {
        $this->serverModel = new ServerModel();
        $this->sshKeyModel = new SshKeyModel();
        $this->certificateModel = new CertificateModel();
    }

    public function index()
    {
        if (!session()->get('logged_in')) {
            return redirect()->to('/login');
        }

        $servers = $this->serverModel->findAll();
        
        // Add certificate information to each server
        foreach ($servers as &$server) {
            $cert = $this->certificateModel->where('server_id', $server['id'])
                                          ->where('is_active', 1)
                                          ->first();
            
            if ($cert) {
                $server['certificate'] = $cert;
                
                // Calculate certificate status
                if ($cert['valid_until']) {
                    $daysUntilExpiry = (strtotime($cert['valid_until']) - time()) / (60 * 60 * 24);
                    
                    if ($daysUntilExpiry < 0) {
                        $server['cert_status'] = 'expired';
                        $server['cert_status_class'] = 'text-red-600';
                    } elseif ($daysUntilExpiry < 30) {
                        $server['cert_status'] = 'expiring';
                        $server['cert_status_class'] = 'text-yellow-600';
                    } else {
                        $server['cert_status'] = 'valid';
                        $server['cert_status_class'] = 'text-green-600';
                    }
                    
                    $server['days_until_expiry'] = round($daysUntilExpiry);
                } else {
                    $server['cert_status'] = 'no_cert';
                    $server['cert_status_class'] = 'text-gray-600';
                }
            } else {
                $server['certificate'] = null;
                $server['cert_status'] = 'no_cert';
                $server['cert_status_class'] = 'text-gray-600';
            }
        }

        $data = [
            'servers' => $servers
        ];

        return view('servers/index', $data);
    }

    public function create()
    {
        if (!session()->get('logged_in')) {
            return redirect()->to('/login');
        }

        return view('servers/create');
    }

    public function store()
    {
        if (!session()->get('logged_in')) {
            return redirect()->to('/login');
        }

        $validation = \Config\Services::validation();
        $validation->setRules([
            'name' => 'required|min_length[3]',
            'hostname' => 'required',
            'ip_address' => 'required|valid_ip',
            'ssh_port' => 'required|numeric',
            'ssh_username' => 'required',
            'certificate_path' => 'required',
        ]);

        if (!$validation->withRequest($this->request)->run()) {
            return redirect()->back()->withInput()->with('errors', $validation->getErrors());
        }

        $data = [
            'name' => $this->request->getPost('name'),
            'hostname' => $this->request->getPost('hostname'),
            'ip_address' => $this->request->getPost('ip_address'),
            'ssh_port' => $this->request->getPost('ssh_port'),
            'ssh_username' => $this->request->getPost('ssh_username'),
            'certificate_path' => $this->request->getPost('certificate_path'),
            'apache_restart_command' => $this->request->getPost('apache_restart_command') ?: 'sudo systemctl restart apache2',
        ];

        $db = \Config\Database::connect();
        $db->table('servers')->insert($data);
        session()->setFlashdata('success', 'Server added successfully');

        return redirect()->to('/servers');
    }

    public function edit($id)
    {
        if (!session()->get('logged_in')) {
            return redirect()->to('/login');
        }

        $data = [
            'server' => $this->serverModel->find($id)
        ];

        if (!$data['server']) {
            session()->setFlashdata('error', 'Server not found');
            return redirect()->to('/servers');
        }

        return view('servers/edit', $data);
    }

    public function update($id)
    {
        if (!session()->get('logged_in')) {
            return redirect()->to('/login');
        }

        $validation = \Config\Services::validation();
        $validation->setRules([
            'name' => 'required|min_length[3]',
            'hostname' => 'required',
            'ip_address' => 'required|valid_ip',
            'ssh_port' => 'required|numeric',
            'ssh_username' => 'required',
            'certificate_path' => 'required',
        ]);

        if (!$validation->withRequest($this->request)->run()) {
            return redirect()->back()->withInput()->with('errors', $validation->getErrors());
        }

        $data = [
            'name' => $this->request->getPost('name'),
            'hostname' => $this->request->getPost('hostname'),
            'ip_address' => $this->request->getPost('ip_address'),
            'ssh_port' => $this->request->getPost('ssh_port'),
            'ssh_username' => $this->request->getPost('ssh_username'),
            'certificate_path' => $this->request->getPost('certificate_path'),
            'apache_restart_command' => $this->request->getPost('apache_restart_command'),
        ];

        $db = \Config\Database::connect();
        $db->table('servers')->where('id', $id)->update($data);
        session()->setFlashdata('success', 'Server updated successfully');

        return redirect()->to('/servers');
    }

    public function delete($id)
    {
        if (!session()->get('logged_in')) {
            return redirect()->to('/login');
        }

        $this->serverModel->delete($id);
        session()->setFlashdata('success', 'Server deleted successfully');

        return redirect()->to('/servers');
    }

    public function generateSshKey($id)
    {
        if (!session()->get('logged_in')) {
            return $this->response->setJSON(['success' => false, 'message' => 'Unauthorized']);
        }

        $id = (int)$id; // Ensure ID is integer
        $server = $this->serverModel->find($id);
        if (!$server) {
            return $this->response->setJSON(['success' => false, 'message' => 'Server not found']);
        }

        // Generate SSH key pair
        $keyPath = WRITEPATH . 'ssh_keys/';
        if (!is_dir($keyPath)) {
            mkdir($keyPath, 0700, true);
        }

        $privateKeyFile = $keyPath . 'server_' . $id . '_rsa';
        $publicKeyFile = $privateKeyFile . '.pub';

        // Remove old keys if they exist
        if (file_exists($privateKeyFile)) {
            unlink($privateKeyFile);
        }
        if (file_exists($publicKeyFile)) {
            unlink($publicKeyFile);
        }

        // Generate key using ssh-keygen
        $command = "ssh-keygen -t rsa -b 4096 -f {$privateKeyFile} -N '' -C 'certmaster@{$server['hostname']}' 2>&1";
        exec($command, $output, $returnVar);

        if ($returnVar !== 0) {
            $errorMsg = implode("\n", $output);
            log_message('error', 'SSH key generation failed: ' . $errorMsg);
            return $this->response->setJSON([
                'success' => false, 
                'message' => 'Failed to generate SSH key: ' . $errorMsg
            ]);
        }

        // Read the generated keys
        $privateKey = file_get_contents($privateKeyFile);
        $publicKey = file_get_contents($publicKeyFile);

        // Calculate fingerprint
        $fingerprintCmd = "ssh-keygen -lf {$publicKeyFile}";
        $fpOutput = [];
        exec($fingerprintCmd, $fpOutput);
        $fingerprint = isset($fpOutput[0]) ? trim($fpOutput[0]) : '';

        // Save to database
        $db = \Config\Database::connect();
        $existingKey = $db->table('ssh_keys')->where('server_id', $id)->limit(1)->get()->getRowArray();
        
        $updateData = [
            'public_key' => $publicKey,
            'private_key' => $privateKey,
            'fingerprint' => $fingerprint
        ];
        
        if ($existingKey) {
            $db->table('ssh_keys')->where('id', $existingKey['id'])->update($updateData);
        } else {
            $insertData = array_merge($updateData, ['server_id' => $id]);
            $db->table('ssh_keys')->insert($insertData);
        }

        return $this->response->setJSON([
            'success' => true,
            'message' => 'SSH key generated successfully',
            'public_key' => $publicKey,
            'fingerprint' => $fingerprint
        ]);
    }
}

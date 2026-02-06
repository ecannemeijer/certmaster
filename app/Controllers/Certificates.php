<?php

namespace App\Controllers;

use App\Models\ServerModel;
use App\Models\CertificateModel;
use App\Models\SshKeyModel;
use App\Models\DeployLogModel;

class Certificates extends BaseController
{
    protected $serverModel;
    protected $certificateModel;
    protected $sshKeyModel;
    protected $deployLogModel;

    public function __construct()
    {
        $this->serverModel = new ServerModel();
        $this->certificateModel = new CertificateModel();
        $this->sshKeyModel = new SshKeyModel();
        $this->deployLogModel = new DeployLogModel();
    }

    public function upload($serverId)
    {
        if (!session()->get('logged_in')) {
            return redirect()->to('/login');
        }

        $server = $this->serverModel->find($serverId);
        if (!$server) {
            session()->setFlashdata('error', 'Server not found');
            return redirect()->to('/dashboard');
        }

        $data = ['server' => $server];
        return view('certificates/upload', $data);
    }

    public function store($serverId)
    {
        if (!session()->get('logged_in')) {
            return $this->response->setJSON(['success' => false, 'message' => 'Unauthorized']);
        }

        $pemFile = $this->request->getFile('pem_file');
        $keyFile = $this->request->getFile('key_file');
        $customFilename = $this->request->getPost('custom_filename') ?? '';

        // Validate file extensions manually
        $allowedPemExts = ['pem', 'crt', 'cer'];
        $allowedKeyExts = ['key', 'pem'];

        $pemExt = strtolower(pathinfo($pemFile->getClientName(), PATHINFO_EXTENSION));
        $keyExt = strtolower(pathinfo($keyFile->getClientName(), PATHINFO_EXTENSION));

        $errors = [];
        if (!in_array($pemExt, $allowedPemExts)) {
            return $this->response->setJSON(['success' => false, 'message' => 'Invalid PEM file extension']);
        }
        if (!in_array($keyExt, $allowedKeyExts)) {
            return $this->response->setJSON(['success' => false, 'message' => 'Invalid KEY file extension']);
        }

        // Validate that files were uploaded
        if (!$pemFile || $pemFile->getError() !== UPLOAD_ERR_OK) {
            return $this->response->setJSON(['success' => false, 'message' => 'PEM file upload failed']);
        }
        if (!$keyFile || $keyFile->getError() !== UPLOAD_ERR_OK) {
            return $this->response->setJSON(['success' => false, 'message' => 'KEY file upload failed']);
        }

        // Create uploads directory if not exists
        $uploadPath = WRITEPATH . 'uploads/certificates/';
        if (!is_dir($uploadPath)) {
            mkdir($uploadPath, 0755, true);
        }

        // Generate filenames - use custom name if provided, otherwise use original uploaded names
        if (!empty($customFilename)) {
            // Sanitize custom filename
            $customFilename = preg_replace('/[^a-zA-Z0-9_-]/', '', $customFilename);
            $pemFileName = $customFilename . '.pem';
            $keyFileName = $customFilename . '.key';
        } else {
            // Use original uploaded filenames (sanitized)
            $originalPemName = pathinfo($pemFile->getClientName(), PATHINFO_FILENAME);
            $originalKeyName = pathinfo($keyFile->getClientName(), PATHINFO_FILENAME);
            
            // Sanitize original filenames
            $originalPemName = preg_replace('/[^a-zA-Z0-9_-]/', '', $originalPemName);
            $originalKeyName = preg_replace('/[^a-zA-Z0-9_-]/', '', $originalKeyName);
            
            $pemFileName = $originalPemName . '.pem';
            $keyFileName = $originalKeyName . '.key';
        }

        // Move files
        if (!$pemFile->move($uploadPath, $pemFileName)) {
            return $this->response->setJSON(['success' => false, 'message' => 'Failed to upload PEM file']);
        }

        if (!$keyFile->move($uploadPath, $keyFileName)) {
            return $this->response->setJSON(['success' => false, 'message' => 'Failed to upload KEY file']);
        }

        // Parse certificate information
        $pemContent = file_get_contents($uploadPath . $pemFileName);
        $certInfo = $this->parseCertificate($pemContent);

        // Deactivate old certificates using direct database query
        $db = \Config\Database::connect();
        $db->table('certificates')
            ->where('server_id', $serverId)
            ->update(['is_active' => 0]);

        // Insert new certificate using direct database query
        $data = [
            'server_id' => $serverId,
            'pem_file' => $pemFileName,
            'key_file' => $keyFileName,
            'common_name' => $certInfo['common_name'] ?? null,
            'valid_from' => $certInfo['valid_from'] ?? null,
            'valid_until' => $certInfo['valid_until'] ?? null,
            'is_active' => 1,
            'uploaded_at' => date('Y-m-d H:i:s'),
        ];

        $db->table('certificates')->insert($data);

        return $this->response->setJSON(['success' => true, 'message' => 'Certificate uploaded successfully']);
    }

    public function info($serverId)
    {
        if (!session()->get('logged_in')) {
            return $this->response->setJSON(['success' => false, 'message' => 'Unauthorized']);
        }

        $server = $this->serverModel->find($serverId);
        if (!$server) {
            return $this->response->setJSON(['success' => false, 'message' => 'Server not found']);
        }

        // Try to get live certificate from the server via HTTPS
        $certData = $this->getLiveCertificate($server['hostname'], $server['ip_address']);

        if ($certData['success']) {
            // Certificate found on server - use live data
            return $this->response->setJSON($certData);
        } else {
            // Fallback to uploaded certificate data
            $certificate = $this->certificateModel->where('server_id', $serverId)
                                                  ->where('is_active', 1)
                                                  ->first();

            if (!$certificate) {
                return $this->response->setJSON(['success' => false, 'message' => 'No active certificate found']);
            }

            // Calculate days until expiry from uploaded certificate
            $daysUntilExpiry = (strtotime($certificate['valid_until']) - time()) / (60 * 60 * 24);
            
            // Parse certificate file for additional details
            $uploadPath = WRITEPATH . 'uploads/certificates/';
            $pemFile = $uploadPath . $certificate['pem_file'];
            
            $certDetails = [
                'success' => true,
                'common_name' => $certificate['common_name'],
                'valid_from' => $certificate['valid_from'],
                'valid_until' => $certificate['valid_until'],
                'days_until_expiry' => round($daysUntilExpiry),
                'source' => 'uploaded'
            ];
            
            // Try to extract additional details from the certificate file
            if (file_exists($pemFile)) {
                try {
                    $pemContent = file_get_contents($pemFile);
                    $certInfo = openssl_x509_parse($pemContent);
                    
                    if ($certInfo) {
                        // Extract issuer
                        if (isset($certInfo['issuer']['O'])) {
                            $issuer = $certInfo['issuer']['O'];
                            if (isset($certInfo['issuer']['CN'])) {
                                $issuer .= ' - ' . $certInfo['issuer']['CN'];
                            }
                            $certDetails['issuer'] = $issuer;
                        } elseif (isset($certInfo['issuer']['CN'])) {
                            $certDetails['issuer'] = $certInfo['issuer']['CN'];
                        }
                        
                        // Extract SAN
                        $san = [];
                        if (isset($certInfo['extensions']['subjectAltName'])) {
                            $sanString = $certInfo['extensions']['subjectAltName'];
                            $sanArray = array_map('trim', explode(',', $sanString));
                            foreach ($sanArray as $name) {
                                $san[] = preg_replace('/^[A-Za-z]+:\s*/', '', $name);
                            }
                            $certDetails['san'] = $san;
                        }
                        
                        // Get fingerprint
                        $fingerprint = openssl_x509_fingerprint($pemContent, 'sha1', false);
                        if ($fingerprint) {
                            $certDetails['fingerprint'] = strtoupper($fingerprint);
                        }
                    }
                } catch (\Exception $e) {
                    // Silently fail - still return basic details
                }
            }

            return $this->response->setJSON($certDetails);
        }
    }

    private function getLiveCertificate($hostname, $ipAddress)
    {
        try {
            // Try hostname first
            $certData = $this->fetchCertificateData($hostname);
            if ($certData === false) {
                // Try IP address if hostname fails
                $certData = $this->fetchCertificateData($ipAddress);
            }

            if ($certData === false) {
                return ['success' => false, 'message' => 'Could not fetch certificate from server'];
            }

            // Parse certificate
            $certInfo = openssl_x509_parse($certData);
            if ($certInfo === false) {
                return ['success' => false, 'message' => 'Could not parse certificate'];
            }

            // Extract certificate information
            $commonName = $certInfo['subject']['CN'] ?? 'Unknown';
            $validFrom = date('Y-m-d H:i:s', $certInfo['validFrom_time_t']);
            $validUntil = date('Y-m-d H:i:s', $certInfo['validTo_time_t']);
            
            // Calculate days until expiry
            $daysUntilExpiry = ($certInfo['validTo_time_t'] - time()) / (60 * 60 * 24);
            
            // Extract issuer information
            $issuer = '';
            if (isset($certInfo['issuer']['O'])) {
                $issuer = $certInfo['issuer']['O'];
                if (isset($certInfo['issuer']['CN'])) {
                    $issuer .= ' - ' . $certInfo['issuer']['CN'];
                }
            } elseif (isset($certInfo['issuer']['CN'])) {
                $issuer = $certInfo['issuer']['CN'];
            }
            
            // Extract Subject Alternative Names (SAN)
            $san = [];
            if (isset($certInfo['extensions']['subjectAltName'])) {
                $sanString = $certInfo['extensions']['subjectAltName'];
                $sanArray = array_map('trim', explode(',', $sanString));
                foreach ($sanArray as $name) {
                    // Remove DNS:, IP:, etc. prefixes
                    $san[] = preg_replace('/^[A-Za-z]+:\s*/', '', $name);
                }
            }
            
            // Get certificate fingerprint (SHA-1)
            $fingerprint = openssl_x509_fingerprint($certData, 'sha1', false);

            $result = [
                'success' => true,
                'common_name' => $commonName,
                'valid_from' => $validFrom,
                'valid_until' => $validUntil,
                'days_until_expiry' => round($daysUntilExpiry),
                'source' => 'live'
            ];
            
            if (!empty($issuer)) {
                $result['issuer'] = $issuer;
            }
            
            if (!empty($san)) {
                $result['san'] = $san;
            }
            
            if ($fingerprint) {
                $result['fingerprint'] = strtoupper($fingerprint);
            }
            
            return $result;
        } catch (\Exception $e) {
            log_message('error', 'Error fetching live certificate: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Error fetching certificate: ' . $e->getMessage()];
        }
    }

    private function fetchCertificateData($host)
    {
        try {
            // Create a stream context with SSL options
            $streamContext = stream_context_create([
                'ssl' => [
                    'capture_peer_cert' => true,
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true
                ]
            ]);

            // Connect to the server on port 443
            $resource = @stream_socket_client(
                'ssl://' . $host . ':443',
                $errno,
                $errstr,
                5,
                STREAM_CLIENT_CONNECT,
                $streamContext
            );

            if (!$resource) {
                return false;
            }

            // Get the certificate
            $params = stream_context_get_params($resource);
            if (!isset($params['options']['ssl']['peer_certificate'])) {
                fclose($resource);
                return false;
            }

            $cert = $params['options']['ssl']['peer_certificate'];
            fclose($resource);

            // Export certificate to PEM format
            openssl_x509_export($cert, $certData);
            return $certData;
        } catch (\Exception $e) {
            log_message('error', 'Error in fetchCertificateData: ' . $e->getMessage());
            return false;
        }
    }

    public function deploy($serverId)
    {
        if (!session()->get('logged_in')) {
            return $this->response->setJSON(['success' => false, 'message' => 'Unauthorized']);
        }

        $server = $this->serverModel->find($serverId);
        if (!$server) {
            return $this->response->setJSON(['success' => false, 'message' => 'Server not found']);
        }

        $certificate = $this->certificateModel->getActiveCertificate($serverId);
        if (!$certificate) {
            return $this->response->setJSON(['success' => false, 'message' => 'No active certificate found']);
        }

        $sshKey = $this->sshKeyModel->getKeyForServer($serverId);
        if (!$sshKey) {
            return $this->response->setJSON(['success' => false, 'message' => 'No SSH key found. Please generate one first.']);
        }

        // Log deployment start using direct database query
        $db = \Config\Database::connect();
        $logData = [
            'server_id' => $serverId,
            'certificate_id' => $certificate['id'],
            'status' => 'pending',
            'message' => 'Deployment started'
        ];
        $db->table('deploy_logs')->insert($logData);
        $logId = $db->insertID();

        // Save private key temporarily
        $keyPath = WRITEPATH . 'ssh_keys/';
        if (!is_dir($keyPath)) {
            mkdir($keyPath, 0700, true);
        }

        $privateKeyFile = $keyPath . 'deploy_key_' . time();
        file_put_contents($privateKeyFile, $sshKey['private_key']);
        chmod($privateKeyFile, 0600);

        // Certificate file paths
        $uploadPath = WRITEPATH . 'uploads/certificates/';
        $pemFile = $uploadPath . $certificate['pem_file'];
        $keyFile = $uploadPath . $certificate['key_file'];

        // Deploy using SCP
        $remotePath = rtrim($server['certificate_path'], '/') . '/';
        $sshPort = $server['ssh_port'];
        $sshUser = $server['ssh_username'];
        $sshHost = $server['ip_address'];

        // Use original filenames for deployment (preserve the custom/original names)
        $remotePemFile = $remotePath . $certificate['pem_file'];
        $remoteKeyFile = $remotePath . $certificate['key_file'];

        // Copy certificate files with original filenames
        $scpPemCmd = "scp -i {$privateKeyFile} -P {$sshPort} -o StrictHostKeyChecking=no {$pemFile} {$sshUser}@{$sshHost}:{$remotePemFile} 2>&1";
        $scpKeyCmd = "scp -i {$privateKeyFile} -P {$sshPort} -o StrictHostKeyChecking=no {$keyFile} {$sshUser}@{$sshHost}:{$remoteKeyFile} 2>&1";

        $commands = [];

        exec($scpPemCmd, $pemOutput, $pemReturn);
        $commands[] = [
            'name' => 'Upload Certificate File',
            'command' => $scpPemCmd,
            'success' => ($pemReturn === 0),
            'output' => implode("\n", $pemOutput)
        ];

        exec($scpKeyCmd, $keyOutput, $keyReturn);
        $commands[] = [
            'name' => 'Upload Key File',
            'command' => $scpKeyCmd,
            'success' => ($keyReturn === 0),
            'output' => implode("\n", $keyOutput)
        ];

        if ($pemReturn !== 0 || $keyReturn !== 0) {
            unlink($privateKeyFile);
            $db->table('deploy_logs')->where('id', $logId)->update([
                'status' => 'failed',
                'message' => 'Failed to copy certificates: ' . implode("\n", array_merge($pemOutput, $keyOutput))
            ]);
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Failed to copy certificates to server',
                'commands' => $commands
            ]);
        }

        // Restart Apache
        $restartCmd = "ssh -i {$privateKeyFile} -p {$sshPort} -o StrictHostKeyChecking=no {$sshUser}@{$sshHost} '{$server['apache_restart_command']}' 2>&1";
        exec($restartCmd, $restartOutput, $restartReturn);

        $commands[] = [
            'name' => 'Restart Apache',
            'command' => $restartCmd,
            'success' => ($restartReturn === 0),
            'output' => implode("\n", $restartOutput)
        ];

        // Clean up
        unlink($privateKeyFile);

        if ($restartReturn !== 0) {
            $db->table('deploy_logs')->where('id', $logId)->update([
                'status' => 'failed',
                'message' => 'Certificates copied but failed to restart Apache: ' . implode("\n", $restartOutput)
            ]);
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Failed to restart Apache service',
                'commands' => $commands
            ]);
        }

        // Update certificate as deployed using direct database query
        $db->table('certificates')->where('id', $certificate['id'])->update([
            'deployed_at' => date('Y-m-d H:i:s')
        ]);

        // Log success
        $db->table('deploy_logs')->where('id', $logId)->update([
            'status' => 'success',
            'message' => 'Certificate deployed and Apache restarted successfully'
        ]);

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Certificate deployed successfully',
            'commands' => $commands
        ]);
    }

    private function parseCertificate($pemContent)
    {
        $cert = openssl_x509_parse($pemContent);
        
        if (!$cert) {
            return [];
        }

        return [
            'common_name' => $cert['subject']['CN'] ?? null,
            'valid_from' => date('Y-m-d H:i:s', $cert['validFrom_time_t']),
            'valid_until' => date('Y-m-d H:i:s', $cert['validTo_time_t']),
        ];
    }
}

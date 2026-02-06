<?php

namespace App\Models;

use CodeIgniter\Model;

class ServerModel extends Model
{
    protected $table = 'servers';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'name',
        'hostname',
        'ip_address',
        'ssh_port',
        'ssh_username',
        'certificate_path',
        'apache_restart_command'
    ];
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    public function getServerWithCertificate($id)
    {
        return $this->select('servers.*, certificates.*, certificates.id as cert_id')
            ->join('certificates', 'certificates.server_id = servers.id AND certificates.is_active = 1', 'left')
            ->where('servers.id', $id)
            ->first();
    }

    public function getAllServersWithCertificates()
    {
        return $this->select('servers.*, certificates.valid_until, certificates.common_name, certificates.id as cert_id')
            ->join('certificates', 'certificates.server_id = servers.id AND certificates.is_active = 1', 'left')
            ->orderBy('servers.name', 'ASC')
            ->findAll();
    }
}

<?php

namespace App\Models;

use CodeIgniter\Model;

class CertificateModel extends Model
{
    protected $table = 'certificates';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'server_id',
        'pem_file',
        'key_file',
        'common_name',
        'valid_from',
        'valid_until',
        'is_active',
        'deployed_at'
    ];
    protected $useTimestamps = false;
    protected $dateFormat = 'datetime';

    public function getActiveCertificate($serverId)
    {
        return $this->where('server_id', $serverId)
            ->where('is_active', 1)
            ->first();
    }

    public function deactivateOldCertificates($serverId)
    {
        return $this->where('server_id', $serverId)
            ->set(['is_active' => 0])
            ->update();
    }

    public function getExpiringCertificates($days = 30)
    {
        $date = date('Y-m-d H:i:s', strtotime("+{$days} days"));
        return $this->select('certificates.*, servers.name as server_name')
            ->join('servers', 'servers.id = certificates.server_id')
            ->where('certificates.is_active', 1)
            ->where('certificates.valid_until <=', $date)
            ->findAll();
    }

    public function getExpiredCertificates()
    {
        return $this->select('certificates.*, servers.name as server_name')
            ->join('servers', 'servers.id = certificates.server_id')
            ->where('certificates.is_active', 1)
            ->where('certificates.valid_until <', date('Y-m-d H:i:s'))
            ->findAll();
    }
}

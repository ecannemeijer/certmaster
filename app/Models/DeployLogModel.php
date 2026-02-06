<?php

namespace App\Models;

use CodeIgniter\Model;

class DeployLogModel extends Model
{
    protected $table = 'deploy_logs';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'server_id',
        'certificate_id',
        'status',
        'message'
    ];
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = false;

    public function getServerLogs($serverId, $limit = 10)
    {
        return $this->where('server_id', $serverId)
            ->orderBy('created_at', 'DESC')
            ->limit($limit)
            ->findAll();
    }

    public function getRecentLogs($limit = 20)
    {
        return $this->select('deploy_logs.*, servers.name as server_name')
            ->join('servers', 'servers.id = deploy_logs.server_id')
            ->orderBy('deploy_logs.created_at', 'DESC')
            ->limit($limit)
            ->findAll();
    }
}

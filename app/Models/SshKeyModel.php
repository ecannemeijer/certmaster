<?php

namespace App\Models;

use CodeIgniter\Model;

class SshKeyModel extends Model
{
    protected $table = 'ssh_keys';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'server_id',
        'public_key',
        'private_key',
        'fingerprint'
    ];
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = false;

    public function getKeyForServer($serverId)
    {
        $db = \Config\Database::connect();
        $query = $db->query("SELECT * FROM {$this->table} WHERE server_id = ? LIMIT 1", [(int)$serverId]);
        return $query->getRowArray();
    }
}

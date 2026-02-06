<?php

namespace App\Controllers;

use App\Models\ServerModel;
use App\Models\CertificateModel;
use App\Models\DeployLogModel;

class Dashboard extends BaseController
{
    protected $serverModel;
    protected $certificateModel;
    protected $deployLogModel;

    public function __construct()
    {
        $this->serverModel = new ServerModel();
        $this->certificateModel = new CertificateModel();
        $this->deployLogModel = new DeployLogModel();
    }

    public function index()
    {
        if (!session()->get('logged_in')) {
            return redirect()->to(site_url('login'));
        }

        $data = [
            'servers' => $this->serverModel->getAllServersWithCertificates(),
            'expiring' => $this->certificateModel->getExpiringCertificates(30),
            'expired' => $this->certificateModel->getExpiredCertificates(),
        ];

        // Calculate certificate status for each server
        foreach ($data['servers'] as &$server) {
            if ($server['valid_until']) {
                $daysUntilExpiry = (strtotime($server['valid_until']) - time()) / (60 * 60 * 24);
                
                if ($daysUntilExpiry < 0) {
                    $server['status'] = 'expired';
                    $server['status_class'] = 'bg-red-100 text-red-800';
                } elseif ($daysUntilExpiry < 30) {
                    $server['status'] = 'expiring';
                    $server['status_class'] = 'bg-yellow-100 text-yellow-800';
                } else {
                    $server['status'] = 'valid';
                    $server['status_class'] = 'bg-green-100 text-green-800';
                }
                
                $server['days_until_expiry'] = round($daysUntilExpiry);
            } else {
                $server['status'] = 'no_cert';
                $server['status_class'] = 'bg-gray-100 text-gray-800';
                $server['days_until_expiry'] = null;
            }
        }

        return view('dashboard/index', $data);
    }

    public function logs()
    {
        if (!session()->get('logged_in')) {
            return redirect()->to(site_url('login'));
        }

        $deployLogModel = new DeployLogModel();
        $logs = $deployLogModel->select('deploy_logs.*, servers.name as server_name')
                              ->join('servers', 'servers.id = deploy_logs.server_id')
                              ->orderBy('deploy_logs.created_at', 'DESC')
                              ->findAll();

        $data = [
            'logs' => $logs
        ];

        return view('dashboard/logs', $data);
    }
}

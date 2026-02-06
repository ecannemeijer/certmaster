<?= $this->extend('layout/app') ?>

<?= $this->section('content') ?>
<div class="space-y-6">
    <!-- Header -->
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">
                <i class="fas fa-dashboard mr-3 text-blue-600"></i>Dashboard
            </h1>
            <p class="text-gray-600 mt-1">Overview of all servers and certificates</p>
        </div>
        <a href="<?= site_url('servers/create') ?>" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg font-medium transition transform hover:scale-105 shadow-md">
            <i class="fas fa-plus mr-2"></i>Add New Server
        </a>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-blue-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm font-medium">Total Servers</p>
                    <p class="text-3xl font-bold text-gray-900 mt-2"><?= count($servers) ?></p>
                </div>
                <div class="bg-blue-100 p-3 rounded-full">
                    <i class="fas fa-server text-blue-600 text-2xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-green-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm font-medium">Valid Certificates</p>
                    <p class="text-3xl font-bold text-gray-900 mt-2">
                        <?= count(array_filter($servers, fn($s) => ($s['status'] ?? '') === 'valid')) ?>
                    </p>
                </div>
                <div class="bg-green-100 p-3 rounded-full">
                    <i class="fas fa-check-circle text-green-600 text-2xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-yellow-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm font-medium">Expiring Soon</p>
                    <p class="text-3xl font-bold text-gray-900 mt-2"><?= count($expiring) ?></p>
                </div>
                <div class="bg-yellow-100 p-3 rounded-full">
                    <i class="fas fa-exclamation-triangle text-yellow-600 text-2xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-red-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm font-medium">Expired</p>
                    <p class="text-3xl font-bold text-gray-900 mt-2"><?= count($expired) ?></p>
                </div>
                <div class="bg-red-100 p-3 rounded-full">
                    <i class="fas fa-times-circle text-red-600 text-2xl"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Servers List -->
    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
            <h2 class="text-xl font-bold text-gray-900">
                <i class="fas fa-list mr-2 text-blue-600"></i>All Servers
            </h2>
        </div>
        <div id="dashboardServerGrid" class="ag-theme-quartz" style="height: 600px;"></div>
    </div>
</div>

<script>
function deployCertificate(serverId) {
    if (!confirm('Are you sure you want to deploy this certificate? This will restart Apache on the server.')) {
        return;
    }

    // Show deployment modal
    document.getElementById('deploymentStatusModal').classList.remove('hidden');
    document.getElementById('deploymentOutput').innerHTML = '<p class="text-blue-600"><i class="fas fa-spinner fa-spin"></i> Starting deployment...</p>';

    fetch(`<?= site_url('certificates/deploy/') ?>${serverId}`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        }
    })
    .then(response => response.json())
    .then(data => {
        let output = '';
        
        if (data.success) {
            output = `
                <div class="space-y-3">
                    <div class="p-4 bg-green-50 border border-green-200 rounded-lg">
                        <p class="text-green-700"><i class="fas fa-check-circle"></i> <strong>Deployment Successful!</strong></p>
                    </div>
            `;
        } else {
            output = `
                <div class="space-y-3">
                    <div class="p-4 bg-red-50 border border-red-200 rounded-lg">
                        <p class="text-red-700"><i class="fas fa-times-circle"></i> <strong>Deployment Failed</strong></p>
                    </div>
            `;
        }
        
        if (data.message) {
            output += `
                    <div class="p-3 bg-gray-100 border border-gray-300 rounded font-mono text-sm break-all">
                        <p class="font-semibold mb-2">Status:</p>
                        <p>${data.message}</p>
                    </div>
            `;
        }
        
        if (data.commands && data.commands.length > 0) {
            output += `<div class="border-t pt-4 mt-4">`;
            data.commands.forEach((cmd, index) => {
                const statusColor = cmd.success ? 'bg-green-50 border-green-200' : 'bg-red-50 border-red-200';
                const statusIcon = cmd.success ? '<i class="fas fa-check text-green-600"></i>' : '<i class="fas fa-times text-red-600"></i>';
                output += `
                    <div class="mb-4">
                        <div class="p-2 ${statusColor} border rounded">
                            <p class="font-semibold text-sm mb-2">${statusIcon} ${cmd.name}</p>
                            <p class="text-xs font-mono break-all text-gray-700">${cmd.command}</p>
                        </div>
                        ${cmd.output ? `<div class="p-2 bg-gray-900 text-green-400 rounded mt-1 text-xs font-mono overflow-auto max-h-40 break-all"><pre>${cmd.output}</pre></div>` : ''}
                    </div>
                `;
            });
            output += `</div>`;
        }
        
        output += `</div>`;
        document.getElementById('deploymentOutput').innerHTML = output;
    })
    .catch(error => {
        console.error(error);
        document.getElementById('deploymentOutput').innerHTML = `
            <div class="p-4 bg-red-50 border border-red-200 rounded-lg">
                <p class="text-red-700"><i class="fas fa-times-circle"></i> <strong>Error:</strong> An error occurred during deployment</p>
            </div>
        `;
    });
}

function viewCertificateInfo(serverId, hostname) {
    fetch(`<?= site_url('certificates/info/') ?>${serverId}`, {
        method: 'GET',
        headers: {
            'Content-Type': 'application/json',
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            document.getElementById('certInfoTitle').textContent = data.common_name || 'Unknown';
            document.getElementById('certInfoHostname').textContent = hostname;
            document.getElementById('certInfoCN').textContent = data.common_name || 'N/A';
            document.getElementById('certInfoValidFrom').textContent = new Date(data.valid_from).toLocaleDateString() + ' ' + new Date(data.valid_from).toLocaleTimeString();
            document.getElementById('certInfoValidUntil').textContent = new Date(data.valid_until).toLocaleDateString() + ' ' + new Date(data.valid_until).toLocaleTimeString();
            
            // Display issuer if available
            if (data.issuer) {
                document.getElementById('certInfoIssuer').textContent = data.issuer;
                document.getElementById('certInfoIssuerRow').classList.remove('hidden');
            } else {
                document.getElementById('certInfoIssuerRow').classList.add('hidden');
            }
            
            // Display fingerprint if available
            if (data.fingerprint) {
                document.getElementById('certInfoFingerprint').textContent = data.fingerprint;
                document.getElementById('certInfoFingerprintRow').classList.remove('hidden');
            } else {
                document.getElementById('certInfoFingerprintRow').classList.add('hidden');
            }
            
            // Display SAN if available
            if (data.san && data.san.length > 0) {
                document.getElementById('certInfoSAN').textContent = data.san.join(', ');
                document.getElementById('certInfoSANRow').classList.remove('hidden');
            } else {
                document.getElementById('certInfoSANRow').classList.add('hidden');
            }
            
            // Display certificate source
            const sourceText = data.source === 'live' ? 'Live (From Server)' : 'Uploaded (Cached)';
            const sourceColor = data.source === 'live' ? 'text-green-600' : 'text-blue-600';
            document.getElementById('certInfoSource').innerHTML = `<span class="${sourceColor} font-semibold">${sourceText}</span>`;
            
            const daysLeft = data.days_until_expiry;
            let statusHTML = '';
            if (daysLeft < 0) {
                statusHTML = `<span class="text-red-600 font-bold">❌ Expired (${Math.abs(daysLeft)} days ago)</span>`;
            } else if (daysLeft < 30) {
                statusHTML = `<span class="text-yellow-600 font-bold">⚠️ Expiring (${daysLeft} days left)</span>`;
            } else {
                statusHTML = `<span class="text-green-600 font-bold">✓ Valid (${daysLeft} days left)</span>`;
            }
            document.getElementById('certInfoStatus').innerHTML = statusHTML;
            document.getElementById('certInfoModal').classList.remove('hidden');
        } else {
            alert('Failed to load certificate info: ' + data.message);
        }
    })
    .catch(error => {
        alert('An error occurred while loading certificate information');
        console.error(error);
    });
}

function closeCertInfoModal() {
    document.getElementById('certInfoModal').classList.add('hidden');
}
</script>

<!-- Certificate Info Modal -->
<div id="certInfoModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-2/3 shadow-lg rounded-md bg-white max-h-[90vh] overflow-y-auto">
        <div class="mt-3">
            <h3 class="text-lg leading-6 font-bold text-gray-900 mb-4">
                <i class="fas fa-certificate mr-2 text-blue-600"></i>Certificate Details
            </h3>
            <div class="mt-4 space-y-4 px-6 py-3">
                <div class="border-b pb-3">
                    <p class="text-sm font-medium text-gray-500">Certificate Name</p>
                    <p class="text-lg font-semibold text-gray-900" id="certInfoTitle">-</p>
                </div>
                
                <div class="border-b pb-3">
                    <p class="text-sm font-medium text-gray-500">Server Hostname</p>
                    <p class="text-lg font-semibold text-gray-900" id="certInfoHostname">-</p>
                </div>
                
                <div class="border-b pb-3">
                    <p class="text-sm font-medium text-gray-500">Common Name (CN)</p>
                    <p class="text-lg font-semibold text-gray-900" id="certInfoCN">-</p>
                </div>
                
                <div class="border-b pb-3 hidden" id="certInfoIssuerRow">
                    <p class="text-sm font-medium text-gray-500">Issued By</p>
                    <p class="text-lg font-semibold text-gray-900" id="certInfoIssuer">-</p>
                </div>
                
                <div class="border-b pb-3 hidden" id="certInfoSANRow">
                    <p class="text-sm font-medium text-gray-500">Subject Alternative Names (SAN)</p>
                    <p class="text-sm text-gray-900" id="certInfoSAN">-</p>
                </div>
                
                <div class="border-b pb-3">
                    <p class="text-sm font-medium text-gray-500">Valid From</p>
                    <p class="text-sm text-gray-900 font-mono" id="certInfoValidFrom">-</p>
                </div>
                
                <div class="border-b pb-3">
                    <p class="text-sm font-medium text-gray-500">Valid Until (Expires)</p>
                    <p class="text-sm text-gray-900 font-mono" id="certInfoValidUntil">-</p>
                </div>
                
                <div class="border-b pb-3 hidden" id="certInfoFingerprintRow">
                    <p class="text-sm font-medium text-gray-500">SHA-1 Fingerprint</p>
                    <p class="text-xs text-gray-900 font-mono break-all" id="certInfoFingerprint">-</p>
                </div>
                
                <div class="border-b pb-3">
                    <p class="text-sm font-medium text-gray-500">Certificate Source</p>
                    <p class="text-sm" id="certInfoSource">-</p>
                </div>
                
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                    <p class="text-sm font-medium text-gray-500 mb-2">Certificate Status</p>
                    <p class="text-lg font-semibold" id="certInfoStatus">-</p>
                </div>
            </div>
            
            <div class="items-center px-4 py-3 border-t border-gray-200">
                <button onclick="closeCertInfoModal()" 
                        class="w-full px-4 py-2 bg-gray-300 text-gray-700 text-base font-medium rounded-md shadow-sm hover:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-gray-300">
                    Close
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Deployment Status Modal -->
<div id="deploymentStatusModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-2/3 shadow-lg rounded-md bg-white max-h-[90vh] overflow-y-auto">
        <div class="mt-3">
            <h3 class="text-lg leading-6 font-bold text-gray-900 mb-4">
                <i class="fas fa-rocket mr-2 text-blue-600"></i>Deployment Status
            </h3>
            <div id="deploymentOutput" class="space-y-4 px-6 py-3">
                <p class="text-blue-600"><i class="fas fa-spinner fa-spin"></i> Deploying certificate...</p>
            </div>
            
            <div class="items-center px-4 py-3 border-t border-gray-200">
                <button onclick="closeDeploymentModal()" 
                        class="w-full px-4 py-2 bg-gray-300 text-gray-700 text-base font-medium rounded-md shadow-sm hover:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-gray-300">
                    Close
                </button>
            </div>
        </div>
    </div>
</div>

<script>
// Dashboard Servers Grid
const dashboardServerData = <?= json_encode($servers) ?>;
const dashboardServerGridOptions = {
    columnDefs: [
        { 
            field: 'name', 
            headerName: 'Server', 
            width: 140,
            sort: 'asc',
            cellStyle: { fontWeight: 500, color: '#1f2937' }
        },
        { 
            field: 'hostname', 
            headerName: 'Hostname', 
            width: 150
        },
        { 
            field: 'ip_address', 
            headerName: 'IP Address', 
            width: 140
        },
        { 
            field: 'common_name', 
            headerName: 'Certificate CN', 
            width: 160,
            cellRenderer: function(params) {
                if (!params.data.certificate) {
                    return '<span class="text-gray-400 italic">No certificate</span>';
                }
                return params.data.certificate.common_name || '<span class="text-gray-400">Unknown</span>';
            }
        },
        {
            field: 'status',
            headerName: 'Status',
            width: 130,
            cellRenderer: function(params) {
                const status = params.data.status || 'no_cert';
                const statusMap = {
                    'valid': { class: 'status-badge valid', icon: 'fa-check-circle', text: 'Valid' },
                    'expiring': { class: 'status-badge expiring', icon: 'fa-exclamation-circle', text: 'Expiring' },
                    'expired': { class: 'status-badge expired', icon: 'fa-times-circle', text: 'Expired' },
                    'no_cert': { class: 'status-badge', style: 'background-color: #f3f4f6; color: #6b7280;', icon: 'fa-question-circle', text: 'No Cert' }
                };
                const info = statusMap[status] || statusMap['no_cert'];
                return `<span class="${info.class}"><i class="fas ${info.icon}"></i>${info.text}</span>`;
            }
        },
        {
            field: 'days_until_expiry',
            headerName: 'Days Until Expiry',
            width: 160,
            cellRenderer: function(params) {
                if (!params.data.valid_until) return '-';
                const days = params.data.days_until_expiry;
                if (days < 0) {
                    return `<span style="color: #dc2626; font-weight: 500;">Expired ${Math.abs(days)}d ago</span>`;
                } else if (days < 30) {
                    return `<span style="color: #d97706; font-weight: 500;">${days} days</span>`;
                } else {
                    return `<span style="color: #059669; font-weight: 500;">${days} days</span>`;
                }
            }
        },
        {
            headerName: 'Actions',
            width: 180,
            cellRenderer: function(params) {
                return `
                    <div class="action-buttons">
                        <button onclick="viewCertificateInfo(${params.data.id})" class="text-blue-600 hover:text-blue-900" title="View Certificate">
                            <i class="fas fa-certificate"></i>
                        </button>
                        <button onclick="deployCertificate(${params.data.id})" class="text-green-600 hover:text-green-900" title="Deploy Certificate">
                            <i class="fas fa-cloud-upload-alt"></i>
                        </button>
                        <a href="<?= site_url('servers/edit/') ?>${params.data.id}" class="text-orange-600 hover:text-orange-900" title="Edit">
                            <i class="fas fa-edit"></i>
                        </a>
                    </div>
                `;
            }
        }
    ],
    rowData: dashboardServerData,
    pagination: true,
    paginationPageSize: 10,
    paginationPageSizeSelector: [10, 25, 50],
    defaultColDef: {
        flex: 1,
        minWidth: 100,
        wrapText: true,
        autoHeight: true
    },
    rowHeight: 45,
    headerHeight: 50
};

const dashboardGrid = new agGrid.Grid(document.getElementById('dashboardServerGrid'), dashboardServerGridOptions);

function changeDashboardPerPage(value) {
    dashboardGridOptions.paginationPageSize = parseInt(value);
    dashboardGrid.setGridOption('paginationPageSize', parseInt(value));
}
</script>

<?= $this->endSection() ?>
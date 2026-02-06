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
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Total Servers Card -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-gray-600 text-sm font-medium">Total Servers</p>
                    <p class="text-3xl font-bold text-gray-900 mt-2"><?= count($servers) ?></p>
                </div>
                <div class="text-blue-600 text-3xl">
                    <i class="fas fa-server"></i>
                </div>
            </div>
        </div>

        <!-- Valid Certificates Card -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-gray-600 text-sm font-medium">Valid Certificates</p>
                    <p class="text-3xl font-bold text-green-600 mt-2"><?= count(array_filter($servers, fn($s) => $s['status'] === 'valid')) ?></p>
                </div>
                <div class="text-green-600 text-3xl">
                    <i class="fas fa-check-circle"></i>
                </div>
            </div>
        </div>

        <!-- Certificates Expiring Soon Card -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-gray-600 text-sm font-medium">Expiring Soon</p>
                    <p class="text-3xl font-bold text-orange-600 mt-2"><?= count(array_filter($servers, fn($s) => $s['status'] === 'expiring')) ?></p>
                </div>
                <div class="text-orange-600 text-3xl">
                    <i class="fas fa-exclamation-circle"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Servers List -->
    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
            <div class="flex justify-between items-center">
                <h2 class="text-xl font-bold text-gray-900">
                    <i class="fas fa-list mr-2 text-blue-600"></i>All Servers
                </h2>
                <div class="flex items-center gap-2">
                    <input type="text" id="dashboardSearchInput" placeholder="Search servers..." 
                           class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <button onclick="clearDashboardSearch()" class="px-3 py-2 bg-gray-300 hover:bg-gray-400 text-gray-700 rounded-lg text-sm transition">
                        <i class="fas fa-times"></i> Clear
                    </button>
                </div>
            </div>
        </div>
        <div id="dashboardServerGrid" class="ag-theme-quartz" style="height: 600px;"></div>
    </div>
</div>

<!-- Deployment Status Modal -->
<div id="deploymentModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50" onclick="closeDeploymentModal(event)">
    <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white max-h-96 overflow-y-auto" onclick="event.stopPropagation()">
        <div class="flex justify-between items-start mb-4">
            <h3 class="text-lg leading-6 font-medium text-gray-900">
                <i class="fas fa-rocket mr-2 text-blue-600"></i>Deployment Status
            </h3>
            <button onclick="closeDeploymentModal()" class="text-gray-400 hover:text-gray-600 text-2xl leading-none font-bold">
                ×
            </button>
        </div>
        <div id="deploymentOutput" class="bg-gray-50 border border-gray-300 rounded-lg p-4 font-mono text-sm whitespace-pre-wrap overflow-auto max-h-64">
            Deploying...
        </div>
        <div class="mt-4 flex justify-end">
            <button onclick="closeDeploymentModal()" 
                    class="px-4 py-2 bg-gray-300 text-gray-700 text-base font-medium rounded-md shadow-sm hover:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-gray-300">
                Close
            </button>
        </div>
    </div>
</div>

<!-- Certificate Info Modal -->
<div id="certInfoModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50" onclick="closeCertInfoModal(event)">
    <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white max-h-96 overflow-y-auto" onclick="event.stopPropagation()">
        <div class="flex justify-between items-start mb-4">
            <h3 class="text-lg leading-6 font-medium text-gray-900">
                <i class="fas fa-certificate mr-2 text-green-600"></i>Certificate Details
            </h3>
            <button onclick="closeCertInfoModal()" class="text-gray-400 hover:text-gray-600 text-2xl leading-none font-bold">
                ×
            </button>
        </div>
        <div id="certInfoContent" class="space-y-3 text-sm">
            <!-- Certificate details will be populated here -->
        </div>
        <div class="mt-4 flex justify-end">
            <button onclick="closeCertInfoModal()" 
                    class="px-4 py-2 bg-gray-300 text-gray-700 text-base font-medium rounded-md shadow-sm hover:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-gray-300">
                Close
            </button>
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
            headerName: 'Server Name', 
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
            field: 'ssh_port', 
            headerName: 'SSH Port', 
            width: 110,
            cellStyle: { textAlign: 'center' }
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
            headerName: 'Cert Status',
            width: 140,
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
            headerName: 'Actions',
            width: 200,
            cellRenderer: function(params) {
                return `
                    <div class="action-buttons">
                        <button onclick="deployCertificate(${params.data.id})" class="text-blue-600 hover:text-blue-900" title="Deploy Certificate">
                            <i class="fas fa-rocket"></i>
                        </button>
                        <button onclick="viewCertificateInfo(${params.data.id})" class="text-green-600 hover:text-green-900" title="View Certificate Info">
                            <i class="fas fa-info-circle"></i>
                        </button>
                        <a href="<?= site_url('servers/edit/') ?>${params.data.id}" class="text-yellow-600 hover:text-yellow-900" title="Edit Server">
                            <i class="fas fa-edit"></i>
                        </a>
                        <a href="<?= site_url('servers') ?>" class="text-purple-600 hover:text-purple-900" title="Manage Servers">
                            <i class="fas fa-cog"></i>
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

const dashboardGridApi = agGrid.createGrid(document.getElementById('dashboardServerGrid'), dashboardServerGridOptions);

// Dashboard search functionality
const dashboardSearchInput = document.getElementById('dashboardSearchInput');
dashboardSearchInput.addEventListener('keyup', (e) => {
    dashboardGridApi.setGridOption('quickFilterText', e.target.value);
});

function clearDashboardSearch() {
    dashboardSearchInput.value = '';
    dashboardGridApi.setGridOption('quickFilterText', '');
}

// Deploy certificate
function deployCertificate(serverId) {
    if (!confirm('Deploy the certificate to this server and restart Apache?')) {
        return;
    }

    document.getElementById('deploymentOutput').textContent = 'Deploying...';
    document.getElementById('deploymentModal').classList.remove('hidden');

    fetch(`<?= site_url('certificates/deploy/') ?>${serverId}`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            document.getElementById('deploymentOutput').textContent = data.output || 'Deployment completed successfully!';
        } else {
            document.getElementById('deploymentOutput').textContent = data.message || 'Deployment failed!';
        }
    })
    .catch(error => {
        document.getElementById('deploymentOutput').textContent = 'Error: ' + error.message;
    });
}

function closeDeploymentModal(event) {
    if (event && event.target !== event.currentTarget) return;
    document.getElementById('deploymentModal').classList.add('hidden');
}

// View certificate info
function viewCertificateInfo(serverId) {
    fetch(`<?= site_url('certificates/info/') ?>${serverId}`)
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const cert = data.certificate;
            let html = '<table class="w-full"><tbody>';
            
            if (cert.common_name) html += `<tr><td class="font-semibold text-gray-700 py-1">Common Name:</td><td class="text-gray-900 py-1">${cert.common_name}</td></tr>`;
            if (cert.issuer) html += `<tr><td class="font-semibold text-gray-700 py-1">Issuer:</td><td class="text-gray-900 py-1">${cert.issuer}</td></tr>`;
            if (cert.valid_from) html += `<tr><td class="font-semibold text-gray-700 py-1">Valid From:</td><td class="text-gray-900 py-1">${new Date(cert.valid_from * 1000).toLocaleString()}</td></tr>`;
            if (cert.valid_until) html += `<tr><td class="font-semibold text-gray-700 py-1">Valid Until:</td><td class="text-gray-900 py-1">${new Date(cert.valid_until * 1000).toLocaleString()}</td></tr>`;
            if (cert.days_until_expiry !== undefined) {
                let color = cert.days_until_expiry < 0 ? 'text-red-600' : (cert.days_until_expiry < 30 ? 'text-orange-600' : 'text-green-600');
                html += `<tr><td class="font-semibold text-gray-700 py-1">Days Until Expiry:</td><td class="py-1 ${color} font-semibold">${cert.days_until_expiry}</td></tr>`;
            }
            if (cert.san) html += `<tr><td class="font-semibold text-gray-700 py-1">Subject Alt Names:</td><td class="text-gray-900 py-1"><small>${cert.san.join(', ')}</small></td></tr>`;
            if (cert.fingerprint) html += `<tr><td class="font-semibold text-gray-700 py-1">Fingerprint:</td><td class="text-gray-900 py-1 font-mono text-xs break-all">${cert.fingerprint}</td></tr>`;
            
            html += '</tbody></table>';
            document.getElementById('certInfoContent').innerHTML = html;
            document.getElementById('certInfoModal').classList.remove('hidden');
        } else {
            alert('Error fetching certificate info: ' + data.message);
        }
    })
    .catch(error => {
        alert('Error: ' + error.message);
    });
}

function closeCertInfoModal(event) {
    if (event && event.target !== event.currentTarget) return;
    document.getElementById('certInfoModal').classList.add('hidden');
}
</script>
<?= $this->endSection() ?>

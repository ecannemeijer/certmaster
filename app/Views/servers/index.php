<?= $this->extend('layout/app') ?>

<?= $this->section('content') ?>
<div class="space-y-6">
    <!-- Header -->
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">
                <i class="fas fa-server mr-3 text-blue-600"></i>Servers
            </h1>
            <p class="text-gray-600 mt-1">Manage all your servers</p>
        </div>
        <a href="<?= site_url('servers/create') ?>" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg font-medium transition transform hover:scale-105 shadow-md">
            <i class="fas fa-plus mr-2"></i>Add New Server
        </a>
    </div>

    <!-- Servers List -->
    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
            <div class="flex justify-between items-center">
                <h2 class="text-xl font-bold text-gray-900">
                    <i class="fas fa-list mr-2 text-blue-600"></i>All Servers
                </h2>
                <div class="flex items-center gap-2">
                    <input type="text" id="serversSearchInput" placeholder="Search servers..." 
                           class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <button onclick="clearServersSearch()" class="px-3 py-2 bg-gray-300 hover:bg-gray-400 text-gray-700 rounded-lg text-sm transition">
                        <i class="fas fa-times"></i> Clear
                    </button>
                </div>
            </div>
        </div>
        <div id="serversGrid" class="ag-theme-quartz" style="height: 600px;"></div>
    </div>
</div>

<!-- SSH Key Modal -->
<div id="sshKeyModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">
                <i class="fas fa-key mr-2 text-purple-600"></i>SSH Public Key
            </h3>
            <div class="mt-2 px-7 py-3">
                <p class="text-sm text-gray-500 mb-3">
                    Copy this public key and add it to the authorized_keys file on your server:
                </p>
                <textarea id="publicKeyText" readonly 
                          class="w-full h-32 p-3 border border-gray-300 rounded-lg font-mono text-xs bg-gray-50"
                          onclick="this.select()"></textarea>
                <p class="text-xs text-gray-500 mt-2" id="fingerprintText"></p>
            </div>
            <div class="items-center px-4 py-3">
                <button onclick="copyToClipboard()" 
                        class="px-4 py-2 bg-blue-600 text-white text-base font-medium rounded-md w-full shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-300">
                    <i class="fas fa-copy mr-2"></i>Copy to Clipboard
                </button>
                <button onclick="closeSshModal()" 
                        class="mt-3 px-4 py-2 bg-gray-300 text-gray-700 text-base font-medium rounded-md w-full shadow-sm hover:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-gray-300">
                    Close
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Certificate Upload Modal -->
<div id="certificateModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white max-h-96 overflow-y-auto">
        <div class="mt-3">
            <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">
                <i class="fas fa-upload mr-2 text-green-600"></i>Upload Certificate
            </h3>
            <form id="certForm" enctype="multipart/form-data" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Certificate File (PEM/CRT) *
                    </label>
                    <input type="file" id="pem_file" name="pem_file" required accept=".pem,.crt,.cer,.PEM,.CRT,.CER"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-green-500 focus:border-green-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Private Key File (KEY) *
                    </label>
                    <input type="file" id="key_file" name="key_file" required accept=".key,.pem,.KEY,.PEM"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-green-500 focus:border-green-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Custom Filename (Optional - leave empty for default)
                    </label>
                    <input type="text" id="custom_filename" name="custom_filename" placeholder="e.g., mycert"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-green-500 focus:border-green-500">
                    <p class="text-xs text-gray-500 mt-1">Files will be saved as: [filename].pem and [filename].key</p>
                </div>
                <div id="certErrors" class="hidden bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded"></div>
            </form>
            <div class="items-center px-4 py-3 space-y-2">
                <button onclick="uploadCertificate()" 
                        class="px-4 py-2 bg-green-600 text-white text-base font-medium rounded-md w-full shadow-sm hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-300">
                    <i class="fas fa-upload mr-2"></i>Upload Certificate
                </button>
                <button onclick="closeCertModal()" 
                        class="px-4 py-2 bg-gray-300 text-gray-700 text-base font-medium rounded-md w-full shadow-sm hover:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-gray-300">
                    Close
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function generateSSHKey(serverId, hostname) {
    // Always ask for confirmation before generating SSH key
    if (!confirm('Generate a new SSH key pair for this server? This will replace any existing key.')) {
        return;
    }

    fetch(`<?= site_url('servers/generate-ssh-key/') ?>${serverId}`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            document.getElementById('publicKeyText').value = data.public_key;
            document.getElementById('fingerprintText').textContent = 'Fingerprint: ' + data.fingerprint;
            document.getElementById('sshKeyModal').classList.remove('hidden');
        } else {
            alert('Failed to generate SSH key: ' + data.message);
        }
    })
    .catch(error => {
        alert('An error occurred while generating SSH key');
    });
}

function closeSshModal() {
    document.getElementById('sshKeyModal').classList.add('hidden');
}

function copyToClipboard() {
    const text = document.getElementById('publicKeyText');
    text.select();
    document.execCommand('copy');
    alert('Public key copied to clipboard!');
}

let currentServerId = null;

function openCertificateModal(serverId) {
    currentServerId = serverId;
    document.getElementById('certForm').reset();
    document.getElementById('certErrors').classList.add('hidden');
    document.getElementById('certificateModal').classList.remove('hidden');
}

function closeCertModal() {
    document.getElementById('certificateModal').classList.add('hidden');
    currentServerId = null;
}

function uploadCertificate() {
    const pemFile = document.getElementById('pem_file').files[0];
    const keyFile = document.getElementById('key_file').files[0];
    const customFilename = document.getElementById('custom_filename').value;
    const errorsDiv = document.getElementById('certErrors');

    if (!pemFile || !keyFile) {
        errorsDiv.textContent = 'Please select both files';
        errorsDiv.classList.remove('hidden');
        return;
    }

    const formData = new FormData();
    formData.append('pem_file', pemFile);
    formData.append('key_file', keyFile);
    formData.append('custom_filename', customFilename);

    fetch(`<?= site_url('certificates/store/') ?>${currentServerId}`, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Certificate uploaded successfully!');
            closeCertModal();
            location.reload();
        } else {
            errorsDiv.textContent = data.message || 'Failed to upload certificate';
            errorsDiv.classList.remove('hidden');
        }
    })
    .catch(error => {
        errorsDiv.textContent = 'An error occurred: ' + error;
        errorsDiv.classList.remove('hidden');
    });
}

function changeServersPerPage(value) {
    serversGridOptions.paginationPageSize = parseInt(value);
    serversGrid.setGridOption('paginationPageSize', parseInt(value));
}
</script>

<script>
// Servers Grid
const serversData = <?= json_encode($servers) ?>;
const serversGridOptions = {
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
                        <button onclick="generateSSHKey(${params.data.id}, '${params.data.hostname}')" class="text-purple-600 hover:text-purple-900" title="Generate SSH Key">
                            <i class="fas fa-key"></i>
                        </button>
                        <button onclick="openCertificateModal(${params.data.id})" class="text-green-600 hover:text-green-900" title="Upload Certificate">
                            <i class="fas fa-upload"></i>
                        </button>
                        <a href="<?= site_url('servers/edit/') ?>${params.data.id}" class="text-blue-600 hover:text-blue-900" title="Edit">
                            <i class="fas fa-edit"></i>
                        </a>
                        <a href="<?= site_url('servers/delete/') ?>${params.data.id}" onclick="return confirm('Are you sure you want to delete this server?')" class="text-red-600 hover:text-red-900" title="Delete">
                            <i class="fas fa-trash"></i>
                        </a>
                    </div>
                `;
            }
        }
    ],
    rowData: serversData,
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

const serversGridApi = agGrid.createGrid(document.getElementById('serversGrid'), serversGridOptions);

// Servers search functionality
const serversSearchInput = document.getElementById('serversSearchInput');
serversSearchInput.addEventListener('keyup', (e) => {
    serversGridApi.setGridOption('quickFilterText', e.target.value);
});

function clearServersSearch() {
    serversSearchInput.value = '';
    serversGridApi.setGridOption('quickFilterText', '');
}
</script>
<?= $this->endSection() ?>

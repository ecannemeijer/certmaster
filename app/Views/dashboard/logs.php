<?= $this->extend('layout/app') ?>

<?= $this->section('content') ?>
<div class="space-y-6">
    <!-- Header -->
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">
                <i class="fas fa-history mr-3 text-blue-600"></i>Deployment Logs
            </h1>
            <p class="text-gray-600 mt-1">View all certificate deployment history</p>
        </div>
        <a href="<?= site_url('dashboard') ?>" class="bg-gray-600 hover:bg-gray-700 text-white px-6 py-3 rounded-lg font-medium transition">
            <i class="fas fa-arrow-left mr-2"></i>Back to Dashboard
        </a>
    </div>

    <!-- Logs Table -->
    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
            <h2 class="text-xl font-bold text-gray-900">
                <i class="fas fa-list mr-2 text-blue-600"></i>All Deployments
            </h2>
        </div>
        <div id="logsGrid" class="ag-theme-quartz" style="height: 600px;"></div>
    </div>
</div>

<script>
// Logs Grid
const logsData = <?= json_encode($logs) ?>;
const logsGridOptions = {
    columnDefs: [
        {
            field: 'created_at',
            headerName: 'Date & Time',
            width: 200,
            sort: 'desc',
            cellRenderer: function(params) {
                const date = new Date(params.data.created_at);
                return date.toLocaleString('en-US', {
                    year: 'numeric',
                    month: 'short',
                    day: 'numeric',
                    hour: '2-digit',
                    minute: '2-digit',
                    second: '2-digit'
                });
            }
        },
        { 
            field: 'server_name', 
            headerName: 'Server', 
            width: 150,
            cellStyle: { fontWeight: 500 }
        },
        {
            field: 'status',
            headerName: 'Status',
            width: 140,
            cellRenderer: function(params) {
                const status = params.data.status;
                const statusMap = {
                    'success': { class: 'status-badge valid', icon: 'fa-check-circle', text: 'Success' },
                    'failed': { class: 'status-badge expired', icon: 'fa-times-circle', text: 'Failed' },
                    'pending': { class: 'status-badge', style: 'background-color: #dbeafe; color: #0c4a6e;', icon: 'fa-hourglass-half', text: 'Pending' }
                };
                const info = statusMap[status] || { class: 'status-badge', icon: 'fa-question-circle', text: 'Unknown' };
                return `<span class="${info.class}"><i class="fas ${info.icon}"></i>${info.text}</span>`;
            }
        },
        { 
            field: 'message', 
            headerName: 'Message', 
            flex: 2,
            minWidth: 200,
            wrapText: true,
            autoHeight: true
        }
    ],
    rowData: logsData,
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

const logsGrid = new agGrid.Grid(document.getElementById('logsGrid'), logsGridOptions);
</script>

<?= $this->endSection() ?>

<?= $this->extend('layout/app') ?>

<?= $this->section('content') ?>
<div class="space-y-6">
    <!-- Header -->
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">
                <i class="fas fa-users mr-3 text-blue-600"></i>User Management
            </h1>
            <p class="text-gray-600 mt-1">Manage system users and permissions</p>
        </div>
        <button onclick="openCreateModal()" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg font-medium transition transform hover:scale-105 shadow-md">
            <i class="fas fa-plus mr-2"></i>Add New User
        </button>
    </div>

    <!-- Users List -->
    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
            <div class="flex justify-between items-center">
                <h2 class="text-xl font-bold text-gray-900">
                    <i class="fas fa-list mr-2 text-blue-600"></i>All Users
                </h2>
                <div class="flex items-center gap-2">
                    <input type="text" id="usersSearchInput" placeholder="Search users..." 
                           class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <button onclick="clearUsersSearch()" class="px-3 py-2 bg-gray-300 hover:bg-gray-400 text-gray-700 rounded-lg text-sm transition">
                        <i class="fas fa-times"></i> Clear
                    </button>
                </div>
            </div>
        </div>
        <div id="usersGrid" class="ag-theme-quartz" style="height: 600px;"></div>
    </div>
</div>

<!-- Create User Modal -->
<div id="createUserModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50" onclick="closeCreateModal(event)">
    <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white max-h-96 overflow-y-auto" onclick="event.stopPropagation()">
        <div class="flex justify-between items-start mb-4">
            <h3 class="text-lg leading-6 font-medium text-gray-900">
                <i class="fas fa-user-plus mr-2 text-blue-600"></i>Create New User
            </h3>
            <button onclick="closeCreateModal()" class="text-gray-400 hover:text-gray-600 text-2xl leading-none font-bold">
                ×
            </button>
        </div>
        <form id="createUserForm" class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Username *</label>
                <input type="text" id="createUsername" name="username" required
                       class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Password *</label>
                <input type="password" id="createPassword" name="password" required
                       class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                <p class="text-xs text-gray-500 mt-1">Minimum 6 characters</p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Confirm Password *</label>
                <input type="password" id="createPasswordConfirm" name="password_confirm" required
                       class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
            </div>
            <div id="createErrors" class="hidden bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded"></div>
        </form>
        <div class="mt-4 flex justify-end gap-2">
            <button onclick="closeCreateModal()" 
                    class="px-4 py-2 bg-gray-300 text-gray-700 text-base font-medium rounded-md shadow-sm hover:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-gray-300">
                Cancel
            </button>
            <button onclick="submitCreateUser()" 
                    class="px-4 py-2 bg-blue-600 text-white text-base font-medium rounded-md shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-300">
                Create User
            </button>
        </div>
    </div>
</div>

<!-- Edit User Modal -->
<div id="editUserModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50" onclick="closeEditModal(event)">
    <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white max-h-96 overflow-y-auto" onclick="event.stopPropagation()">
        <div class="flex justify-between items-start mb-4">
            <h3 class="text-lg leading-6 font-medium text-gray-900">
                <i class="fas fa-user-edit mr-2 text-yellow-600"></i>Edit User
            </h3>
            <button onclick="closeEditModal()" class="text-gray-400 hover:text-gray-600 text-2xl leading-none font-bold">
                ×
            </button>
        </div>
        <form id="editUserForm" class="space-y-4">
            <input type="hidden" id="editUserId">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Username *</label>
                <input type="text" id="editUsername" name="username" required
                       class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
            </div>
            <div id="editErrors" class="hidden bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded"></div>
        </form>
        <div class="mt-4 flex justify-end gap-2">
            <button onclick="closeEditModal()" 
                    class="px-4 py-2 bg-gray-300 text-gray-700 text-base font-medium rounded-md shadow-sm hover:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-gray-300">
                Cancel
            </button>
            <button onclick="submitEditUser()" 
                    class="px-4 py-2 bg-yellow-600 text-white text-base font-medium rounded-md shadow-sm hover:bg-yellow-700 focus:outline-none focus:ring-2 focus:ring-yellow-300">
                Update User
            </button>
        </div>
    </div>
</div>

<!-- Change Password Modal -->
<div id="changePasswordModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50" onclick="closePasswordModal(event)">
    <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white max-h-96 overflow-y-auto" onclick="event.stopPropagation()">
        <div class="flex justify-between items-start mb-4">
            <h3 class="text-lg leading-6 font-medium text-gray-900">
                <i class="fas fa-key mr-2 text-purple-600"></i>Change Password
            </h3>
            <button onclick="closePasswordModal()" class="text-gray-400 hover:text-gray-600 text-2xl leading-none font-bold">
                ×
            </button>
        </div>
        <form id="changePasswordForm" class="space-y-4">
            <input type="hidden" id="passwordUserId">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">New Password *</label>
                <input type="password" id="newPassword" name="new_password" required
                       class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                <p class="text-xs text-gray-500 mt-1">Minimum 6 characters</p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Confirm New Password *</label>
                <input type="password" id="newPasswordConfirm" name="new_password_confirm" required
                       class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
            </div>
            <div id="passwordErrors" class="hidden bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded"></div>
        </form>
        <div class="mt-4 flex justify-end gap-2">
            <button onclick="closePasswordModal()" 
                    class="px-4 py-2 bg-gray-300 text-gray-700 text-base font-medium rounded-md shadow-sm hover:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-gray-300">
                Cancel
            </button>
            <button onclick="submitChangePassword()" 
                    class="px-4 py-2 bg-purple-600 text-white text-base font-medium rounded-md shadow-sm hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-purple-300">
                Change Password
            </button>
        </div>
    </div>
</div>

<script>
// Users Grid
const usersData = <?= json_encode($users) ?>;
const usersGridOptions = {
    columnDefs: [
        { 
            field: 'id', 
            headerName: 'ID', 
            width: 80,
            sort: 'desc'
        },
        { 
            field: 'username', 
            headerName: 'Username', 
            width: 200,
            cellStyle: { fontWeight: 500, color: '#1f2937' }
        },
        {
            field: 'created_at',
            headerName: 'Created',
            width: 200,
            cellRenderer: function(params) {
                const date = new Date(params.data.created_at);
                return date.toLocaleString('en-US', {
                    year: 'numeric',
                    month: 'short',
                    day: 'numeric',
                    hour: '2-digit',
                    minute: '2-digit'
                });
            }
        },
        {
            field: 'updated_at',
            headerName: 'Updated',
            width: 200,
            cellRenderer: function(params) {
                const date = new Date(params.data.updated_at);
                return date.toLocaleString('en-US', {
                    year: 'numeric',
                    month: 'short',
                    day: 'numeric',
                    hour: '2-digit',
                    minute: '2-digit'
                });
            }
        },
        {
            headerName: 'Actions',
            width: 200,
            cellRenderer: function(params) {
                return `
                    <div class="action-buttons">
                        <button onclick="openEditModal(${params.data.id}, '${params.data.username.replace(/'/g, "\\'")}')" class="text-yellow-600 hover:text-yellow-900" title="Edit User">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button onclick="openPasswordModal(${params.data.id})" class="text-purple-600 hover:text-purple-900" title="Change Password">
                            <i class="fas fa-key"></i>
                        </button>
                        <button onclick="deleteUser(${params.data.id})" class="text-red-600 hover:text-red-900" title="Delete User">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                `;
            }
        }
    ],
    rowData: usersData,
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

const usersGridApi = agGrid.createGrid(document.getElementById('usersGrid'), usersGridOptions);

// Users search functionality
const usersSearchInput = document.getElementById('usersSearchInput');
usersSearchInput.addEventListener('keyup', (e) => {
    usersGridApi.setGridOption('quickFilterText', e.target.value);
});

function clearUsersSearch() {
    usersSearchInput.value = '';
    usersGridApi.setGridOption('quickFilterText', '');
}

// Create User Modal
function openCreateModal() {
    document.getElementById('createUserForm').reset();
    document.getElementById('createErrors').classList.add('hidden');
    document.getElementById('createUserModal').classList.remove('hidden');
}

function closeCreateModal(event) {
    if (event && event.target !== event.currentTarget) return;
    document.getElementById('createUserModal').classList.add('hidden');
}

function submitCreateUser() {
    const username = document.getElementById('createUsername').value;
    const password = document.getElementById('createPassword').value;
    const password_confirm = document.getElementById('createPasswordConfirm').value;
    const errorsDiv = document.getElementById('createErrors');

    if (!username || !password || !password_confirm) {
        errorsDiv.textContent = 'All fields are required';
        errorsDiv.classList.remove('hidden');
        return;
    }

    fetch(`<?= site_url('users/store') ?>`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `username=${encodeURIComponent(username)}&password=${encodeURIComponent(password)}&password_confirm=${encodeURIComponent(password_confirm)}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('User created successfully!');
            closeCreateModal();
            location.reload();
        } else {
            errorsDiv.textContent = data.message || 'Failed to create user';
            errorsDiv.classList.remove('hidden');
        }
    })
    .catch(error => {
        errorsDiv.textContent = 'An error occurred: ' + error;
        errorsDiv.classList.remove('hidden');
    });
}

// Edit User Modal
function openEditModal(id, username) {
    document.getElementById('editUserId').value = id;
    document.getElementById('editUsername').value = username;
    document.getElementById('editErrors').classList.add('hidden');
    document.getElementById('editUserModal').classList.remove('hidden');
}

function closeEditModal(event) {
    if (event && event.target !== event.currentTarget) return;
    document.getElementById('editUserModal').classList.add('hidden');
}

function submitEditUser() {
    const id = document.getElementById('editUserId').value;
    const username = document.getElementById('editUsername').value;
    const errorsDiv = document.getElementById('editErrors');

    if (!username) {
        errorsDiv.textContent = 'Username is required';
        errorsDiv.classList.remove('hidden');
        return;
    }

    fetch(`<?= site_url('users/update/') ?>${id}`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `username=${encodeURIComponent(username)}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('User updated successfully!');
            closeEditModal();
            location.reload();
        } else {
            errorsDiv.textContent = data.message || 'Failed to update user';
            errorsDiv.classList.remove('hidden');
        }
    })
    .catch(error => {
        errorsDiv.textContent = 'An error occurred: ' + error;
        errorsDiv.classList.remove('hidden');
    });
}

// Change Password Modal
function openPasswordModal(id) {
    document.getElementById('passwordUserId').value = id;
    document.getElementById('changePasswordForm').reset();
    document.getElementById('passwordErrors').classList.add('hidden');
    document.getElementById('changePasswordModal').classList.remove('hidden');
}

function closePasswordModal(event) {
    if (event && event.target !== event.currentTarget) return;
    document.getElementById('changePasswordModal').classList.add('hidden');
}

function submitChangePassword() {
    const id = document.getElementById('passwordUserId').value;
    const new_password = document.getElementById('newPassword').value;
    const new_password_confirm = document.getElementById('newPasswordConfirm').value;
    const errorsDiv = document.getElementById('passwordErrors');

    if (!new_password || !new_password_confirm) {
        errorsDiv.textContent = 'All fields are required';
        errorsDiv.classList.remove('hidden');
        return;
    }

    fetch(`<?= site_url('users/updatePassword/') ?>${id}`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `new_password=${encodeURIComponent(new_password)}&new_password_confirm=${encodeURIComponent(new_password_confirm)}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Password changed successfully!');
            closePasswordModal();
            location.reload();
        } else {
            errorsDiv.textContent = data.message || 'Failed to change password';
            errorsDiv.classList.remove('hidden');
        }
    })
    .catch(error => {
        errorsDiv.textContent = 'An error occurred: ' + error;
        errorsDiv.classList.remove('hidden');
    });
}

// Delete User
function deleteUser(id) {
    if (!confirm('Are you sure you want to delete this user? This action cannot be undone.')) {
        return;
    }

    fetch(`<?= site_url('users/delete/') ?>${id}`, {
        method: 'POST'
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('User deleted successfully!');
            location.reload();
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        alert('An error occurred: ' + error);
    });
}
</script>
<?= $this->endSection() ?>

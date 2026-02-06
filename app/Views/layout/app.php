<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'CertMaster' ?> - Certificate Management System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="/public/js/ag-grid/styles/ag-grid.min.css">
    <link rel="stylesheet" href="/public/js/ag-grid/styles/ag-theme-quartz.min.css">
    <link rel="stylesheet" href="/public/css/ag-grid-custom.css">
    <script src="/public/js/ag-grid/ag-grid-community.min.js"></script>
    <style>
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .animate-fade-in {
            animation: fadeIn 0.3s ease-in-out;
        }
    </style>
</head>
<body class="bg-gray-50 min-h-screen">
    <!-- Navigation -->
    <nav class="bg-gradient-to-r from-blue-600 to-blue-700 shadow-lg">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <div class="flex-shrink-0 flex items-center">
                        <i class="fas fa-certificate text-white text-2xl mr-3"></i>
                        <span class="text-white text-xl font-bold">CertMaster</span>
                    </div>
                    <div class="hidden md:ml-10 md:flex md:space-x-4">
                        <a href="<?= site_url('dashboard') ?>" class="text-white hover:bg-blue-500 px-3 py-2 rounded-md text-sm font-medium transition">
                            <i class="fas fa-dashboard mr-2"></i>Dashboard
                        </a>
                        <a href="<?= site_url('servers') ?>" class="text-white hover:bg-blue-500 px-3 py-2 rounded-md text-sm font-medium transition">
                            <i class="fas fa-server mr-2"></i>Servers
                        </a>
                        <a href="<?= site_url('logs') ?>" class="text-white hover:bg-blue-500 px-3 py-2 rounded-md text-sm font-medium transition">
                            <i class="fas fa-history mr-2"></i>Logs
                        </a>
                    </div>
                </div>
                <div class="flex items-center">
                    <span class="text-white text-sm mr-4">
                        <i class="fas fa-user mr-2"></i><?= session()->get('username') ?>
                    </span>
                    <a href="<?= site_url('logout') ?>" class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-md text-sm font-medium transition">
                        <i class="fas fa-sign-out-alt mr-2"></i>Logout
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Flash Messages -->
    <?php if (session()->getFlashdata('success')): ?>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4">
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative animate-fade-in" role="alert">
                <span class="block sm:inline"><?= session()->getFlashdata('success') ?></span>
            </div>
        </div>
    <?php endif; ?>

    <?php if (session()->getFlashdata('error')): ?>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4">
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative animate-fade-in" role="alert">
                <span class="block sm:inline"><?= session()->getFlashdata('error') ?></span>
            </div>
        </div>
    <?php endif; ?>

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <?= $this->renderSection('content') ?>
    </main>

    <!-- Footer -->
    <footer class="bg-white border-t border-gray-200 mt-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
            <p class="text-center text-gray-500 text-sm">
                &copy; <?= date('Y') ?> CertMaster - SSL Certificate Management System
            </p>
        </div>
    </footer>

    <script>
        // Auto-hide flash messages after 5 seconds
        setTimeout(() => {
            const alerts = document.querySelectorAll('[role="alert"]');
            alerts.forEach(alert => {
                alert.style.transition = 'opacity 0.5s';
                alert.style.opacity = '0';
                setTimeout(() => alert.remove(), 500);
            });
        }, 5000);

        // Session expiration check - Check every 5 minutes (300000 ms)
        let sessionCheckInterval = setInterval(checkSessionStatus, 300000);

        function checkSessionStatus() {
            fetch('<?= site_url('check-session') ?>', {
                method: 'GET',
                credentials: 'include'
            })
            .then(response => response.json())
            .then(data => {
                if (!data.success) {
                    // Session expired - redirect to login
                    clearInterval(sessionCheckInterval);
                    showSessionExpiredNotification();
                }
            })
            .catch(error => {
                console.error('Session check failed:', error);
                // On network error, assume session might be expired
            });
        }

        function showSessionExpiredNotification() {
            // Create a full-screen notification
            const notification = document.createElement('div');
            notification.className = 'fixed inset-0 bg-red-50 z-[9999] flex items-center justify-center';
            notification.innerHTML = `
                <div class="bg-white rounded-lg shadow-2xl p-8 text-center max-w-md">
                    <div class="text-5xl mb-4">
                        <i class="fas fa-clock text-red-600"></i>
                    </div>
                    <h2 class="text-2xl font-bold text-gray-900 mb-4">Session Expired</h2>
                    <p class="text-gray-600 mb-6">Your login session has expired for security reasons. You will be redirected to the login page.</p>
                    <div class="flex gap-4">
                        <button onclick="window.location.href = '<?= site_url('login') ?>'" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-medium transition">
                            Go to Login
                        </button>
                    </div>
                </div>
            `;
            document.body.appendChild(notification);

            // Auto-redirect after 3 seconds
            setTimeout(() => {
                window.location.href = '<?= site_url('login') ?>';
            }, 3000);
        }
    </script>
</body>
</html>

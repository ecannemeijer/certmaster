<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>
<div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-blue-500 to-purple-600 px-4">
    <div class="max-w-md w-full space-y-8 bg-white p-10 rounded-xl shadow-2xl animate-fade-in">
        <div class="text-center">
            <div class="flex justify-center mb-4">
                <div class="bg-blue-100 p-4 rounded-full">
                    <i class="fas fa-certificate text-blue-600 text-5xl"></i>
                </div>
            </div>
            <h2 class="text-3xl font-extrabold text-gray-900">
                CertMaster
            </h2>
            <p class="mt-2 text-sm text-gray-600">
                SSL Certificate Management System
            </p>
        </div>

        <?php if (session()->getFlashdata('error')): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                <span class="block sm:inline"><?= session()->getFlashdata('error') ?></span>
            </div>
        <?php endif; ?>

        <form class="mt-8 space-y-6" action="<?= site_url('login') ?>" method="POST">
            <div class="rounded-md shadow-sm space-y-4">
                <div>
                    <label for="username" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-user mr-2"></i>Username
                    </label>
                    <input id="username" name="username" type="text" required 
                           class="appearance-none relative block w-full px-4 py-3 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition"
                           placeholder="Enter your username">
                </div>
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-lock mr-2"></i>Password
                    </label>
                    <input id="password" name="password" type="password" required 
                           class="appearance-none relative block w-full px-4 py-3 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition"
                           placeholder="Enter your password">
                </div>
            </div>

            <div>
                <button type="submit" 
                        class="group relative w-full flex justify-center py-3 px-4 border border-transparent text-sm font-medium rounded-lg text-white bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200 transform hover:scale-105">
                    <span class="absolute left-0 inset-y-0 flex items-center pl-3">
                        <i class="fas fa-sign-in-alt text-blue-300"></i>
                    </span>
                    Sign in
                </button>
            </div>
        </form>

        <div class="text-center">
            <p class="text-xs text-gray-500 mt-4">
                Default credentials: admin / password
            </p>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->extend('layout/app') ?>

<?= $this->section('content') ?>
<div class="max-w-3xl mx-auto">
    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <div class="px-6 py-4 bg-gradient-to-r from-green-600 to-green-700">
            <h2 class="text-2xl font-bold text-white">
                <i class="fas fa-upload mr-2"></i>Upload Certificate
            </h2>
            <p class="text-green-100 text-sm mt-1">Server: <?= esc($server['name']) ?></p>
        </div>

        <form action="<?= site_url('certificates/store/' . $server['id']) ?>" method="POST" enctype="multipart/form-data" class="p-6 space-y-6">
            <?php if (session()->get('errors')): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                    <ul class="list-disc list-inside">
                        <?php foreach (session()->get('errors') as $error): ?>
                            <li><?= esc($error) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <i class="fas fa-info-circle text-blue-600 text-xl"></i>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-blue-800">Information</h3>
                        <div class="mt-2 text-sm text-blue-700">
                            <ul class="list-disc list-inside space-y-1">
                                <li>Upload both the PEM certificate file and the private KEY file</li>
                                <li>Certificate information will be automatically extracted</li>
                                <li>This will replace any existing certificate for this server</li>
                                <li>Use the Deploy button on the dashboard to push the certificate to the server</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <div class="space-y-4">
                <div>
                    <label for="pem_file" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-file-certificate mr-1"></i>Certificate File (PEM/CRT) *
                    </label>
                    <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-lg hover:border-blue-500 transition">
                        <div class="space-y-1 text-center">
                            <i class="fas fa-file-upload text-gray-400 text-3xl mb-3"></i>
                            <div class="flex text-sm text-gray-600">
                                <label for="pem_file" class="relative cursor-pointer bg-white rounded-md font-medium text-blue-600 hover:text-blue-500 focus-within:outline-none">
                                    <span>Upload a file</span>
                                    <input id="pem_file" name="pem_file" type="file" class="sr-only" required accept=".pem,.crt,.cer" onchange="updateFileName('pem_file', 'pem_file_name')">
                                </label>
                                <p class="pl-1">or drag and drop</p>
                            </div>
                            <p class="text-xs text-gray-500">PEM, CRT, or CER file</p>
                            <p id="pem_file_name" class="text-sm text-blue-600 font-medium"></p>
                        </div>
                    </div>
                </div>

                <div>
                    <label for="key_file" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-key mr-1"></i>Private Key File (KEY) *
                    </label>
                    <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-lg hover:border-blue-500 transition">
                        <div class="space-y-1 text-center">
                            <i class="fas fa-file-upload text-gray-400 text-3xl mb-3"></i>
                            <div class="flex text-sm text-gray-600">
                                <label for="key_file" class="relative cursor-pointer bg-white rounded-md font-medium text-blue-600 hover:text-blue-500 focus-within:outline-none">
                                    <span>Upload a file</span>
                                    <input id="key_file" name="key_file" type="file" class="sr-only" required accept=".key,.pem" onchange="updateFileName('key_file', 'key_file_name')">
                                </label>
                                <p class="pl-1">or drag and drop</p>
                            </div>
                            <p class="text-xs text-gray-500">KEY or PEM file</p>
                            <p id="key_file_name" class="text-sm text-blue-600 font-medium"></p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex justify-end space-x-3 pt-4 border-t">
                <a href="<?= site_url('dashboard') ?>" 
                   class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition">
                    <i class="fas fa-times mr-2"></i>Cancel
                </a>
                <button type="submit" 
                        class="px-6 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition">
                    <i class="fas fa-upload mr-2"></i>Upload Certificate
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function updateFileName(inputId, displayId) {
    const input = document.getElementById(inputId);
    const display = document.getElementById(displayId);
    if (input.files.length > 0) {
        display.textContent = 'Selected: ' + input.files[0].name;
    }
}
</script>
<?= $this->endSection() ?>

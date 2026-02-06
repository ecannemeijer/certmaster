<?= $this->extend('layout/app') ?>

<?= $this->section('content') ?>
<div class="max-w-3xl mx-auto">
    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <div class="px-6 py-4 bg-gradient-to-r from-blue-600 to-blue-700">
            <h2 class="text-2xl font-bold text-white">
                <i class="fas fa-edit mr-2"></i>Edit Server
            </h2>
        </div>

        <form action="<?= site_url('servers/update/' . $server['id']) ?>" method="POST" class="p-6 space-y-6">
            <?php if (session()->get('errors')): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                    <ul class="list-disc list-inside">
                        <?php foreach (session()->get('errors') as $error): ?>
                            <li><?= esc($error) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="md:col-span-2">
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-tag mr-1"></i>Server Name *
                    </label>
                    <input type="text" name="name" id="name" required
                           value="<?= old('name', $server['name']) ?>"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>

                <div>
                    <label for="hostname" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-globe mr-1"></i>Hostname *
                    </label>
                    <input type="text" name="hostname" id="hostname" required
                           value="<?= old('hostname', $server['hostname']) ?>"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>

                <div>
                    <label for="ip_address" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-network-wired mr-1"></i>IP Address *
                    </label>
                    <input type="text" name="ip_address" id="ip_address" required
                           value="<?= old('ip_address', $server['ip_address']) ?>"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>

                <div>
                    <label for="ssh_port" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-door-open mr-1"></i>SSH Port *
                    </label>
                    <input type="number" name="ssh_port" id="ssh_port" required
                           value="<?= old('ssh_port', $server['ssh_port']) ?>"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>

                <div>
                    <label for="ssh_username" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-user mr-1"></i>SSH Username *
                    </label>
                    <input type="text" name="ssh_username" id="ssh_username" required
                           value="<?= old('ssh_username', $server['ssh_username']) ?>"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>

                <div class="md:col-span-2">
                    <label for="certificate_path" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-folder mr-1"></i>Certificate Path on Server *
                    </label>
                    <input type="text" name="certificate_path" id="certificate_path" required
                           value="<?= old('certificate_path', $server['certificate_path']) ?>"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>

                <div class="md:col-span-2">
                    <label for="apache_restart_command" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-terminal mr-1"></i>Apache Restart Command
                    </label>
                    <input type="text" name="apache_restart_command" id="apache_restart_command"
                           value="<?= old('apache_restart_command', $server['apache_restart_command']) ?>"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>
            </div>

            <div class="flex justify-end space-x-3 pt-4 border-t">
                <a href="<?= site_url('servers') ?>" 
                   class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition">
                    <i class="fas fa-times mr-2"></i>Cancel
                </a>
                <button type="submit" 
                        class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                    <i class="fas fa-save mr-2"></i>Update Server
                </button>
            </div>
        </form>
    </div>
</div>
<?= $this->endSection() ?>

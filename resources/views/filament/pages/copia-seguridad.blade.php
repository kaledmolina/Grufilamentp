<x-filament-panels::page>
    <div class="space-y-6">
        {{-- Tarjetas de información de la Base de Datos --}}
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <div class="p-5 bg-white border border-gray-200 rounded-xl shadow-sm dark:bg-gray-900 dark:border-gray-800">
                <div class="flex items-center gap-3">
                    <div class="p-2.5 text-primary-600 bg-primary-50 rounded-lg dark:bg-primary-950 dark:text-primary-400">
                        <x-heroicon-o-circle-stack class="w-6 h-6" />
                    </div>
                    <div>
                        <p class="text-xs font-medium text-gray-500 uppercase dark:text-gray-400">Motor de BD</p>
                        <p class="text-lg font-bold text-gray-900 dark:text-white">{{ $dbStats['driver'] ?? 'Desconocido' }}</p>
                    </div>
                </div>
            </div>

            <div class="p-5 bg-white border border-gray-200 rounded-xl shadow-sm dark:bg-gray-900 dark:border-gray-800">
                <div class="flex items-center gap-3">
                    <div class="p-2.5 text-emerald-600 bg-emerald-50 rounded-lg dark:bg-emerald-950 dark:text-emerald-400">
                        <x-heroicon-o-circle-stack class="w-6 h-6" />
                    </div>
                    <div>
                        <p class="text-xs font-medium text-gray-500 uppercase dark:text-gray-400">Base de Datos</p>
                        <p class="text-lg font-bold text-gray-900 truncate dark:text-white max-w-[180px]" title="{{ $dbStats['database'] ?? '' }}">
                            {{ basename($dbStats['database'] ?? 'laravel') }}
                        </p>
                    </div>
                </div>
            </div>

            <div class="p-5 bg-white border border-gray-200 rounded-xl shadow-sm dark:bg-gray-900 dark:border-gray-800">
                <div class="flex items-center gap-3">
                    <div class="p-2.5 text-blue-600 bg-blue-50 rounded-lg dark:bg-blue-950 dark:text-blue-400">
                        <x-heroicon-o-table-cells class="w-6 h-6" />
                    </div>
                    <div>
                        <p class="text-xs font-medium text-gray-500 uppercase dark:text-gray-400">Total de Tablas</p>
                        <p class="text-lg font-bold text-gray-900 dark:text-white">{{ $dbStats['table_count'] ?? 0 }}</p>
                    </div>
                </div>
            </div>

            <div class="p-5 bg-white border border-gray-200 rounded-xl shadow-sm dark:bg-gray-900 dark:border-gray-800">
                <div class="flex items-center gap-3">
                    <div class="p-2.5 text-amber-600 bg-amber-50 rounded-lg dark:bg-amber-950 dark:text-amber-400">
                        <x-heroicon-o-circle-stack class="w-6 h-6" />
                    </div>
                    <div>
                        <p class="text-xs font-medium text-gray-500 uppercase dark:text-gray-400">Tamaño Estimado</p>
                        <p class="text-lg font-bold text-gray-900 dark:text-white">
                            {{ $dbStats['total_size_mb'] ?? 0 }} MB
                        </p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Formulario de Opciones y Descarga --}}
        <div class="p-6 bg-white border border-gray-200 rounded-xl shadow-sm dark:bg-gray-900 dark:border-gray-800 space-y-6">
            <div class="flex items-start gap-4">
                <div class="p-3 text-primary-600 bg-primary-50 rounded-xl dark:bg-primary-950 dark:text-primary-400">
                    <x-heroicon-o-arrow-down-tray class="w-7 h-7" />
                </div>
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                        Exportar Volcado SQL Completo
                    </h3>
                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                        Genera un archivo SQL con la estructura completa de tablas y todos los datos registrados. Compatible para restauración inmediata en phpMyAdmin, DBeaver o la consola de MySQL.
                    </p>
                </div>
            </div>

            <form wire:submit="download" class="space-y-6">
                {{ $this->form }}

                <div class="p-4 bg-amber-50 border border-amber-200 rounded-lg dark:bg-amber-950/40 dark:border-amber-800/60">
                    <div class="flex gap-3">
                        <x-heroicon-s-shield-check class="w-5 h-5 text-amber-600 shrink-0 dark:text-amber-400" />
                        <p class="text-xs text-amber-800 dark:text-amber-300">
                            <strong>Seguridad y Privacidad:</strong> La copia de seguridad contiene datos sensibles como usuarios, contraseñas encriptadas y órdenes de servicio. Guarda el archivo en un almacenamiento cifrado y no lo compartas con personas no autorizadas.
                        </p>
                    </div>
                </div>

                <div class="flex items-center gap-3 pt-2">
                    <x-filament::button type="submit" size="lg" icon="heroicon-o-arrow-down-tray">
                        Descargar Copia de Seguridad Ahora
                    </x-filament::button>
                </div>
            </form>
        </div>
    </div>
</x-filament-panels::page>

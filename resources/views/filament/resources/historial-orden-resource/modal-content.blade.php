<div class="space-y-4">
    @if($orden->logs->isEmpty())
        <div class="p-4 text-center text-gray-500">
            No hay registros de historial para esta orden.
        </div>
    @else
        <div class="relative overflow-x-auto">
            <table class="w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400">
                <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                    <tr>
                        <th scope="col" class="px-6 py-3">Fecha</th>
                        <th scope="col" class="px-6 py-3">Usuario</th>
                        <th scope="col" class="px-6 py-3">Acción</th>
                        <th scope="col" class="px-6 py-3">Detalles</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($orden->logs->sortByDesc('created_at') as $log)
                        <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
                            <td class="px-6 py-4">{{ $log->created_at->format('d/m/Y H:i') }}</td>
                            <td class="px-6 py-4">{{ $log->user->name ?? 'Sistema' }}</td>
                            <td class="px-6 py-4 uppercase font-bold">{{ $log->action }}</td>
                            <td class="px-6 py-4">
                                <code class="text-xs">
                                    {{ $log->description }}
                                </code>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>

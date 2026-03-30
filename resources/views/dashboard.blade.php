<x-layouts::app :title="__('Dashboard CRM')">
    <div class="space-y-6">
        {{-- Header --}}
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-white">Dashboard CRM</h1>
                <p class="text-gray-400 mt-1">Bienvenido a tu panel de control</p>
            </div>
            <div class="text-right">
                <p class="text-sm text-gray-400">{{ now()->format('d/m/Y') }}</p>
                <p class="text-lg font-semibold text-white">{{ auth()->user()->name }}</p>
            </div>
        </div>

        {{-- Stats Cards --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            @php
                $totalContacts = \App\Models\Contact::count();
                $totalTasks = \App\Models\Task::count();
                $pendingTasks = \App\Models\Task::where('status', 'pending')->count();
                $completedTasks = \App\Models\Task::where('status', 'completed')->count();
            @endphp

            {{-- Total Contacts --}}
            <div class="card group">
                <div class="card-header">
                    <h3>
                        <i class="fas fa-users text-blue-400"></i>
                        Total Contactos
                    </h3>
                </div>
                <div class="card-body">
                    <div class="text-4xl font-bold text-white mb-2">{{ number_format($totalContacts) }}</div>
                    <p class="text-gray-400 text-sm">Contactos registrados</p>
                </div>
            </div>

            {{-- Total Tasks --}}
            <div class="card group">
                <div class="card-header">
                    <h3>
                        <i class="fas fa-clipboard-list text-green-400"></i>
                        Total Tareas
                    </h3>
                </div>
                <div class="card-body">
                    <div class="text-4xl font-bold text-white mb-2">{{ number_format($totalTasks) }}</div>
                    <p class="text-gray-400 text-sm">Tareas creadas</p>
                </div>
            </div>

            {{-- Pending Tasks --}}
            <div class="card group">
                <div class="card-header">
                    <h3>
                        <i class="fas fa-clock text-yellow-400"></i>
                        Tareas Pendientes
                    </h3>
                </div>
                <div class="card-body">
                    <div class="text-4xl font-bold text-white mb-2">{{ number_format($pendingTasks) }}</div>
                    <p class="text-gray-400 text-sm">Esperando acción</p>
                </div>
            </div>

            {{-- Completed Tasks --}}
            <div class="card group">
                <div class="card-header">
                    <h3>
                        <i class="fas fa-check-circle text-emerald-400"></i>
                        Tareas Completadas
                    </h3>
                </div>
                <div class="card-body">
                    <div class="text-4xl font-bold text-white mb-2">{{ number_format($completedTasks) }}</div>
                    <p class="text-gray-400 text-sm">Finalizadas exitosamente</p>
                </div>
            </div>
        </div>

        {{-- Recent Activity & Quick Actions --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            {{-- Recent Tasks --}}
            <div class="card">
                <div class="card-header">
                    <h3>
                        <i class="fas fa-history text-purple-400"></i>
                        Tareas Recientes
                    </h3>
                </div>
                <div class="card-body">
                    @php
                        $recentTasks = \App\Models\Task::latest()->take(5)->get();
                    @endphp
                    @if($recentTasks->count() > 0)
                        <div class="space-y-3">
                            @foreach($recentTasks as $task)
                                <div class="flex items-center justify-between p-3 rounded-lg bg-gray-800/50 hover:bg-gray-800/70 transition-colors">
                                    <div class="flex-1">
                                        <p class="text-white font-medium text-sm">{{ $task->title }}</p>
                                        <p class="text-gray-400 text-xs">{{ $task->created_at->diffForHumans() }}</p>
                                    </div>
                                    <span class="px-2 py-1 text-xs rounded-full
                                        @if($task->status === 'completed') bg-green-500/20 text-green-400
                                        @elseif($task->status === 'in_progress') bg-blue-500/20 text-blue-400
                                        @elseif($task->status === 'pending') bg-yellow-500/20 text-yellow-400
                                        @else bg-red-500/20 text-red-400 @endif">
                                        {{ $task->status }}
                                    </span>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-gray-400 text-center py-8">No hay tareas recientes</p>
                    @endif
                </div>
            </div>

            {{-- Quick Actions --}}
            <div class="card">
                <div class="card-header">
                    <h3>
                        <i class="fas fa-bolt text-orange-400"></i>
                        Acciones Rápidas
                    </h3>
                </div>
                <div class="card-body">
                    <div class="grid grid-cols-2 gap-4">
                        <a href="{{ route('app.contacts.index') }}" class="flex flex-col items-center p-4 rounded-lg bg-gradient-to-br from-blue-500/20 to-blue-600/20 hover:from-blue-500/30 hover:to-blue-600/30 transition-all group">
                            <i class="fas fa-user-plus text-2xl text-blue-400 mb-2 group-hover:scale-110 transition-transform"></i>
                            <span class="text-white text-sm font-medium">Nuevo Contacto</span>
                        </a>
                        <a href="{{ route('app.tasks.index') }}" class="flex flex-col items-center p-4 rounded-lg bg-gradient-to-br from-green-500/20 to-green-600/20 hover:from-green-500/30 hover:to-green-600/30 transition-all group">
                            <i class="fas fa-plus-circle text-2xl text-green-400 mb-2 group-hover:scale-110 transition-transform"></i>
                            <span class="text-white text-sm font-medium">Nueva Tarea</span>
                        </a>
                        <a href="{{ route('admin.index') }}" class="flex flex-col items-center p-4 rounded-lg bg-gradient-to-br from-purple-500/20 to-purple-600/20 hover:from-purple-500/30 hover:to-purple-600/30 transition-all group">
                            <i class="fas fa-cog text-2xl text-purple-400 mb-2 group-hover:scale-110 transition-transform"></i>
                            <span class="text-white text-sm font-medium">Admin Panel</span>
                        </a>
                        <a href="{{ route('profile.edit') }}" class="flex flex-col items-center p-4 rounded-lg bg-gradient-to-br from-pink-500/20 to-pink-600/20 hover:from-pink-500/30 hover:to-pink-600/30 transition-all group">
                            <i class="fas fa-user-cog text-2xl text-pink-400 mb-2 group-hover:scale-110 transition-transform"></i>
                            <span class="text-white text-sm font-medium">Mi Perfil</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        {{-- Additional Stats --}}
        <div class="card">
            <div class="card-header">
                <h3>
                    <i class="fas fa-chart-line text-cyan-400"></i>
                    Resumen del Mes
                </h3>
            </div>
            <div class="card-body">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    @php
                        $thisMonthContacts = \App\Models\Contact::whereMonth('created_at', now()->month)->count();
                        $thisMonthTasks = \App\Models\Task::whereMonth('created_at', now()->month)->count();
                        $completionRate = $totalTasks > 0 ? round(($completedTasks / $totalTasks) * 100, 1) : 0;
                    @endphp

                    <div class="text-center">
                        <div class="text-3xl font-bold text-cyan-400 mb-2">{{ $thisMonthContacts }}</div>
                        <p class="text-gray-400 text-sm">Nuevos contactos este mes</p>
                    </div>
                    <div class="text-center">
                        <div class="text-3xl font-bold text-cyan-400 mb-2">{{ $thisMonthTasks }}</div>
                        <p class="text-gray-400 text-sm">Nuevas tareas este mes</p>
                    </div>
                    <div class="text-center">
                        <div class="text-3xl font-bold text-cyan-400 mb-2">{{ $completionRate }}%</div>
                        <p class="text-gray-400 text-sm">Tasa de completación</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-layouts::app>

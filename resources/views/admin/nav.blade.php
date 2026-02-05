<nav class="bg-gray-900 text-white p-4 shadow-lg">
    <div class="max-w-7xl mx-auto flex justify-between items-center">
        
        <div class="flex items-center gap-6">
            <span class="font-bold text-xl text-red-500">Vilcanet<span class="text-white">Admin</span></span>
            
            <div class="space-x-4 text-sm font-medium">
                
                <a href="{{ route('admin.tickets') }}" 
                   class="{{ request()->routeIs('admin.tickets*') ? 'text-white border-b-2 border-red-500 pb-1' : 'text-gray-400 hover:text-white transition' }}">
                    🎫 Tickets
                </a>

                @if(Auth::user()->isBilling() || Auth::user()->role == 'super_admin' || Auth::user()->role == 'admin')
                <a href="{{ route('admin.billing.index') }}" 
                   class="{{ request()->routeIs('admin.billing*') ? 'text-white border-b-2 border-red-500 pb-1' : 'text-gray-400 hover:text-white transition' }}">
                    💰 Pagos
                </a>
                @endif

                <a href="{{ route('admin.loyalty.index') }}" 
                   class="{{ request()->routeIs('admin.loyalty.index') || request()->routeIs('admin.loyalty.manual') ? 'text-white border-b-2 border-red-500 pb-1' : 'text-gray-400 hover:text-white transition' }}">
                    ⭐ Fidelidad
                </a>

                <a href="{{ route('rewards.index') }}" 
                   class="{{ request()->routeIs('rewards.*') ? 'text-white border-b-2 border-red-500 pb-1' : 'text-gray-400 hover:text-white transition' }}">
                    🎁 Catálogo
                </a>

            </div>
        </div>

        <div class="flex items-center gap-4">
            <span class="text-xs text-gray-400">Hola, {{ Auth::user()->name }}</span>
            <form action="{{ route('admin.logout') }}" method="POST" class="inline">
                @csrf
                <button class="bg-red-600 hover:bg-red-700 text-white text-xs font-bold py-1 px-3 rounded transition">
                    Salir 🚪
                </button>
            </form>
        </div>
    </div>
</nav>
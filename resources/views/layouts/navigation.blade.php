<nav x-data="{ open: false }" class="bg-white border-b-2 border-blue-200 shadow-sm">
    <!-- Primary Navigation Menu -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <!-- Logo -->
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('dashboard') }}" class="flex items-center space-x-3">
                        <img src="{{ asset('assets/images/LOGOCompuPlay.png') }}" alt="Logo" class="h-10 w-auto">
                    </a>
                </div>

                <!-- Navigation Links -->
                <div class="hidden space-x-2 -my-px ms-10 md:flex items-center">
                    @if($currentUser && $currentUser->role === 'teacher')
                        <a href="{{ route('classrooms.index') }}"
                            class="inline-flex items-center px-1 pt-1 border-b-2 {{ request()->is('classrooms*') ? 'border-blue-500 text-gray-900' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }} text-sm font-medium leading-5 focus:outline-none focus:text-gray-700 focus:border-gray-300 transition duration-150 ease-in-out">
                            {{ __('Kelas Saya') }}
                        </a>

                        <div class="flex items-center">
                            <x-dropdown align="left" width="48">
                                <x-slot name="trigger">
                                    <button
                                        class="inline-flex items-center px-1 pt-1 border-b-2 border-transparent text-sm font-medium leading-5 text-gray-500 hover:text-gray-700 hover:border-gray-300 focus:outline-none focus:text-gray-700 focus:border-gray-300 transition duration-150 ease-in-out">
                                        <div>Pelajaran</div>
                                        <div class="ms-1">
                                            <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg"
                                                viewBox="0 0 20 20">
                                                <path fill-rule="evenodd"
                                                    d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                                    clip-rule="evenodd" />
                                            </svg>
                                        </div>
                                    </button>
                                </x-slot>

                                <x-slot name="content">
                                    <x-dropdown-link :href="route('lessons.index')">
                                        Urus Pelajaran
                                    </x-dropdown-link>
                                    <x-dropdown-link :href="route('lesson.index')">
                                        Pratonton Pelajaran
                                    </x-dropdown-link>
                                    <x-dropdown-link :href="route('submission.index')">
                                        Penyerahan
                                    </x-dropdown-link>
                                    <x-dropdown-link :href="route('assignments.create')">
                                        Tugaskan Pelajaran
                                    </x-dropdown-link>
                                </x-slot>
                            </x-dropdown>
                        </div>

                        <div class="flex items-center">
                            <x-dropdown align="left" width="48">
                                <x-slot name="trigger">
                                    <button
                                        class="inline-flex items-center px-1 pt-1 border-b-2 border-transparent text-sm font-medium leading-5 text-gray-500 hover:text-gray-700 hover:border-gray-300 focus:outline-none focus:text-gray-700 focus:border-gray-300 transition duration-150 ease-in-out">
                                        <div>{{ __('Prestasi') }}</div>
                                        <div class="ms-1">
                                            <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg"
                                                viewBox="0 0 20 20">
                                                <path fill-rule="evenodd"
                                                    d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                                    clip-rule="evenodd" />
                                            </svg>
                                        </div>
                                    </button>
                                </x-slot>

                                <x-slot name="content">
                                    <x-dropdown-link :href="route('progress.index')">
                                        {{ __('Lihat Perkembangan') }}
                                    </x-dropdown-link>
                                    <x-dropdown-link :href="route('monitoring.index')">
                                        {{ __('Pantau Pelajar') }}
                                    </x-dropdown-link>
                                    <x-dropdown-link :href="route('performance.index')">
                                        {{ __('Lihat Prestasi') }}
                                    </x-dropdown-link>
                                </x-slot>
                            </x-dropdown>
                        </div>
                    @else
                        <a href="{{ route('lesson.index') }}"
                            class="inline-flex items-center px-1 pt-1 border-b-2 {{ request()->is('lesson') || request()->is('lesson/*') ? 'border-blue-500 text-gray-900' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }} text-sm font-medium leading-5 focus:outline-none focus:text-gray-700 focus:border-gray-300 transition duration-150 ease-in-out">
                            Pelajaran Saya
                        </a>
                        <a href="{{ route('submission.show') }}"
                            class="inline-flex items-center px-1 pt-1 border-b-2 {{ request()->is('submission') || request()->is('submission/*') ? 'border-blue-500 text-gray-900' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }} text-sm font-medium leading-5 focus:outline-none focus:text-gray-700 focus:border-gray-300 transition duration-150 ease-in-out">
                            Penyerahan Saya
                        </a>
                        <a href="{{ route('performance.index') }}"
                            class="inline-flex items-center px-1 pt-1 border-b-2 {{ request()->is('performance') || request()->is('performance/*') ? 'border-blue-500 text-gray-900' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }} text-sm font-medium leading-5 focus:outline-none focus:text-gray-700 focus:border-gray-300 transition duration-150 ease-in-out">
                            {{ __('Lihat Prestasi') }}
                        </a>
                        <a href="{{ route('badges.my') }}"
                            class="inline-flex items-center px-1 pt-1 border-b-2 {{ request()->routeIs('badges.my') ? 'border-blue-500 text-gray-900' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }} text-sm font-medium leading-5 focus:outline-none focus:text-gray-700 focus:border-gray-300 transition duration-150 ease-in-out">
                            {{ __('Lencana Saya') }}
                        </a>
                    @endif

                    @if($currentUser && $currentUser->role === 'teacher')
                        <a href="{{ route('badges.manage') }}"
                            class="inline-flex items-center px-1 pt-1 border-b-2 {{ request()->is('badges*') ? 'border-blue-500 text-gray-900' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }} text-sm font-medium leading-5 focus:outline-none focus:text-gray-700 focus:border-gray-300 transition duration-150 ease-in-out">
                            Lencana
                        </a>
                    @endif

                    <a href="{{ url('/forums') }}"
                        class="inline-flex items-center px-1 pt-1 border-b-2 {{ request()->is('forums*') || request()->is('forum*') ? 'border-blue-500 text-gray-900' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }} text-sm font-medium leading-5 focus:outline-none focus:text-gray-700 focus:border-gray-300 transition duration-150 ease-in-out">
                        Forum
                    </a>

                    @if($currentUser && $currentUser->role === 'teacher')
                    <div class="flex items-center">
                        <x-dropdown align="left" width="48">
                            <x-slot name="trigger">
                                <button
                                    class="inline-flex items-center px-1 pt-1 border-b-2 border-transparent text-sm font-medium leading-5 text-gray-500 hover:text-gray-700 hover:border-gray-300 focus:outline-none focus:text-gray-700 focus:border-gray-300 transition duration-150 ease-in-out">
                                    <div>AI</div>
                                    <div class="ms-1">
                                        <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg"
                                            viewBox="0 0 20 20">
                                            <path fill-rule="evenodd"
                                                d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                                clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                </button>
                            </x-slot>

                            <x-slot name="content">
                                <x-dropdown-link :href="route('ai-generator.index')">
                                    Penjana AI
                                </x-dropdown-link>
                                <x-dropdown-link :href="route('ai-generator.slides')">
                                    Jana Slaid
                                </x-dropdown-link>
                                <x-dropdown-link :href="route('ai-generator.slaid-dijana')">
                                    Slaid Dijana
                                </x-dropdown-link>
                                <x-dropdown-link :href="route('ai-generator.quiz')">
                                    Jana Kuiz
                                </x-dropdown-link>
                            </x-slot>
                        </x-dropdown>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Right Side Icons and Dropdown -->
            <div class="flex items-center ms-6 gap-3">
                <!-- Notification Icon -->
                <div class="relative">
                    <button id="notificationBtn"
                        class="inline-flex items-center justify-center p-2 rounded-lg text-gray-600 hover:text-gray-900 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 transition duration-150 ease-in-out relative">
                        <i class="fas fa-bell text-lg"></i>
                        <span id="notificationBadge"
                            class="hidden absolute top-0 right-0 inline-flex items-center justify-center min-w-[1.25rem] h-5 px-1.5 py-0.5 text-xs font-bold leading-none text-white transform translate-x-1/2 -translate-y-1/2 bg-red-600 rounded-full z-10"></span>
                    </button>
                    <div id="notificationMenu"
                        class="hidden absolute right-0 mt-2 w-80 bg-white rounded-md shadow-lg z-50 border border-gray-200">
                        <div class="px-4 py-2 border-b border-gray-200">
                            <h3 class="text-sm font-semibold text-gray-900">Pemberitahuan</h3>
                        </div>
                        <div id="notificationList" class="py-1 max-h-96 overflow-y-auto">
                            <div class="px-4 py-3 text-sm text-gray-500 text-center">Tiada pemberitahuan</div>
                        </div>
                        <div class="px-4 py-2 border-t border-gray-200 bg-gray-50">
                            <a href="{{ route('notifications.index') }}"
                                class="block text-center text-sm font-medium text-blue-600 hover:text-blue-800">
                                Lihat Semua Pemberitahuan
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Message Icon -->
                <div class="relative">
                    <a href="{{ route('messaging.index') }}"
                        class="inline-flex items-center justify-center p-2 rounded-lg text-gray-600 hover:text-gray-900 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 transition duration-150 ease-in-out relative">
                        <i class="fas fa-envelope text-lg"></i>
                        <span id="messageBadge"
                            class="hidden absolute top-0 right-0 inline-flex items-center justify-center px-1.5 py-0.5 text-xs font-bold leading-none text-white transform translate-x-1/2 -translate-y-1/2 bg-red-600 rounded-full"></span>
                    </a>
                </div>

                <!-- Profile Dropdown -->
                <div class="relative hidden lg:block">
                    <x-dropdown align="right" width="48">
                        <x-slot name="trigger">
                            <button
                                class="inline-flex items-center px-3 py-2 border border-gray-200 text-sm leading-4 font-medium rounded-lg text-gray-800 bg-white hover:bg-blue-50 hover:border-blue-300 focus:outline-none transition ease-in-out duration-150 gap-2">
                                @if($currentUser->avatar_url)
                                    <img src="{{ asset($currentUser->avatar_url) }}"
                                        alt="{{ $currentUser->full_name ?? 'User' }}"
                                        class="h-8 w-8 rounded-full object-cover border-2 border-gray-200">
                                @else
                                    <div
                                        class="h-8 w-8 rounded-full bg-gradient-to-br from-blue-500 to-blue-600 flex items-center justify-center text-white font-semibold text-sm border-2 border-gray-200">
                                        {{ strtoupper(substr($currentUser->full_name ?? $currentUser->username ?? 'U', 0, 1)) }}
                                    </div>
                                @endif
                                <div>{{ $currentUser->full_name ?? $currentUser->email ?? 'User' }}</div>
                                <div class="ms-1">
                                    <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg"
                                        viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                            clip-rule="evenodd" />
                                    </svg>
                                </div>
                            </button>
                        </x-slot>

                        <x-slot name="content">
                            <x-dropdown-link :href="route('profile.show', $currentUser->id)">
                                {{ __('Profil') }}
                            </x-dropdown-link>

                            <x-dropdown-link :href="route('settings.index')">
                                {{ __('Tetapan') }}
                            </x-dropdown-link>

                            <!-- Authentication -->
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf

                                <x-dropdown-link :href="route('logout')" onclick="event.preventDefault();
                                                    this.closest('form').submit();">
                                    {{ __('Log Keluar') }}
                                </x-dropdown-link>
                            </form>
                        </x-slot>
                    </x-dropdown>
                </div>
            </div>

            <!-- Hamburger -->
            <div class="-me-2 flex items-center md:hidden">
                <button @click="open = ! open"
                    class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 focus:text-gray-500 transition duration-150 ease-in-out">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex"
                            stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round"
                            stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Responsive Navigation Menu -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden">
        <div class="pt-2 pb-3 space-y-1">
            @if($currentUser && $currentUser->role === 'teacher')
                <a href="{{ route('classrooms.index') }}"
                    class="block px-4 py-2 text-base font-medium {{ request()->routeIs('classrooms.*') ? 'text-gray-900 bg-gray-100' : 'text-gray-500 hover:text-gray-800 hover:bg-gray-100' }} transition duration-150 ease-in-out">
                    {{ __('Kelas Saya') }}
                </a>

                <div class="pt-2 pb-1 border-t border-gray-200">
                    <div class="px-4 text-xs text-gray-500 uppercase font-semibold">
                        {{ __('Pelajaran') }}
                    </div>
                    <a href="{{ route('lessons.index') }}"
                        class="block px-4 py-2 text-base font-medium {{ request()->routeIs('lessons.*') ? 'text-gray-900 bg-gray-100' : 'text-gray-500 hover:text-gray-800 hover:bg-gray-100' }} transition duration-150 ease-in-out">
                        {{ __('Urus Pelajaran') }}
                    </a>
                    <a href="{{ route('lesson.index') }}"
                        class="block px-4 py-2 text-base font-medium {{ request()->routeIs('lesson.*') ? 'text-gray-900 bg-gray-100' : 'text-gray-500 hover:text-gray-800 hover:bg-gray-100' }} transition duration-150 ease-in-out">
                        {{ __('Pratonton Pelajaran') }}
                    </a>
                    <a href="{{ route('submission.index') }}"
                        class="block px-4 py-2 text-base font-medium {{ request()->routeIs('submission.index') ? 'text-gray-900 bg-gray-100' : 'text-gray-500 hover:text-gray-800 hover:bg-gray-100' }} transition duration-150 ease-in-out">
                        {{ __('Serahan') }}
                    </a>
                    <a href="{{ route('assignments.create') }}"
                        class="block px-4 py-2 text-base font-medium {{ request()->routeIs('assignments.create') ? 'text-gray-900 bg-gray-100' : 'text-gray-500 hover:text-gray-800 hover:bg-gray-100' }} transition duration-150 ease-in-out">
                        {{ __('Tugaskan Pelajaran') }}
                    </a>
                </div>



                <div class="pt-2 pb-1 border-t border-gray-200">
                    <div class="px-4 text-xs text-gray-500 uppercase font-semibold">
                        {{ __('Prestasi') }}
                    </div>
                    <a href="{{ route('progress.index') }}"
                        class="block px-4 py-2 text-base font-medium {{ request()->routeIs('progress.index') ? 'text-gray-900 bg-gray-100' : 'text-gray-500 hover:text-gray-800 hover:bg-gray-100' }} transition duration-150 ease-in-out">
                        {{ __('Lihat Perkembangan') }}
                    </a>
                    <a href="{{ route('performance.index') }}"
                        class="block px-4 py-2 text-base font-medium {{ request()->routeIs('performance.index') ? 'text-gray-900 bg-gray-100' : 'text-gray-500 hover:text-gray-800 hover:bg-gray-100' }} transition duration-150 ease-in-out">
                        {{ __('Lihat Prestasi') }}
                    </a>
                </div>
            @else
                <a href="{{ route('lesson.index') }}"
                    class="block px-4 py-2 text-base font-medium {{ request()->routeIs('lesson.*') ? 'text-gray-900 bg-gray-100' : 'text-gray-500 hover:text-gray-800 hover:bg-gray-100' }} transition duration-150 ease-in-out">
                    {{ __('Lihat Pelajaran') }}
                </a>
                <a href="{{ route('submission.show') }}"
                    class="block px-4 py-2 text-base font-medium {{ request()->routeIs('submission.*') ? 'text-gray-900 bg-gray-100' : 'text-gray-500 hover:text-gray-800 hover:bg-gray-100' }} transition duration-150 ease-in-out">
                    {{ __('Serahan Saya') }}
                </a>
                <div class="pt-2 pb-1 border-t border-gray-200">
                    <a href="{{ route('performance.index') }}"
                        class="block px-4 py-2 text-base font-medium {{ request()->routeIs('performance.index') ? 'text-gray-900 bg-gray-100' : 'text-gray-500 hover:text-gray-800 hover:bg-gray-100' }} transition duration-150 ease-in-out">
                        {{ __('Lihat Prestasi') }}
                    </a>
                    <a href="{{ route('badges.my') }}"
                        class="block px-4 py-2 text-base font-medium {{ request()->routeIs('badges.my') ? 'text-gray-900 bg-gray-100' : 'text-gray-500 hover:text-gray-800 hover:bg-gray-100' }} transition duration-150 ease-in-out">
                        {{ __('Lencana Saya') }}
                    </a>
                </div>
            @endif

            @if($currentUser && $currentUser->role === 'teacher')
                <a href="{{ route('badges.manage') }}"
                    class="block px-4 py-2 text-base font-medium {{ request()->is('badges*') ? 'text-gray-900 bg-gray-100' : 'text-gray-500 hover:text-gray-800 hover:bg-gray-100' }} transition duration-150 ease-in-out">
                    {{ __('Lencana') }}
                </a>
            @endif

            <a href="{{ url('/forums') }}"
                class="block px-4 py-2 text-base font-medium {{ request()->routeIs('forum.*') ? 'text-gray-900 bg-gray-100' : 'text-gray-500 hover:text-gray-800 hover:bg-gray-100' }} transition duration-150 ease-in-out">
                {{ __('Forum') }}
            </a>

            @if($currentUser && $currentUser->role === 'teacher')
            <div class="pt-2 pb-1 border-t border-gray-200">
                <div class="px-4 text-xs text-gray-500 uppercase font-semibold">
                    {{ __('AI') }}
                </div>
                <a href="{{ route('ai-generator.index') }}"
                    class="block px-4 py-2 text-base font-medium {{ request()->routeIs('ai-generator.index') ? 'text-gray-900 bg-gray-100' : 'text-gray-500 hover:text-gray-800 hover:bg-gray-100' }} transition duration-150 ease-in-out">
                    {{ __('Penjana AI') }}
                </a>
                <a href="{{ route('ai-generator.slides') }}"
                    class="block px-4 py-2 text-base font-medium {{ request()->routeIs('ai-generator.slides') ? 'text-gray-900 bg-gray-100' : 'text-gray-500 hover:text-gray-800 hover:bg-gray-100' }} transition duration-150 ease-in-out">
                    {{ __('Jana Slaid') }}
                </a>
                <a href="{{ route('ai-generator.slaid-dijana') }}"
                    class="block px-4 py-2 text-base font-medium {{ request()->routeIs('ai-generator.slaid-dijana') ? 'text-gray-900 bg-gray-100' : 'text-gray-500 hover:text-gray-800 hover:bg-gray-100' }} transition duration-150 ease-in-out">
                    {{ __('Slaid Dijana') }}
                </a>
                <a href="{{ route('ai-generator.quiz') }}"
                    class="block px-4 py-2 text-base font-medium {{ request()->routeIs('ai-generator.quiz') ? 'text-gray-900 bg-gray-100' : 'text-gray-500 hover:text-gray-800 hover:bg-gray-100' }} transition duration-150 ease-in-out">
                    {{ __('Jana Kuiz') }}
                </a>
            </div>
            @endif
        </div>

        <!-- Responsive Settings Options -->
        <div class="pt-4 pb-1 border-t border-gray-200">
            <div class="px-4 flex items-center gap-3">
                @if($currentUser->avatar_url)
                    <img src="{{ asset($currentUser->avatar_url) }}" alt="{{ $currentUser->full_name ?? 'User' }}"
                        class="h-12 w-12 rounded-full object-cover border-2 border-gray-200">
                @else
                    <div
                        class="h-12 w-12 rounded-full bg-gradient-to-br from-blue-500 to-blue-600 flex items-center justify-center text-white font-semibold text-lg border-2 border-gray-200">
                        {{ strtoupper(substr($currentUser->full_name ?? $currentUser->username ?? 'U', 0, 1)) }}
                    </div>
                @endif
                <div>
                    <div class="font-medium text-base text-gray-800">
                        {{ $currentUser->full_name ?? $currentUser->email ?? 'User' }}
                    </div>
                    <div class="font-medium text-sm text-gray-500">{{ $currentUser->email ?? '' }}</div>
                </div>
            </div>

            <div class="mt-3 space-y-1">
                <a href="{{ route('profile.show', $currentUser->id) }}"
                    class="block px-4 py-2 text-base font-medium text-gray-500 hover:text-gray-800 hover:bg-gray-100 transition duration-150 ease-in-out">
                    {{ __('Profil') }}
                </a>

                <a href="{{ route('settings.index') }}"
                    class="block px-4 py-2 text-base font-medium text-gray-500 hover:text-gray-800 hover:bg-gray-100 transition duration-150 ease-in-out">
                    {{ __('Tetapan') }}
                </a>

                <!-- Authentication -->
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <a href="{{ route('logout') }}" onclick="event.preventDefault(); this.closest('form').submit();"
                        class="block px-4 py-2 text-base font-medium text-gray-500 hover:text-gray-800 hover:bg-gray-100 transition duration-150 ease-in-out">
                        {{ __('Log Keluar') }}
                    </a>
                </form>
            </div>
        </div>
    </div>
</nav>

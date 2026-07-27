<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}"
      x-data="{ sidebarOpen: false, sidebarCollapsed: false }"
      x-init="
          darkMode.init();
          sidebarCollapsed = localStorage.getItem('sidebar-collapsed') === 'true';
          $watch('sidebarCollapsed', v => localStorage.setItem('sidebar-collapsed', v));
      "
      :class="{ 'dark': false }">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <meta name="application-name" content="{{ config('app.name') }}" />
    <meta name="description" content="Modern ERP System" />
    <meta name="theme-color" content="#3b82f6" />

    {{-- PWA --}}
    <link rel="manifest" href="/build/manifest.webmanifest" />
    <link rel="apple-touch-icon" href="/icons/icon-192x192.png" />
    <meta name="apple-mobile-web-app-capable" content="yes" />
    <meta name="apple-mobile-web-app-status-bar-style" content="default" />

    <title>@yield('title', config('app.name'))</title>

    <style>[x-cloak] { display: none !important; }</style>

    @filamentStyles
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="min-h-screen bg-gray-50 font-sans antialiased dark:bg-gray-950">

    {{-- Mobile overlay --}}
    <div x-show="sidebarOpen"
         x-cloak
         @click="sidebarOpen = false"
         class="fixed inset-0 z-30 bg-gray-900/60 backdrop-blur-sm lg:hidden"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"></div>

    <div class="flex h-screen overflow-hidden">

        {{-- Sidebar --}}
        <aside x-cloak
               :class="{
                   'translate-x-0': sidebarOpen,
                   '-translate-x-full': !sidebarOpen,
                   'w-64': !sidebarCollapsed,
                   'w-16': sidebarCollapsed,
               }"
               class="sidebar-transition fixed inset-y-0 left-0 z-40 flex flex-col border-r border-gray-200 bg-white shadow-xl dark:border-gray-800 dark:bg-gray-900 lg:static lg:translate-x-0">

            {{-- Logo / Brand --}}
            <div class="flex h-16 flex-shrink-0 items-center gap-3 border-b border-gray-100 px-4 dark:border-gray-800">
                <div class="flex h-9 w-9 flex-shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-blue-500 to-indigo-600 shadow-md">
                    <svg class="h-5 w-5 text-white" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 3v11.25A2.25 2.25 0 0 0 6 16.5h2.25M3.75 3h-1.5m1.5 0h16.5m0 0h1.5m-1.5 0v11.25A2.25 2.25 0 0 1 18 16.5h-2.25m-7.5 0h7.5m-7.5 0-1 3m8.5-3 1 3m0 0 .5 1.5m-.5-1.5h-9.5m0 0-.5 1.5"/>
                    </svg>
                </div>
                <span x-show="!sidebarCollapsed"
                      x-transition:enter="transition-opacity duration-200"
                      x-transition:enter-start="opacity-0"
                      x-transition:enter-end="opacity-100"
                      class="gradient-text text-lg font-bold truncate">
                    {{ config('app.name') }}
                </span>
            </div>

            {{-- Navigation --}}
            <nav class="flex-1 overflow-y-auto px-2 py-4">
                @yield('sidebar-nav')
            </nav>

            {{-- Collapse toggle (desktop) --}}
            <div class="hidden border-t border-gray-100 p-2 dark:border-gray-800 lg:block">
                <button @click="sidebarCollapsed = !sidebarCollapsed"
                        class="flex w-full items-center justify-center rounded-xl p-2 text-gray-400 transition hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-gray-800 dark:hover:text-gray-200">
                    <svg x-show="!sidebarCollapsed" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m18.75 4.5-7.5 7.5 7.5 7.5m-6-15L5.25 12l7.5 7.5"/>
                    </svg>
                    <svg x-show="sidebarCollapsed" x-cloak class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m5.25 4.5 7.5 7.5-7.5 7.5m6-15 7.5 7.5-7.5 7.5"/>
                    </svg>
                </button>
            </div>
        </aside>

        {{-- Main content area --}}
        <div class="flex flex-1 flex-col overflow-hidden">

            {{-- Top bar --}}
            <header class="flex h-16 flex-shrink-0 items-center gap-4 border-b border-gray-200 bg-white/80 px-4 backdrop-blur-md dark:border-gray-800 dark:bg-gray-900/80 sm:px-6">

                {{-- Mobile hamburger --}}
                <button @click="sidebarOpen = !sidebarOpen"
                        class="rounded-xl p-2 text-gray-400 hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-gray-800 dark:hover:text-gray-200 lg:hidden">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5"/>
                    </svg>
                </button>

                {{-- Global search --}}
                <div class="flex flex-1 items-center">
                    @livewire('global-search')
                </div>

                {{-- Right actions --}}
                <div class="flex items-center gap-2">
                    {{-- Dark mode toggle --}}
                    <button @click="darkMode.toggle()"
                            class="rounded-xl p-2 text-gray-400 hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-gray-800 dark:hover:text-gray-200">
                        <svg x-show="!document.documentElement.classList.contains('dark')"
                             class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21.752 15.002A9.72 9.72 0 0 1 18 15.75c-5.385 0-9.75-4.365-9.75-9.75 0-1.33.266-2.597.748-3.752A9.753 9.753 0 0 0 3 11.25C3 16.635 7.365 21 12.75 21a9.753 9.753 0 0 0 9.002-5.998Z"/>
                        </svg>
                        <svg x-show="document.documentElement.classList.contains('dark')" x-cloak
                             class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v2.25m6.364.386-1.591 1.591M21 12h-2.25m-.386 6.364-1.591-1.591M12 18.75V21m-4.773-4.227-1.591 1.591M5.25 12H3m4.227-4.773L5.636 5.636M15.75 12a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0Z"/>
                        </svg>
                    </button>

                    {{-- Notifications --}}
                    <button class="relative rounded-xl p-2 text-gray-400 hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-gray-800 dark:hover:text-gray-200">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0"/>
                        </svg>
                        <span class="absolute right-1.5 top-1.5 h-2 w-2 rounded-full bg-blue-500"></span>
                    </button>

                    {{-- User avatar --}}
                    @auth
                        <div class="relative" x-data="{ open: false }">
                            <button @click="open = !open" class="flex h-9 w-9 items-center justify-center overflow-hidden rounded-xl ring-2 ring-blue-500/20 transition hover:ring-blue-500/50">
                                <img src="{{ auth()->user()->avatar_url }}"
                                     alt="{{ auth()->user()->name }}"
                                     class="h-full w-full object-cover">
                            </button>
                            <div x-show="open" @click.away="open = false" x-cloak
                                 class="absolute right-0 mt-2 w-56 rounded-2xl border border-gray-100 bg-white py-1 shadow-xl dark:border-gray-800 dark:bg-gray-900">
                                <div class="border-b border-gray-100 px-4 py-3 dark:border-gray-800">
                                    <p class="text-sm font-medium text-gray-900 dark:text-white">{{ auth()->user()->name }}</p>
                                    <p class="text-xs text-gray-500">{{ auth()->user()->email }}</p>
                                </div>
                                <a href="/admin" class="flex items-center gap-2 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 dark:text-gray-300 dark:hover:bg-gray-800">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 3v11.25A2.25 2.25 0 0 0 6 16.5h2.25M3.75 3h-1.5m1.5 0h16.5m0 0h1.5m-1.5 0v11.25A2.25 2.25 0 0 1 18 16.5h-2.25m-7.5 0h7.5"/>
                                    </svg>
                                    Panel Admin
                                </a>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="flex w-full items-center gap-2 px-4 py-2 text-sm text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-900/20">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 9V5.25A2.25 2.25 0 0 1 10.5 3h6a2.25 2.25 0 0 1 2.25 2.25v13.5A2.25 2.25 0 0 1 16.5 21h-6a2.25 2.25 0 0 1-2.25-2.25V15m-3 0-3-3m0 0 3-3m-3 3H15"/>
                                        </svg>
                                        Keluar
                                    </button>
                                </form>
                            </div>
                        </div>
                    @endauth
                </div>
            </header>

            {{-- Page content --}}
            <main class="flex-1 overflow-y-auto p-4 pb-24 sm:p-6 lg:pb-6">
                {{ $slot ?? '' }}
                @yield('content')
            </main>

        </div>
    </div>

    {{-- Mobile Bottom Navigation --}}
    <nav class="bottom-nav fixed inset-x-0 bottom-0 z-30 border-t border-gray-200 bg-white/90 backdrop-blur-md dark:border-gray-800 dark:bg-gray-900/90 lg:hidden">
        <div class="grid grid-cols-5 gap-1 px-2 pt-2">
            <a href="/" class="flex flex-col items-center gap-0.5 rounded-xl p-2 text-blue-600 dark:text-blue-400">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 12 8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25"/>
                </svg>
                <span class="text-xs font-medium">Home</span>
            </a>
            <a href="/admin" class="flex flex-col items-center gap-0.5 rounded-xl p-2 text-gray-400 hover:text-blue-600 dark:hover:text-blue-400">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 3v11.25A2.25 2.25 0 0 0 6 16.5h2.25M3.75 3h-1.5m1.5 0h16.5"/>
                </svg>
                <span class="text-xs font-medium">Admin</span>
            </a>
            {{-- FAB placeholder --}}
            <div class="flex flex-col items-center">
                <div class="-mt-6 flex h-14 w-14 items-center justify-center rounded-full bg-gradient-to-br from-blue-500 to-indigo-600 shadow-lg ring-4 ring-white dark:ring-gray-950">
                    <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
                    </svg>
                </div>
                <span class="mt-1 text-xs font-medium text-gray-400">Baru</span>
            </div>
            <a href="/admin/users" class="flex flex-col items-center gap-0.5 rounded-xl p-2 text-gray-400 hover:text-blue-600 dark:hover:text-blue-400">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z"/>
                </svg>
                <span class="text-xs font-medium">Users</span>
            </a>
            <a href="/admin/profile" class="flex flex-col items-center gap-0.5 rounded-xl p-2 text-gray-400 hover:text-blue-600 dark:hover:text-blue-400">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z"/>
                </svg>
                <span class="text-xs font-medium">Profil</span>
            </a>
        </div>
    </nav>

    {{-- Toast notifications --}}
    @livewire('toast-notification')

    @filamentScripts
    @livewireScripts
</body>
</html>

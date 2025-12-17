<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>
            @php
                try {
                    $appName = \App\Models\SystemSetting::get('app_name') ?? config('app.name', 'Road Master');
                } catch (\Exception $e) {
                    $appName = config('app.name', 'Road Master');
                }
            @endphp
            {{ $appName }}
        </title>

        <!-- Favicon -->
        @php
            try {
                $faviconPath = \App\Models\SystemSetting::get('system_favicon');
                $faviconUrl = $faviconPath ? route('storage.serve', ['path' => $faviconPath]) : asset('favicon.ico');
                $faviconType = $faviconPath ? (pathinfo($faviconPath, PATHINFO_EXTENSION) === 'svg' ? 'image/svg+xml' : (pathinfo($faviconPath, PATHINFO_EXTENSION) === 'png' ? 'image/png' : 'image/x-icon')) : 'image/x-icon';
            } catch (\Exception $e) {
                $faviconUrl = asset('favicon.ico');
                $faviconType = 'image/x-icon';
            }
        @endphp
        <link rel="icon" type="{{ $faviconType }}" href="{{ $faviconUrl }}">

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-gray-900 antialiased">
        <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-gray-100 dark:bg-gray-900">
            <div class="w-full sm:max-w-md bg-white dark:bg-gray-800 shadow-lg overflow-hidden sm:rounded-lg">
                <!-- Card Header com Logo e Título -->
                <div class="px-6 py-8 text-center bg-gradient-to-br from-gray-50 to-white dark:from-gray-700 dark:to-gray-800 border-b border-gray-200 dark:border-gray-700">
                    <a href="/" class="block">
                        @php
                            try {
                                $logoPath = \App\Models\SystemSetting::get('system_logo');
                                $logoUrl = $logoPath ? route('storage.serve', ['path' => $logoPath]) : null;
                            } catch (\Exception $e) {
                                $logoUrl = null;
                            }
                        @endphp
                        @if($logoUrl)
                            <img src="{{ $logoUrl }}" alt="{{ config('app.name', 'Laravel') }}" class="w-44 h-44 mx-auto object-contain">
                        @else
                            <x-application-logo class="w-44 h-44 mx-auto fill-current text-gray-500 dark:text-gray-400" />
                        @endif
                    </a>
                    @php
                        try {
                            $appName = \App\Models\SystemSetting::get('app_name') ?? config('app.name', 'Road Master');
                        } catch (\Exception $e) {
                            $appName = config('app.name', 'Road Master');
                        }
                    @endphp
                    <h1 class="mt-4 text-2xl font-bold text-gray-900 dark:text-gray-100">{{ $appName }}</h1>
                </div>

                <!-- Card Body -->
                <div class="px-6 py-6">
                    {{ $slot }}
                </div>
            </div>
        </div>
    </body>
</html>

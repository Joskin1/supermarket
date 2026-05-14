<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'White-Mart' }}</title>
    <meta name="description" content="White-Mart at Iyana Era, Ijanikin, Lagos. Fresh groceries, household essentials, beverages & more.">
    <link rel="icon" href="/favicon.ico" sizes="any">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800,900" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="bg-white text-gray-800 antialiased" style="font-family:'Inter',ui-sans-serif,system-ui,sans-serif">
    {{ $slot }}
    @livewireScripts
</body>
</html>

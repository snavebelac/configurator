<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Configurator</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
<a class="block p-6 uppercase m-6 underline font-bold border-2 border-white hover:border-amber-500 rounded-full" href="/login">Login</a>

<p class="bg-gray-100 p-6 rounded-xl m-6">
    {{ $users_count }}
</p>
</body>
</html>

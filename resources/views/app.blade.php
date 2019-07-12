<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>

    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Laravel</title>
    <link rel="stylesheet" href="/css/app.css">

</head>

<body style="font-family: 'Open Sans', sans-serif;">
    <div id='app'>
        <router-view></router-view>
    </div>
    <script src='/js/app.js'></script>
</body>

</html>
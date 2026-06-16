<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'EPMS')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>body{background:linear-gradient(135deg,#1e3a5f,#2c5282);min-height:100vh;}</style>
</head>
<body class="d-flex align-items-center">
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="text-center text-white mb-4">
                <h1 class="fw-bold">EPMS</h1>
                <p class="mb-0">Event Planning Management System</p>
            </div>
            <div class="card shadow-lg border-0">
                <div class="card-body p-4">@yield('content')</div>
            </div>
        </div>
    </div>
</div>
</body>
</html>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <style>
        body {
            margin: 0;
            padding: 0;
            background-color: #ffffff;
            font-family: Arial, sans-serif;
            color: #333333;
        }
        .wrapper {
            padding: 32px;
        }
        .header {
            margin-bottom: 32px;
        }
        .content {
            font-size: 16px;
            line-height: 24px;
        }
        .footer {
            margin-top: 40px;
            padding-top: 16px;
            border-top: 1px solid #eeeeee;
            font-size: 12px;
            color: #999999;
        }
        hr {
            border: 0;
            border-top: 1px solid #eeeeee;
            margin: 24px 0;
            text-align: left;
            width: 100%;
        }
    </style>
</head>
<body>
<div class="wrapper">
    <div class="header">
        <img src="{{ asset('images/logo.png') }}" alt="Logo" width="40" height="40" style="display: block;">
    </div>

    <div class="content">
        @yield('content')
    </div>

    <div class="footer">
        {{ __('app.' . config('app.name') . '.name') }} © {{ date('Y') }}
    </div>
</div>
</body>
</html>

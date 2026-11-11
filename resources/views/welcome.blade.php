<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>سامانه مدیریت خدمات فنی</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-50">
    <div class="mx-auto flex min-h-screen max-w-lg flex-col justify-center px-4 py-12">
        <div class="ui-card text-center">
            <span class="mx-auto mb-4 flex h-12 w-12 items-center justify-center rounded-xl bg-teal-700 text-lg font-bold text-white">ف</span>
            <h1 class="text-xl font-semibold text-slate-900">سامانه مدیریت خدمات فنی</h1>
            <p class="mt-2 text-sm text-slate-500">مدیریت درخواست خدمت، ارجاع تکنسین، گزارش، فاکتور و پرداخت</p>
            <div class="mt-6 flex flex-col gap-3 sm:flex-row sm:justify-center">
                <x-button href="{{ route('login') }}">ورود کارکنان</x-button>
                <x-button variant="secondary" href="{{ route('customer.login') }}">ورود مشتری</x-button>
            </div>
        </div>
    </div>
</body>
</html>

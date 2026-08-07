<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Access Restricted | Our Envelopes</title>

    @vite(['resources/css/app.css', 'resources/js/app.ts'])
</head>

<body class="min-h-screen bg-[#f4f7f5]">
    <div class="flex min-h-screen items-center justify-center px-6">

        <div
            class="w-full max-w-lg rounded-3xl border border-[#dde4df]
                   bg-white p-8 text-center shadow-sm"
        >
            <div
                class="mx-auto mb-5 flex h-16 w-16 items-center justify-center
                       rounded-2xl bg-[#477b67] text-3xl text-white"
            >
                ✉
            </div>

            <h1 class="text-2xl font-bold tracking-tight text-[#18332b]">
                Oops — this page is for administrators
            </h1>

            <p class="mt-4 text-base leading-7 text-[#63736d]">
                You’re signed in, but this part of Our Envelopes is only
                available to an administrator.
            </p>

            <p class="mt-2 text-base leading-7 text-[#63736d]">
                You can return to your dashboard and continue managing
                your household budget.
            </p>

            <a
                href="{{ route('dashboard') }}"
                class="mt-7 inline-flex items-center justify-center rounded-xl
                       bg-[#477b67] px-6 py-3 font-medium text-white
                       transition hover:bg-[#3b6958]"
            >
                Return to Dashboard
            </a>

            <div class="mt-7 text-sm text-[#98a49f]">
                Error 403
            </div>
        </div>

    </div>
</body>
</html>

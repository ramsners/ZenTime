<!DOCTYPE html>
<html lang="en" class="antialiased h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ZenTime | Welcome</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Outfit', sans-serif; }
    </style>
</head>
<body class="h-full bg-yellow-50 text-emerald-950 flex">

    <!-- Left Side: Image / Holiday Vibe -->
    <div class="hidden lg:flex w-1/2 bg-yellow-100 flex-col justify-center items-center relative overflow-hidden">
        <!-- Floating decor circles -->
        <div class="absolute top-[-5%] left-[-5%] w-96 h-96 bg-lime-200 rounded-full mix-blend-multiply opacity-50 blur-3xl"></div>
        <div class="absolute bottom-[-5%] right-[-5%] w-96 h-96 bg-yellow-300 rounded-full mix-blend-multiply opacity-50 blur-3xl"></div>
        
        <img src="/img/lemongrass.png" alt="Relaxing Lemongrass" class="absolute inset-0 w-full h-full object-cover z-10" />
        
        <div class="absolute inset-0 bg-yellow-900/10 z-20"></div>
        
        <div class="relative z-30 p-12 text-center bg-white/40 backdrop-blur-md m-8 rounded-3xl border border-white/50 shadow-xl">
            <h1 class="text-4xl font-bold text-emerald-900 mb-4 tracking-tight">Time to unwind.</h1>
            <p class="text-emerald-800 text-lg">Leave the stress behind. Request your next vacation in seconds.</p>
        </div>
    </div>

    <!-- Right Side: Login Form -->
    <div class="w-full lg:w-1/2 flex items-center justify-center p-8 bg-yellow-50 relative">
        <div class="max-w-md w-full space-y-8 bg-white p-10 rounded-3xl shadow-xl shadow-lime-900/5 relative z-10">
            <div class="text-center">
                <div class="mx-auto w-16 h-16 bg-gradient-to-tr from-lime-400 to-yellow-300 rounded-2xl flex justify-center items-center mb-6 shadow-lg shadow-lime-400/30">
                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 15a4 4 0 004 4h9a5 5 0 10-.1-9.999 5.002 5.002 0 10-9.78 2.096A4.001 4.001 0 003 15z"></path></svg>
                </div>
                <h2 class="text-3xl font-bold text-emerald-900 tracking-tight">ZenTime</h2>
                <p class="mt-2 text-emerald-600">Welcome back. Please sign in to your workspace.</p>
            </div>

            <?php if (isset($_GET['error']) && $_GET['error'] === 'login_failed'): ?>
                <div class="bg-red-50 border border-red-200 text-red-600 text-sm px-4 py-3 rounded-xl text-center">
                    Invalid credentials. Please try again.
                </div>
            <?php endif; ?>

            <form class="mt-8 space-y-6" action="/?action=login" method="POST">
                <div class="space-y-4">
                    <div>
                        <label for="login" class="block text-sm font-medium text-emerald-800">Email or Employee. No.</label>
                        <input id="login" name="login" type="text" required class="appearance-none relative block w-full px-4 py-3 border border-yellow-200 bg-yellow-50/50 placeholder-emerald-300 text-emerald-900 rounded-xl focus:outline-none focus:ring-2 focus:ring-lime-400 focus:border-lime-400 focus:z-10 sm:text-sm transition-all" placeholder="admin">
                    </div>
                    <div>
                        <label for="password" class="block text-sm font-medium text-emerald-800">Password</label>
                        <input id="password" name="password" type="password" required class="appearance-none relative block w-full px-4 py-3 border border-yellow-200 bg-yellow-50/50 placeholder-emerald-300 text-emerald-900 rounded-xl focus:outline-none focus:ring-2 focus:ring-lime-400 focus:border-lime-400 focus:z-10 sm:text-sm transition-all" placeholder="••••••••">
                    </div>
                </div>

                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <input id="remember-me" name="remember-me" type="checkbox" class="h-4 w-4 text-lime-500 focus:ring-lime-400 border-yellow-300 rounded">
                        <label for="remember-me" class="ml-2 block text-sm text-emerald-700">Remember me</label>
                    </div>
                    <div class="text-sm">
                        <a href="#" class="font-medium text-lime-600 hover:text-lime-500">Forgot password?</a>
                    </div>
                </div>

                <div>
                    <button type="submit" class="group relative w-full flex justify-center py-3.5 px-4 border border-transparent text-sm font-semibold rounded-xl text-emerald-900 bg-lime-400 hover:bg-lime-500 hover:shadow-lg hover:shadow-lime-400/40 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-lime-500 transition-all">
                        Sign In Relaxed
                    </button>
                </div>
            </form>
            
            <div class="mt-6 text-center text-xs text-emerald-500">
                Hint: Login with "admin" and "admin" if you just started the app.
            </div>
        </div>
    </div>
</body>
</html>

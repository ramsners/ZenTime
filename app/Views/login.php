<?php
use App\Core\I18n;

$isReset = isset($_GET['action']) && $_GET['action'] === 'reset_password' && isset($_GET['token']);
$isForgot = isset($_GET['forgot']);
?>
<!DOCTYPE html>
<html lang="<?= $_SESSION['lang'] ?? 'en' ?>" class="antialiased h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ZenTime | <?= I18n::get('login.welcome') ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Outfit', sans-serif; }
    </style>
</head>
<body class="h-full bg-yellow-50 text-emerald-950 flex relative">
    <!-- Language Toggle -->
    <div class="absolute top-4 right-4 z-50 flex gap-2 bg-white/70 backdrop-blur px-3 py-1.5 rounded-full border border-yellow-200 shadow-sm">
        <a href="?lang=en" class="<?= ($_SESSION['lang'] ?? 'en') === 'en' ? 'font-bold text-lime-600' : 'text-emerald-600' ?>">EN</a>
        <span class="text-emerald-300">|</span>
        <a href="?lang=de" class="<?= ($_SESSION['lang'] ?? 'en') === 'de' ? 'font-bold text-lime-600' : 'text-emerald-600' ?>">DE</a>
    </div>

    <!-- Left Side: Image / Holiday Vibe -->
    <div class="hidden lg:flex w-1/2 bg-yellow-100 flex-col justify-center items-center relative overflow-hidden">
        <!-- Floating decor circles -->
        <div class="absolute top-[-5%] left-[-5%] w-96 h-96 bg-lime-200 rounded-full mix-blend-multiply opacity-50 blur-3xl"></div>
        <div class="absolute bottom-[-5%] right-[-5%] w-96 h-96 bg-yellow-300 rounded-full mix-blend-multiply opacity-50 blur-3xl"></div>
        
        <img src="/img/lemongrass.png" alt="Relaxing Lemongrass" class="absolute inset-0 w-full h-full object-cover z-10 opacity-80" />
        
        <div class="absolute inset-0 bg-yellow-900/10 z-20"></div>
        
        <div class="relative z-30 p-12 text-center bg-white/60 backdrop-blur-md m-8 rounded-3xl border border-white/50 shadow-xl">
            <h1 class="text-4xl font-bold text-emerald-900 mb-4 tracking-tight"><?= I18n::get('login.title') ?></h1>
            <p class="text-emerald-800 text-lg font-medium"><?= I18n::get('login.subtitle') ?></p>
        </div>
    </div>

    <!-- Right Side: Login Form -->
    <div class="w-full lg:w-1/2 flex items-center justify-center p-8 bg-yellow-50 relative">
        <div class="max-w-md w-full space-y-8 bg-white p-10 rounded-3xl shadow-xl shadow-lime-900/5 relative z-10 border border-yellow-100/50">
            <div class="text-center">
                <div class="mx-auto w-16 h-16 bg-gradient-to-tr from-lime-400 to-yellow-300 rounded-2xl flex justify-center items-center mb-6 shadow-lg shadow-lime-400/30">
                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 15a4 4 0 004 4h9a5 5 0 10-.1-9.999 5.002 5.002 0 10-9.78 2.096A4.001 4.001 0 003 15z"></path></svg>
                </div>
                <h2 class="text-3xl font-bold text-emerald-900 tracking-tight">ZenTime</h2>
                <?php if ($isReset): ?>
                    <p class="mt-2 text-emerald-600"><?= I18n::get('newpw.title') ?></p>
                <?php elseif ($isForgot): ?>
                    <p class="mt-2 text-emerald-600"><?= I18n::get('reset.title') ?></p>
                <?php else: ?>
                    <p class="mt-2 text-emerald-600"><?= I18n::get('login.welcome') ?></p>
                <?php endif; ?>
            </div>

            <?php if (isset($_GET['error']) && $_GET['error'] === 'login_failed'): ?>
                <div class="bg-red-50 border border-red-200 text-red-600 text-sm px-4 py-3 rounded-xl text-center font-medium">
                    <?= I18n::get('login.invalid_credentials') ?>
                </div>
            <?php endif; ?>
            
            <?php if (isset($_GET['error']) && $_GET['error'] === 'invalid_token'): ?>
                <div class="bg-red-50 border border-red-200 text-red-600 text-sm px-4 py-3 rounded-xl text-center font-medium">
                    <?= I18n::get('msg.invalid_token') ?>
                </div>
            <?php endif; ?>

            <?php if (isset($_GET['success'])): ?>
                <div class="bg-lime-50 border border-lime-200 text-lime-700 text-sm px-4 py-3 rounded-xl text-center font-medium">
                    <?php 
                        if ($_GET['success'] === 'password_reset_sent') echo I18n::get('msg.password_reset_sent');
                        elseif ($_GET['success'] === 'action_success') echo I18n::get('msg.action_success');
                    ?>
                </div>
            <?php endif; ?>

            <?php if ($isReset): ?>
                <!-- RESET PASSWORD FORM -->
                <form class="mt-8 space-y-6" action="/?action=do_reset_password" method="POST">
                    <input type="hidden" name="token" value="<?= htmlspecialchars($_GET['token']) ?>">
                    <div>
                        <label for="password" class="block text-sm font-semibold text-emerald-800 mb-1.5"><?= I18n::get('newpw.password') ?></label>
                        <div class="relative">
                            <input id="password" name="password" type="password" required class="appearance-none relative block w-full px-4 py-3 border border-yellow-200 bg-yellow-50/50 text-emerald-900 rounded-xl focus:outline-none focus:ring-2 focus:ring-lime-400 transition-all font-medium">
                            <button type="button" onclick="togglePw('password')" class="absolute inset-y-0 right-0 pr-3 flex items-center text-emerald-500 hover:text-lime-600">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                            </button>
                        </div>
                    </div>
                    <div>
                        <button type="submit" class="w-full flex justify-center py-3.5 px-4 border border-transparent text-sm font-bold rounded-xl text-emerald-900 bg-lime-400 hover:bg-lime-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-lime-500 transition-all shadow-lg shadow-lime-400/40">
                            <?= I18n::get('newpw.submit') ?>
                        </button>
                    </div>
                </form>
            <?php elseif ($isForgot): ?>
                <!-- FORGOT PASSWORD FORM -->
                <form class="mt-8 space-y-6" action="/?action=forgot_password" method="POST">
                    <div>
                        <p class="text-sm text-emerald-600 mb-4"><?= I18n::get('reset.info') ?></p>
                        <label for="email" class="block text-sm font-semibold text-emerald-800 mb-1.5"><?= I18n::get('reset.email') ?></label>
                        <input id="email" name="email" type="email" required class="appearance-none relative block w-full px-4 py-3 border border-yellow-200 bg-yellow-50/50 text-emerald-900 rounded-xl focus:outline-none focus:ring-2 focus:ring-lime-400 transition-all font-medium">
                    </div>
                    <div class="flex flex-col gap-3">
                        <button type="submit" class="w-full flex justify-center py-3.5 px-4 border border-transparent text-sm font-bold rounded-xl text-emerald-900 bg-lime-400 hover:bg-lime-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-lime-500 transition-all shadow-lg shadow-lime-400/40">
                            <?= I18n::get('reset.send') ?>
                        </button>
                        <a href="/" class="text-center text-sm font-medium text-emerald-600 hover:text-lime-600 transition-colors">
                            <?= I18n::get('reset.back') ?>
                        </a>
                    </div>
                </form>
            <?php else: ?>
                <!-- LOGIN FORM -->
                <form class="mt-8 space-y-6" action="/?action=login" method="POST">
                    <div class="space-y-4">
                        <div>
                            <label for="login" class="block text-sm font-semibold text-emerald-800 mb-1.5"><?= I18n::get('login.email_mnr') ?></label>
                            <input id="login" name="login" type="text" pattern="[a-zA-Z0-9@.\-_]+" required class="appearance-none relative block w-full px-4 py-3 border border-yellow-200 bg-yellow-50/50 text-emerald-900 rounded-xl focus:outline-none focus:ring-2 focus:ring-lime-400 transition-all font-medium">
                        </div>
                        <div>
                            <label for="password" class="block text-sm font-semibold text-emerald-800 mb-1.5"><?= I18n::get('login.password') ?></label>
                            <div class="relative">
                                <input id="password" name="password" type="password" required class="appearance-none relative block w-full px-4 py-3 border border-yellow-200 bg-yellow-50/50 text-emerald-900 rounded-xl focus:outline-none focus:ring-2 focus:ring-lime-400 transition-all font-medium">
                                <button type="button" onclick="togglePw('password')" class="absolute inset-y-0 right-0 pr-3 flex items-center text-emerald-500 hover:text-lime-600">
                                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                                </button>
                            </div>
                        </div>
                    </div>
    
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <input id="remember-me" name="remember-me" type="checkbox" class="h-4 w-4 text-lime-500 border-yellow-300 rounded focus:ring-lime-400">
                            <label for="remember-me" class="ml-2 block text-sm font-medium text-emerald-700"><?= I18n::get('login.remember_me') ?></label>
                        </div>
                        <div class="text-sm">
                            <a href="?forgot=1" class="font-bold text-lime-600 hover:text-lime-500"><?= I18n::get('login.forgot_password') ?></a>
                        </div>
                    </div>
    
                    <div>
                        <button type="submit" class="w-full flex justify-center py-3.5 px-4 border border-transparent text-sm font-bold rounded-xl text-emerald-900 bg-lime-400 hover:bg-lime-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-lime-500 transition-all shadow-lg shadow-lime-400/40">
                            <?= I18n::get('login.sign_in') ?>
                        </button>
                    </div>
                </form>
            <?php endif; ?>
        </div>
    </div>
    
    <script>
        function togglePw(id) {
            const input = document.getElementById(id);
            if (input.type === 'password') {
                input.type = 'text';
            } else {
                input.type = 'password';
            }
        }
    </script>
</body>
</html>

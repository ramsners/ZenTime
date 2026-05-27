<?php
use App\Core\I18n;
if (!isset($currentRole)) exit;
?>
<!DOCTYPE html>
<html lang="<?= $_SESSION['lang'] ?? 'en' ?>" class="antialiased">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EasyTime | Dashboard</title>
    <link rel="icon" type="image/svg+xml" href="/assets/icons/urlaubsplaner_icon.svg">
    <script>
        window.easytimeTailwindConfig = {
            theme: {
                extend: {
                    colors: {
                        emerald: {
                            50: '#fffdf2',
                            100: '#fff7cc',
                            200: '#fff0a3',
                            300: '#ffe866',
                            400: '#FFD600',
                            500: '#E8007D',
                            600: '#4a4a4a',
                            700: '#2d2d2d',
                            800: '#1f1f1f',
                            900: '#1a1a1a',
                            950: '#111111'
                        },
                        lime: {
                            50: '#fff0f7',
                            100: '#ffd6eb',
                            200: '#ffadd8',
                            300: '#ff73bd',
                            400: '#E8007D',
                            500: '#c8006c',
                            600: '#a60059',
                            700: '#7d0044',
                            800: '#56002f',
                            900: '#33001c'
                        },
                        yellow: {
                            50: '#fffdf2',
                            100: '#fff7cc',
                            200: '#fff0a3',
                            300: '#ffe866',
                            400: '#FFD600',
                            500: '#e6c100',
                            600: '#b89600',
                            700: '#806900',
                            800: '#4d3f00',
                            900: '#1a1a1a'
                        },
                        green: {
                            50:  '#f0fdf4',
                            100: '#dcfce7',
                            200: '#bbf7d0',
                            500: '#22c55e',
                            600: '#16a34a',
                            700: '#15803d',
                            800: '#166534'
                        },
                        red: {
                            50:  '#fef2f2',
                            100: '#fee2e2',
                            200: '#fecaca',
                            500: '#ef4444',
                            600: '#dc2626',
                            700: '#b91c1c',
                            800: '#991b1b'
                        },
                        orange: {
                            50:  '#fff7ed',
                            100: '#ffedd5',
                            200: '#fed7aa',
                            300: '#fdba74',
                            400: '#fb923c',
                            500: '#f97316',
                            600: '#ea580c',
                            700: '#c2410c',
                            800: '#9a3412'
                        },
                        blue: {
                            50:  '#eff6ff',
                            100: '#dbeafe',
                            200: '#bfdbfe',
                            500: '#3b82f6',
                            600: '#2563eb',
                            700: '#1d4ed8'
                        },
                        pink: {
                            50: '#fff0f7',
                            100: '#ffd6eb',
                            200: '#ffadd8',
                            300: '#ff73bd',
                            400: '#f52b95',
                            500: '#E8007D',
                            600: '#c8006c'
                        }
                    }
                }
            }
        };
    </script>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        window.tailwind.config = window.easytimeTailwindConfig;
    </script>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.js'></script>
    <style>
        body { font-family: 'Outfit', sans-serif; background-color: #fffdf2; color: #1a1a1a; }
        .glass { background: rgba(255, 255, 255, 0.72); backdrop-filter: blur(12px); border: 1px solid rgba(232, 0, 125, 0.26); }
        .fc-toolbar-title { font-weight: 700 !important; color: #1a1a1a; font-family: 'Outfit', sans-serif;}
        .fc-button-primary { background-color: #FFD600 !important; border-color: #1a1a1a !important; color: #1a1a1a !important; font-weight: bold !important; text-transform: capitalize; }
        .fc-button-primary:hover { background-color: #E8007D !important; color: #ffffff !important; }
        .fc-day-today { background-color: transparent !important; }
        .fc-day-today .fc-daygrid-day-number {
            background-color: #FFD600;
            border: 1px solid #E8007D;
            border-radius: 9999px;
            width: 1.75rem;
            height: 1.75rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
        }
        .fc-col-header-cell-cushion { color: #1a1a1a !important; }
        .fc-daygrid-day-number { color: #1a1a1a !important; }
        .fc-event { border: none !important; border-radius: 4px; padding: 2px 4px; font-weight: 600; font-size: 0.75rem; text-overflow: ellipsis; white-space: nowrap; overflow: hidden;}
        /* KW-Anzeige */
        .fc-daygrid-week-number { font-size: 0.6rem !important; font-weight: 700 !important; color: #E8007D !important; background: rgba(232,0,125,0.08); border-radius: 4px; padding: 1px 5px !important; min-width: 2.2rem; text-align: center; }
        /* Multi-Auswahl Highlight */
        .fc-day-multiselected { background-color: rgba(255,214,0,0.3) !important; }
        .fc-day-multiselected .fc-daygrid-day-number { font-weight: 700 !important; color: #E8007D !important; }
    </style>
</head>
<body class="min-h-screen flex flex-col relative overflow-x-hidden">
    <!-- Sunny accents -->
    <div class="absolute top-[-10%] right-[-10%] w-96 h-96 bg-yellow-300 rounded-full mix-blend-multiply opacity-20 blur-3xl z-[-1]"></div>
    <div class="absolute bottom-[-10%] left-[-10%] w-96 h-96 bg-lime-300 rounded-full mix-blend-multiply opacity-20 blur-3xl z-[-1]"></div>

    <!-- Force Password Reset Modal -->
    <?php if (isset($requirePasswordChange) && $requirePasswordChange): ?>
    <div class="fixed inset-0 z-[100] flex items-center justify-center bg-emerald-950/40 backdrop-blur-md p-4">
        <div class="max-w-md w-full bg-white p-10 rounded-3xl shadow-2xl border border-yellow-200">
            <h2 class="text-2xl font-bold text-emerald-900 mb-2"><?= I18n::get('force.title') ?></h2>
            <p class="text-emerald-700 mb-6 text-sm"><?= I18n::get('force.info') ?></p>
            <form action="/?action=change_password" method="POST" class="space-y-5">
                <div>
                    <label class="block text-sm font-semibold text-emerald-800 mb-1.5"><?= I18n::get('newpw.password') ?></label>
                    <div class="relative">
                        <input type="password" name="password" id="force_pw" required class="appearance-none block w-full px-4 py-3 text-emerald-900 border border-yellow-200 bg-yellow-50/50 rounded-xl focus:outline-none focus:ring-2 focus:ring-lime-400 font-medium transition-all">
                        <button type="button" onclick="togglePw('force_pw')" class="absolute inset-y-0 right-0 pr-3 flex items-center text-emerald-500 hover:text-lime-600">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                        </button>
                    </div>
                </div>
                <button type="submit" class="w-full bg-lime-400 hover:bg-lime-500 text-emerald-900 font-bold py-3.5 rounded-xl transition-all shadow-lg shadow-lime-400/40">
                    <?= I18n::get('force.submit') ?>
                </button>
            </form>
        </div>
    </div>
    <?php endif; ?>

    <div class="relative z-10 flex min-h-screen w-full flex-col lg:flex-row" x-data="{ tab: '<?= (isset($_GET['tab']) && $_GET['tab'] === 'team') ? 'team' : 'operations' ?>' }">
        <aside class="w-full border-b border-lime-200/60 bg-white/80 p-4 shadow-sm backdrop-blur-lg lg:sticky lg:top-0 lg:h-screen lg:w-72 lg:flex-none lg:border-b-0 lg:border-r lg:p-6">
            <div class="flex h-full flex-col gap-5">
                <div class="flex items-center gap-3">
                    <img src="/assets/icons/urlaubsplaner_icon.svg" alt="Urlaubsplaner" class="h-12 w-12 rounded-xl shadow-md shadow-lime-400/20">
                    <div>
                        <span class="block text-2xl font-bold tracking-tight text-emerald-900">Easy<span class="text-lime-600">Time</span></span>
                        <span class="text-xs font-bold uppercase tracking-wider text-emerald-600/70"><?= in_array($currentRole, ['CEO', 'Admin'], true) ? 'Admin Dashboard' : 'Dashboard' ?></span>
                    </div>
                </div>

                <nav class="flex flex-wrap gap-2 lg:flex-col" aria-label="Dashboard Navigation">
                    <?php if (in_array($currentRole, ['CEO', 'Admin'], true)): ?>
                        <button id="ceo-requests-tab-btn" type="button" @click="tab = 'operations'; setTimeout(() => window.dispatchEvent(new Event('resize')), 100);" :class="tab === 'operations' ? 'bg-lime-400 text-emerald-950 shadow-sm' : 'bg-yellow-50 text-emerald-700 hover:bg-white'" class="flex items-center justify-between rounded-xl border border-lime-100 px-4 py-3 text-sm font-bold transition-all lg:w-full">
                            <span>Kalender & Genehmigungen</span>
                            <span class="text-lime-700">›</span>
                        </button>
                        <button type="button" @click="tab = 'team'" :class="tab === 'team' ? 'bg-lime-400 text-emerald-950 shadow-sm' : 'bg-yellow-50 text-emerald-700 hover:bg-white'" class="flex items-center justify-between rounded-xl border border-lime-100 px-4 py-3 text-sm font-bold transition-all lg:w-full">
                            <span>Team & Stammdaten</span>
                            <span class="text-lime-700">›</span>
                        </button>
                    <?php else: ?>
                        <a href="#employee-plan" class="rounded-xl border border-lime-100 bg-yellow-50 px-4 py-3 text-sm font-bold text-emerald-700 transition-all hover:bg-white lg:w-full">Urlaub planen</a>
                        <a href="#employee-overview" class="rounded-xl border border-lime-100 bg-yellow-50 px-4 py-3 text-sm font-bold text-emerald-700 transition-all hover:bg-white lg:w-full">Meine Anträge</a>
                        <a href="#employee-comments" class="rounded-xl border border-lime-100 bg-yellow-50 px-4 py-3 text-sm font-bold text-emerald-700 transition-all hover:bg-white lg:w-full">Kommentarverlauf</a>
                    <?php endif; ?>
                </nav>

                <div class="mt-auto grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-1">
                    <div class="flex items-center justify-between gap-2 rounded-xl border border-yellow-200 bg-white px-3 py-2 text-sm">
                        <span class="font-semibold text-emerald-700">Inbox</span>
                        <span class="inline-flex items-center justify-center min-w-6 h-6 px-2 rounded-full bg-lime-400 text-emerald-900 font-bold text-xs"><?= (int)($notificationUnreadCount ?? 0) ?></span>
                    </div>
                    <div class="flex items-center justify-center gap-2 rounded-xl border border-yellow-200 bg-yellow-50 px-3 py-2 text-sm">
                        <a href="?lang=en" class="<?= ($_SESSION['lang'] ?? 'en') === 'en' ? 'font-bold text-lime-600' : 'text-emerald-600' ?>">EN</a>
                        <span class="text-emerald-300">|</span>
                        <a href="?lang=de" class="<?= ($_SESSION['lang'] ?? 'en') === 'de' ? 'font-bold text-lime-600' : 'text-emerald-600' ?>">DE</a>
                    </div>
                    <div class="flex flex-col gap-3 rounded-2xl border border-yellow-200 bg-yellow-50/80 p-3 shadow-sm sm:col-span-2 lg:col-span-1">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-full bg-lime-400 flex items-center justify-center text-emerald-900 font-bold text-sm uppercase shadow-inner">
                                <?= substr(htmlspecialchars($currentUser['firstname']), 0, 1) ?>
                            </div>
                            <div class="min-w-0">
                                <span class="block text-[10px] uppercase tracking-wider font-bold text-emerald-600/70 leading-none"><?= I18n::get('nav.hi') ?></span>
                                <span class="block truncate text-sm font-bold text-emerald-900 mt-0.5"><?= htmlspecialchars($currentUser['firstname'] . ' ' . $currentUser['lastname']) ?></span>
                                <span class="block truncate text-xs text-emerald-700"><?= htmlspecialchars($currentUser['email']) ?></span>
                            </div>
                        </div>
                        <a href="/?action=logout" class="flex items-center justify-center gap-1.5 rounded-xl border border-red-200 bg-white px-3 py-2 text-sm font-bold text-red-600 transition-colors hover:bg-red-50">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                            <span><?= I18n::get('nav.logout') ?></span>
                        </a>
                    </div>
                </div>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="relative z-10 flex-1 w-full p-4 sm:p-6 lg:p-8 flex flex-col gap-8 overflow-x-hidden">
        
        <?php if (isset($_GET['success'])): ?>
            <div class="bg-lime-50 border border-lime-200 text-lime-800 px-4 py-3 rounded-xl text-sm flex items-center shadow-sm mb-4">
                <svg class="w-5 h-5 mr-3 text-lime-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                <?php 
                    if ($_GET['success'] === 'employee_created') echo I18n::get('msg.employee_created');
                    else echo I18n::get('msg.action_success');
                ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_GET['error'])): ?>
            <div class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-xl text-sm flex items-center shadow-sm mb-4">
                <svg class="w-5 h-5 mr-3 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                <?php 
                    if ($_GET['error'] === 'invalid_mnr') echo (($_SESSION['lang'] ?? 'en') === 'de' ? 'Personalnummer (MNR) darf nur aus Zahlen bestehen!' : 'Staff number (MNR) must only contain digits!');
                    elseif ($_GET['error'] === 'blocked_period') echo (($_SESSION['lang'] ?? 'en') === 'de' ? 'In diesem Zeitraum ist keine Urlaubsbuchung erlaubt.' : 'Vacation booking is blocked for this selected period.');
                    elseif ($_GET['error'] === 'request_conflict') echo (($_SESSION['lang'] ?? 'en') === 'de' ? 'Dieser Zeitraum überschneidet sich mit einem bestehenden Urlaubsantrag.' : 'This range overlaps with an existing vacation request.');
                    elseif ($_GET['error'] === 'blocked_exists') echo (($_SESSION['lang'] ?? 'en') === 'de' ? 'Dieser Sperrbereich existiert bereits oder überschneidet einen bestehenden.' : 'This blocked period already exists or overlaps an existing blocked period.');
                    elseif ($_GET['error'] === 'past_date') echo (($_SESSION['lang'] ?? 'en') === 'de' ? 'Urlaub kann nicht in der Vergangenheit beantragt werden.' : 'Vacation cannot be requested for past dates.');
                    elseif ($_GET['error'] === 'coverage_conflict') echo (($_SESSION['lang'] ?? 'en') === 'de' ? 'Genehmigung nicht möglich: Mindestbesetzung würde unterschritten.' : 'Approval failed: minimum staffing would be violated.');
                    elseif ($_GET['error'] === 'fenstertage_exceeded') echo (($_SESSION['lang'] ?? 'en') === 'de' ? 'Dein Urlaubsantrag enthält zu viele Fenstertage (Brückentage). Bitte teile den Zeitraum auf.' : 'Your request contains too many window days (bridge days). Please split the period.');
                    elseif ($_GET['error'] === 'self_delete_forbidden') echo (($_SESSION['lang'] ?? 'en') === 'de' ? 'Du kannst deinen eigenen Admin-Account nicht löschen.' : 'You cannot delete your own admin account.');
                    else echo "An error occurred.";
                ?>
            </div>
        <?php endif; ?>

        <?php if ($currentRole === 'Employee'): ?>
            <!-- EMPLOYEE VIEW -->
            <div id="employee-plan" class="grid grid-cols-1 lg:grid-cols-3 gap-8 scroll-mt-4">
                <div class="lg:col-span-1">
                    <div class="bg-white rounded-3xl p-8 shadow-xl shadow-lime-900/5 relative overflow-hidden border border-lime-100">
                        <div class="absolute top-0 right-0 w-32 h-32 bg-yellow-100 rounded-bl-full -z-10 mix-blend-multiply opacity-50"></div>
                        <h2 class="text-2xl font-bold text-emerald-900 mb-6 tracking-tight"><?= I18n::get('emp.plan') ?></h2>
                        <p class="text-sm text-emerald-700 mb-5">
                            <?= (($_SESSION['lang'] ?? 'en') === 'de') ? 'Wähle direkt im Kalender einen Zeitraum oder setze die Daten manuell.' : 'Select a range directly in the calendar or set the dates manually.' ?>
                        </p>
                        <div class="grid grid-cols-2 gap-2 mb-5">
                            <div class="bg-lime-50 border border-lime-200 rounded-xl p-3">
                                <div class="text-[10px] uppercase font-bold text-emerald-700">Gesamt</div>
                                <div class="font-bold text-xl text-emerald-900"><?= (int)($userVacationStats['entitlement'] ?? 0) ?></div>
                            </div>
                            <div class="bg-yellow-50 border border-yellow-200 rounded-xl p-3">
                                <div class="text-[10px] uppercase font-bold text-emerald-700">Geplant</div>
                                <div class="font-bold text-xl text-emerald-900"><?= (int)($userVacationStats['planned'] ?? 0) ?></div>
                            </div>
                            <div class="bg-white border border-emerald-200 rounded-xl p-3">
                                <div class="text-[10px] uppercase font-bold text-emerald-700">Genommen</div>
                                <div class="font-bold text-xl text-emerald-900"><?= (int)($userVacationStats['approved'] ?? 0) ?></div>
                            </div>
                            <div class="bg-emerald-50 border border-emerald-200 rounded-xl p-3">
                                <div class="text-[10px] uppercase font-bold text-emerald-700">Rest</div>
                                <div class="font-bold text-xl text-emerald-900"><?= (int)($userVacationStats['remaining'] ?? 0) ?></div>
                            </div>
                        </div>
                        <form id="employee-request-form" action="/?action=create_request" method="POST" x-data="vacationForm()" class="space-y-5">
                            <div>
                                <label class="block text-sm font-semibold text-emerald-800 mb-1.5"><?= I18n::get('emp.start_date') ?></label>
                                <input id="employee-start-date" type="date" name="start_date" x-model="start" @change="calculateDays" min="<?= date('Y-m-d') ?>" required class="w-full bg-yellow-50 border border-yellow-200 rounded-xl px-4 py-3 text-emerald-900 focus:ring-2 focus:ring-lime-400 outline-none transition-all">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-emerald-800 mb-1.5"><?= I18n::get('emp.end_date') ?></label>
                                <input id="employee-end-date" type="date" name="end_date" x-model="end" @change="calculateDays" min="<?= date('Y-m-d') ?>" required class="w-full bg-yellow-50 border border-yellow-200 rounded-xl px-4 py-3 text-emerald-900 focus:ring-2 focus:ring-lime-400 outline-none transition-all">
                            </div>
                            <div class="bg-lime-50 border border-lime-200 rounded-xl p-4 flex justify-between items-center shadow-inner">
                                <span class="font-medium text-emerald-700"><?= I18n::get('emp.days_deduct') ?></span>
                                <span class="text-3xl font-bold text-emerald-900" x-text="netDays">0</span>
                                <input type="hidden" name="net_days" x-model="netDays">
                            </div>
                            <button type="submit" class="w-full bg-lime-400 hover:bg-lime-500 text-emerald-900 font-bold py-3.5 px-4 rounded-xl shadow-lg shadow-lime-400/30 transition-all outline-none disabled:opacity-50 disabled:cursor-not-allowed" :disabled="netDays <= 0 || !start || !end">
                                <?= I18n::get('emp.send_request') ?>
                            </button>
                        </form>
                    </div>
                </div>

                <div class="lg:col-span-2 bg-white p-8 rounded-3xl shadow-xl border border-lime-100">
                    <div class="flex flex-wrap justify-between gap-3 items-center mb-6">
                        <h2 class="text-2xl font-bold text-emerald-900 tracking-tight"><?= I18n::get('ceo.calendar') ?></h2>
                        <div class="flex flex-wrap gap-2">
                            <button type="button" id="emp-multi-toggle" onclick="toggleEmpMultiMode()"
                                class="px-3 py-2 rounded-lg text-sm font-semibold border border-yellow-200 text-emerald-700 hover:bg-yellow-50 transition-colors">
                                ☰ Multi-Auswahl
                            </button>
                            <button type="button" onclick="openExportModal(false)" class="px-3 py-2 rounded-lg text-sm font-semibold border border-lime-200 text-emerald-700 hover:bg-lime-50">ICS Export</button>
                        </div>
                    </div>
                    <div id="employee-calendar"></div>

                    <!-- Multi-Auswahl Panel -->
                    <div id="emp-multi-panel" class="hidden mt-5 bg-yellow-50 border border-yellow-200 rounded-2xl p-5">
                        <div class="flex items-center justify-between mb-3">
                            <span id="emp-multi-count" class="font-bold text-emerald-900 text-sm"></span>
                            <button type="button" onclick="clearEmpMultiSelect()"
                                class="text-xs font-bold text-red-500 hover:text-red-700 transition-colors">Alle löschen ✕</button>
                        </div>
                        <div id="emp-multi-list" class="flex flex-wrap gap-2 mb-4 text-xs"></div>
                        <form id="emp-multi-form" action="/?action=create_multi_request" method="POST">
                            <input type="hidden" id="multi-dates-input" name="multi_dates" value="">
                            <button type="submit"
                                class="w-full bg-lime-400 hover:bg-lime-500 text-emerald-900 font-bold py-3 rounded-xl shadow-md shadow-lime-400/30 transition-all">
                                Ausgewählte Tage beantragen
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <div id="employee-overview" class="space-y-6 mt-8">
                <h2 class="text-2xl font-bold text-emerald-900 tracking-tight"><?= I18n::get('emp.timeline') ?></h2>
                <div class="bg-white rounded-3xl overflow-hidden shadow-xl shadow-lime-900/5 border border-lime-100">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse min-w-[600px]">
                            <thead>
                                <tr class="bg-lime-50 border-b border-lime-100 text-sm uppercase text-emerald-700 tracking-wider font-semibold">
                                    <th class="p-5"><?= I18n::get('emp.period') ?></th>
                                    <th class="p-5"><?= I18n::get('emp.days') ?></th>
                                    <th class="p-5"><?= I18n::get('emp.status') ?></th>
                                    <th class="p-5 text-right"><?= I18n::get('ceo.actions') ?></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-lime-100 text-emerald-800 font-medium text-sm">
                                <?php foreach ($requests as $req): ?>
                                    <tr id="request-row-<?= $req['id'] ?>" data-request-id="<?= $req['id'] ?>" class="hover:bg-yellow-50/50 transition-colors">
                                        <td class="p-5">
                                            <?= date('d.m.Y', strtotime($req['start_date'])) ?> <span class="text-lime-500 mx-2">→</span> <?= date('d.m.Y', strtotime($req['end_date'])) ?>
                                        </td>
                                        <td class="p-5"><?= $req['net_days'] ?> <?= I18n::get('emp.limit') ?></td>
                                        <td class="p-5">
                                            <?php if ($req['status'] === 'approved'): ?>
                                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-green-100 text-green-700"><?= I18n::get('emp.status_approved') ?></span>
                                            <?php elseif ($req['status'] === 'rejected'): ?>
                                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-red-100 text-red-700"><?= I18n::get('emp.status_rejected') ?></span>
                                            <?php elseif ($req['status'] === 'storno_requested'): ?>
                                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-orange-100 text-orange-700"><?= I18n::get('emp.status_storno_requested') ?></span>
                                            <?php elseif ($req['status'] === 'cancelled'): ?>
                                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-gray-100 text-gray-600"><?= I18n::get('emp.status_cancelled') ?></span>
                                            <?php else: ?>
                                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-yellow-100 text-yellow-700"><?= I18n::get('emp.status_pending') ?></span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="p-5 flex justify-end gap-2">
                                            <?php if ($req['status'] === 'pending'): ?>
                                                <form method="POST" action="/?action=withdraw_request">
                                                    <input type="hidden" name="request_id" value="<?= $req['id'] ?>">
                                                    <button type="submit" class="text-red-500 hover:text-white hover:bg-red-500 border border-red-200 px-3 py-1 rounded-lg transition-colors text-xs font-bold">
                                                        <?= I18n::get('emp.retract') ?>
                                                    </button>
                                                </form>
                                            <?php elseif ($req['status'] === 'approved'): ?>
                                                <form method="POST" action="/?action=request_storno">
                                                    <input type="hidden" name="request_id" value="<?= $req['id'] ?>">
                                                    <button type="submit" class="text-orange-500 hover:text-white hover:bg-orange-500 border border-orange-200 px-3 py-1 rounded-lg transition-colors text-xs font-bold">
                                                        <?= I18n::get('emp.storno') ?>
                                                    </button>
                                                </form>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                                <?php if (empty($requests)): ?>
                                    <tr><td colspan="4" class="p-8 text-center text-emerald-600/60 font-medium"><?= I18n::get('emp.empty') ?></td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div id="employee-comments" class="bg-white rounded-3xl p-6 shadow-xl shadow-lime-900/5 border border-lime-100 scroll-mt-4">
                    <h3 class="text-xl font-bold text-emerald-900 mb-4">Kommentarverlauf</h3>
                    <div class="space-y-4">
                        <?php foreach ($requests as $req): ?>
                            <div class="border border-yellow-100 rounded-xl p-4">
                                <div class="text-sm font-bold text-emerald-900 mb-2">
                                    Antrag #<?= (int)$req['id'] ?> | <?= htmlspecialchars($req['start_date']) ?> - <?= htmlspecialchars($req['end_date']) ?>
                                </div>
                                <div class="space-y-2 mb-3">
                                    <?php foreach (($requestCommentsById[$req['id']] ?? []) as $c): ?>
                                        <div class="text-xs bg-yellow-50 border border-yellow-100 rounded-lg p-2">
                                            <span class="font-bold"><?= htmlspecialchars($c['firstname'] . ' ' . $c['lastname']) ?>:</span>
                                            <?= htmlspecialchars($c['comment']) ?>
                                        </div>
                                    <?php endforeach; ?>
                                    <?php if (empty($requestCommentsById[$req['id']] ?? [])): ?>
                                        <div class="text-xs text-emerald-600">Noch keine Kommentare.</div>
                                    <?php endif; ?>
                                </div>
                                <form method="POST" action="/?action=add_request_comment" class="flex gap-2">
                                    <input type="hidden" name="request_id" value="<?= $req['id'] ?>">
                                    <input type="text" name="comment" required class="flex-1 bg-white border border-yellow-200 rounded-lg px-3 py-2 text-xs text-emerald-900 outline-none" placeholder="Kommentar hinzufügen...">
                                    <button type="submit" class="bg-lime-400 text-emerald-900 px-3 py-2 rounded-lg text-xs font-bold">Senden</button>
                                </form>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-3xl p-6 shadow-xl shadow-lime-900/5 border border-lime-100">
                <h3 class="text-xl font-bold text-emerald-900 mb-4">Benachrichtigungen</h3>
                <div class="space-y-2 max-h-64 overflow-auto pr-1">
                    <?php foreach (($notificationList ?? []) as $note): ?>
                        <div class="p-3 rounded-xl border <?= (int)$note['is_read'] === 1 ? 'border-yellow-100 bg-white' : 'border-lime-200 bg-lime-50' ?>">
                            <div class="font-semibold text-sm text-emerald-900"><?= htmlspecialchars($note['title']) ?></div>
                            <div class="text-xs text-emerald-700"><?= htmlspecialchars($note['message']) ?></div>
                            <div class="text-[10px] text-emerald-500 mt-1"><?= htmlspecialchars($note['created_at']) ?></div>
                        </div>
                    <?php endforeach; ?>
                    <?php if (empty($notificationList)): ?>
                        <div class="text-sm text-emerald-600">Keine Benachrichtigungen.</div>
                    <?php endif; ?>
                </div>
            </div>

        <?php elseif (in_array($currentRole, ['CEO', 'Admin'], true)): ?>
            <!-- ADMIN VIEW -->
            <div>
                <!-- OPERATIONS TAB -->
                <div x-show="tab === 'operations'" class="space-y-8">
                    <div class="grid grid-cols-1 xl:grid-cols-3 gap-8">
                        <div class="xl:col-span-2 bg-white p-6 rounded-3xl shadow-xl border border-lime-100">
                            <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
                                <h2 class="text-2xl font-bold text-emerald-900">Kalender + Requests</h2>
                                <button type="button" onclick="openExportModal(true)" class="px-3 py-2 rounded-lg text-sm font-semibold border border-lime-200 text-emerald-700 hover:bg-lime-50">ICS Export</button>
                            </div>
                            <p class="text-sm text-emerald-700 mb-4">Im Kalender kannst du gesperrte Zeitraeume markieren. Diese Tage sind fuer Mitarbeiter nicht buchbar.</p>
                            <div id="ceo-calendar"></div>
                        </div>
                        <div class="space-y-4 calendar-side-panel">
                            <div id="calendar-info-panel" class="bg-white p-6 rounded-3xl shadow-xl border border-lime-100">
                                <h3 class="text-xl font-bold text-emerald-900 mb-4">Kalender Infos</h3>
                            <div id="calendar-info-content" class="hidden">
                            <div class="grid grid-cols-3 gap-2 mb-4">
                                <div class="rounded-xl border border-lime-200 bg-lime-50 p-2">
                                    <div class="text-[10px] uppercase text-emerald-700 font-bold">Mitarbeiter</div>
                                    <div class="text-lg font-bold text-emerald-900"><?= (int)($capacitySummary['employees_total'] ?? 0) ?></div>
                                </div>
                                <div class="rounded-xl border border-yellow-200 bg-yellow-50 p-2">
                                    <div class="text-[10px] uppercase text-emerald-700 font-bold">Abwesend</div>
                                    <div class="text-lg font-bold text-emerald-900"><?= (int)($capacitySummary['absent_approved'] ?? 0) ?></div>
                                </div>
                                <div class="rounded-xl border border-emerald-200 bg-white p-2">
                                    <div class="text-[10px] uppercase text-emerald-700 font-bold">Verfuegbar</div>
                                    <div class="text-lg font-bold text-emerald-900"><?= (int)($capacitySummary['available'] ?? 0) ?></div>
                                </div>
                            </div>
                            <div id="calendar-info-meta" class="text-xs text-emerald-700 bg-yellow-50 border border-yellow-200 rounded-xl p-3 mb-4 hidden"></div>
                            <div class="space-y-2 mb-4">
                                <form method="POST" action="/?action=update_min_staff" class="flex items-end gap-2">
                                    <div class="flex-1">
                                        <label class="block text-xs font-bold text-emerald-700 mb-1">Mindestbesetzung (Mitarbeiter/Tag)</label>
                                        <input type="number" min="0" name="min_staff_available" value="<?= (int)($minStaffAvailable ?? 1) ?>" class="w-full bg-yellow-50 border border-yellow-200 rounded-xl px-3 py-2 text-emerald-900 outline-none">
                                    </div>
                                    <button type="submit" class="bg-lime-400 hover:bg-lime-500 text-emerald-900 font-bold px-3 py-2 rounded-xl">✓</button>
                                </form>
                                <form method="POST" action="/?action=update_max_fenstertage" class="flex items-end gap-2">
                                    <div class="flex-1">
                                        <label class="block text-xs font-bold text-emerald-700 mb-1">Max. Fenstertage pro Antrag <span class="font-normal text-emerald-500">(0 = kein Limit)</span></label>
                                        <input type="number" min="0" name="max_fenstertage" value="<?= (int)($maxFenstertage ?? 0) ?>" class="w-full bg-yellow-50 border border-yellow-200 rounded-xl px-3 py-2 text-emerald-900 outline-none">
                                    </div>
                                    <button type="submit" class="bg-lime-400 hover:bg-lime-500 text-emerald-900 font-bold px-3 py-2 rounded-xl">✓</button>
                                </form>
                            </div>
                            </div>
                            </div>

                            <div class="bg-white p-6 rounded-3xl shadow-xl border border-lime-100">
                            <h3 class="text-xl font-bold text-emerald-900 mb-4">Kalender Actions</h3>
                            <div id="calendar-action-empty" class="hidden"></div>
                            <form id="calendar-action-block-form" method="POST" action="/?action=create_blocked_period" class="space-y-3 hidden">
                                <div class="grid grid-cols-2 gap-2">
                                    <button type="button" id="action-mode-block-btn" class="bg-red-100 text-red-700 border border-red-200 py-2 rounded-xl text-sm font-bold">Sperrbereich</button>
                                    <button type="button" id="action-mode-vacation-btn" class="bg-yellow-50 text-emerald-700 border border-yellow-200 py-2 rounded-xl text-sm font-bold">Urlaubszeit buchen</button>
                                </div>
                                <h4 class="text-sm uppercase tracking-wider font-bold text-emerald-700">Sperrbereich setzen</h4>
                                <div>
                                    <label class="block text-sm font-semibold text-emerald-800 mb-1">Start</label>
                                    <input id="blocked-start-date" type="date" name="start_date" required class="w-full bg-yellow-50 border border-yellow-200 rounded-xl px-3 py-2.5 text-emerald-900 outline-none">
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-emerald-800 mb-1">Ende</label>
                                    <input id="blocked-end-date" type="date" name="end_date" required class="w-full bg-yellow-50 border border-yellow-200 rounded-xl px-3 py-2.5 text-emerald-900 outline-none">
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-emerald-800 mb-1">Bezeichnung (optional)</label>
                                    <input type="text" name="label" placeholder="z.B. Betriebsurlaub" class="w-full bg-yellow-50 border border-yellow-200 rounded-xl px-3 py-2.5 text-emerald-900 outline-none">
                                </div>
                                <button type="submit" class="w-full bg-red-400 hover:bg-red-500 text-white font-bold py-2.5 rounded-xl">Sperrzeit speichern</button>
                            </form>

                            <form id="calendar-action-vacation-form" method="POST" action="/?action=admin_create_vacation" class="space-y-3 hidden">
                                <div class="grid grid-cols-2 gap-2">
                                    <button type="button" id="action-mode-block-btn-2" class="bg-yellow-50 text-emerald-700 border border-yellow-200 py-2 rounded-xl text-sm font-bold">Sperrbereich</button>
                                    <button type="button" id="action-mode-vacation-btn-2" class="bg-lime-100 text-emerald-700 border border-lime-200 py-2 rounded-xl text-sm font-bold">Urlaubszeit buchen</button>
                                </div>
                                <h4 class="text-sm uppercase tracking-wider font-bold text-emerald-700">Urlaubszeit fuer Mitarbeiter buchen</h4>
                                <div>
                                    <label class="block text-sm font-semibold text-emerald-800 mb-1">Mitarbeiter</label>
                                    <select name="user_id" required class="w-full bg-yellow-50 border border-yellow-200 rounded-xl px-3 py-2.5 text-emerald-900 outline-none">
                                        <option value="">Bitte auswaehlen</option>
                                        <?php foreach (($employees ?? []) as $empOpt): ?>
                                            <?php if (($empOpt['role'] ?? '') !== 'Employee') continue; ?>
                                            <option value="<?= $empOpt['id'] ?>"><?= htmlspecialchars($empOpt['firstname'] . ' ' . $empOpt['lastname']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-emerald-800 mb-1">Start</label>
                                    <input id="admin-vacation-start-date" type="date" name="start_date" min="<?= date('Y-m-d') ?>" required class="w-full bg-yellow-50 border border-yellow-200 rounded-xl px-3 py-2.5 text-emerald-900 outline-none">
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-emerald-800 mb-1">Ende</label>
                                    <input id="admin-vacation-end-date" type="date" name="end_date" min="<?= date('Y-m-d') ?>" required class="w-full bg-yellow-50 border border-yellow-200 rounded-xl px-3 py-2.5 text-emerald-900 outline-none">
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-emerald-800 mb-1">Kommentar im Verlauf (optional)</label>
                                    <input type="text" name="admin_comment" class="w-full bg-yellow-50 border border-yellow-200 rounded-xl px-3 py-2.5 text-emerald-900 outline-none">
                                </div>
                                <button type="submit" class="w-full bg-lime-400 hover:bg-lime-500 text-emerald-900 font-bold py-2.5 rounded-xl">Urlaubszeit buchen</button>
                            </form>

                            <div id="calendar-action-unblock" class="space-y-3 hidden">
                                <h4 class="text-sm uppercase tracking-wider font-bold text-emerald-700">Sperrbereich aufheben</h4>
                                <div id="calendar-action-unblock-list" class="space-y-2"></div>
                            </div>

                            <div id="calendar-action-event" class="space-y-3 hidden">
                                <h4 class="text-sm uppercase tracking-wider font-bold text-emerald-700">Termin Details</h4>
                                <div id="calendar-selected-event-info" class="text-sm text-emerald-800 bg-yellow-50 border border-yellow-200 rounded-xl p-4"></div>
                                <form method="POST" action="/?action=decide_request" class="space-y-3">
                                    <input type="hidden" id="calendar-selected-request-id" name="request_id" value="">
                                    <input type="hidden" id="calendar-selected-action-decline-value" value="rejected">
                                    <input type="hidden" id="calendar-selected-action-approve-value" value="approved">
                                    <input type="text" name="admin_comment" placeholder="Kommentar zur Entscheidung (optional)" class="w-full bg-white border border-yellow-200 rounded-xl px-4 py-2.5 text-sm text-emerald-900 outline-none">
                                    <div class="grid grid-cols-2 gap-2">
                                        <button type="submit" id="calendar-event-decline-btn" name="status" value="rejected" class="bg-red-50 hover:bg-red-100 text-red-600 border border-red-200 py-2 rounded-xl text-sm font-bold"><?= I18n::get('ceo.decline') ?></button>
                                        <button type="submit" id="calendar-event-approve-btn" name="status" value="approved" class="bg-lime-400 hover:bg-lime-500 text-emerald-900 py-2 rounded-xl text-sm font-bold"><?= I18n::get('ceo.approve') ?></button>
                                    </div>
                                </form>
                            </div>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 xl:grid-cols-3 gap-8">
                        <div class="xl:col-span-2">
                            <h2 class="text-3xl font-bold mb-6 text-emerald-900 tracking-tight"><?= I18n::get('ceo.need_approval') ?></h2>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <?php 
                        $hasPending = false;
                        foreach ($requests as $req): 
                            if (!in_array($req['status'], ['pending', 'storno_requested'])) continue;
                            $hasPending = true;
                            $isStorno = $req['status'] === 'storno_requested';
                        ?>
                            <div id="request-card-<?= $req['id'] ?>" data-request-id="<?= $req['id'] ?>" class="bg-white rounded-3xl p-6 shadow-xl shadow-lime-900/5 border <?= $isStorno ? 'border-orange-300' : 'border-lime-200' ?> relative overflow-hidden flex flex-col hover:-translate-y-1 transition-transform duration-300">
                                <div class="absolute top-0 right-0 w-24 h-24 <?= $isStorno ? 'bg-orange-100' : 'bg-yellow-100' ?> rounded-bl-full -z-10 mix-blend-multiply opacity-50"></div>
                                <div class="flex justify-between items-start mb-5">
                                    <div>
                                        <h3 class="font-bold text-xl text-emerald-900 flex items-center gap-2">
                                            <?= htmlspecialchars($req['firstname'] . ' ' . $req['lastname']) ?>
                                            <?php if($isStorno): ?>
                                                <span class="bg-orange-500/10 text-orange-600 text-[10px] uppercase font-black px-2 py-0.5 rounded-md">Storno</span>
                                            <?php endif; ?>
                                        </h3>
                                        <p class="text-sm font-medium text-emerald-600"><?= htmlspecialchars($req['email']) ?></p>
                                    </div>
                                    <span class="<?= $isStorno ? 'bg-orange-100 text-orange-800 border-orange-200' : 'bg-lime-100 text-emerald-800 border-lime-200' ?> text-xs px-3 py-1.5 rounded-lg font-bold border whitespace-nowrap">
                                        <?= $req['net_days'] ?> <?= I18n::get('ceo.days') ?>
                                    </span>
                                </div>
                                
                                <div class="bg-yellow-50/50 rounded-2xl p-4 mb-6 flex gap-4 text-center items-center justify-center border border-yellow-100">
                                    <div>
                                        <div class="text-[10px] text-emerald-600/70 font-bold uppercase tracking-widest mb-1"><?= I18n::get('ceo.from') ?></div>
                                        <div class="font-bold text-emerald-800"><?= date('d.m.Y', strtotime($req['start_date'])) ?></div>
                                    </div>
                                    <div class="text-lime-400 font-black">→</div>
                                    <div>
                                        <div class="text-[10px] text-emerald-600/70 font-bold uppercase tracking-widest mb-1"><?= I18n::get('ceo.to') ?></div>
                                        <div class="font-bold text-emerald-800"><?= date('d.m.Y', strtotime($req['end_date'])) ?></div>
                                    </div>
                                </div>

                                <form action="/?action=decide_request" method="POST" class="mt-auto space-y-3 relative z-10">
                                    <input type="hidden" name="request_id" value="<?= $req['id'] ?>">
                                    <input type="text" name="admin_comment" placeholder="Kommentar zur Entscheidung (optional)" class="w-full bg-white border border-yellow-200 rounded-xl px-4 py-2.5 text-sm text-emerald-900 focus:ring-2 focus:ring-lime-400 outline-none transition-all placeholder:text-emerald-300">
                                    <div class="flex gap-3">
                                        <?php if ($isStorno): ?>
                                            <!-- Approving Storno = Cancelled -->
                                            <!-- Rejecting Storno = Stays Approved -->
                                            <button type="submit" name="status" value="approved" class="flex-1 bg-red-50 hover:bg-red-100 text-red-600 border border-red-200 py-3 rounded-xl text-sm font-bold transition-all">Decline Storno</button>
                                            <button type="submit" name="status" value="cancelled" class="flex-1 bg-orange-400 hover:bg-orange-500 shadow-md shadow-orange-400/20 text-white py-3 rounded-xl text-sm font-bold transition-all">Approve Storno</button>
                                        <?php else: ?>
                                            <button type="submit" name="status" value="rejected" class="flex-1 bg-red-50 hover:bg-red-100 text-red-600 border border-red-200 py-3 rounded-xl text-sm font-bold transition-all"><?= I18n::get('ceo.decline') ?></button>
                                            <button type="submit" name="status" value="approved" class="flex-1 bg-lime-400 hover:bg-lime-500 shadow-md shadow-lime-400/20 text-emerald-900 py-3 rounded-xl text-sm font-bold transition-all"><?= I18n::get('ceo.approve') ?></button>
                                        <?php endif; ?>
                                    </div>
                                </form>

                                <div class="mt-4 border-t border-yellow-100 pt-3 space-y-2">
                                    <div class="text-xs uppercase tracking-wider font-bold text-emerald-700">Kommentare</div>
                                    <?php foreach (($requestCommentsById[$req['id']] ?? []) as $c): ?>
                                        <div class="text-xs bg-yellow-50 border border-yellow-100 rounded-lg p-2">
                                            <span class="font-bold"><?= htmlspecialchars($c['firstname'] . ' ' . $c['lastname']) ?>:</span>
                                            <?= htmlspecialchars($c['comment']) ?>
                                        </div>
                                    <?php endforeach; ?>
                                    <?php if (empty($requestCommentsById[$req['id']] ?? [])): ?>
                                        <div class="text-xs text-emerald-600">Noch keine Kommentare. Der optionale Entscheidungskommentar erscheint hier.</div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                        
                        <?php if (!$hasPending): ?>
                            <div class="col-span-full py-16 text-center text-emerald-600/60 bg-white/50 rounded-3xl border-2 border-dashed border-lime-200">
                                <svg class="w-16 h-16 mx-auto mb-4 text-lime-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                <p class="text-xl font-bold tracking-tight text-emerald-800"><?= I18n::get('ceo.empty_requests') ?></p>
                                <p class="font-medium text-emerald-600/80"><?= I18n::get('ceo.empty_desc') ?></p>
                            </div>
                        <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <!-- ── URLAUBSSUCHE ─────────────────────────────── -->
                    <div class="bg-white p-6 rounded-3xl shadow-xl border border-lime-100">
                        <div class="flex flex-wrap items-center justify-between gap-3 mb-5">
                            <h3 class="text-xl font-bold text-emerald-900">Urlaubssuche</h3>
                            <div class="flex gap-2 flex-wrap">
                                <input type="text" id="req-search-name"
                                    placeholder="Mitarbeiter suchen…"
                                    oninput="filterRequests()"
                                    class="bg-yellow-50 border border-yellow-200 rounded-xl px-4 py-2 text-sm text-emerald-900 outline-none focus:ring-2 focus:ring-lime-400 w-52">
                                <select id="req-search-status" onchange="filterRequests()"
                                    class="bg-yellow-50 border border-yellow-200 rounded-xl px-3 py-2 text-sm text-emerald-900 outline-none focus:ring-2 focus:ring-lime-400">
                                    <option value="">Alle Status</option>
                                    <option value="pending">Ausstehend</option>
                                    <option value="approved">Genehmigt</option>
                                    <option value="rejected">Abgelehnt</option>
                                    <option value="storno_requested">Storno angefragt</option>
                                    <option value="cancelled">Storniert</option>
                                </select>
                            </div>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full text-left border-collapse min-w-[560px]">
                                <thead>
                                    <tr class="bg-lime-50 border-b border-lime-100 text-xs uppercase text-emerald-700 tracking-wider font-semibold">
                                        <th class="p-3">Mitarbeiter</th>
                                        <th class="p-3">Zeitraum</th>
                                        <th class="p-3">Tage</th>
                                        <th class="p-3">Status</th>
                                        <th class="p-3 text-right">Aktion</th>
                                    </tr>
                                </thead>
                                <tbody id="req-search-tbody" class="divide-y divide-lime-100 text-emerald-800 text-sm font-medium"></tbody>
                            </table>
                            <div id="req-search-empty" class="py-8 text-center text-emerald-600/60 font-medium hidden">Keine Ergebnisse.</div>
                        </div>
                    </div>

                    <div class="bg-white p-6 rounded-3xl shadow-xl border border-lime-100">
                        <h3 class="text-xl font-bold text-emerald-900 mb-4">Audit Log</h3>
                        <div class="space-y-2 max-h-64 overflow-auto pr-1">
                            <?php foreach (($recentAuditLogs ?? []) as $log): ?>
                                <div class="p-3 rounded-xl border border-yellow-100 bg-yellow-50">
                                    <div class="text-xs font-bold text-emerald-800"><?= htmlspecialchars($log['action']) ?></div>
                                    <div class="text-xs text-emerald-700"><?= htmlspecialchars(($log['firstname'] ?? 'System') . ' ' . ($log['lastname'] ?? '')) ?></div>
                                    <div class="text-[10px] text-emerald-500"><?= htmlspecialchars($log['created_at']) ?></div>
                                </div>
                            <?php endforeach; ?>
                            <?php if (empty($recentAuditLogs)): ?>
                                <div class="text-sm text-emerald-600">Noch keine Audit-Eintraege.</div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- TEAM TAB -->
                <div x-show="tab === 'team'" style="display: none;">
                    <?php $isTeamDetail = isset($_GET['team_view']) && $_GET['team_view'] === 'detail'; ?>
                    <?php if ($isTeamDetail && isset($selectedTeamUser) && $selectedTeamUser): ?>
                        <div class="bg-white rounded-3xl p-6 shadow-xl shadow-lime-900/5 border border-lime-100">
                            <div class="flex flex-wrap items-center justify-between gap-3 mb-5">
                                <a href="/?tab=team" class="text-sm font-bold text-emerald-700 hover:text-emerald-900">← Zurueck zur Team-Uebersicht</a>
                                <span class="text-xs font-bold px-3 py-1 rounded-full <?= $selectedTeamUser['role'] === 'CEO' ? 'bg-blue-100 text-blue-700' : 'bg-lime-100 text-emerald-700' ?>"><?= $selectedTeamUser['role'] === 'CEO' ? 'Admin' : htmlspecialchars($selectedTeamUser['role']) ?></span>
                            </div>
                            <h3 class="text-2xl font-bold text-emerald-900"><?= htmlspecialchars($selectedTeamUser['firstname'] . ' ' . $selectedTeamUser['lastname']) ?></h3>
                            <p class="text-sm text-emerald-600 mb-5"><?= htmlspecialchars($selectedTeamUser['email']) ?> | MNR <?= htmlspecialchars($selectedTeamUser['mnr']) ?></p>

                            <div class="grid grid-cols-1 md:grid-cols-3 gap-3 mb-5">
                                <div class="bg-lime-50 border border-lime-200 rounded-xl p-3"><div class="text-xs uppercase font-bold text-emerald-700">Urlaubstage gesamt</div><div class="text-xl font-bold text-emerald-900"><?= (int) $selectedTeamUser['vacation_entitlement_days'] ?></div></div>
                                <div class="bg-yellow-50 border border-yellow-200 rounded-xl p-3"><div class="text-xs uppercase font-bold text-emerald-700">Bereits genehmigt</div><div class="text-xl font-bold text-emerald-900"><?= (int) ($selectedTeamUserUsedDays ?? 0) ?></div></div>
                                <div class="bg-white border border-emerald-200 rounded-xl p-3"><div class="text-xs uppercase font-bold text-emerald-700">Resturlaub</div><div class="text-xl font-bold text-emerald-900"><?= max(0, (int)$selectedTeamUser['vacation_entitlement_days'] - (int)($selectedTeamUserUsedDays ?? 0)) ?></div></div>
                            </div>

                            <?php $isOwnAdminAccount = ((int) $selectedTeamUser['id'] === (int) $currentUser['id']) && (($currentUser['role'] ?? '') === 'CEO'); ?>
                            <?php if ($isOwnAdminAccount): ?>
                                <div class="flex justify-end mb-3">
                                    <span class="text-xs font-semibold text-emerald-700 bg-yellow-50 border border-yellow-200 rounded-lg px-3 py-1.5">
                                        <?= (($_SESSION['lang'] ?? 'en') === 'de') ? 'Eigener Admin-Account kann nicht gelöscht werden.' : 'Own admin account cannot be deleted.' ?>
                                    </span>
                                </div>
                            <?php else: ?>
                                <form method="POST" action="/?action=delete_employee" onsubmit="return confirm('Ensure you want to delete this employee?');" class="flex justify-end mb-3">
                                    <input type="hidden" name="emp_id" value="<?= $selectedTeamUser['id'] ?>">
                                    <button type="submit" class="text-red-600 font-bold text-sm"><?= I18n::get('ceo.delete') ?></button>
                                </form>
                            <?php endif; ?>

                            <form method="POST" action="/?action=edit_employee" class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                <input type="hidden" name="emp_id" value="<?= $selectedTeamUser['id'] ?>">
                                <div><label class="block text-xs font-bold text-emerald-700 mb-1">Vorname</label><input type="text" name="firstname" value="<?= htmlspecialchars($selectedTeamUser['firstname']) ?>" class="w-full bg-yellow-50 border border-yellow-200 rounded-xl px-3 py-2.5 text-emerald-900 outline-none"></div>
                                <div><label class="block text-xs font-bold text-emerald-700 mb-1">Nachname</label><input type="text" name="lastname" value="<?= htmlspecialchars($selectedTeamUser['lastname']) ?>" class="w-full bg-yellow-50 border border-yellow-200 rounded-xl px-3 py-2.5 text-emerald-900 outline-none"></div>
                                <div><label class="block text-xs font-bold text-emerald-700 mb-1">E-Mail</label><input type="email" name="email" value="<?= htmlspecialchars($selectedTeamUser['email']) ?>" class="w-full bg-yellow-50 border border-yellow-200 rounded-xl px-3 py-2.5 text-emerald-900 outline-none"></div>
                                <div><label class="block text-xs font-bold text-emerald-700 mb-1">MNR</label><input type="text" name="mnr" value="<?= htmlspecialchars($selectedTeamUser['mnr']) ?>" pattern="[A-Za-z]?[0-9]+" title="MNR, z.B. M002 oder 002" class="w-full bg-yellow-50 border border-yellow-200 rounded-xl px-3 py-2.5 text-emerald-900 outline-none"></div>
                                <div><label class="block text-xs font-bold text-emerald-700 mb-1">Rolle</label><select name="role" class="w-full bg-yellow-50 border border-yellow-200 rounded-xl px-3 py-2.5 text-emerald-900 outline-none">
                                    <option value="Employee" <?= $selectedTeamUser['role'] === 'Employee' ? 'selected' : '' ?>>Employee</option>
                                    <option value="Admin" <?= $selectedTeamUser['role'] === 'CEO' ? 'selected' : '' ?>>Admin</option>
                                </select></div>
                                <div><label class="block text-xs font-bold text-emerald-700 mb-1">Department</label><select name="department_id" class="w-full bg-yellow-50 border border-yellow-200 rounded-xl px-3 py-2.5 text-emerald-900 outline-none">
                                    <option value="">Department</option>
                                    <?php foreach (($departments ?? []) as $dept): ?>
                                        <option value="<?= $dept['id'] ?>" <?= ((string)$selectedTeamUser['department_id'] === (string)$dept['id']) ? 'selected' : '' ?>><?= htmlspecialchars($dept['name']) ?></option>
                                    <?php endforeach; ?>
                                </select></div>
                                <div><label class="block text-xs font-bold text-emerald-700 mb-1">Urlaubstage</label><input type="number" min="0" name="vacation_entitlement_days" value="<?= (int)$selectedTeamUser['vacation_entitlement_days'] ?>" class="w-full bg-yellow-50 border border-yellow-200 rounded-xl px-3 py-2.5 text-emerald-900 outline-none"></div>
                                <div><label class="block text-xs font-bold text-emerald-700 mb-1">Ueberstunden</label><input type="number" min="0" step="0.5" name="overtime_hours" value="<?= htmlspecialchars($selectedTeamUser['overtime_hours']) ?>" class="w-full bg-yellow-50 border border-yellow-200 rounded-xl px-3 py-2.5 text-emerald-900 outline-none"></div>
                                <div class="md:col-span-2"><label class="block text-xs font-bold text-emerald-700 mb-1">Neues Passwort (optional)</label><input type="password" name="password" class="w-full bg-yellow-50 border border-yellow-200 rounded-xl px-3 py-2.5 text-emerald-900 outline-none"></div>
                                <div class="md:col-span-2 flex justify-end items-center mt-1">
                                    <button type="submit" class="bg-lime-400 text-emerald-900 px-4 py-2 rounded-xl font-bold"><?= I18n::get('ceo.save') ?></button>
                                </div>
                            </form>
                        </div>

                        <div class="bg-white rounded-3xl p-6 shadow-xl shadow-lime-900/5 border border-lime-100 mt-6">
                            <h4 class="text-xl font-bold text-emerald-900 mb-4">Urlaubsuebersicht dieses Users</h4>
                            <div class="overflow-x-auto">
                                <table class="w-full text-left border-collapse min-w-[560px]">
                                    <thead>
                                        <tr class="bg-lime-50 border-b border-lime-100 text-xs uppercase text-emerald-700 tracking-wider font-semibold">
                                            <th class="p-3">Zeitraum</th>
                                            <th class="p-3">Tage</th>
                                            <th class="p-3">Status</th>
                                            <th class="p-3">Kommentar</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-lime-100 text-emerald-800 text-sm">
                                        <?php foreach (($selectedTeamUserRequests ?? []) as $req): ?>
                                            <tr>
                                                <td class="p-3"><?= htmlspecialchars($req['start_date']) ?> - <?= htmlspecialchars($req['end_date']) ?></td>
                                                <td class="p-3"><?= (int)$req['net_days'] ?></td>
                                                <td class="p-3"><?= htmlspecialchars($req['status']) ?></td>
                                                <td class="p-3">
                                                    <?php
                                                        $requestCommentList = $requestCommentsById[$req['id']] ?? [];
                                                        $latestComment = !empty($requestCommentList) ? $requestCommentList[count($requestCommentList) - 1] : null;
                                                    ?>
                                                    <?= $latestComment ? htmlspecialchars($latestComment['comment']) : '-' ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                        <?php if (empty($selectedTeamUserRequests)): ?>
                                            <tr><td class="p-4 text-emerald-600" colspan="4">Keine Urlaubsantraege vorhanden.</td></tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="grid grid-cols-1 xl:grid-cols-3 gap-8" x-data="{ teamSearch: '' }">
                            <div class="xl:col-span-2 bg-white rounded-3xl p-6 shadow-xl shadow-lime-900/5 border border-lime-100">
                                <div class="flex items-center justify-between gap-3 mb-4">
                                    <h3 class="text-2xl font-bold text-emerald-900">Alle User</h3>
                                    <input type="text" x-model="teamSearch" placeholder="Suche nach Name, E-Mail, MNR..." class="w-full max-w-md bg-yellow-50 border border-yellow-200 rounded-xl px-3 py-2.5 text-emerald-900 outline-none">
                                </div>
                                <div class="space-y-2 max-h-[620px] overflow-auto pr-1">
                                    <?php if (isset($employees)): foreach ($employees as $emp): ?>
                                        <a
                                            href="/?tab=team&team_view=detail&team_user=<?= $emp['id'] ?>"
                                            x-show="'<?= strtolower(htmlspecialchars($emp['firstname'] . ' ' . $emp['lastname'] . ' ' . $emp['email'] . ' ' . $emp['mnr'])) ?>'.includes(teamSearch.toLowerCase())"
                                            class="flex items-center justify-between gap-3 p-4 rounded-xl border border-yellow-100 bg-white hover:bg-yellow-50"
                                        >
                                            <div>
                                                <div class="font-semibold text-emerald-900"><?= htmlspecialchars($emp['firstname'] . ' ' . $emp['lastname']) ?></div>
                                                <div class="text-xs text-emerald-600"><?= htmlspecialchars($emp['email']) ?> | MNR <?= htmlspecialchars($emp['mnr']) ?></div>
                                            </div>
                                            <span class="text-xs font-bold px-2 py-1 rounded-full <?= $emp['role'] === 'CEO' ? 'bg-blue-100 text-blue-700' : 'bg-lime-100 text-emerald-700' ?>"><?= $emp['role'] === 'CEO' ? 'Admin' : htmlspecialchars($emp['role']) ?></span>
                                        </a>
                                    <?php endforeach; endif; ?>
                                </div>
                            </div>

                            <div class="bg-white rounded-3xl p-6 shadow-xl shadow-lime-900/5 border border-lime-100">
                                <h3 class="text-xl font-bold text-emerald-900 mb-4">Neuen User erstellen</h3>
                                <form action="/?action=create_employee" method="POST" class="space-y-3">
                                    <div class="grid grid-cols-2 gap-3">
                                        <input type="text" name="firstname" placeholder="<?= I18n::get('ceo.firstname') ?>" required class="w-full bg-yellow-50 border border-yellow-200 rounded-xl px-3 py-2.5 text-emerald-900 outline-none">
                                        <input type="text" name="lastname" placeholder="<?= I18n::get('ceo.lastname') ?>" required class="w-full bg-yellow-50 border border-yellow-200 rounded-xl px-3 py-2.5 text-emerald-900 outline-none">
                                    </div>
                                    <input type="email" name="email" placeholder="<?= I18n::get('ceo.email') ?>" required class="w-full bg-yellow-50 border border-yellow-200 rounded-xl px-3 py-2.5 text-emerald-900 outline-none">
                                    <input type="text" name="mnr" placeholder="<?= I18n::get('ceo.mnr') ?>" pattern="[A-Za-z]?[0-9]+" title="MNR, z.B. M002 oder 002" required class="w-full bg-yellow-50 border border-yellow-200 rounded-xl px-3 py-2.5 text-emerald-900 outline-none">
                                    <input type="password" id="new_emp_pw" name="password" placeholder="<?= I18n::get('ceo.initial_pw') ?>" required class="w-full bg-yellow-50 border border-yellow-200 rounded-xl px-3 py-2.5 text-emerald-900 outline-none">
                                    <div class="grid grid-cols-2 gap-3">
                                        <select name="role" class="w-full bg-yellow-50 border border-yellow-200 rounded-xl px-3 py-2.5 text-emerald-900 outline-none">
                                            <option value="Employee">Employee</option>
                                            <option value="Admin">Admin</option>
                                        </select>
                                        <select name="department_id" class="w-full bg-yellow-50 border border-yellow-200 rounded-xl px-3 py-2.5 text-emerald-900 outline-none">
                                            <option value="">Department</option>
                                            <?php foreach (($departments ?? []) as $dept): ?>
                                                <option value="<?= $dept['id'] ?>"><?= htmlspecialchars($dept['name']) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="grid grid-cols-2 gap-3">
                                        <input type="number" min="0" name="vacation_entitlement_days" value="25" placeholder="Urlaubstage" class="w-full bg-yellow-50 border border-yellow-200 rounded-xl px-3 py-2.5 text-emerald-900 outline-none">
                                        <input type="number" min="0" step="0.5" name="overtime_hours" value="0" placeholder="Ueberstunden" class="w-full bg-yellow-50 border border-yellow-200 rounded-xl px-3 py-2.5 text-emerald-900 outline-none">
                                    </div>
                                    <button type="submit" class="w-full bg-lime-400 hover:bg-lime-500 text-emerald-900 font-bold py-2.5 rounded-xl"><?= I18n::get('ceo.register_btn') ?></button>
                                </form>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>

            </div>
        <?php endif; ?>
        </main>
    </div>

    <div id="export-modal" class="fixed inset-0 z-[120] hidden items-center justify-center bg-emerald-950/50 backdrop-blur-sm p-4">
        <div class="w-full max-w-lg bg-white rounded-3xl shadow-2xl border border-lime-100 p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-xl font-bold text-emerald-900">Kalender Export</h3>
                <button type="button" onclick="closeExportModal()" class="text-emerald-600 hover:text-emerald-900 font-bold">✕</button>
            </div>
            <form action="/" method="GET" class="space-y-4">
                <input type="hidden" name="action" value="calendar_ics">
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-emerald-700 mb-1">Von</label>
                        <input type="date" name="export_start" class="w-full bg-yellow-50 border border-yellow-200 rounded-xl px-3 py-2 text-emerald-900 outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-emerald-700 mb-1">Bis</label>
                        <input type="date" name="export_end" class="w-full bg-yellow-50 border border-yellow-200 rounded-xl px-3 py-2 text-emerald-900 outline-none">
                    </div>
                </div>
                <div class="grid grid-cols-1 gap-1 text-sm text-emerald-800">
                    <label><input type="checkbox" name="include_approved" value="1" checked class="mr-2">Genehmigt</label>
                    <label><input type="checkbox" name="include_pending" value="1" checked class="mr-2">Ausstehend</label>
                    <label><input type="checkbox" name="include_storno" value="1" checked class="mr-2">Storno angefragt</label>
                    <label id="export-include-blocked-row" class="hidden"><input type="checkbox" name="include_blocked" value="1" class="mr-2">Sperrzeiten</label>
                </div>
                <button type="submit" class="w-full bg-lime-400 hover:bg-lime-500 text-emerald-900 font-bold py-2.5 rounded-xl">Exportieren</button>
            </form>
        </div>
    </div>

    <script>
        const fcEvents = <?= isset($fcEvents) ? json_encode($fcEvents) : '[]' ?>;
        const currentLang = '<?= $_SESSION['lang'] ?? 'en' ?>';
        const currentRole = '<?= $currentRole ?>';
        const requestLookup = <?= isset($requests) ? json_encode($requests) : '[]' ?>;
        const blockedPeriodLookup = <?= isset($blockedPeriods) ? json_encode($blockedPeriods) : '[]' ?>;
        const blockedRanges = fcEvents
            .filter((e) => e.extendedProps && e.extendedProps.isBlocked)
            .map((e) => ({ start: e.start, end: e.end }));
        let ceoCalendarInstance = null;
        let ceoSelectedRange = null;
        const ceoSelectionStorageKey = 'easytime_ceo_calendar_selection';
        let ceoProgrammaticSelect = false;
        
        document.addEventListener('DOMContentLoaded', function() {
            function formatLocalDate(dateObj) {
                const year = dateObj.getFullYear();
                const month = String(dateObj.getMonth() + 1).padStart(2, '0');
                const day = String(dateObj.getDate()).padStart(2, '0');
                return `${year}-${month}-${day}`;
            }

            function focusRequestInOverview(requestId) {
                if (!requestId) return;
                const target = document.querySelector(`[data-request-id="${requestId}"]`);
                if (!target) return;
                target.scrollIntoView({ behavior: 'smooth', block: 'center' });
                target.classList.add('ring-2', 'ring-lime-400');
                setTimeout(() => target.classList.remove('ring-2', 'ring-lime-400'), 1800);
            }

            function setEmployeeFormDates(startStr, endExclusiveStr) {
                const startInput = document.getElementById('employee-start-date');
                const endInput = document.getElementById('employee-end-date');
                if (!startInput || !endInput) return;

                const endDate = new Date(endExclusiveStr);
                endDate.setDate(endDate.getDate() - 1);
                const localEnd = formatLocalDate(endDate);

                startInput.value = startStr;
                endInput.value = localEnd;

                startInput.dispatchEvent(new Event('input', { bubbles: true }));
                startInput.dispatchEvent(new Event('change', { bubbles: true }));
                endInput.dispatchEvent(new Event('input', { bubbles: true }));
                endInput.dispatchEvent(new Event('change', { bubbles: true }));
                startInput.focus();
            }

            function setBlockedFormDates(startStr, endExclusiveStr) {
                const startInput = document.getElementById('blocked-start-date');
                const endInput = document.getElementById('blocked-end-date');
                if (!startInput || !endInput) return;

                const endDate = new Date(endExclusiveStr);
                endDate.setDate(endDate.getDate() - 1);
                const localEnd = formatLocalDate(endDate);

                startInput.value = startStr;
                endInput.value = localEnd;
            }

            function setAdminVacationFormDates(startStr, endExclusiveStr) {
                const startInput = document.getElementById('admin-vacation-start-date');
                const endInput = document.getElementById('admin-vacation-end-date');
                if (!startInput || !endInput) return;

                const endDate = new Date(endExclusiveStr);
                endDate.setDate(endDate.getDate() - 1);
                const localEnd = endDate.toISOString().slice(0, 10);

                startInput.value = startStr;
                endInput.value = localEnd;
            }

            function hasBlockedOverlap(startStr, endExclusiveStr) {
                const start = new Date(startStr);
                const endInclusive = new Date(endExclusiveStr);
                endInclusive.setDate(endInclusive.getDate() - 1);
                return blockedRanges.some((r) => {
                    const blockStart = new Date(r.start);
                    const blockEnd = new Date(r.end);
                    blockEnd.setDate(blockEnd.getDate() - 1);
                    return start <= blockEnd && endInclusive >= blockStart;
                });
            }

            function clearCalendarActions() {
                document.getElementById('calendar-info-content')?.classList.add('hidden');
                document.getElementById('calendar-action-empty')?.classList.add('hidden');
                document.getElementById('calendar-action-block-form')?.classList.add('hidden');
                document.getElementById('calendar-action-vacation-form')?.classList.add('hidden');
                document.getElementById('calendar-action-unblock')?.classList.add('hidden');
                document.getElementById('calendar-action-event')?.classList.add('hidden');
            }

            function setCalendarInfo(type, start, end, meta = '') {
                const panel = document.getElementById('calendar-info-panel');
                const metaEl = document.getElementById('calendar-info-meta');
                if (!panel || !metaEl) return;
                if (meta && meta.trim() !== '') {
                    metaEl.textContent = `${type}: ${start} bis ${end} | ${meta}`;
                    metaEl.classList.remove('hidden');
                } else {
                    metaEl.textContent = '';
                    metaEl.classList.add('hidden');
                }
                document.getElementById('calendar-info-content')?.classList.remove('hidden');
            }

            function getTodayRange() {
                const now = new Date();
                const start = formatLocalDate(now);
                const end = new Date(now);
                end.setDate(end.getDate() + 1);
                return { start, end: formatLocalDate(end) };
            }

            function loadPersistedCeoRange() {
                try {
                    const raw = localStorage.getItem(ceoSelectionStorageKey);
                    if (!raw) return null;
                    const parsed = JSON.parse(raw);
                    if (!parsed || !parsed.start || !parsed.end) return null;
                    return parsed;
                } catch (e) {
                    return null;
                }
            }

            function persistCeoRange(range) {
                try {
                    localStorage.setItem(ceoSelectionStorageKey, JSON.stringify(range));
                } catch (e) {
                    // ignore storage errors
                }
            }

            function applyCeoSelection(range, syncCalendarSelection = true) {
                if (!range || !range.start || !range.end) return;
                ceoSelectedRange = { start: range.start, end: range.end };
                persistCeoRange(ceoSelectedRange);
                if (syncCalendarSelection && ceoCalendarInstance) {
                    ceoProgrammaticSelect = true;
                    ceoCalendarInstance.select(ceoSelectedRange.start, ceoSelectedRange.end);
                }
                if (hasBlockedOverlap(ceoSelectedRange.start, ceoSelectedRange.end)) {
                    showActionUnblockSelection(ceoSelectedRange.start, ceoSelectedRange.end);
                } else {
                    showActionBlockedSelection(ceoSelectedRange.start, ceoSelectedRange.end);
                }
            }

            function showActionBlockedSelection(startStr, endExclusiveStr) {
                setBlockedFormDates(startStr, endExclusiveStr);
                const endDate = new Date(endExclusiveStr);
                endDate.setDate(endDate.getDate() - 1);
                setCalendarInfo('Zeitraum', startStr, formatLocalDate(endDate), 'Aktion: Sperrbereich setzen oder auf Urlaubszeit buchen wechseln.');
                document.getElementById('calendar-action-block-form')?.classList.remove('hidden');
                document.getElementById('calendar-action-vacation-form')?.classList.add('hidden');
                document.getElementById('calendar-action-unblock')?.classList.add('hidden');
                document.getElementById('calendar-action-event')?.classList.add('hidden');
            }

            function showActionVacationSelection(startStr, endExclusiveStr) {
                setAdminVacationFormDates(startStr, endExclusiveStr);
                const endDate = new Date(endExclusiveStr);
                endDate.setDate(endDate.getDate() - 1);
                setCalendarInfo('Zeitraum', startStr, formatLocalDate(endDate), 'Aktion: Urlaubszeit für Mitarbeiter buchen.');
                document.getElementById('calendar-action-block-form')?.classList.add('hidden');
                document.getElementById('calendar-action-vacation-form')?.classList.remove('hidden');
                document.getElementById('calendar-action-unblock')?.classList.add('hidden');
                document.getElementById('calendar-action-event')?.classList.add('hidden');
            }

            function getBlockedOverlaps(startStr, endExclusiveStr) {
                const start = new Date(startStr);
                const endInclusive = new Date(endExclusiveStr);
                endInclusive.setDate(endInclusive.getDate() - 1);
                return blockedPeriodLookup.filter((b) => {
                    const bStart = new Date(b.start_date);
                    const bEnd = new Date(b.end_date);
                    return start <= bEnd && endInclusive >= bStart;
                });
            }

            function showActionUnblockSelection(startStr, endExclusiveStr) {
                const list = document.getElementById('calendar-action-unblock-list');
                if (!list) return;
                const overlaps = getBlockedOverlaps(startStr, endExclusiveStr);
                const endDate = new Date(endExclusiveStr);
                endDate.setDate(endDate.getDate() - 1);
                setCalendarInfo('Zeitraum', startStr, formatLocalDate(endDate), `Aktion: ${overlaps.length} Sperrbereich(e) aufheben.`);
                list.innerHTML = '';
                overlaps.forEach((b) => {
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = '/?action=delete_blocked_period';
                    form.className = 'flex items-center justify-between gap-3 bg-red-50 border border-red-100 rounded-xl p-3';
                    form.innerHTML = `
                        <div class="text-xs text-emerald-800">
                            <div class="font-bold">${(b.label || 'Blocked')}</div>
                            <div>${b.start_date} - ${b.end_date}</div>
                        </div>
                        <input type="hidden" name="blocked_id" value="${b.id}">
                        <button type="submit" class="text-red-600 hover:text-red-800 font-bold text-xs">Aufheben</button>
                    `;
                    list.appendChild(form);
                });
                document.getElementById('calendar-action-block-form')?.classList.add('hidden');
                document.getElementById('calendar-action-vacation-form')?.classList.add('hidden');
                document.getElementById('calendar-action-unblock')?.classList.remove('hidden');
                document.getElementById('calendar-action-event')?.classList.add('hidden');
            }

            window.showActionEventDetails = function showActionEventDetails(requestId) {
                const request = requestLookup.find((r) => String(r.id) === String(requestId));
                if (!request) return;
                const info = document.getElementById('calendar-selected-event-info');
                const hiddenId = document.getElementById('calendar-selected-request-id');
                const declineBtn = document.getElementById('calendar-event-decline-btn');
                const approveBtn = document.getElementById('calendar-event-approve-btn');
                if (!info || !hiddenId) return;
                hiddenId.value = request.id;

                if (declineBtn && approveBtn) {
                    if (request.status === 'storno_requested') {
                        declineBtn.value = 'approved';
                        approveBtn.value = 'cancelled';
                        declineBtn.textContent = 'Decline Storno';
                        approveBtn.textContent = 'Approve Storno';
                    } else {
                        declineBtn.value = 'rejected';
                        approveBtn.value = 'approved';
                        declineBtn.textContent = '<?= I18n::get('ceo.decline') ?>';
                        approveBtn.textContent = '<?= I18n::get('ceo.approve') ?>';
                    }
                    const canDecide = !['rejected', 'cancelled'].includes(request.status);
                    declineBtn.disabled = !canDecide;
                    approveBtn.disabled = !canDecide;
                    if (!canDecide) {
                        declineBtn.classList.add('opacity-50', 'cursor-not-allowed');
                        approveBtn.classList.add('opacity-50', 'cursor-not-allowed');
                    } else {
                        declineBtn.classList.remove('opacity-50', 'cursor-not-allowed');
                        approveBtn.classList.remove('opacity-50', 'cursor-not-allowed');
                    }
                }

                info.innerHTML = `
                    <div class="font-bold text-base mb-2">${request.firstname} ${request.lastname}</div>
                    <div><span class="font-semibold">Zeitraum:</span> ${request.start_date} bis ${request.end_date}</div>
                    <div><span class="font-semibold">Status:</span> ${request.status}</div>
                    <div><span class="font-semibold">Tage:</span> ${request.net_days}</div>
                    <div><span class="font-semibold">Kontakt:</span> ${request.email}</div>
                `;
                setCalendarInfo('Termin', request.start_date, request.end_date, `Status: ${request.status} | Mitarbeiter: ${request.firstname} ${request.lastname}`);
                document.getElementById('calendar-action-block-form')?.classList.add('hidden');
                document.getElementById('calendar-action-vacation-form')?.classList.add('hidden');
                document.getElementById('calendar-action-unblock')?.classList.add('hidden');
                document.getElementById('calendar-action-event')?.classList.remove('hidden');
            }

            function initFC(elemId) {
                const el = document.getElementById(elemId);
                if (!el) return;
                const calendar = new FullCalendar.Calendar(el, {
                    initialView: 'dayGridMonth',
                    locale: currentLang,
                    events: fcEvents,
                    headerToolbar: {
                        left: 'prev,next today',
                        center: 'title',
                        right: 'dayGridMonth,dayGridWeek'
                    },
                    height: 'auto',
                    firstDay: 1, // Start on Monday
                    weekNumbers: true,
                    weekNumberContent: function(arg) { return 'KW ' + arg.num; },
                    eventDisplay: 'block',
                    unselectAuto: false,
                    unselectCancel: '.calendar-side-panel',
                    selectable: elemId === 'employee-calendar' || (elemId === 'ceo-calendar' && (currentRole === 'CEO' || currentRole === 'Admin')),
                    selectAllow: function(info) {
                        if (elemId !== 'employee-calendar') return true;
                        return !hasBlockedOverlap(info.startStr, info.endStr);
                    },
                    select: function(info) {
                        if (elemId === 'employee-calendar') {
                            setEmployeeFormDates(info.startStr, info.endStr);
                            return;
                        }
                        if (elemId === 'ceo-calendar' && (currentRole === 'CEO' || currentRole === 'Admin')) {
                            if (ceoProgrammaticSelect) {
                                ceoProgrammaticSelect = false;
                                return;
                            }
                            // Persist and update UI without re-triggering programmatic select recursion.
                            applyCeoSelection({ start: info.startStr, end: info.endStr }, false);
                        }
                    },
                    dateClick: function(info) {
                        // Multi-Auswahl für Employee-Kalender
                        if (elemId === 'employee-calendar' && empMultiMode) {
                            const dateStr = info.dateStr;
                            if (empMultiDates.has(dateStr)) {
                                empMultiDates.delete(dateStr);
                                info.dayEl.classList.remove('fc-day-multiselected');
                            } else {
                                empMultiDates.add(dateStr);
                                info.dayEl.classList.add('fc-day-multiselected');
                            }
                            updateEmpMultiPanel();
                            return;
                        }
                        if (elemId === 'ceo-calendar' && (currentRole === 'CEO' || currentRole === 'Admin')) {
                            const start = info.dateStr;
                            const endDate = new Date(info.date);
                            endDate.setDate(endDate.getDate() + 1);
                            const end = formatLocalDate(endDate);
                            applyCeoSelection({ start, end }, true);
                        }
                    },
                    dayCellDidMount: function(arg) {
                        // Multi-Auswahl Highlight nach Monatsnavigation wiederherstellen
                        if (elemId === 'employee-calendar' && empMultiMode) {
                            const d = arg.date;
                            const key = d.getFullYear() + '-' +
                                String(d.getMonth() + 1).padStart(2, '0') + '-' +
                                String(d.getDate()).padStart(2, '0');
                            if (empMultiDates.has(key)) {
                                arg.el.classList.add('fc-day-multiselected');
                            }
                        }
                    },
                    eventClick: function(info) {
                        if (info.event.extendedProps && info.event.extendedProps.isBlocked) {
                            return;
                        }
                        const requestId = info.event.extendedProps.requestId;
                        if (elemId === 'ceo-calendar') {
                            const tabButton = document.getElementById('ceo-requests-tab-btn');
                            if (tabButton) tabButton.click();
                            showActionEventDetails(requestId);
                            setTimeout(() => focusRequestInOverview(requestId), 120);
                            return;
                        }
                        document.getElementById('employee-overview')?.scrollIntoView({ behavior: 'smooth', block: 'start' });
                        setTimeout(() => focusRequestInOverview(requestId), 120);
                    }
                });
                calendar.render();
                if (elemId === 'ceo-calendar') {
                    ceoCalendarInstance = calendar;
                    const persistedRange = loadPersistedCeoRange();
                    applyCeoSelection(persistedRange || getTodayRange());
                }
            }

            initFC('employee-calendar');
            initFC('ceo-calendar');

            document.getElementById('action-mode-vacation-btn')?.addEventListener('click', function() {
                const start = document.getElementById('blocked-start-date')?.value;
                const end = document.getElementById('blocked-end-date')?.value;
                if (start && end) {
                    const endPlusOne = new Date(end);
                    endPlusOne.setDate(endPlusOne.getDate() + 1);
                    showActionVacationSelection(start, formatLocalDate(endPlusOne));
                }
            });
            document.getElementById('action-mode-block-btn-2')?.addEventListener('click', function() {
                const start = document.getElementById('admin-vacation-start-date')?.value;
                const end = document.getElementById('admin-vacation-end-date')?.value;
                if (start && end) {
                    const endPlusOne = new Date(end);
                    endPlusOne.setDate(endPlusOne.getDate() + 1);
                    showActionBlockedSelection(start, formatLocalDate(endPlusOne));
                }
            });

            // Admin calendar keeps a persistent selection by design.

            // Auto-populate the Urlaubssuche table on first load
            if (document.getElementById('req-search-tbody')) {
                filterRequests();
            }
        });

        /* ── URLAUBSSUCHE filterRequests() ─────────────────────────── */
        function filterRequests() {
            const nameRaw  = (document.getElementById('req-search-name')?.value ?? '').toLowerCase().trim();
            const statusF  = document.getElementById('req-search-status')?.value ?? '';
            const tbody    = document.getElementById('req-search-tbody');
            const emptyEl  = document.getElementById('req-search-empty');
            if (!tbody) return;

            const filtered = requestLookup.filter(function(r) {
                const name = (r.firstname + ' ' + r.lastname).toLowerCase();
                return (!nameRaw || name.includes(nameRaw)) && (!statusF || r.status === statusF);
            });

            tbody.innerHTML = '';

            const badges = {
                pending:          '<span class="inline-flex px-2 py-0.5 rounded-full bg-yellow-100 text-yellow-700 text-xs font-bold">Ausstehend</span>',
                approved:         '<span class="inline-flex px-2 py-0.5 rounded-full bg-green-100 text-green-700 text-xs font-bold">Genehmigt</span>',
                rejected:         '<span class="inline-flex px-2 py-0.5 rounded-full bg-red-100 text-red-700 text-xs font-bold">Abgelehnt</span>',
                storno_requested: '<span class="inline-flex px-2 py-0.5 rounded-full bg-orange-100 text-orange-700 text-xs font-bold">Storno</span>',
                cancelled:        '<span class="inline-flex px-2 py-0.5 rounded-full bg-gray-100 text-gray-600 text-xs font-bold">Storniert</span>',
            };

            if (filtered.length === 0) {
                emptyEl?.classList.remove('hidden');
                return;
            }
            emptyEl?.classList.add('hidden');

            // Sort: newest first by start_date
            filtered.sort((a, b) => (b.start_date > a.start_date ? 1 : -1));

            filtered.forEach(function(r) {
                const tr = document.createElement('tr');
                tr.className = 'hover:bg-yellow-50/50 transition-colors';
                tr.innerHTML =
                    '<td class="p-3 font-semibold">' + r.firstname + ' ' + r.lastname + '</td>' +
                    '<td class="p-3 text-emerald-700">' + r.start_date + ' → ' + r.end_date + '</td>' +
                    '<td class="p-3">' + r.net_days + ' T</td>' +
                    '<td class="p-3">' + (badges[r.status] ?? r.status) + '</td>' +
                    '<td class="p-3 text-right">' +
                        '<button onclick="showActionEventDetails(' + r.id + ')" ' +
                            'class="text-xs font-bold text-lime-600 hover:text-emerald-900 border border-lime-200 px-3 py-1 rounded-lg hover:bg-lime-50 transition-colors">' +
                            'Details' +
                        '</button>' +
                    '</td>';
                tbody.appendChild(tr);
            });
        }

        function openExportModal(isAdminExport) {
            const modal = document.getElementById('export-modal');
            const blockedRow = document.getElementById('export-include-blocked-row');
            if (!modal || !blockedRow) return;
            blockedRow.classList.toggle('hidden', !isAdminExport);
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }

        function closeExportModal() {
            const modal = document.getElementById('export-modal');
            if (!modal) return;
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }

        function vacationForm() {
            return {
                start: '',
                end: '',
                netDays: 0,
                calculateDays() {
                    if (this.start && this.end) {
                        const s = new Date(this.start);
                        const e = new Date(this.end);
                        if (e >= s) {
                            const diffTime = Math.abs(e - s);
                            const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24)) + 1;
                            this.netDays = diffDays;
                        } else {
                            this.netDays = 0;
                        }
                    }
                }
            }
        }

        function togglePw(id) {
            const el = document.getElementById(id);
            if (el.type === 'password') {
                el.type = 'text';
            } else {
                el.type = 'password';
            }
        }

        /* ── MULTI-AUSWAHL (Employee-Kalender) ─────────────────────── */
        let empMultiMode  = false;
        let empMultiDates = new Set();

        function toggleEmpMultiMode() {
            empMultiMode = !empMultiMode;
            const btn = document.getElementById('emp-multi-toggle');
            if (btn) {
                if (empMultiMode) {
                    btn.textContent = '✓ Multi-Auswahl aktiv';
                    btn.className = 'px-3 py-2 rounded-lg text-sm font-semibold bg-lime-400 text-emerald-900 border border-lime-500 transition-colors';
                } else {
                    btn.textContent = '☰ Multi-Auswahl';
                    btn.className = 'px-3 py-2 rounded-lg text-sm font-semibold border border-yellow-200 text-emerald-700 hover:bg-yellow-50 transition-colors';
                    clearEmpMultiSelect();
                }
            }
            updateEmpMultiPanel();
        }

        function clearEmpMultiSelect() {
            empMultiDates.clear();
            // Highlights aus allen sichtbaren Zellen entfernen
            document.querySelectorAll('.fc-day-multiselected').forEach(function(el) {
                el.classList.remove('fc-day-multiselected');
            });
            updateEmpMultiPanel();
        }

        function updateEmpMultiPanel() {
            const panel   = document.getElementById('emp-multi-panel');
            const countEl = document.getElementById('emp-multi-count');
            const listEl  = document.getElementById('emp-multi-list');
            const input   = document.getElementById('multi-dates-input');
            if (!panel) return;

            const dates = Array.from(empMultiDates).sort();

            if (dates.length === 0 || !empMultiMode) {
                panel.classList.add('hidden');
                return;
            }
            panel.classList.remove('hidden');

            if (countEl) countEl.textContent = dates.length + ' Tag' + (dates.length !== 1 ? 'e' : '') + ' ausgewählt';
            if (input)   input.value = JSON.stringify(dates);

            // Aufeinanderfolgende Daten zu Zeitraumblöcken zusammenfassen
            const ranges = [];
            let rs = dates[0], rp = dates[0];
            for (let i = 1; i < dates.length; i++) {
                const diff = (new Date(dates[i]) - new Date(rp)) / 86400000;
                if (diff <= 1) {
                    rp = dates[i];
                } else {
                    ranges.push({ s: rs, e: rp });
                    rs = dates[i]; rp = dates[i];
                }
            }
            ranges.push({ s: rs, e: rp });

            if (listEl) {
                listEl.innerHTML = ranges.map(function(r) {
                    const label = r.s === r.e ? r.s : r.s + ' → ' + r.e;
                    return '<span class="inline-flex items-center gap-1 px-3 py-1 bg-lime-100 border border-lime-200 rounded-full text-xs font-semibold text-emerald-800">' +
                        label +
                        '<button type="button" onclick="removeEmpRange(\'' + r.s + '\',\'' + r.e + '\')" class="text-red-400 hover:text-red-600 font-bold ml-1">✕</button>' +
                        '</span>';
                }).join('');
            }
        }

        function removeEmpRange(startStr, endStr) {
            // Alle Tage im Zeitraum aus der Auswahl entfernen
            const s = new Date(startStr), e = new Date(endStr);
            for (let d = new Date(s); d <= e; d.setDate(d.getDate() + 1)) {
                const key = d.getFullYear() + '-' +
                    String(d.getMonth() + 1).padStart(2, '0') + '-' +
                    String(d.getDate()).padStart(2, '0');
                empMultiDates.delete(key);
            }
            // Highlights auffrischen
            document.querySelectorAll('.fc-day-multiselected').forEach(function(el) {
                el.classList.remove('fc-day-multiselected');
            });
            document.querySelectorAll('.fc-daygrid-day').forEach(function(el) {
                const dateAttr = el.getAttribute('data-date');
                if (dateAttr && empMultiDates.has(dateAttr)) {
                    el.classList.add('fc-day-multiselected');
                }
            });
            updateEmpMultiPanel();
        }
    </script>
</body>
</html>

<?php
use App\Core\I18n;
if (!isset($currentRole)) exit;
?>
<!DOCTYPE html>
<html lang="<?= $_SESSION['lang'] ?? 'en' ?>" class="antialiased">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ZenTime | Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.js'></script>
    <style>
        body { font-family: 'Outfit', sans-serif; background-color: #fefce8; color: #064e3b; }
        .glass { background: rgba(255, 255, 255, 0.6); backdrop-filter: blur(12px); border: 1px solid rgba(163, 230, 53, 0.3); }
        .fc-toolbar-title { font-weight: 700 !important; color: #064e3b; font-family: 'Outfit', sans-serif;}
        .fc-button-primary { background-color: #a3e635 !important; border-color: #84cc16 !important; color: #064e3b !important; font-weight: bold !important; text-transform: capitalize; }
        .fc-button-primary:hover { background-color: #84cc16 !important; }
        .fc-day-today { background-color: #fefce8 !important; }
        .fc-col-header-cell-cushion { color: #064e3b !important; }
        .fc-daygrid-day-number { color: #064e3b !important; }
        .fc-event { border: none !important; border-radius: 4px; padding: 2px 4px; font-weight: 600; font-size: 0.75rem; text-overflow: ellipsis; white-space: nowrap; overflow: hidden;}
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

    <!-- Navigation -->
    <nav class="relative z-10 border-b border-lime-200/50 bg-white/70 backdrop-blur-lg px-6 py-4 flex flex-wrap gap-4 justify-between items-center shadow-sm">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-lime-400 to-yellow-300 flex items-center justify-center shadow-md shadow-lime-400/20">
                <svg class="w-6 h-6 text-emerald-900" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M13 8c0-2.76-2.46-5-5.5-5S2 5.24 2 8h2l1-1 1 1h4"></path><path d="M13 7.14A5.82 5.82 0 0 1 16.5 6c3.04 0 5.5 2.24 5.5 5h-3l-1-1-1 1h-3"></path><path d="M5.89 9.71c-1.15 1.47-1.89 3.17-1.89 5.29A7.1 7.1 0 0 0 7 20"></path><path d="M14.11 9.71c1.15 1.47 1.89 3.17 1.89 5.29A7.1 7.1 0 0 1 13 20"></path><path d="M10.8 11c.5 0 .9.4.9 1v9"></path><path d="M8 22h4"></path></svg>
            </div>
            <span class="text-2xl font-bold tracking-tight text-emerald-900">Zen<span class="text-lime-600">Time</span></span>
        </div>
        
        <div class="flex items-center gap-4 md:gap-6">
            <!-- Improved Greeting -->
            <div class="hidden sm:flex items-center gap-2.5 bg-yellow-50/80 pr-4 pl-1.5 py-1.5 rounded-full border border-yellow-200 shadow-sm">
                <div class="w-7 h-7 rounded-full bg-lime-400 flex items-center justify-center text-emerald-900 font-bold text-xs uppercase shadow-inner">
                    <?= substr(htmlspecialchars($currentUser['firstname']), 0, 1) ?>
                </div>
                <div class="flex flex-col">
                    <span class="text-[10px] uppercase tracking-wider font-bold text-emerald-600/70 leading-none"><?= I18n::get('nav.hi') ?></span>
                    <span class="text-sm font-bold text-emerald-900 leading-none mt-0.5"><?= htmlspecialchars($currentUser['firstname']) ?></span>
                </div>
            </div>
            
            <!-- Language Toggle -->
            <div class="flex gap-2 bg-yellow-50 px-3 py-1.5 rounded-full border border-yellow-200 text-sm">
                <a href="?lang=en" class="<?= ($_SESSION['lang'] ?? 'en') === 'en' ? 'font-bold text-lime-600' : 'text-emerald-600' ?>">EN</a>
                <span class="text-emerald-300">|</span>
                <a href="?lang=de" class="<?= ($_SESSION['lang'] ?? 'en') === 'de' ? 'font-bold text-lime-600' : 'text-emerald-600' ?>">DE</a>
            </div>

            <a href="/?action=logout" class="flex items-center gap-1.5 text-sm font-semibold text-emerald-700 hover:text-red-500 transition-colors bg-white border border-yellow-200 px-3 py-2 rounded-xl">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                <span class="hidden sm:inline"><?= I18n::get('nav.logout') ?></span>
            </a>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="relative z-10 flex-1 max-w-7xl mx-auto w-full p-6 lg:p-8 flex flex-col gap-8">
        
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
                    else echo "An error occurred.";
                ?>
            </div>
        <?php endif; ?>

        <?php if ($currentRole === 'Employee'): ?>
            <!-- EMPLOYEE VIEW -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <div class="lg:col-span-1">
                    <div class="bg-white rounded-3xl p-8 shadow-xl shadow-lime-900/5 relative overflow-hidden border border-lime-100">
                        <div class="absolute top-0 right-0 w-32 h-32 bg-yellow-100 rounded-bl-full -z-10 mix-blend-multiply opacity-50"></div>
                        <h2 class="text-2xl font-bold text-emerald-900 mb-6 tracking-tight"><?= I18n::get('emp.plan') ?></h2>
                        <p class="text-sm text-emerald-700 mb-5">
                            <?= (($_SESSION['lang'] ?? 'en') === 'de') ? 'Wähle direkt im Kalender einen Zeitraum oder setze die Daten manuell.' : 'Select a range directly in the calendar or set the dates manually.' ?>
                        </p>
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
                            <button type="button" onclick="openExportModal(false)" class="px-3 py-2 rounded-lg text-sm font-semibold border border-lime-200 text-emerald-700 hover:bg-lime-50">ICS Export</button>
                        </div>
                    </div>
                    <div id="employee-calendar"></div>
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
            </div>

        <?php elseif (in_array($currentRole, ['CEO', 'Admin'], true)): ?>
            <!-- ADMIN VIEW -->
            <div x-data="{ tab: '<?= (isset($_GET['tab']) && $_GET['tab'] === 'team') ? 'team' : 'operations' ?>' }">
                <div class="flex flex-wrap gap-4 mb-8">
                    <button id="ceo-requests-tab-btn" @click="tab = 'operations'; setTimeout(() => window.dispatchEvent(new Event('resize')), 100);" :class="tab === 'operations' ? 'bg-lime-400 text-emerald-900 shadow-md shadow-lime-400/30' : 'bg-white text-emerald-700 border border-lime-100'" class="px-6 py-2.5 rounded-xl font-bold tracking-tight transition-all">
                        Ops Board
                    </button>
                    <button @click="tab = 'team'" :class="tab === 'team' ? 'bg-lime-400 text-emerald-900 shadow-md shadow-lime-400/30' : 'bg-white text-emerald-700 border border-lime-100'" class="px-6 py-2.5 rounded-xl font-bold tracking-tight transition-all">
                        <?= I18n::get('ceo.team') ?>
                    </button>
                </div>

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
                                    <label class="block text-sm font-semibold text-emerald-800 mb-1">Kommentar (optional)</label>
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
                                    <input type="text" name="admin_comment" placeholder="Optional comment..." class="w-full bg-white border border-yellow-200 rounded-xl px-4 py-2.5 text-sm text-emerald-900 outline-none">
                                    <div class="grid grid-cols-2 gap-2">
                                        <button type="submit" id="calendar-event-decline-btn" name="status" value="rejected" class="bg-red-50 hover:bg-red-100 text-red-600 border border-red-200 py-2 rounded-xl text-sm font-bold"><?= I18n::get('ceo.decline') ?></button>
                                        <button type="submit" id="calendar-event-approve-btn" name="status" value="approved" class="bg-lime-400 hover:bg-lime-500 text-emerald-900 py-2 rounded-xl text-sm font-bold"><?= I18n::get('ceo.approve') ?></button>
                                    </div>
                                </form>
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
                                    <input type="text" name="admin_comment" placeholder="Optional comment..." class="w-full bg-white border border-yellow-200 rounded-xl px-4 py-2.5 text-sm text-emerald-900 focus:ring-2 focus:ring-lime-400 outline-none transition-all placeholder:text-emerald-300">
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
                        <div class="bg-white p-6 rounded-3xl shadow-xl border border-lime-100 space-y-4">
                            <h3 class="text-xl font-bold text-emerald-900">Hinweis</h3>
                            <p class="text-sm text-emerald-700">Das Actions-Menue zeigt Inhalte nur bei aktiver Auswahl im Kalender.</p>
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

                            <form method="POST" action="/?action=delete_employee" onsubmit="return confirm('Ensure you want to delete this employee?');" class="flex justify-end mb-3">
                                <input type="hidden" name="emp_id" value="<?= $selectedTeamUser['id'] ?>">
                                <button type="submit" class="text-red-600 font-bold text-sm"><?= I18n::get('ceo.delete') ?></button>
                            </form>

                            <form method="POST" action="/?action=edit_employee" class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                <input type="hidden" name="emp_id" value="<?= $selectedTeamUser['id'] ?>">
                                <div><label class="block text-xs font-bold text-emerald-700 mb-1">Vorname</label><input type="text" name="firstname" value="<?= htmlspecialchars($selectedTeamUser['firstname']) ?>" class="w-full bg-yellow-50 border border-yellow-200 rounded-xl px-3 py-2.5 text-emerald-900 outline-none"></div>
                                <div><label class="block text-xs font-bold text-emerald-700 mb-1">Nachname</label><input type="text" name="lastname" value="<?= htmlspecialchars($selectedTeamUser['lastname']) ?>" class="w-full bg-yellow-50 border border-yellow-200 rounded-xl px-3 py-2.5 text-emerald-900 outline-none"></div>
                                <div><label class="block text-xs font-bold text-emerald-700 mb-1">E-Mail</label><input type="email" name="email" value="<?= htmlspecialchars($selectedTeamUser['email']) ?>" class="w-full bg-yellow-50 border border-yellow-200 rounded-xl px-3 py-2.5 text-emerald-900 outline-none"></div>
                                <div><label class="block text-xs font-bold text-emerald-700 mb-1">MNR</label><input type="text" name="mnr" value="<?= htmlspecialchars($selectedTeamUser['mnr']) ?>" pattern="[0-9]+" class="w-full bg-yellow-50 border border-yellow-200 rounded-xl px-3 py-2.5 text-emerald-900 outline-none"></div>
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
                                                <td class="p-3"><?= htmlspecialchars($req['admin_comment'] ?? '-') ?></td>
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
                                    <input type="text" name="mnr" placeholder="<?= I18n::get('ceo.mnr') ?>" pattern="[0-9]+" required class="w-full bg-yellow-50 border border-yellow-200 rounded-xl px-3 py-2.5 text-emerald-900 outline-none">
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
        
        document.addEventListener('DOMContentLoaded', function() {
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
                const localEnd = endDate.toISOString().slice(0, 10);

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
                const localEnd = endDate.toISOString().slice(0, 10);

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
                document.getElementById('calendar-action-empty')?.classList.add('hidden');
                document.getElementById('calendar-action-block-form')?.classList.add('hidden');
                document.getElementById('calendar-action-vacation-form')?.classList.add('hidden');
                document.getElementById('calendar-action-unblock')?.classList.add('hidden');
                document.getElementById('calendar-action-event')?.classList.add('hidden');
            }

            function showActionBlockedSelection(startStr, endExclusiveStr) {
                setBlockedFormDates(startStr, endExclusiveStr);
                document.getElementById('calendar-action-block-form')?.classList.remove('hidden');
                document.getElementById('calendar-action-vacation-form')?.classList.add('hidden');
                document.getElementById('calendar-action-unblock')?.classList.add('hidden');
                document.getElementById('calendar-action-event')?.classList.add('hidden');
            }

            function showActionVacationSelection(startStr, endExclusiveStr) {
                setAdminVacationFormDates(startStr, endExclusiveStr);
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

            function showActionEventDetails(requestId) {
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
                    const canDecide = ['pending', 'storno_requested'].includes(request.status);
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
                    eventDisplay: 'block',
                    unselectAuto: elemId !== 'ceo-calendar',
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
                            ceoSelectedRange = { start: info.startStr, end: info.endStr };
                            if (hasBlockedOverlap(info.startStr, info.endStr)) {
                                showActionUnblockSelection(info.startStr, info.endStr);
                            } else {
                                showActionBlockedSelection(info.startStr, info.endStr);
                            }
                        }
                    },
                    unselect: function() {
                        if (elemId === 'ceo-calendar') {
                            ceoSelectedRange = null;
                            clearCalendarActions();
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
                }
            }

            initFC('employee-calendar');
            initFC('ceo-calendar');
            clearCalendarActions();

            document.getElementById('action-mode-vacation-btn')?.addEventListener('click', function() {
                const start = document.getElementById('blocked-start-date')?.value;
                const end = document.getElementById('blocked-end-date')?.value;
                if (start && end) {
                    const endPlusOne = new Date(end);
                    endPlusOne.setDate(endPlusOne.getDate() + 1);
                    showActionVacationSelection(start, endPlusOne.toISOString().slice(0, 10));
                }
            });
            document.getElementById('action-mode-block-btn-2')?.addEventListener('click', function() {
                const start = document.getElementById('admin-vacation-start-date')?.value;
                const end = document.getElementById('admin-vacation-end-date')?.value;
                if (start && end) {
                    const endPlusOne = new Date(end);
                    endPlusOne.setDate(endPlusOne.getDate() + 1);
                    showActionBlockedSelection(start, endPlusOne.toISOString().slice(0, 10));
                }
            });

            document.addEventListener('click', function(event) {
                const calendarEl = document.getElementById('ceo-calendar');
                const actionPanel = document.querySelector('#calendar-action-block-form')?.closest('.bg-white');
                if (!calendarEl || !actionPanel) return;
                const clickedInsideCalendar = calendarEl.contains(event.target);
                const clickedInsideActionPanel = actionPanel.contains(event.target);
                if (!clickedInsideCalendar && !clickedInsideActionPanel) {
                    if (ceoCalendarInstance) {
                        ceoCalendarInstance.unselect();
                    }
                    clearCalendarActions();
                }
            });
        });

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
    </script>
</body>
</html>

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
                    else echo "An error occurred.";
                ?>
            </div>
        <?php endif; ?>

        <?php if ($currentRole === 'Employee'): ?>
            <!-- EMPLOYEE VIEW -->
            <div x-data="{ empTab: 'timeline' }">
                <div class="flex flex-wrap gap-4 mb-8">
                    <button @click="empTab = 'timeline'" :class="empTab === 'timeline' ? 'bg-lime-400 text-emerald-900 shadow-md shadow-lime-400/30' : 'bg-white text-emerald-700 border border-lime-100'" class="px-6 py-2.5 rounded-xl font-bold tracking-tight transition-all">
                        <?= I18n::get('emp.timeline') ?>
                    </button>
                    <button @click="empTab = 'calendar'; setTimeout(() => window.dispatchEvent(new Event('resize')), 100);" :class="empTab === 'calendar' ? 'bg-lime-400 text-emerald-900 shadow-md shadow-lime-400/30' : 'bg-white text-emerald-700 border border-lime-100'" class="px-6 py-2.5 rounded-xl font-bold tracking-tight transition-all flex border justify-center items-center gap-2">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>    
                        <?= I18n::get('ceo.calendar') ?>
                    </button>
                </div>

                <div x-show="empTab === 'timeline'" class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    <!-- Submit Form -->
                <div class="lg:col-span-1">
                    <div class="bg-white rounded-3xl p-8 shadow-xl shadow-lime-900/5 relative overflow-hidden border border-lime-100">
                        <div class="absolute top-0 right-0 w-32 h-32 bg-yellow-100 rounded-bl-full -z-10 mix-blend-multiply opacity-50"></div>
                        <h2 class="text-2xl font-bold text-emerald-900 mb-6 tracking-tight"><?= I18n::get('emp.plan') ?></h2>
                        <form action="/?action=create_request" method="POST" x-data="vacationForm()" class="space-y-5">
                            <div>
                                <label class="block text-sm font-semibold text-emerald-800 mb-1.5"><?= I18n::get('emp.start_date') ?></label>
                                <input type="date" name="start_date" x-model="start" @change="calculateDays" required class="w-full bg-yellow-50 border border-yellow-200 rounded-xl px-4 py-3 text-emerald-900 focus:ring-2 focus:ring-lime-400 outline-none transition-all">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-emerald-800 mb-1.5"><?= I18n::get('emp.end_date') ?></label>
                                <input type="date" name="end_date" x-model="end" @change="calculateDays" required class="w-full bg-yellow-50 border border-yellow-200 rounded-xl px-4 py-3 text-emerald-900 focus:ring-2 focus:ring-lime-400 outline-none transition-all">
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

                <!-- My Requests list -->
                <div class="lg:col-span-2 space-y-6">
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
                                        <tr class="hover:bg-yellow-50/50 transition-colors">
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
                </div>

                <!-- EMPLOYEE CALENDAR -->
                <div x-show="empTab === 'calendar'" style="display: none;" class="bg-white p-8 rounded-3xl shadow-xl border border-lime-100">
                     <div id="employee-calendar"></div>
                </div>
            </div>

        <?php elseif ($currentRole === 'CEO'): ?>
            <!-- ADMIN VIEW -->
            <div x-data="{ tab: 'requests' }">
                <div class="flex flex-wrap gap-4 mb-8">
                    <button @click="tab = 'requests'" :class="tab === 'requests' ? 'bg-lime-400 text-emerald-900 shadow-md shadow-lime-400/30' : 'bg-white text-emerald-700 border border-lime-100'" class="px-6 py-2.5 rounded-xl font-bold tracking-tight transition-all">
                        <?= I18n::get('ceo.requests') ?>
                    </button>
                    <button @click="tab = 'calendar'; setTimeout(() => window.dispatchEvent(new Event('resize')), 100);" :class="tab === 'calendar' ? 'bg-lime-400 text-emerald-900 shadow-md shadow-lime-400/30' : 'bg-white text-emerald-700 border border-lime-100'" class="px-6 py-2.5 rounded-xl font-bold tracking-tight transition-all flex border justify-center items-center gap-2">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>    
                        <?= I18n::get('ceo.calendar') ?>
                    </button>
                    <button @click="tab = 'team'" :class="tab === 'team' ? 'bg-lime-400 text-emerald-900 shadow-md shadow-lime-400/30' : 'bg-white text-emerald-700 border border-lime-100'" class="px-6 py-2.5 rounded-xl font-bold tracking-tight transition-all">
                        <?= I18n::get('ceo.team') ?>
                    </button>
                </div>

                <!-- REQUESTS TAB -->
                <div x-show="tab === 'requests'">
                    <h2 class="text-3xl font-bold mb-6 text-emerald-900 tracking-tight"><?= I18n::get('ceo.need_approval') ?></h2>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
                        <?php 
                        $hasPending = false;
                        foreach ($requests as $req): 
                            if (!in_array($req['status'], ['pending', 'storno_requested'])) continue;
                            $hasPending = true;
                            $isStorno = $req['status'] === 'storno_requested';
                        ?>
                            <div class="bg-white rounded-3xl p-6 shadow-xl shadow-lime-900/5 border <?= $isStorno ? 'border-orange-300' : 'border-lime-200' ?> relative overflow-hidden flex flex-col hover:-translate-y-1 transition-transform duration-300">
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

                <!-- CALENDAR TAB -->
                <div x-show="tab === 'calendar'" style="display: none;" class="bg-white p-8 rounded-3xl shadow-xl border border-lime-100">
                   <h2 class="text-3xl font-bold mb-6 text-emerald-900 tracking-tight flex items-center justify-between">
                       <?= I18n::get('ceo.calendar') ?>
                       <div class="text-sm font-normal text-emerald-600 flex gap-4">
                           <span class="flex items-center gap-1.5"><span class="w-3 h-3 rounded-full bg-lime-400 block"></span> <?= I18n::get('emp.status_approved') ?></span>
                           <span class="flex items-center gap-1.5"><span class="w-3 h-3 rounded-full bg-yellow-300 block"></span> <?= I18n::get('emp.status_pending') ?></span>
                           <span class="flex items-center gap-1.5"><span class="w-3 h-3 rounded-full bg-orange-400 block"></span> <?= I18n::get('emp.status_storno_requested') ?></span>
                       </div>
                   </h2>
                   
                   <div id="ceo-calendar"></div>
                </div>

                <!-- TEAM TAB -->
                <div x-show="tab === 'team'" style="display: none;">
                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                        <div class="lg:col-span-1">
                            <div class="bg-white rounded-3xl p-8 shadow-xl shadow-lime-900/5 border border-lime-100">
                                <h3 class="text-2xl font-bold text-emerald-900 mb-6 tracking-tight"><?= I18n::get('ceo.onboard') ?></h3>
                                <form action="/?action=create_employee" method="POST" class="space-y-4">
                                    <div class="grid grid-cols-2 gap-4">
                                        <div>
                                            <label class="block text-sm font-semibold text-emerald-800 mb-1.5"><?= I18n::get('ceo.firstname') ?></label>
                                            <input type="text" name="firstname" required class="w-full bg-yellow-50 border border-yellow-200 rounded-xl px-4 py-2.5 text-emerald-900 focus:ring-2 focus:ring-lime-400 outline-none">
                                        </div>
                                        <div>
                                            <label class="block text-sm font-semibold text-emerald-800 mb-1.5"><?= I18n::get('ceo.lastname') ?></label>
                                            <input type="text" name="lastname" required class="w-full bg-yellow-50 border border-yellow-200 rounded-xl px-4 py-2.5 text-emerald-900 focus:ring-2 focus:ring-lime-400 outline-none">
                                        </div>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-semibold text-emerald-800 mb-1.5"><?= I18n::get('ceo.email') ?></label>
                                        <input type="email" name="email" required class="w-full bg-yellow-50 border border-yellow-200 rounded-xl px-4 py-2.5 text-emerald-900 focus:ring-2 focus:ring-lime-400 outline-none">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-semibold text-emerald-800 mb-1.5"><?= I18n::get('ceo.mnr') ?></label>
                                        <input type="text" name="mnr" pattern="[0-9]+" title="Only numbers" required class="w-full bg-yellow-50 border border-yellow-200 rounded-xl px-4 py-2.5 text-emerald-900 focus:ring-2 focus:ring-lime-400 outline-none">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-semibold text-emerald-800 mb-1.5"><?= I18n::get('ceo.initial_pw') ?></label>
                                        <div class="relative">
                                            <input type="password" id="new_emp_pw" name="password" required class="w-full bg-yellow-50 border border-yellow-200 rounded-xl px-4 py-2.5 text-emerald-900 focus:ring-2 focus:ring-lime-400 outline-none">
                                            <button type="button" onclick="togglePw('new_emp_pw')" class="absolute inset-y-0 right-0 pr-3 flex items-center text-emerald-500 hover:text-lime-600">
                                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                                            </button>
                                        </div>
                                    </div>
                                    <button type="submit" class="w-full mt-4 bg-lime-400 hover:bg-lime-500 text-emerald-900 font-bold py-3.5 px-4 rounded-xl shadow-lg shadow-lime-400/30 transition-all outline-none">
                                        <?= I18n::get('ceo.register_btn') ?>
                                    </button>
                                </form>
                            </div>
                        </div>

                        <div class="lg:col-span-2">
                            <div class="bg-white rounded-3xl overflow-hidden shadow-xl shadow-lime-900/5 border border-lime-100">
                                <table class="w-full text-left border-collapse min-w-[600px]">
                                    <thead>
                                        <tr class="bg-lime-50 border-b border-lime-100 text-sm uppercase text-emerald-700 tracking-wider font-semibold">
                                            <th class="p-5"><?= I18n::get('ceo.name') ?></th>
                                            <th class="p-5"><?= I18n::get('ceo.email') ?></th>
                                            <th class="p-5"><?= I18n::get('ceo.mnr') ?></th>
                                            <th class="p-5"><?= I18n::get('ceo.role') ?></th>
                                            <th class="p-5"></th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-lime-100 text-emerald-800 font-medium text-sm">
                                        <?php if (isset($employees)): foreach ($employees as $emp): ?>
                                            <tr x-data="{ editing: false }" class="hover:bg-yellow-50/50 transition-colors">
                                                <!-- View Mode -->
                                                <td x-show="!editing" class="p-5 flex items-center gap-3">
                                                    <div class="w-8 h-8 rounded-full bg-lime-200 flex items-center justify-center text-lime-800 font-bold text-xs"><?= substr($emp['firstname'], 0, 1) ?></div>
                                                    <?= htmlspecialchars($emp['firstname'] . ' ' . $emp['lastname']) ?>
                                                </td>
                                                <td x-show="!editing" class="p-5"><?= htmlspecialchars($emp['email']) ?></td>
                                                <td x-show="!editing" class="p-5"><?= htmlspecialchars($emp['mnr']) ?></td>
                                                <td x-show="!editing" class="p-5 text-emerald-600/70 font-semibold"><?= $emp['role'] ?></td>
                                                <td x-show="!editing" class="p-5 text-right space-x-2">
                                                    <button @click="editing = true" class="text-lime-600 hover:text-lime-800 text-xs font-bold px-2 py-1"><?= I18n::get('ceo.edit') ?></button>
                                                    <form method="POST" action="/?action=delete_employee" class="inline" onsubmit="return confirm('Ensure you want to delete this employee?');">
                                                        <input type="hidden" name="emp_id" value="<?= $emp['id'] ?>">
                                                        <button type="submit" class="text-red-500 hover:text-red-700 text-xs font-bold px-2 py-1"><?= I18n::get('ceo.delete') ?></button>
                                                    </form>
                                                </td>
                                                
                                                <!-- Edit Form -->
                                                <td x-show="editing" colspan="5" class="p-5 bg-yellow-50/80">
                                                    <form method="POST" action="/?action=edit_employee" class="flex flex-wrap gap-3 items-center">
                                                        <input type="hidden" name="emp_id" value="<?= $emp['id'] ?>">
                                                        <input type="text" name="firstname" value="<?= htmlspecialchars($emp['firstname']) ?>" class="bg-white border border-yellow-200 rounded-lg px-2 py-1 text-sm outline-none">
                                                        <input type="text" name="lastname" value="<?= htmlspecialchars($emp['lastname']) ?>" class="bg-white border border-yellow-200 rounded-lg px-2 py-1 text-sm outline-none">
                                                        <input type="email" name="email" value="<?= htmlspecialchars($emp['email']) ?>" class="bg-white border border-yellow-200 rounded-lg px-2 py-1 text-sm outline-none">
                                                        <input type="text" name="mnr" value="<?= htmlspecialchars($emp['mnr']) ?>" pattern="[0-9]+" class="bg-white border border-yellow-200 rounded-lg px-2 py-1 text-sm outline-none">
                                                        <input type="password" name="password" placeholder="New Password (optional)" class="bg-white border border-yellow-200 rounded-lg px-2 py-1 text-sm w-40 outline-none">
                                                        <div class="flex gap-2 w-full justify-end mt-2 md:mt-0 md:w-auto">
                                                            <button type="submit" class="bg-lime-400 text-emerald-900 px-3 py-1.5 rounded-lg text-xs font-bold shadow-sm shadow-lime-400/30"><?= I18n::get('ceo.save') ?></button>
                                                            <button type="button" @click="editing = false" class="text-gray-500 hover:text-gray-700 text-xs font-bold"><?= I18n::get('ceo.cancel') ?></button>
                                                        </div>
                                                    </form>
                                                </td>
                                            </tr>
                                        <?php endforeach; endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        <?php endif; ?>
    </main>

    <script>
        const fcEvents = <?= isset($fcEvents) ? json_encode($fcEvents) : '[]' ?>;
        const currentLang = '<?= $_SESSION['lang'] ?? 'en' ?>';
        
        document.addEventListener('DOMContentLoaded', function() {
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
                    eventDisplay: 'block'
                });
                calendar.render();
            }

            initFC('employee-calendar');
            initFC('ceo-calendar');
        });

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

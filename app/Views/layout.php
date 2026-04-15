<?php
if (!isset($currentRole)) exit;
?>
<!DOCTYPE html>
<html lang="en" class="antialiased">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ZenTime | Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        body { font-family: 'Outfit', sans-serif; background-color: #fefce8; color: #064e3b; }
        .glass { background: rgba(255, 255, 255, 0.6); backdrop-filter: blur(12px); border: 1px solid rgba(163, 230, 53, 0.3); }
    </style>
</head>
<body class="min-h-screen flex flex-col relative overflow-hidden">
    <!-- Sunny accents -->
    <div class="absolute top-[-10%] right-[-10%] w-96 h-96 bg-yellow-300 rounded-full mix-blend-multiply opacity-20 blur-3xl z-[-1]"></div>
    <div class="absolute bottom-[-10%] left-[-10%] w-96 h-96 bg-lime-300 rounded-full mix-blend-multiply opacity-20 blur-3xl z-[-1]"></div>

    <!-- Navigation -->
    <nav class="relative z-10 border-b border-lime-200/50 bg-white/70 backdrop-blur-lg px-6 py-4 flex justify-between items-center shadow-sm">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-lime-400 to-yellow-300 flex items-center justify-center shadow-md shadow-lime-400/20">
                <svg class="w-6 h-6 text-emerald-900" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 15a4 4 0 004 4h9a5 5 0 10-.1-9.999 5.002 5.002 0 10-9.78 2.096A4.001 4.001 0 003 15z"></path></svg>
            </div>
            <span class="text-2xl font-bold tracking-tight text-emerald-900">Zen<span class="text-lime-600">Time</span></span>
        </div>
        
        <div class="flex items-center gap-6">
            <span class="font-medium text-emerald-800 bg-emerald-50 px-3 py-1.5 rounded-lg border border-emerald-100">
                Hi, <?= htmlspecialchars($currentUser['firstname']) ?> (<?= $currentRole ?>)
            </span>
            <a href="/?action=logout" class="text-sm font-semibold text-emerald-700 hover:text-red-500 transition-colors">Log Out</a>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="relative z-10 flex-1 max-w-7xl mx-auto w-full p-6 lg:p-8 flex flex-col gap-8">
        
        <?php if (isset($_GET['success'])): ?>
            <div class="bg-lime-50 border border-lime-200 text-lime-800 px-4 py-3 rounded-xl text-sm flex items-center shadow-sm">
                <svg class="w-5 h-5 mr-3 text-lime-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                <?php 
                    if ($_GET['success'] === 'employee_created') echo "New employee successfully registered!";
                    else echo "Action completed successfully.";
                ?>
            </div>
        <?php endif; ?>

        <?php if ($currentRole === 'Employee'): ?>
            <!-- EMPLOYEE VIEW -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Submit Form -->
                <div class="lg:col-span-1">
                    <div class="bg-white rounded-3xl p-8 shadow-xl shadow-lime-900/5 relative overflow-hidden border border-lime-100">
                        <div class="absolute top-0 right-0 w-32 h-32 bg-yellow-100 rounded-bl-full -z-10 mix-blend-multiply opacity-50"></div>
                        <h2 class="text-2xl font-bold text-emerald-900 mb-6 tracking-tight">Plan Vacation</h2>
                        <form action="/?action=create_request" method="POST" x-data="vacationForm()" class="space-y-5">
                            <div>
                                <label class="block text-sm font-semibold text-emerald-800 mb-1.5">Start Date</label>
                                <input type="date" name="start_date" x-model="start" @change="calculateDays" required class="w-full bg-yellow-50 border border-yellow-200 rounded-xl px-4 py-3 text-emerald-900 focus:ring-2 focus:ring-lime-400 focus:border-lime-400 outline-none transition-all">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-emerald-800 mb-1.5">End Date</label>
                                <input type="date" name="end_date" x-model="end" @change="calculateDays" required class="w-full bg-yellow-50 border border-yellow-200 rounded-xl px-4 py-3 text-emerald-900 focus:ring-2 focus:ring-lime-400 focus:border-lime-400 outline-none transition-all">
                            </div>
                            <div class="bg-lime-50 border border-lime-200 rounded-xl p-4 flex justify-between items-center shadow-inner">
                                <span class="font-medium text-emerald-700">Days to Deduct</span>
                                <span class="text-3xl font-bold text-emerald-900" x-text="netDays">0</span>
                                <input type="hidden" name="net_days" x-model="netDays">
                            </div>
                            <button type="submit" class="w-full bg-lime-400 hover:bg-lime-500 text-emerald-900 font-bold py-3.5 px-4 rounded-xl shadow-lg shadow-lime-400/30 transition-all outline-none disabled:opacity-50 disabled:cursor-not-allowed" :disabled="netDays <= 0 || !start || !end">
                                Send Request
                            </button>
                        </form>
                    </div>
                </div>

                <!-- My Requests list -->
                <div class="lg:col-span-2 space-y-6">
                    <h2 class="text-2xl font-bold text-emerald-900 tracking-tight">My Timeline</h2>
                    <div class="bg-white rounded-3xl overflow-hidden shadow-xl shadow-lime-900/5 border border-lime-100">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-lime-50 border-b border-lime-100 text-sm uppercase text-emerald-700 tracking-wider font-semibold">
                                    <th class="p-5">Period</th>
                                    <th class="p-5">Days</th>
                                    <th class="p-5">Status</th>
                                    <th class="p-5">Applied On</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-lime-100 text-emerald-800 font-medium text-sm">
                                <?php foreach ($requests as $req): ?>
                                    <tr class="hover:bg-yellow-50/50 transition-colors">
                                        <td class="p-5">
                                            <?= date('M j, Y', strtotime($req['start_date'])) ?> <span class="text-lime-500 mx-2">→</span> <?= date('M j, Y', strtotime($req['end_date'])) ?>
                                        </td>
                                        <td class="p-5"><?= $req['net_days'] ?> limit</td>
                                        <td class="p-5">
                                            <?php if ($req['status'] === 'approved'): ?>
                                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-green-100 text-green-700">Approved</span>
                                            <?php elseif ($req['status'] === 'rejected'): ?>
                                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-red-100 text-red-700">Declined</span>
                                            <?php else: ?>
                                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-yellow-100 text-yellow-700">Pending</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="p-5 text-emerald-600/70"><?= date('M j', strtotime($req['created_at'])) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                                <?php if (empty($requests)): ?>
                                    <tr><td colspan="4" class="p-8 text-center text-emerald-600/60 font-medium">No vacation requests found. Time to plan one!</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        <?php elseif ($currentRole === 'CEO'): ?>
            <!-- ADMIN VIEW -->
            <div x-data="{ tab: 'requests' }">
                <div class="flex gap-4 mb-8">
                    <button @click="tab = 'requests'" :class="tab === 'requests' ? 'bg-lime-400 text-emerald-900 shadow-md shadow-lime-400/30' : 'bg-white text-emerald-700 border border-lime-100'" class="px-6 py-2.5 rounded-xl font-bold tracking-tight transition-all">Requests</button>
                    <button @click="tab = 'team'" :class="tab === 'team' ? 'bg-lime-400 text-emerald-900 shadow-md shadow-lime-400/30' : 'bg-white text-emerald-700 border border-lime-100'" class="px-6 py-2.5 rounded-xl font-bold tracking-tight transition-all">Team Management</button>
                </div>

                <!-- REQUESTS TAB -->
                <div x-show="tab === 'requests'">
                    <h2 class="text-3xl font-bold mb-6 text-emerald-900 tracking-tight">Need Approval</h2>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
                        <?php 
                        $hasPending = false;
                        foreach ($requests as $req): 
                            if ($req['status'] !== 'pending') continue;
                            $hasPending = true;
                        ?>
                            <div class="bg-white rounded-3xl p-6 shadow-xl shadow-lime-900/5 border border-lime-200 relative overflow-hidden flex flex-col hover:-translate-y-1 transition-transform duration-300">
                                <div class="absolute top-0 right-0 w-24 h-24 bg-yellow-100 rounded-bl-full -z-10 mix-blend-multiply opacity-50"></div>
                                <div class="flex justify-between items-start mb-5">
                                    <div>
                                        <h3 class="font-bold text-xl text-emerald-900"><?= htmlspecialchars($req['firstname'] . ' ' . $req['lastname']) ?></h3>
                                        <p class="text-sm font-medium text-emerald-600"><?= htmlspecialchars($req['email']) ?></p>
                                    </div>
                                    <span class="bg-lime-100 text-emerald-800 text-xs px-3 py-1.5 rounded-lg font-bold border border-lime-200"><?= $req['net_days'] ?> Days</span>
                                </div>
                                
                                <div class="bg-yellow-50/50 rounded-2xl p-4 mb-6 flex gap-4 text-center items-center justify-center border border-yellow-100">
                                    <div>
                                        <div class="text-[10px] text-emerald-600/70 font-bold uppercase tracking-widest mb-1">From</div>
                                        <div class="font-bold text-emerald-800"><?= date('M j', strtotime($req['start_date'])) ?></div>
                                    </div>
                                    <div class="text-lime-400 font-black">→</div>
                                    <div>
                                        <div class="text-[10px] text-emerald-600/70 font-bold uppercase tracking-widest mb-1">To</div>
                                        <div class="font-bold text-emerald-800"><?= date('M j', strtotime($req['end_date'])) ?></div>
                                    </div>
                                </div>

                                <form action="/?action=decide_request" method="POST" class="mt-auto space-y-3 relative z-10">
                                    <input type="hidden" name="request_id" value="<?= $req['id'] ?>">
                                    <input type="text" name="admin_comment" placeholder="Optional comment..." class="w-full bg-white border border-yellow-200 rounded-xl px-4 py-2.5 text-sm text-emerald-900 focus:ring-2 focus:ring-lime-400 focus:border-lime-400 outline-none transition-all placeholder:text-emerald-300">
                                    <div class="flex gap-3">
                                        <button type="submit" name="status" value="rejected" class="flex-1 bg-red-50 hover:bg-red-100 text-red-600 border border-red-200 py-3 rounded-xl text-sm font-bold transition-all">Decline</button>
                                        <button type="submit" name="status" value="approved" class="flex-1 bg-lime-400 hover:bg-lime-500 shadow-md shadow-lime-400/20 text-emerald-900 py-3 rounded-xl text-sm font-bold transition-all">Approve</button>
                                    </div>
                                </form>
                            </div>
                        <?php endforeach; ?>
                        
                        <?php if (!$hasPending): ?>
                            <div class="col-span-full py-16 text-center text-emerald-600/60 bg-white/50 rounded-3xl border-2 border-dashed border-lime-200">
                                <svg class="w-16 h-16 mx-auto mb-4 text-lime-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                <p class="text-xl font-bold tracking-tight text-emerald-800">Everyone's at the beach!</p>
                                <p class="font-medium text-emerald-600/80">Zero pending vacation requests.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- TEAM TAB -->
                <div x-show="tab === 'team'" style="display: none;">
                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                        <div class="lg:col-span-1">
                            <div class="bg-white rounded-3xl p-8 shadow-xl shadow-lime-900/5 border border-lime-100">
                                <h3 class="text-2xl font-bold text-emerald-900 mb-6 tracking-tight">Onboard Employee</h3>
                                <form action="/?action=create_employee" method="POST" class="space-y-4">
                                    <div class="grid grid-cols-2 gap-4">
                                        <div>
                                            <label class="block text-sm font-semibold text-emerald-800 mb-1.5">First Name</label>
                                            <input type="text" name="firstname" required class="w-full bg-yellow-50 border border-yellow-200 rounded-xl px-4 py-2.5 text-emerald-900 focus:ring-2 focus:ring-lime-400 outline-none">
                                        </div>
                                        <div>
                                            <label class="block text-sm font-semibold text-emerald-800 mb-1.5">Last Name</label>
                                            <input type="text" name="lastname" required class="w-full bg-yellow-50 border border-yellow-200 rounded-xl px-4 py-2.5 text-emerald-900 focus:ring-2 focus:ring-lime-400 outline-none">
                                        </div>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-semibold text-emerald-800 mb-1.5">Email Address</label>
                                        <input type="email" name="email" required class="w-full bg-yellow-50 border border-yellow-200 rounded-xl px-4 py-2.5 text-emerald-900 focus:ring-2 focus:ring-lime-400 outline-none">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-semibold text-emerald-800 mb-1.5">Staff No. (MNR)</label>
                                        <input type="text" name="mnr" required class="w-full bg-yellow-50 border border-yellow-200 rounded-xl px-4 py-2.5 text-emerald-900 focus:ring-2 focus:ring-lime-400 outline-none">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-semibold text-emerald-800 mb-1.5">Initial Password</label>
                                        <input type="password" name="password" required class="w-full bg-yellow-50 border border-yellow-200 rounded-xl px-4 py-2.5 text-emerald-900 focus:ring-2 focus:ring-lime-400 outline-none">
                                    </div>
                                    <button type="submit" class="w-full mt-4 bg-lime-400 hover:bg-lime-500 text-emerald-900 font-bold py-3.5 px-4 rounded-xl shadow-lg shadow-lime-400/30 transition-all outline-none">
                                        Register Team Member
                                    </button>
                                </form>
                            </div>
                        </div>

                        <div class="lg:col-span-2">
                            <div class="bg-white rounded-3xl overflow-hidden shadow-xl shadow-lime-900/5 border border-lime-100">
                                <table class="w-full text-left border-collapse">
                                    <thead>
                                        <tr class="bg-lime-50 border-b border-lime-100 text-sm uppercase text-emerald-700 tracking-wider font-semibold">
                                            <th class="p-5">Name</th>
                                            <th class="p-5">Email</th>
                                            <th class="p-5">MNR</th>
                                            <th class="p-5">Role</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-lime-100 text-emerald-800 font-medium text-sm">
                                        <?php if (isset($employees)): foreach ($employees as $emp): ?>
                                            <tr class="hover:bg-yellow-50/50 transition-colors">
                                                <td class="p-5 flex items-center gap-3">
                                                    <div class="w-8 h-8 rounded-full bg-lime-200 flex items-center justify-center text-lime-800 font-bold text-xs"><?= substr($emp['firstname'], 0, 1) ?></div>
                                                    <?= htmlspecialchars($emp['firstname'] . ' ' . $emp['lastname']) ?>
                                                </td>
                                                <td class="p-5"><?= htmlspecialchars($emp['email']) ?></td>
                                                <td class="p-5"><?= htmlspecialchars($emp['mnr']) ?></td>
                                                <td class="p-5 text-emerald-600/70 font-semibold"><?= $emp['role'] ?></td>
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
    </script>
</body>
</html>

<?php
use App\Core\I18n;

$categoryLabels = [
    'approval' => 'Genehmigung',
    'success'  => 'Erfolg',
    'rejected' => 'Abgelehnt',
    'info'     => 'Info',
];
$categoryColors = [
    'approval' => 'bg-yellow-100 text-yellow-800 border-yellow-200',
    'success'  => 'bg-green-100 text-green-800 border-green-200',
    'rejected' => 'bg-red-100 text-red-800 border-red-200',
    'info'     => 'bg-lime-100 text-emerald-800 border-lime-200',
];
?>
<div class="max-w-3xl">
    <h2 class="text-2xl font-bold text-emerald-900 tracking-tight mb-2">Inbox</h2>
    <p class="text-sm text-emerald-700 mb-6">
        <?= (($_SESSION['lang'] ?? 'de') === 'de')
            ? 'Benachrichtigungen zu Urlaubsanträgen, Genehmigungen, Ablehnungen und Storno-Anfragen.'
            : 'Notifications about vacation requests, approvals, rejections and cancellation requests.' ?>
    </p>

    <div class="space-y-3">
        <?php foreach (($notificationList ?? []) as $note): ?>
            <?php
                $cat = (string) ($note['category'] ?? 'info');
                $badgeClass = $categoryColors[$cat] ?? $categoryColors['info'];
                $badgeLabel = $categoryLabels[$cat] ?? ucfirst($cat);
                $isUnread = (int) ($note['is_read'] ?? 0) === 0;
            ?>
            <article class="rounded-2xl border p-4 shadow-sm <?= $isUnread ? 'border-lime-300 bg-lime-50/80' : 'border-yellow-100 bg-white' ?>">
                <div class="flex flex-wrap items-start justify-between gap-2 mb-2">
                    <h3 class="font-bold text-emerald-900"><?= htmlspecialchars((string) $note['title']) ?></h3>
                    <span class="inline-flex px-2 py-0.5 rounded-full text-[10px] font-bold border <?= $badgeClass ?>"><?= htmlspecialchars($badgeLabel) ?></span>
                </div>
                <p class="text-sm text-emerald-800 leading-relaxed"><?= htmlspecialchars((string) $note['message']) ?></p>
                <time class="block text-[11px] text-emerald-500 mt-2 font-medium"><?= htmlspecialchars((string) ($note['created_at'] ?? '')) ?></time>
            </article>
        <?php endforeach; ?>

        <?php if (empty($notificationList)): ?>
            <div class="py-16 text-center rounded-3xl border-2 border-dashed border-lime-200 bg-white/60">
                <svg class="w-14 h-14 mx-auto mb-3 text-lime-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2  2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/></svg>
                <p class="text-lg font-bold text-emerald-800"><?= (($_SESSION['lang'] ?? 'de') === 'de') ? 'Keine Nachrichten' : 'No messages' ?></p>
                <p class="text-sm text-emerald-600 mt-1"><?= (($_SESSION['lang'] ?? 'de') === 'de') ? 'Neue Ereignisse erscheinen hier automatisch.' : 'New events will appear here automatically.' ?></p>
            </div>
        <?php endif; ?>
    </div>

    <?php if (in_array($currentRole ?? '', ['CEO', 'Admin'], true)): ?>
        <p class="text-xs text-emerald-600 mt-6">
            <a href="/?tab=operations" class="font-bold text-lime-600 hover:text-emerald-900">→ Zum Kalender & Genehmigungen</a>
        </p>
    <?php else: ?>
        <p class="text-xs text-emerald-600 mt-6">
            <a href="/?tab=plan" class="font-bold text-lime-600 hover:text-emerald-900">→ Urlaub planen</a>
        </p>
    <?php endif; ?>
</div>

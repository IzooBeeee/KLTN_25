<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$admin = \App\Models\Admin::first();
$bds = \App\Models\BatDongSan::first();
if ($admin && $bds) {
    $admin->notify(new \App\Notifications\NewPostPendingApprovalNotification($bds));
    echo "Sent!\n";
} else {
    echo "No admin or bds.\n";
}

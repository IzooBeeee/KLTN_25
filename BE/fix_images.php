<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\BatDongSan;
use App\Models\HinhAnhBatDongSan;

$properties = BatDongSan::doesntHave('hinhAnh')->get();
$count = 0;
$images = [
    'https://images.unsplash.com/photo-1600596542815-ffad4c1539a9?w=800&q=80',
    'https://images.unsplash.com/photo-1600585154340-be6161a56a0c?w=800&q=80',
    'https://images.unsplash.com/photo-1600607687931-cecebd802404?w=800&q=80',
    'https://images.unsplash.com/photo-1512917774080-9991f1c4c750?w=800&q=80',
    'https://images.unsplash.com/photo-1600566753190-17f0baa2a6c3?w=800&q=80',
    'https://images.unsplash.com/photo-1600566753086-00f18fb6b3ea?w=800&q=80',
];

foreach ($properties as $bds) {
    HinhAnhBatDongSan::create([
        'bds_id' => $bds->id,
        'url' => $images[array_rand($images)],
        'thu_tu' => 1,
        'is_anh_dai_dien' => true,
    ]);
    $count++;
}
echo "Added images to $count properties.\n";

<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== CMS PAGES ===" . PHP_EOL;
foreach (App\Models\CmsPage::all() as $p) {
    echo "ID {$p->id} | Title: {$p->title} | Slug: {$p->slug} | HeroImg: {$p->hero_image_url} | Gallery: " . json_encode($p->gallery_images) . PHP_EOL;
}

echo PHP_EOL . "=== CMS BLOG POSTS ===" . PHP_EOL;
foreach (App\Models\CmsBlogPost::all() as $b) {
    echo "ID {$b->id} | Title: {$b->title} | Img: {$b->image_url}" . PHP_EOL;
}


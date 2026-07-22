<?php

use App\Support\Cache\ApiCacheWarmer;
use Database\Seeders\CategoryRevertSeeder;
use Database\Seeders\CategorySeeder;
use Database\Seeders\ImageRevertSeeder;
use Database\Seeders\ImageSeeder;
use Database\Seeders\NewsRevertSeeder;
use Database\Seeders\NewsSeeder;
use Database\Seeders\RevertSeeder;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('seed:rollback {target=all : all, images, news, or categories}', function (string $target) {
    $map = [
        'all' => RevertSeeder::class,
        'images' => ImageRevertSeeder::class,
        'news' => NewsRevertSeeder::class,
        'categories' => CategoryRevertSeeder::class,
    ];

    if (! array_key_exists($target, $map)) {
        $this->components->error("Unknown target [{$target}]. Use: all, images, news, categories");

        return 1;
    }

    $this->call('db:seed', ['--class' => $map[$target]]);

    return 0;
})->purpose('Rollback seeded data (reverse of db:seed)');

Artisan::command('seed:refresh', function () {
    $this->components->info('Rolling back then re-seeding...');

    $this->call('seed:rollback', ['target' => 'all']);
    $this->call('db:seed');

    $this->components->info('Seed refresh complete.');

    return 0;
})->purpose('Rollback all seed data then run DatabaseSeeder again');

Artisan::command('seed:run {target : categories, news, images, or all}', function (string $target) {
    $map = [
        'all' => null,
        'categories' => CategorySeeder::class,
        'news' => NewsSeeder::class,
        'images' => ImageSeeder::class,
    ];

    if (! array_key_exists($target, $map)) {
        $this->components->error("Unknown target [{$target}]. Use: all, categories, news, images");

        return 1;
    }

    if ($target === 'all') {
        $this->call('db:seed');
    } else {
        $this->call('db:seed', ['--class' => $map[$target]]);
    }

    return 0;
})->purpose('Run seeders by name (shortcut for db:seed --class)');

Artisan::command('cache:warm', function (ApiCacheWarmer $warmer) {
    $this->components->info('Warming API cache...');

    $warmer->warm();

    $this->components->info('API cache warmed.');

    return 0;
})->purpose('Pre-load common API cache keys for faster first request');

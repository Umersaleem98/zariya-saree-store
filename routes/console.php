<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('orders:send-emails {--limit=50}', function () {
    $sent=\App\Support\OrderEmails::deliver(max(1,min(200,(int)$this->option('limit'))));
    $this->info("Sent {$sent} order emails.");
})->purpose('Deliver pending order notifications with bounded retries');

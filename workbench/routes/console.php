<?php

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use Workbench\App\Support\TokenCharger;

// Charges the due token schedules of the workbench. Run it once with
// `vendor/bin/testbench barion:charge-due`, or keep `vendor/bin/testbench schedule:work` running.
Artisan::command('barion:charge-due', function (TokenCharger $charger): void {
    $charged = $charger->chargeDue();

    if ($charged === []) {
        $this->info('Nincs esedékes token terhelés.');

        return;
    }

    foreach ($charged as $schedule) {
        $this->line("{$schedule['id']}: {$schedule['lastResult']} (következő: {$schedule['nextChargeAt']})");
    }
})->purpose('Charge the due Barion token schedules of the workbench');

Schedule::command('barion:charge-due')->everyMinute();

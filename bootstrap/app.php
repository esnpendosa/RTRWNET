<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
  ->withRouting(
    web: __DIR__.'/../routes/web.php',
    commands: __DIR__.'/../routes/console.php',
    health: '/up',
  )
  ->withSchedule(function (\Illuminate\Console\Scheduling\Schedule $schedule) {
      $schedule->command('billing:generate')->dailyAt('01:00')->withoutOverlapping();
      $schedule->command('billing:remind')->dailyAt('08:00')->withoutOverlapping();
      $schedule->command('billing:disable-unpaid')->hourly()->withoutOverlapping();
      $schedule->command('billing:enable-paid')->everyFiveMinutes()->withoutOverlapping(); // Auto-ON jika sudah bayar
      $schedule->command('mikrotik:sync-all')->everyFiveMinutes()->withoutOverlapping();
      $schedule->command('monitor:pelanggan')->everyFiveMinutes()->withoutOverlapping();
      $schedule->command('bot:goodbye')->everyMinute()->withoutOverlapping();
      $schedule->command('status:publish')->everyMinute()->withoutOverlapping();
  })
  ->withMiddleware(function (Middleware $middleware) {
      $middleware->validateCsrfTokens(except: [
          'payment/callback',
          'whatsapp/webhook',
          'whatsapp/status',
          'whatsapp/train',
      ]);
  })
  ->withExceptions(function (Exceptions $exceptions) {
    //
  })->create();
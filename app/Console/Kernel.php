<?php

namespace App\Console;
use App\Level;
use Illuminate\Console\Scheduling\Schedule;
use Laravel\Lumen\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        Commands\TestCommand::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->command("addLevel")
        ->everyMinute();

        // $schedule->call(function(){
        //   $level = new Level();
        //   $level->id_level = time();
        //   $level->nama_level = "Tester";
        //   $level->save();
        // })->everyMinute();
    }

}

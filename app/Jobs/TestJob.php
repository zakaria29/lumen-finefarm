<?php
namespace App\Jobs;
use App\Level;
/**
 *
 */
class TestJob extends Job
{
  // protected $signature = "demo:cron";
  protected $description = "Command descriptions";
  function __construct()
  {
    // parent::__construct();
  }

  public function handle()
  {
    \Log::info("Cron is working fine!");
    $level = new Level();
    $level->id_level = time();
    $level->nama_level = "Tester";
    $level->save();
  }
}

 ?>

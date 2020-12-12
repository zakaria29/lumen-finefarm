<?php
namespace App\Console\Commands;
use App\Level;
use Illuminate\Console\Command;

/**
 *
 */
class TestCommand extends Command
{

  function __construct()
  {
    parent::__construct();
  }
  
  protected $signature = "addLevel";

  function handle()
  {
    $level = new Level();
    $level->id_level = time();
    $level->nama_level = "Tester";
    $level->save();
  }
}

 ?>

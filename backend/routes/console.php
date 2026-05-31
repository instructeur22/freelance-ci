<?php
use Illuminate\Support\Facades\Schedule;
Schedule::command("genius-pay:sync")->everyTenMinutes();
Schedule::command("escrow:release-auto")->hourly();
Schedule::command("boosts:expire")->daily();

<?php

use App\Jobs\CheckPendingDeposits;
use App\Jobs\CheckPendingOrders;
use Illuminate\Support\Facades\Schedule;

Schedule::job(new CheckPendingOrders)->everyMinute();
Schedule::job(new CheckPendingDeposits)->everyFiveMinutes();

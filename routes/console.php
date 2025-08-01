<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('beatmaps:sync-ranked')->hourly();
Schedule::command('beatmaps:recalculate-bayesian')->daily();

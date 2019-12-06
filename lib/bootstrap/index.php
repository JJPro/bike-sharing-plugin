<?php
include_once __DIR__ . '/../../vendor/autoload.php';

use shared_bikes\CronScheduler;


// init cron scheduler
CronScheduler::scheduleEvents();

// setup work
register_activation_hook(BIKE_SHARING_PLUGIN_FILENAME, ['shared_bikes\Activation', 'init'] );
add_action(BLUE_BIKES_PULLER_JOB_CRON_NAME, ['shared_bikes\BlueBikesDataPuller', 'pull']);


// Test pull
// shared_bikes\BlueBikesDataPuller::pull();
<?php
include_once __DIR__ . '/../../vendor/autoload.php';

use shared_bikes\CronScheduler;


// setup work
register_activation_hook(BIKE_SHARING_PLUGIN_FILENAME, ['shared_bikes\Activation', 'init'] );

// Cron Events
add_filter('cron_schedules', ['shared_bikes\CronScheduler', 'registerIntervals']);
add_action('init', ['shared_bikes\CronScheduler', 'scheduleEvents']);
add_action(CronScheduler::blue_bikes_puller_cron, ['shared_bikes\BlueBikesDataPuller', 'pull']);

<?php

/**
 * Global Constants
 */
define('BIKE_SHARING_PLUGIN_DIR_URL', \plugin_dir_url(dirname(__FILE__)));
define('BIKE_SHARING_PLUGIN_DIR_PATH', \plugin_dir_path(dirname(__FILE__)));
define('BIKE_SHARING_PLUGIN_FILENAME', \plugin_dir_path(dirname(__FILE__)) . 'bike-sharing-plugin.php');
define('BIKE_SHARING_PLUGIN_NAME', 'Shared Bikes Cross Factor Analysis');

define('BLUE_BIKES_PULLER_JOB_CRON_NAME', 'shared_bikes_pull_bike_data_cron');

define('DARK_SKY_API_HOST', 'dark-sky.p.rapidapi.com');
define('BOSTON_COORD', ['long' => '-71.0589', 'lat' => '42.3601']);

require_once(dirname(__FILE__) . '/api-keys.php');
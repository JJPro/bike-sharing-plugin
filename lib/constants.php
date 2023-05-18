<?php

/**
 * Global Constants
 */
define('BIKE_SHARING_PLUGIN_DIR_URL', \plugin_dir_url(dirname(__FILE__)));
define('BIKE_SHARING_PLUGIN_DIR_PATH', \plugin_dir_path(dirname(__FILE__)));
define('BIKE_SHARING_PLUGIN_FILENAME', \plugin_dir_path(dirname(__FILE__)) . 'bike-sharing-plugin.php');
define('BIKE_SHARING_PLUGIN_NAME', 'Shared Bikes Cross Factor Analysis');
define('DARK_SKY_API_KEY', getenv('DARK_SKY_API_KEY'));
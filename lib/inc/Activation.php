<?php
/**
 * Activation Hook Handlers
 */
namespace shared_bikes;

class Activation
{
  public static function init()
  {
    self::migrations();   // Create DB tables needed for analysis
  }

  private static function migrations() {
    self::blueBikeMigrations();
    self::weatherMigrations();
  }

  private static function blueBikeMigrations() {
    global $wpdb;
    $charset_collate = $wpdb -> get_charset_collate();

    $create_region_table = <<<region_table
      CREATE TABLE IF NOT EXISTS region (
        region_id INT NOT NULL,
        name VARCHAR(255) NOT NULL,

        PRIMARY KEY (region_id)
      ) $charset_collate;
    region_table;

    $create_station_table = <<<station_table
      CREATE TABLE IF NOT EXISTS station (
        station_id INT NOT NULL,
        region_id INT NOT NULL,
        name VARCHAR(255) NOT NULL,
        capacity INT DEFAULT NULL,

        PRIMARY KEY (station_id),
        FOREIGN KEY (region_id) REFERENCES region (region_id) ON DELETE NO ACTION
      ) $charset_collate;
    station_table;

    $create_trip_table = <<<trip_table
      CREATE TABLE IF NOT EXISTS trip (
        id BIGINT NOT NULL AUTO_INCREMENT,
        duration INT NOT NULL,
        starttime DATETIME NOT NULL,
        stoptime DATETIME NOT NULL,
        start_station_id INT NOT NULL,
        end_station_id INT NOT NULL,
        birth_year INT(4) DEFAULT NULL,
        gender INT(2) DEFAULT 0,

        PRIMARY KEY (id),
        KEY idx_trip_starttime (starttime),
        KEY idx_trip_start_station_id (start_station_id),
        KEY idx_trip_gender (gender),
        FOREIGN KEY (start_station_id) REFERENCES station (station_id) ON DELETE NO ACTION ,
        FOREIGN KEY (end_station_id) REFERENCES station (station_id) ON DELETE NO ACTION
      ) $charset_collate;
    trip_table;

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($create_region_table);
    dbDelta($create_station_table);
    dbDelta($create_trip_table);
  }

  private static function weatherMigrations() {
    global $wpdb;
    $charset_collate = $wpdb->get_charset_collate();

    /**
     * cache weather info, save by day
     */
    $create_weather_table = <<<weather_table
      CREATE TABLE IF NOT EXISTS weather (
        precipProbability FLOAT DEFAULT NULL,
        precipType VARCHAR(255) DEFAULT NULL,
        precipIntensity FLOAT DEFAULT NULL,
        precipAccumulation FLOAT DEFAULT NULL,
        apparentTemperatureMax FLOAT NOT NULL,
        apparentTemperatureMin FLOAT NOT NULL,
        pressure FLOAT NOT NULL,
        cloudCover FLOAT NOT NULL,
        humidity FLOAT NOT NULL,
        ozone FLOAT DEFAULT NULL,
        uvIndex INT NOT NULL,
        windSpeed FLOAT NOT NULL,
        visibility FLOAT NOT NULL,
        `date` DATE NOT NULL,

        INDEX idx_weather_date (`date`),
        UNIQUE KEY (`date`)
      ) $charset_collate;
    weather_table;

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($create_weather_table);
  }
}

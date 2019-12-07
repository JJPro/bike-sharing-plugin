<?php

namespace shared_bikes;

/**
 * 1. update station info
 * 2. update region info
 * 3. download and process trip data
 */
class BlueBikesDataPuller {
  const option__last_successful_pull_yearmonth = 'last_trip_successful_pull_yearmonth';

  public static function pull() {
    self::updateRegions();
    self::updateStations();
    self::getNewTripData();
  }

  /**
   * Download and check for region info updates
   */
  private static function updateRegions(){
    $json = file_get_contents('https://gbfs.bluebikes.com/gbfs/en/system_regions.json');
    if ($regions_data = json_decode($json)){
      global $wpdb;

      foreach ($regions_data->data->regions as $region) {
        $sql = "INSERT INTO region (region_id, name) VALUES (%d, %s) ON DUPLICATE KEY UPDATE name = %s";
        $sql = $wpdb->prepare($sql, $region->region_id, $region->name, $region->name);
        $wpdb->query($sql);
      }
    }
  }


  /**
   * Download and check for station updates
   */
  private static function updateStations() {
    $json = file_get_contents('https://gbfs.bluebikes.com/gbfs/en/station_information.json');
    if ($stations_data = json_decode($json)) {
      global $wpdb;

      foreach ($stations_data->data->stations as $station) {
        $sql = <<<station_query
          INSERT INTO station (station_id, region_id, name, capacity)
          VALUES ($station->station_id, $station->region_id, "$station->name", $station->capacity)
          ON DUPLICATE KEY UPDATE
          region_id = $station->region_id,
          name = "$station->name",
          capacity = $station->capacity;
        station_query;

/*         $wpdb->replace('station', [
          'station_id' => $station->station_id,
          'region_id' => $station->region_id,
          'name' => $station->name,
          'capacity' => $station->capacity
        ]);
 */
        $wpdb->query($sql);
      }

      $wpdb->query("
        INSERT INTO station (station_id, region_id, name, capacity)
        VALUES (1, 10, '18 Dorrance St', NULL)
        ON DUPLICATE KEY UPDATE
        region_id = 10,
        name = '18 Dorrance St',
        capacity = NULL;
      ");
    }

  }

  /**
   * Download, unzip and save new trip data
   *
   * Steps:
   * - fetch last_trip_successful_pull_date
   * - save new trip data and update last_trip_successful_pull_date if new trip download available
   */
  private static function getNewTripData() {
    $str_lastmonth = date('Ym', strtotime("-1 month"));

    $last_success_pull_yearmonth = get_option( self::option__last_successful_pull_yearmonth, '201812' );

    $yearmonth = date_create_from_format('Ym', $last_success_pull_yearmonth)->add(date_interval_create_from_date_string('1 month'))->format('Ym');
    while ($yearmonth !== $str_lastmonth) {
      $url = "https://s3.amazonaws.com/hubway-data/$yearmonth-bluebikes-tripdata.zip";
      if (!self::downloadNSaveTripData($url)) return; // just exit if data file is not available

      update_option(self::option__last_successful_pull_yearmonth, $yearmonth);
      $yearmonth = date_create_from_format('Ym', $yearmonth)->add(date_interval_create_from_date_string('1 month'))->format('Ym');
    }



  }

  private static function downloadNSaveTripData($url) {
    $dir = BIKE_SHARING_PLUGIN_DIR_PATH . '/tmp';
    $tmp_filename = 'tmp.zip';
    global $wpdb;

    if (!file_exists($dir)) mkdir($dir);
    if (!copy($url, "$dir/$tmp_filename")) {
      echo "Failed to download file\n";
      return false;
    }
    $zip = new \ZipArchive();
    if ($zip->open("$dir/$tmp_filename")) {
      $csv_filename = $zip->getNameIndex(0);
      if ($zip->extractTo($dir, $csv_filename)) {

        $sql = <<<IMPORT_QUERY
          LOAD DATA
            LOCAL
            INFILE "$dir/$csv_filename"
            INTO TABLE trip
            FIELDS
              TERMINATED BY ','
              ENCLOSED BY '"'
            IGNORE 1 LINES
            (duration, starttime, stoptime, start_station_id, @dummy, @dummy, @dummy, end_station_id, @dummy, @dummy, @dummy, @dummy, @dummy, birth_year, gender);
        IMPORT_QUERY;
        $wpdb->query($sql);

        return true;

        // Uncomment the following if `local_infile` cannot be enabled
        /*
        $fd = fopen("$dir/$csv_filename", 'r');
        // skip first line
        fgetcsv($fd, 0, ',');
        while (($line = fgetcsv($fd, 0, ',')) !== false){
          $duration = $line[0];
          $starttime = $line[1];
          $stoptime = $line[2];
          $start_station_id = $line[3];
          $end_station_id = $line[7];
          $birth_year = $line[13];
          $gender = $line[14];

          $wpdb->insert('trip', [
            'duration' => $duration,
            'starttime' => $starttime,
            'stoptime' => $stoptime,
            'start_station_id' => $start_station_id,
            'end_station_id' => $end_station_id,
            'birth_year' => $birth_year,
            'gender' => $gender,
          ]);
        }
        */
      }
    }

    return false;
  }
}

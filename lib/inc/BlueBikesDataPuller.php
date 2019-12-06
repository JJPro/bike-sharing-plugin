<?php

namespace shared_bikes;

/**
 * // TODO:
 * 1. update station info
 * 2. update region info
 * 3. download and process trip data
 */
class BlueBikesDataPuller {
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
    $regions_data = json_decode($json);

    global $wpdb;

    foreach ($regions_data->data->regions as $region) {
      $wpdb->replace('region', [
        'region_id' => $region->region_id,
        'name' => $region->name,
      ]);
    }

  }


  /**
   * Download and check for station updates
   */
  private static function updateStations() {

  }

  /**
   * Download, unzip and save new trip data
   *
   * // TODO:
   * - fetch last_trip_successful_pull_date
   * - save new trip data and update last_trip_successful_pull_date if new trip download available
   */
  private static function getNewTripData() {

  }
}
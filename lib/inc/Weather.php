<?php
namespace shared_bikes;

class Weather {
  const api_url = 'dark-sky.p.rapidapi.com';
  const api_key = DARK_SKY_API_KEY;
  const bostonCoord = ['long' => '-71.0589', 'lat' => '42.3601'];

  /**
   * @param String $date format: YYYY-mm-dd
   * @return Object weather information
   */
  public static function getWeatherFor($date) {
    /**
     * 1. search in local database
     * 2. pull from api if not found in local db
     */
    global $wpdb;
    $record = $wpdb->get_row($wpdb->prepare("SELECT * FROM weather WHERE `date` = %s", $date));
    if (!$record){
      // pull and save record to db
      $req_url = 'https://' . self::api_url . '/'
            . self::bostonCoord['lat'] . ','
            . self::bostonCoord['long'] . ','
            . "{$date}T11:59:00";

      $client = new \GuzzleHttp\Client();
      $resp = $client->request('GET', $req_url, [
        'headers' => [
          'X-RapidAPI-Host' => self::api_url,
          'X-RapidAPI-Key' => self::api_key
        ],
      ]);

      if ($resp->getStatusCode() === 200) {
        $json = json_decode($resp->getBody());
        $daily_data = $json->daily->data[0];

        // save to db
        $wpdb->insert('weather', [
          'date' => $date,
          'precipProbability' => $daily_data->precipProbability,
          'precipType' => $daily_data->precipType ?? 'NULL',
          'precipIntensity' => $daily_data->precipIntensity ?? 'NULL',
          'precipAccumulation' => $daily_data->precipAccumulation ?? 'NULL',
          'apparentTemperatureMax' => $daily_data->apparentTemperatureMax,
          'apparentTemperatureMin' => $daily_data->apparentTemperatureMin,
          'pressure' => $daily_data->pressure,
          'cloudCover' => $daily_data->cloudCover,
          'humidity' => $daily_data->humidity,
          'ozone' => $daily_data->ozone,
          'uvIndex' => $daily_data->uvIndex,
          'windSpeed' => $daily_data->windSpeed,
          'visibility' => $daily_data->visibility,
        ]);

        // fetch the new record
        $record = $wpdb->get_row($wpdb->prepare("SELECT * FROM weather WHERE `date` = %s", $date));
      }
    }

    return $record;
  }
}

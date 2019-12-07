<?php
namespace shared_bikes;

class RESTful {
  const namespace = 'bikes/v1';

  public static function init() {

    self::weatherAPI(); // weather endpoint -- /bikes/v1/weather/YYYYmmdd-YYYYmmdd GET
    self::tripAPI();    // trip endpoint -- /bikes/v1/trip/YYYYmmdd-YYYYmmdd[?[gender=..][age=..][regions=..,..,..]]
    self::regionsAPI(); // list regions -- /bikes/v1/regions GET
  }

  private static function weatherAPI() {
    $route = '/weather/(?P<from>[\d]{8})-(?P<to>[\d]{8})';
    register_rest_route(self::namespace, $route, [
      [
        'methods' => 'GET',
        'callback' => function ($data) {
          $current = $data['from'];
          $to = $data['to'];

          $result = [];

          while ($current !== $to) {
            // fetch weather for current day
            $result[$current] = Weather::getWeatherFor($current);

            $current = date_create_from_format('Ymd', $current)->add(date_interval_create_from_date_string('1 day'))->format('Ymd');
          }
          // makeup for last day data
          $result[$to] = Weather::getWeatherFor($to);

          return rest_ensure_response($result);
        }
      ]
    ]);
  }

  private static function tripAPI() {
    $route = '/trip/(?P<from>[\d]{8})-(?P<to>[\d]{8})';
    // $route = '/trip/(?P<from>[\d]{8})-(?P<to>[\d]{8})/?\?(?=gender=\w+)(?=age=.+)(?=regions=.+)';

    register_rest_route( self::namespace, $route, [
      [
        'methods' => 'GET',
        'callback' => function($data) {
          $current = $data['from'];
          $to = $data['to'];

          // return rental counts for each day in given date range with given params
          /**
           * Params:
           * - gender
           * - age
           * - regions
           */
          $result = [];

        }
      ]
    ]);
  }

  private static function regionsAPI() {
    $route = '/regions';

    register_rest_route( self::namespace, $route, [
      [
        'methods' => 'GET',
        'callback' => function() {
          global $wpdb;
          // return list of region_id and name
          $regions = $wpdb->get_results('SELECT * FROM region');
          return rest_ensure_response($regions);
        }
      ]
    ]);
  }
}
<?php
namespace shared_bikes;

class RESTful {
  const namespace = 'bikes/v1';

  public static function init() {

    // weather endpoint -- /bikes/v1/weather GET
    self::weatherAPI();
  }

  private static function weatherAPI() {
    $route = '/weather/(?P<date>[\d-]+)';

    register_rest_route( self::namespace, $route, [
      [
        'methods' => 'GET',
        'callback' => function ($data) {
          $weather = Weather::getWeatherFor($data['date']);
          return rest_ensure_response( (array)$weather );
        }
      ]
    ]);
  }
}
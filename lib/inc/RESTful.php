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
          $from = $data['from'];
          $to = $data['to'];

          return rest_ensure_response(Weather::getWeatherForRange($from, $to));
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
          global $wpdb;
          $from = $data['from'];
          $to = $data['to'];

          // return rental counts for each day in given date range with given params
          /**
           * Params:
           * - gender -- 0|1|2: 0 for all, 1 for men, 2 for women
           * - age -- string 'All'|'<= 20'|'BETWEEN 20 AND 30'|'BETWEEN 30 AND 40'|'> 50'
           * - regions -- list of region ids
           */
          $filter_gender = $data['gender'] ?? false;
          $filter_age = $data['age'] ?? false;
          $filter_regions = $data['regions'] ?? false;
          $scatteredUserFilter = $data['scatteredUserFilter'] != 'false' ? $data['scatteredUserFilter'] : false;

          $filter_gender = (int)$filter_gender ? " AND gender = $filter_gender " : '';
          $filter_age = ($filter_age && $filter_age !== 'All') ? " AND (YEAR(NOW()) - birth_year) $filter_age " : '';
          $filter_regions = ($filter_regions && $filter_regions[0] != 0) ? " AND region_id IN ($filter_regions) " : '';
          $filter_valid_age_fixer = ' AND (YEAR(NOW()) - birth_year) < 120 ';
          // $query_join = $filter_regions ? " JOIN station ON start_station_id = station.station_id
          //   JOIN region USING(region_id) " : '';

          // $regions = ($filter_regions && $filter_regions[0] != 0) ? explode(',', $filter_regions) : $wpdb->get_col('SELECT region_id FROM region');

          if (!$scatteredUserFilter) {
            $sql = "SELECT DATE(starttime) as `date`, COUNT(id) AS count
              FROM trip
              JOIN station ON start_station_id = station.station_id
              -- JOIN region USING(region_id)
              WHERE starttime BETWEEN %s AND %s
                    $filter_valid_age_fixer
                    $filter_gender
                    $filter_age
                    $filter_regions
              GROUP BY `date`";
            $sql = $wpdb->prepare($sql, $from, $to);
            $results = $wpdb->get_results($sql);
          } else {
            switch ($scatteredUserFilter) {
              case 'Gender': // group by gender
                $sql = "SELECT gender, DATE(starttime) as `date`, COUNT(id) AS count
                  FROM trip
                  JOIN station ON start_station_id = station.station_id
                  -- JOIN region USING(region_id)
                  WHERE starttime BETWEEN %s AND %s
                        $filter_valid_age_fixer
                        $filter_age
                        $filter_regions
                  GROUP BY gender, `date`";
                $sql = $wpdb->prepare($sql, $from, $to);
                break;
              case 'Age': // group by custom
                $ages = [
                  [
                    'label' => '<= 20',
                    'query' => '<= 20'
                  ],
                  [
                    'label' => '20-30',
                    'query' => 'BETWEEN 20 AND 30'
                  ],
                  [
                    'label' => '30-40',
                    'query' => 'BETWEEN 30 AND 40'
                  ],
                  [
                    'label' => '40-50',
                    'query' => 'BETWEEN 40 AND 50'
                  ],
                  [
                    'label' => '> 50',
                    'query' => '> 50'
                  ]
                ];
                $sql_parts = array_map(function($age) use ($wpdb, $from, $to, $filter_valid_age_fixer) {

                  $current_sql = $wpdb->prepare("SELECT %s AS age, `date`, count
                    FROM (
                      SELECT (YEAR(NOW()) - birth_year) AS age, DATE(starttime) AS `date`, COUNT(id) AS count
                      FROM trip
                      WHERE starttime BETWEEN %s AND %s
                            $filter_valid_age_fixer
                      GROUP BY `date`
                      HAVING age {$age['query']}
                    ) AS t
                  ", $age['label'], $from, $to);
                  return $current_sql;
                }, $ages);
                $sql = implode(' UNION ', $sql_parts);
                break;
              case 'Regions': // group by region_id
                $sql = "SELECT region_id AS regions, DATE(starttime) as `date`, COUNT(id) AS count
                  FROM trip
                  JOIN station ON start_station_id = station.station_id
                  -- JOIN region USING(region_id)
                  WHERE starttime BETWEEN %s AND %s
                        $filter_valid_age_fixer
                        $filter_gender
                        $filter_age
                        $filter_regions
                  GROUP BY region_id, `date`";
                $sql = $wpdb->prepare($sql, $from, $to);
                break;

              default:
                break;
            }
            $results = $wpdb->get_results($sql);
          }

          return rest_ensure_response($results);
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
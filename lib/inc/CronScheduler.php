<?php
/**
 * // TODO:
 * 1. setup cron to pull data from blue bikes once a month
 * 2. save last successful fetched data-month
 */
namespace shared_bikes;

class CronScheduler {
  const blue_bikes_puller_cron = 'shared_bikes_pull_bike_data_cron';
  const monthly_interval_name = 'monthly';

  public static function registerIntervals($schedules) {
    $schedules[self::monthly_interval_name] = array(
      'interval'    => 3600*24*30,
      'display'     => __('Monthly')
    );
    return $schedules;
  }

  public static function scheduleEvents() {
    if (!wp_next_scheduled( self::blue_bikes_puller_cron )) {
      wp_schedule_event( time(), self::monthly_interval_name, self::blue_bikes_puller_cron );
    }
  }
}
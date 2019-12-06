<?php
/**
 * // TODO:
 * 1. setup cron to pull data from blue bikes once a month
 * 2. save last successful fetched data-month
 */
namespace shared_bikes;

class CronScheduler {

  public static function scheduleEvents() {
    if (!wp_next_scheduled( BLUE_BIKES_PULLER_JOB_CRON_NAME )) {
      wp_schedule_event( time(), 'monthly', BLUE_BIKES_PULLER_JOB_CRON_NAME );
    }
  }
}
<?php

class FamousBirthdaysUtility
{
  private static $notices;

  public static function add_notice($notice)
  {
    if (!is_array(self::$notices)) {
      self::$notices = array();
    }

    self::$notices[] = $notice;
  }

  public static function display_admin_notices()
  {
    if (is_array(self::$notices) && count(self::$notices) > 0) {
      foreach (self::$notices as $notice) {

        if (!is_array($notice) || count($notice) !== 2) {
          continue;
        }

        echo "<div class=\"" . $notice[0] . "\"><p>" . $notice[1] . "</p></div>";

      }
    }
  }

  public static function get_birthdays_today()
  {
    $todayIndex   = self::get_today_index();

    $todaysBirthdays = get_option('fambday_todays_birthdays');

    if (!empty($todaysBirthdays) && is_array($todaysBirthdays)
        && count($todaysBirthdays) === 2 && $todaysBirthdays[0] === $todayIndex
        && count($todaysBirthdays[1]) > 0) {
      return $todaysBirthdays[1];
    }

    $birthdayList = self::get_birthday_list();

    if ($birthdayList !== false) {
      if (isset($birthdayList[$todayIndex])) {
        update_option('fambday_todays_birthdays', array(
          $todayIndex,
          $birthdayList[$todayIndex]
        ));
        return $birthdayList[$todayIndex];
      }
    }

    return array();
  }

  private static function get_birthday_list()
  {
    $birthdayList = array();

    if (($temporaryFile = get_transient('fambday_temporary_file')) === false
        || !file_exists($temporaryFile) || FAMBDAY_CACHE_FILE_EXPIRY < 1) {
      $temporaryFile = self::get_and_save_remote_file();
    }

    if ($temporaryFile === false) {
      return $birthdayList;
    }

    $handle = fopen($temporaryFile, 'r');

    if ($handle) {
      while (($line = fgets($handle)) !== false) {

        $line = trim($line);
        if (substr($line, 0, 1) === '#' || empty($line)) {
          continue;
        }

        if (strpos($line, ':') !== false) {
          $currentMonthAndDay = $line;
          $birthdayList[$currentMonthAndDay] = array();
        } else {
          $birthdayList[$currentMonthAndDay][] = trim($line);
        }

      }

      return $birthdayList;
    } else {
      return false;
    }
  }

  private static function get_today_index()
  {
    $timestamp = current_time('timestamp');
    return date('m:d', $timestamp);
  }

  public static function delete_transient_data()
  {
    if (($temporaryFile = get_transient('fambday_temporary_file')) !== false) {
      self::delete_temporary_file($temporaryFile);
    }

    delete_transient('fambday_temporary_file');
    delete_option('fambday_todays_birthdays');
  }

  /**
   * Returns the absolute path of the temporary
   * file containing the birthday data.
   */
  private static function get_and_save_remote_file()
  {
    $response = wp_remote_get(FAMBDAY_REMOTE_FILE_URL);

    if (!is_wp_error($response)) {
      if (is_array($response) && isset($response['body'])) {

        $temporaryFile = FAMBDAY_PATH . 'temp.txt';

        self::write_temporary_file($temporaryFile, $response['body']);

        $cacheExpiry = FAMBDAY_CACHE_FILE_EXPIRY * (86400);

        if ($cacheExpiry > 0) {
          set_transient('fambday_temporary_file', $temporaryFile, $cacheExpiry);
        }

        return $temporaryFile;

      }
    }

    return false;
  }

  private static function write_temporary_file($file, $data)
  {
    $handle = fopen($file, 'w+');
    fwrite($handle, $data);
    fclose($handle);
  }

  private static function delete_temporary_file($filePath)
  {
    if (file_exists($filePath)) {
      unlink($filePath);
    }
  }

}

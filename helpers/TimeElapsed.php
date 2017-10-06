<?php

namespace webzop\notifications\helpers;

use Yii;

class TimeElapsed {

    /* time intervals in seconds */
    public static $intervals = [
        'year' => 31556926,
        'month' => 2629744,
        'week' => 604800,
        'day' => 86400,
        'hour' => 3600,
        'minute' => 60,
        'second' => 1
    ];


    /**
     * Get time elapsed (Facebook Style)
     *
     * Example Output(s):
     *     10 hours ago
     *
     * @param string  $fromTime start date time
     * @param boolean $human if true returns an approximate human friendly output. If set to `false`,
     * will attempt an exact conversion of time intervals.
     * @param string  $toTime end date time (defaults to current system time)
     * @param string  $append the string to append for the converted elapsed time. Defaults to ' ago'.
     *
     * @return string
     */
    public static function timeElapsed($fromTime = null, $human = true, $toTime = null, $append = null)
    {
        if ($fromTime != null) {
            if(!is_numeric($fromTime)) {
                $fromTime = strtotime($fromTime);
            }

            $toTime = ($toTime == null) ? time() : (int)$toTime;
        }
        return static::timeInterval($toTime - $fromTime, $append, $human);
    }

    /**
     * Get time interval (Facebook Style)
     *
     * Example Output(s):
     *     10 hours ago
     *
     * @param int     $interval time interval in seconds
     * @param string  $append the string to append for the converted elapsed time. Defaults to ' ago'.
     * @param boolean $human if true returns an approximate human friendly output. If set to `false`,
     * will attempt an exact conversion of time intervals.
     *
     * @return string
     */
    public static function timeInterval($interval, $append = null, $human = true)
    {
        $intervals = static::$intervals;
        $elapsed = '';

        if ($append === null) {
            $append = ' ' . Yii::t('modules/notifications', 'ago');
        }
        if ($human) {
            if ($interval <= 0) {
                $elapsed = Yii::t('modules/notifications', 'a moment') . $append;
            } elseif ($interval < 60) {
                $elapsed = Yii::t('modules/notifications', '{n, plural, one{one second} other{# seconds}}',
                        ['n' => $interval]) . $append;
            } elseif ($interval >= 60 && $interval < $intervals['hour']) {
                $interval = floor($interval / $intervals['minute']);
                $elapsed = Yii::t('modules/notifications', '{n, plural, one{one minute} other{# minutes}}',
                        ['n' => $interval]) . $append;
            } elseif ($interval >= $intervals['hour'] && $interval < $intervals['day']) {
                $interval = floor($interval / $intervals['hour']);
                $elapsed = Yii::t('modules/notifications', '{n, plural, one{one hour} other{# hours}}', ['n' => $interval]) . $append;
            } elseif ($interval >= $intervals['day'] && $interval < $intervals['week']) {
                $interval = floor($interval / $intervals['day']);
                $elapsed = Yii::t('modules/notifications', '{n, plural, one{one day} other{# days}}', ['n' => $interval]) . $append;
            } elseif ($interval >= $intervals['week'] && $interval < $intervals['month']) {
                $interval = floor($interval / $intervals['week']);
                $elapsed = Yii::t('modules/notifications', '{n, plural, one{one week} other{# weeks}}', ['n' => $interval]) . $append;
            } elseif ($interval >= $intervals['month'] && $interval < $intervals['year']) {
                $interval = floor($interval / $intervals['month']);
                $elapsed = Yii::t('modules/notifications', '{n, plural, one{one month} other{# months}}',
                        ['n' => $interval]) . $append;
            } elseif ($interval >= $intervals['year']) {
                $interval = floor($interval / $intervals['year']);
                $elapsed = Yii::t('modules/notifications', '{n, plural, one{one year} other{# years}}', ['n' => $interval]) . $append;
            }
        } else {
            $elapsed = static::time2String($interval, $intervals) . $append;
        }
        return $elapsed;
    }

    /**
     * Get elapsed time converted to string
     *
     * Example Output:
     *    1 year 5 months 3 days ago
     *
     * @param integer $timeline elapsed number of seconds
     * @param array   $intervals configuration of time intervals in seconds
     *
     * @return string
     */
    protected static function time2String($timeline, $intervals)
    {
        $output = '';
        foreach ($intervals as $name => $seconds) {
            $num = floor($timeline / $seconds);
            $timeline -= ($num * $seconds);
            if ($num > 0) {
                $output .= $num . ' ' . $name . (($num > 1) ? 's' : '') . ' ';
            }
        }
        return trim($output);
    }

}
?>

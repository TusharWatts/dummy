<?php

namespace api\modules\v1\components;

use Yii;
use yii\base\Component;
use \DateTime;
use \DateTimeZone;

class dateTimeManipulation extends Component{

    /*
    * Function to convert date-time value (any format) to time elapsed string
    * (eg, 9 hrs ago, 3 weeks ago, etc)
    */
    public static function time_elapsed_string($datetime, $full = false) {

            $currentTimestamp = Yii::$app->formatter->asTimestamp('now');
            $agoTimestamp = Yii::$app->formatter->asTimestamp($datetime);

            //If no. of hrs >= 2, show actual time
            if($currentTimestamp - $agoTimestamp >= 2 * 60 * 60)
                return Yii::$app->formatter->asDatetime($datetime, 'php:M d, g:i a');
        
            $now = new DateTime;
            $ago = new DateTime($datetime);
            $diff = $now->diff($ago);

            $diff->w = floor($diff->d / 7);
            $diff->d -= $diff->w * 7;

            $string = array(
                'y' => 'year',
                'm' => 'month',
                'w' => 'week',
                'd' => 'day',
                'h' => 'hour',
                'i' => 'minute',
                's' => 'second',
            );
            foreach ($string as $k => &$v) {
                if ($diff->$k) {
                    $v = $diff->$k . ' ' . $v . ($diff->$k > 1 ? 's' : '');
                } else {
                    unset($string[$k]);
                }
            }

            if (!$full) $string = array_slice($string, 0, 1);
            return $string ? implode(', ', $string) . ' ago' : 'just now';
    }


    /*
    * Function to convert UNIX timestamp to date format as
    * given or default format specified in config/main-local
    * configuration file
    *
    * @parmas:
    *   {int} 'timestamp' : UNIX timestamp
    *   {string} 'timezone' : Timezone to which date is to be convered
    *   {string} 'dateTimeFormat' : Date time format to which date it to be 
    *            converted
    *
    * @return : 
    *       {string | null} : If timezone and date-time format is properly set,
    *       then corresponding date-time format is returned
    *       else null is returned
    */
    public static function convertTimestampToDateFormat($timestamp, $timezone = null, $dateTimeFormat = null){

        $dt = new DateTime();
        $dt->setTimestamp($timestamp);

        //Fetch timezone, if not given then fetch default timezone set
        //in config file
        $time_zone = ($timezone == null) ? Yii::$app->formatter->defaultTimeZone : $timezone;

        //Date-time format, if not given then fetch default timezone set
        //in config file
        $date_time_format = ($dateTimeFormat == null) ? Yii::$app->formatter->dateFormat : $dateTimeFormat;

        if($time_zone && $date_time_format){

                $dt->setTimezone(new DateTimeZone($time_zone));

                //In case date-time format is of the form php:...etc,
                //then we need to explode 
                $date_format_expl = explode(':', $date_time_format);
                $format = ($date_format_expl == null) ? $date_time_format : $date_format_expl[1];

                return $dt->format($format); 

        }

        return null;    

    }

}

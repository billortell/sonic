<?php
namespace Sonic;

/**
 * Util
 *
 * @package Sonic
 * @subpackage Util
 * @author Craig Campbell
 */
class Util
{
    /**
	 * gets a weighted random key from an array of weights
	 *
	 * for example if you pass in
	 * array(0 => 10, 1 => 10, 2 => 20)
	 *
	 * half the time this would return 3
	 * a quarter of the time it would return 1
	 * a quarter of the time it would return 2
	 *
	 * @param array $weights
	 * @return mixed
	 */
	public static function getWeightedRandomKey(array $weights)
	{
        // get the total
        $total_weight = array_sum($weights);

        // choose a random value between 0 and one less than the total
        $random_value = mt_rand(0, $total_weight - 1);

	    // order the weights in order
        asort($weights);

        // subtract the weights in descending order until the number is now negative
        $i = count($weights);
        while ($random_value >= 0) {
            $random_value -= $weights[--$i];
        }

        return $i;
	}

    /**
     * maps english representation of time to seconds
     *
     * @param string
     * @return int
     */
     public static function toSeconds($time)
     {
         // time is already in seconds
         if (is_numeric($time)) {
             return (int) $time;
         }

         $bits = explode(' ', $time);

         if (!isset($bits[1])) {
             throw new Exception('time ' . $time . ' is not in proper format!');
         }

         $time = $bits[0];
         $unit = $bits[1];

         switch ($unit) {
             case 'second':
             case 'seconds':
                return $time;
                break;
             case 'minute':
             case 'minutes':
                return $time * 60;
                break;
             case 'hour':
             case 'hours':
                return $time * 60 * 60;
                break;
            case 'day':
            case 'days':
                return $time * 60 * 60 * 24;
                break;
            case 'week':
            case 'weeks':
                return $time * 60 * 60 * 24 * 7;
                break;
            case 'year':
            case 'years':
                return $time * 60 * 60 * 24 * 365;
                break;
         }
     }
}
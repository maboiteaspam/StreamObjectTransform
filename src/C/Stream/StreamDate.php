<?php
namespace C\Stream;

/**
 * Class StreamDate
 * provides date property transform
 *
 * @package C\BlogData\Fixture
 */
class StreamDate{
    /**
     * update any written $chunk->$prop
     * to set a date string
     * formatted with $format
     *
     * @param $prop
     * @param string $format
     * @param null $date
     * @return \Closure
     */
    public function generate ($prop, $format='Y-m-d H:i', $date=null) {
        $date = $date===null? new \DateTime() :$date;
        return function ($chunk, $stream) use($prop, $format, $date) {
            $chunk->{$prop} = date_format($date, $format);
            $stream->push($chunk);
            return $chunk->{$prop};
        };
    }

    /**
     * update any written $chunk->$prop
     * as a date string or object
     * to format it with $format
     *
     * @param $prop
     * @param $format
     * @return \Closure
     */
    public function format ($prop, $format) {
        return function ($chunk, $stream) use($prop, $format) {
            $chunk->{$prop} = date_format($chunk->{$prop}, $format);
            $stream->push($chunk);
            return $chunk->{$prop};
        };
    }

    /**
     * update any written $chunk->$prop
     * as a date string or object
     * to remove given $interval of time
     *
     * @param $prop
     * @param $interval
     * @param $format
     * @return \Closure
     */
    public function sub ($prop, $interval, $format='Y-m-d H:i') {
        return function ($chunk, $stream) use($prop, $interval, $format) {
            $date = $chunk->{$prop};
            if (is_string($date)) {
                $date = new \DateTime($date);
            }
            date_sub($date, date_interval_create_from_date_string($interval));
            if (is_string($chunk->{$prop})) {
                $date = $date->format($format);
            }
            $chunk->{$prop} = $date;
            $stream->push($chunk);
            return $date;
        };
    }

    /**
     * update any written $chunk->$prop
     * as a date string or object
     * to add given $interval of time
     *
     * @param $prop
     * @param $interval
     * @param $format
     * @return \Closure
     */
    public function add ($prop, $interval, $format='Y-m-d H:i') {
        return function ($chunk, $stream) use($prop, $interval, $format) {
            $date = $chunk->{$prop};
            if (is_string($date)) {
                $date = new \DateTime($date);
            }
            date_add($date, date_interval_create_from_date_string($interval));
            if (is_string($chunk->{$prop})) {
                $date = $date->format($format);
            }
            $chunk->{$prop} = $date;
            $stream->push($chunk);
            return $date;
        };
    }

    /**
     * update any written $chunk->$prop
     * as a date string or object
     * to modify it with $fn
     *
     * if $fn returns a \Callable
     * it is invoked on current stream
     *      $fn($chunk, $stream);
     * and must return the modified $date value
     *
     *
     * @param $prop
     * @param $fn
     * @param string $format
     * @return \Closure
     */
    public function modify ($prop, $fn, $format='Y-m-d H:i') {
        return function ($chunk, $stream) use($prop, $fn, $format) {
            $date = $fn($chunk, $prop, $format);
            if (is_callable($date)) {
                $date = $date($chunk, $stream);
            }
            $chunk->{$prop} = $date;
            $stream->push($chunk);
        };
    }
}

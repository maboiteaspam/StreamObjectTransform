<?php
namespace C\Stream;

/**
 * Class StreamObject
 * provides object's property transform
 *
 * @package C\Stream
 */
class StreamObject{
    /**
     * update any written $chunk
     * to set $chunk->$prop to the provided $value
     *
     * @param $prop
     * @param $value
     * @return \Closure
     */
    public function setProp ($prop, $value) {
        return function ($chunk, $stream) use($prop, $value) {
            $chunk->{$prop} = $value;
            $stream->push($chunk);
            return $chunk->{$prop};
        };
    }
    /**
     * update any written $chunk
     * to increment the value of $chunk->$prop
     *
     * @param $prop
     * @param int $range_start
     * @return \Closure
     */
    public function incProp ($prop, $range_start=0) {
        $inc = new \stdClass();
        $inc->range_start = $range_start;
        return function ($chunk, $stream) use($prop, &$inc) {
            $inc->range_start = $inc->range_start+1;
            $chunk->{$prop} = $inc->range_start;
            $stream->push($chunk);
            return $chunk->{$prop};
        };
    }

    /**
     * update any written $chunk
     * to modify the value of $chunk->$prop
     * with $fn(chunk, $prop);
     *
     * if $fn is_callable
     * it must returns the modified value
     * to update $chunk->$prop
     *
     * @param $prop
     * @param $fn
     * @return \Closure
     */
    public function modify ($prop, $fn) {
        return function ($chunk, $stream) use($prop, $fn) {
            $data = $fn($chunk, $prop);
            if (is_callable($data)) {
                $data = $data($chunk, $stream);
            }
            $chunk->{$prop} = $data;
            $stream->push($chunk);
            return $chunk->{$prop};
        };
    }
}

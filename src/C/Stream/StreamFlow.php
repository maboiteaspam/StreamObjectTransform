<?php
namespace C\Stream;

/**
 * Class StreamFlow
 * provides flow transform
 *
 * @package C\BlogData\Fixture
 */
class StreamFlow{

    /**
     * given any written $chunk
     * clone and pushes $len times
     *
     * @param int $len
     * @return StreamObjectTransform
     */
    public static function duplex ($len=10) {
        return new StreamObjectTransform(function ($chunk, $stream) use($len) {
            for ($i=0;$i<$len;$i++) {
                $cloned = is_object($chunk)?clone($chunk):$chunk;
                $stream->push($cloned);
            }
        });
    }

    /**
     * given any written $chunk
     * pushes $len times
     *
     * @param int $len
     * @return StreamObjectTransform
     */
    public static function repeater ($len=10) {
        return new StreamObjectTransform(function ($chunk, $stream) use($len) {
            for ($i=0;$i<$len;$i++) {
                $stream->push($chunk);
            }
        });
    }

    /**
     * filter any written $chunk
     * to push only
     * those matching $prop against $match
     *
     * @param $prop
     * @param $match
     * @return StreamObjectTransform
     */
    public static function filter ($prop, $match) {
        return new StreamObjectTransform(function ($chunk, $stream) use($prop, $match) {
            if ($chunk->{$prop}==$match) {
                $stream->push($chunk);
            } else if (is_array($match) && in_array($chunk->{$prop}, $match)) {
                $stream->push($chunk);
            }
        });
    }
}

<?php
namespace C\Stream;

class StreamImgUrl{
    public function __construct() {}

    public function imgUrl ($prop, $c) {
        return function ($chunk, $stream) use($prop, $c) {
            $chunk->{$prop} = 'some';
            $stream->push($chunk);
            return $chunk->{$prop};
        };
    }
}

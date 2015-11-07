<?php
namespace C\Stream;

class StreamObject{
    public function setProp ($prop, $value) {
        return function ($chunk, $stream) use($prop, $value) {
            $chunk->{$prop} = $value;
            $stream->push($chunk);
            return $chunk->{$prop};
        };
    }
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
    public function appendTo ($prop, $fn) {
        return function ($chunk, $stream) use($prop, $fn) {
            $chunk->{$prop} = $fn($chunk->{$prop}, $chunk, $prop);
            $stream->push($chunk);
            return $chunk->{$prop};
        };
    }
}

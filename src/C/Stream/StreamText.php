<?php
namespace C\Stream;

class StreamText{
    protected $ipsum;
    public $nicknames;
    public function __construct() {
        $this->ipsum = new \joshtronic\LoremIpsum();
        $this->nicknames = [
            'maboiteaspam',
            'Eric Cartman',
            'Kenny McCormick',
            'Stanley Kubrick',
            'Stanley Marsh',
            'John Doe',
            'John Connor',
        ];
    }
    public function words ($prop, $c) {
        $ipsum = $this->ipsum;
        return function ($chunk, $stream) use($ipsum, $prop, $c) {
            $chunk->{$prop} = $ipsum->words($c);
            $stream->push($chunk);
            return $chunk->{$prop};
        };
    }
    public function sentences ($prop, $c) {
        $ipsum = $this->ipsum;
        return function ($chunk, $stream) use($ipsum, $prop, $c) {
            $chunk->{$prop} = $ipsum->sentences($c);
            $stream->push($chunk);
            return $chunk->{$prop};
        };
    }
    public function enum ($prop, $enumValues) {
        $enum = [
            'values'=>$enumValues,
            'cntValues'=>count($enumValues),
            'consumed'=>[],
        ];
        return function ($chunk, $stream) use($prop, &$enum) {
            $nicknames = $result = array_diff($enum['values'], $enum['consumed']);
            $chunk->{$prop} = $nicknames[rand(0, $enum['cntValues']-1)];
            $consumed[] = $chunk->{$prop};
            if (count($enum['consumed'])===$enum['cntValues']) {
                $enum['consumed'] = [];
            }
            $stream->push($chunk);
            return $chunk->{$prop};
        };
    }
}

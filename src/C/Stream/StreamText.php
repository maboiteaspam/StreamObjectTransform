<?php
namespace C\Stream;

/**
 * Class StreamText
 * provides text generation stream object transform
 *
 * text generation is implemented
 * with this awesome module
 * joshtronic\LoremIpsum
 *
 * @package C\Stream
 */
class StreamText{
    /**
     * @var \joshtronic\LoremIpsum
     */
    protected $ipsum;
    /**
     * @var array
     */
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

    /**
     * update any written $chunk
     * to set $prop with generated $c words
     *
     * @param $prop
     * @param $c
     * @return \Closure
     */
    public function words ($prop, $c) {
        $ipsum = $this->ipsum;
        return function ($chunk, $stream) use($ipsum, $prop, $c) {
            $chunk->{$prop} = $ipsum->words($c);
            $stream->push($chunk);
            return $chunk->{$prop};
        };
    }

    /**
     * update any written $chunk
     * to set $prop with generated $c sentences
     *
     * @param $prop
     * @param $c
     * @return \Closure
     */
    public function sentences ($prop, $c) {
        $ipsum = $this->ipsum;
        return function ($chunk, $stream) use($ipsum, $prop, $c) {
            $chunk->{$prop} = $ipsum->sentences($c);
            $stream->push($chunk);
            return $chunk->{$prop};
        };
    }

    /**
     * update any written $chunk
     * to set $prop with one value within $enumValues
     *
     * values of $enumValues are distributed
     * uniquely until they are all consumed.
     * consumed values of $enumValues are then
     * reset to be re used.
     *
     * @param $prop
     * @param $enumValues
     * @return \Closure
     */
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

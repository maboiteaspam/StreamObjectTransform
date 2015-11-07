<?php
namespace C\Stream;

/**
 * Class StreamConcat
 * provides appender transform
 *
 * @package C\BlogData\Fixture
 */
class StreamConcat{
    /**
     * append any written $chunk
     * to $refArray
     *
     * @param \ArrayObject $refArray
     * @return \Closure
     */
    public function appendTo (\ArrayObject $refArray) {
        return function ($chunk, $stream) use($refArray) {
            $refArray->append($chunk);
            $stream->push($chunk);
        };
    }
}

<?php
namespace C\Stream;

/**
 * Class StreamObjectTransform
 * is the pipes implementation
 * to simulate stream-like api
 *
 * @package C\Stream
 */
class StreamObjectTransform{

    /**
     * @var \Closure|null
     */
    protected $onData;
    /**
     * @var \Closure|null
     */
    protected $onFlush;
    /**
     * @var array
     */
    public $streams = [];

    /**
     * @param \Closure|null $onData
     * @param \Closure|null $onFlush
     */
    public function __construct ($onData=null, $onFlush=null) {
        $this->onData = $onData
            ? $onData
            // a stream transform function
            : function ($chunk, $stream) {
                // must push $chunk
                // if, it want to forward the data
                /* @var $stream StreamObjectTransform */
                $stream->push($chunk);
                // sometimes, it want to skip them from following processing.
            };
        $this->onFlush = $onFlush
            ? $onFlush
            : function () {
                // is the function called when
                // $stream->write(null) is invoked
                // to signal the end of the underlying data stream
            };
    }

    /**
     * pipe $stream
     * $stream can be one of
     * StreamObjectTransform or \Callable
     *
     * if $stream is_callable, it is transformed
     * into a new StreamObjectTransform($stream) instance.
     *
     * Returns $this pipe instance.
     *
     * @param StreamObjectTransform|\Callable $stream
     * @return StreamObjectTransform
     */
    public function pipe($stream) {

        $stream = $stream instanceof StreamObjectTransform
            ? $stream
            : new StreamObjectTransform($stream);

        $this->streams[] = $stream;
        return $this;
    }

    /**
     * remove given $stream object
     *
     * @param mixed $stream
     */
    public function unpipe($stream) {
        $this->streams = array_diff($this->streams, array($stream));
    }

    /**
     * Write $some data to underlying $streams
     *
     * @param mixed $some
     */
    public function push($some) {
        foreach($this->streams as $stream) {
            /* @var $stream StreamObjectTransform */
            $stream->write($some);
        }
    }

    /**
     * Write $some data on this $stream
     *
     * @param mixed $some
     */
    public function write($some) {
        if ($some!==NULL) {
            if ($this->onData) {
                $boundCl = $this->onData;
                $boundCl($some, $this);
            }

        } else {
            $this->flush($some);
        }
    }

    /**
     * Flush the stream.
     *
     * @param $remains
     */
    public function flush($remains) {
        if ($this->onFlush) {
            // Should it emit close / end event here ?
            $boundCl = $this->onFlush;
            $boundCl($remains, $this);
        }
    }

    /**
     * @param $some
     */
    public function writeFlush($some) {
        $this->write($some);
        $this->write(NULL);
    }

    #region this should probably belong to an EventEmitter trait
    protected $events = [];
    public function on($event, $then) {
        if (!isset($this->events[$event])) {
            $this->events[$event] = [];
        }
        $this->events[$event][] = $then;
    }
    public function off($event, $then) {
        if (!isset($this->events[$event])) {
            $this->events[$event] = [];
        }
        $this->events[$event] = array_diff($this->events[$event], array($then));
    }
    public function emit($event) {
        $args = func_get_args();
        array_shift($args);
        if (isset($this->events[$event])) {
            foreach ($this->events[$event] as $handler) {
                call_user_func_array($handler, $args);
            }
        }
    }
    #endregion


    /**
     * helper to create new stream
     * @param \Closure|null $onData
     * @param \Closure|null $onFlush
     * @return StreamObjectTransform
     */
    public static function through ($onData=null, $onFlush=null) {
        return new StreamObjectTransform($onData, $onFlush);
    }
}


/**
 * @param \Closure|null $onData
 * @param \Closure|null $onFlush
 * @return StreamObjectTransform
 */
function through ($onData=null, $onFlush=null) {
    return new StreamObjectTransform($onData, $onFlush);
}
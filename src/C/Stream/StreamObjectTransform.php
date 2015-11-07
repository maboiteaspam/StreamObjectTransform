<?php
namespace C\Stream;

class StreamObjectTransform{

    protected $onData;
    protected $onFlush;
    public $streams = [];

    public function __construct ($onData=null, $onFlush=null) {
        $this->onData = $onData ? $onData : function ($chunk) {$this->push($chunk);};
        $this->onFlush = $onFlush ? $onFlush : function () {};
    }

    /**
     * @param mixed $stream
     * @return StreamObjectTransform
     */
    public function pipe($stream) {
        $stream = $stream instanceof StreamObjectTransform ? $stream : new StreamObjectTransform($stream);
        $this->streams[] = $stream;
        return $this;
    }

    /**
     * @param mixed $stream
     */
    public function unpipe($stream) {
        $this->streams = array_diff($this->streams, array($stream));
    }

    /**
     * @param mixed $some
     */
    public function push($some) {
        foreach($this->streams as $stream) {
            $stream->write($some);
        }
    }

    /**
     * @param mixed $some
     */
    public function write($some) {
        if ($this->onData) {
            if ($some!==NULL) {
                $boundCl = $this->onData;
                $boundCl($some, $this);

            } else {
                // Should it emit close / end event here ?
                $boundCl = $this->onFlush;
                $boundCl($some, $this);

            }
        }
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
     * @param null $onData
     * @param null $onFlush
     * @return StreamObjectTransform
     */
    public static function through ($onData=null, $onFlush=null) {
        return new StreamObjectTransform($onData, $onFlush);
    }
}


/**
 * @param null $onData
 * @param null $onFlush
 * @return StreamObjectTransform
 */
function through ($onData=null, $onFlush=null) {
    return new StreamObjectTransform($onData, $onFlush);
}
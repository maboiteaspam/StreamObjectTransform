<?php

class StreamObjectTransform{

    protected $onData;
    protected $onFlush;
    protected $streams = [];

    public function __construct ($onData=null, $onFlush=null) {
        $this->onData = $onData ? $onData : function ($chunk) {$this->push($chunk);};
        $this->onFlush = $onFlush ? $onFlush : function () {};
    }

    /**
     * @param StreamObjectTransform $stream
     * @return StreamObjectTransform
     */
    public function pipe($stream) {
        $this->streams[] = $stream;
        return $stream;
    }

    /**
     * @param StreamObjectTransform $stream
     */
    public function unpipe($stream) {
        $this->streams = array_diff($this->streams, array($stream));
    }

    /**
     * @param mixed $some
     */
    protected function push($some) {
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
                $boundCl = $this->onData->bindTo($this, $this);
                $boundCl($some);

            } else {
                // Should it emit close / end event here ?
                $boundCl = $this->onFlush->bindTo($this, $this);
                $boundCl($some);

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
}


/**
 * @param null $onData
 * @param null $onFlush
 * @return StreamObjectTransform
 */
function through ($onData=null, $onFlush=null) {
    return new StreamObjectTransform($onData, $onFlush);
}
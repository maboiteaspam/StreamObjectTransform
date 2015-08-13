# StreamObjectTransform
port of rvagg/through2 object stream to PHP.

### Example

```
<?php

include("StreamObjectTransform.php");


$stream = through();

$stream->pipe(through(function ($chunk) {
    var_dump('--------');
    $this->push($chunk);
}))->pipe(through(function ($chunk) {
    var_dump('________');
    $this->push($chunk);
}))->pipe(through(function ($chunk) {
    $this->push($chunk);
    var_dump($chunk);
}))
;

$stream->write(['some', 'objects']);
```


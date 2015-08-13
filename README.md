# StreamObjectTransform
port of https://github.com/rvagg/through2 object stream to PHP.

It does not aim to be as powerful as the node stream interface, 
it s more about the programming pattern that i found really powerful.

### Example

```php
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


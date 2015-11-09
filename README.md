# StreamObjectTransform
port of https://github.com/rvagg/through2 object stream to PHP.

It does not aim to be as powerful as the node stream interface, 
it s more about the programming pattern that i found really powerful.

### Usage

require dependencies and start

to pipe object streams

to generate and transform data.


```php
<?php
require 'vendor/autoload.php';

use \C\Stream\StreamFlow;
use \C\Stream\StreamObjectTransform;


// create a stream
StreamObjectTransform::through()

    // pipe data and flow transformers
    ->pipe(function($chunk){
        var_dump("not duplexed $chunk");
        $stream->push($chunk); // push the chunk to the conneccted pipes.
        // sometimes itis interesting to not push the chunk.
    })

    // write a data or an objeect to stream
    ->write('some');

```

## Install

Until the module is published,
add this repository to the `composer` file
then run `composer update`.

or run `c2-bin require-gh`

```
c2-bin require-gh -m=maboiteaspam/StreamObjectTransform
```

Read more about [c2-bin](https://github.com/maboiteaspam/c2-bin)


### Example

Stream `some`,

then `demultiplex` it 2 times

```php
<?php
require 'vendor/autoload.php';

use \C\Stream\StreamFlow;
use \C\Stream\StreamObjectTransform;

// create a stream
StreamObjectTransform::through()

    // pipe it to a demultiplexer
    ->pipe( StreamFlow::demultiplex(2)

            // transform demultiplexed data
            ->pipe(function($chunk){
                var_dump("demultiplexed $chunk");
                $stream->push($chunk);
            })
    )
    // transform data of the initial stream
    ->pipe(function($chunk){
        var_dump("not demultiplexed $chunk");
        $stream->push($chunk);
    })
    ->write('some');

```


### API

Class __StreamObjectTransform__

Apply transform on streamed objects.

__StreamObjectTransform:: pipe ($stream)__

    /*
     * pipe $stream
     *      $stream can be one of
     *      StreamObjectTransform or \Callable
     *
     * if $stream is_callable, it is transformed
     * into a new StreamObjectTransform($stream) instance.
     *
     * Returns $this pipe instance.
     */


__StreamObjectTransform:: write ($some)__

    /**
     * Write $some data on this $stream.
     * It will call the transform,
     * which can
     * transform, multiply, pass or drop
     * the stream chunk of data.
     *
     * to drop the chunk, do not call push.
     *
     * @param mixed $some
     */


__StreamObjectTransform:: push ($some)__

    /**
     * Write $some data to underlying $streams.
     *
     * please consider this :
     * It should be __protected__, but for some reason,
     * i had to make it public, and also, pass the $stream
     * instance along the onData callback.
     *
     * I don t recommend to use it outside of the transform callbacks.
     *
     * @param mixed $some
     */


Class __StreamConcat__

Watch for streamed objects and append them to array.

Class __StreamDate__

Manipulate date of streamed objects.

Class __StreamFlow__

Control the flow of objects passed to the connected pipes.

Class __StreamObject__

Manipulate streamed chunks as objects and modify them.

Class __StreamText__

Generate text, words, sentences, enums and apply them to streamed chunks.



### Read more

Read more with this example, it s a bit simplified

but gathers multiple elements to provide a real example of use.

##### Generator

If you define and use a class help to generate entities such,

__File:__ /C/Fixture/Generator.php

```php
<?php
namespace C\Fixture;

use C\Stream\StreamConcat;
use C\Stream\StreamFlow;

class Generator{

    /**
     * push $len times
     * a clone of $what
     * modified with $transform
     * returns the resulting array $results of data
     *
     * @param $what
     * @param $transform
     * @param int $len
     * @return \ArrayObject
     */
    public static function generate ($what, $transform, $len=10) {

        $results = new \ArrayObject();

        $concat = new StreamConcat();

        StreamFlow::demultiplex($len)
            ->pipe($transform)
            ->pipe($concat->appendTo($results))
            ->write($what);

        return $results;
    }

}
```

It will demultiplex `$len` times the provided `$what` data.

For each demultiplexed data, it transforms is with `$how`.

It then pipe demultiplexed data to a resulting array `$results`.

##### Fixture Generator

This helps to write simple `fixtures` generators such as,

```php
<?php

use \C\Fixture\Generator;

use \C\BlogData\Entity\Entry as EntryEntity;
use \C\BlogData\Entity\Comment as CommentEntity;

use \C\BlogData\Modifier\Entry as EntryModifier;
use \C\BlogData\Modifier\Comment as CommentModifier;

$entry      = new EntryModifier();
$comment    = new CommentModifier();

/**
 * generate a hundred entries
 * each entry has 2 comments
 *
 * their status (VISIBLE, HIDDEN)
 * is random.
 *
 */
return Generator::generate( new EntryEntity(), // the model object to demultiplex
    $entry->transform() // the stream transform to apply to each object
        ->pipe( $object->modify('comments', // update comments property
            function ($chunk) use($comment) {
                return Generator::generate( new CommentEntity(), // generate 2 comments
                    $comment->transform($chunk->id), // forge them with this stream transform
                    2 );
            })
        )
    , 100); // count of objects to create
```

In this example a hundred `Entry` objects are created.

Each object get its properties populated, 2 `Comment` are also attached.

It finally returns an array of forged `Entity` objects.

Ready to use.

##### Fixture Modifier

`EntryModifier` and `CommentModifier` are streams object,

they are qualified to receive respective kind of object,

and forge their properties with help of stream transforms,

```php
namespace \C\BlogData\Modifier\Entry;

use \C\Stream\StreamObject;
use \C\Stream\StreamObjectTransform;
use \C\BlogData\Entity\Entry;

$object = new StreamObject();

StreamObjectTransform::through()

    ->pipe($object->incProp('id', $range_start))
    ->pipe($object->setProp('blog_entry_id', $range_start))

    ->write(new Entry());
```

- https://github.com/maboiteaspam/BlogData/blob/master/src/C/Modifier/Entry.php
- https://github.com/maboiteaspam/BlogData/blob/master/src/C/Modifier/Comment.php

##### Object Modifiers

Object modifiers such `StreamObject`, `StreamDate` are streams transforms
to update streamed objects.

```php
<?php
namespace C\Stream;

/**
 * Class StreamDate
 * provides date property transform
 *
 * @package C\BlogData\Fixture
 */
class StreamDate{
    /**
     * update any written $chunk->$prop
     * to set a date string
     * formatted with $format
     *
     * @param $prop
     * @param string $format
     * @param null $date
     * @return \Closure
     */
    public function generate ($prop, $format='Y-m-d H:i', $date=null) {
        $date = $date===null? new \DateTime() :$date;
        return function ($chunk, $stream) use($prop, $format, $date) {
            $chunk->{$prop} = date_format($date, $format);
            $stream->push($chunk);
            return $chunk->{$prop};
        };
    }
    //....
}
```

- https://github.com/maboiteaspam/StreamObjectTransform/blob/master/src/C/Stream/StreamText.php
- https://github.com/maboiteaspam/StreamObjectTransform/blob/master/src/C/Stream/StreamFlow.php

##### Fixture Entity

`EntryEntity` and `CommentEntity` are simple `PO` class.

```php
<?php
namespace C\BlogData\Entity;

class Entry{

    public $id;
    public $created_at;
    public $updated_at;
    public $title;
    public $author;
    public $img_alt;
    public $content;
    public $status;
    /**
     * @var array C\BlogData\Entity\Comment
     */
    public $comments = [];
}
```

- https://github.com/maboiteaspam/BlogData/blob/master/src/C/Entity/Entry.php
- https://github.com/maboiteaspam/BlogData/blob/master/src/C/Entity/Comment.php

##### Conclusion

Stream-Transforms are really cool : )

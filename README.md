# StreamObjectTransform
port of https://github.com/rvagg/through2 object stream to PHP.

It does not aim to be as powerful as the node stream interface, 
it s more about the programming pattern that i found really cool.

See yourself!


### Usage

require dependencies, then start
to connect pipe to stream
and transforms objects.


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
        // sometimes it is interesting to not push the chunk.
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

If you define and use a class helper to generate entities such,

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

For each chunk, it transforms it with `$transform`.

It then pipe each chunks to a resulting array `$results`.

##### Fixture Generator

With this new helper on hand, a `fixtures` generator can look like this,

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
return Generator::generate(
    new EntryEntity(),      // the model object to demultiplex

    $entry->transform()     // the stream transform to apply to each object
        ->pipe( $object->modify('comments', function ($chunk) use($comment) { // update comments property
                return Generator::generate(
                    new CommentEntity(), // generate 2 comments
                    $comment->transform($chunk->id), // forge them with this stream transform
                    2 );
        }))
    , 100); // count of objects to create
```

In this example a hundred `Entry` objects are created.

Each object get its properties populated by
`entry->transform()`.

A pipe is then connected to generate and attach 2 `Comment`
to the current `Entry` chunk.

Each `Comment` is transformed by `comment->transform($chunk->id)`.
The entry id is used to distribute ids of the `Comment` objects.

It finally returns an array of forged `Entity` objects.

Ready to use.

##### Fixture Modifier

`EntryModifier` and `CommentModifier` are streams object,

they are qualified to receive respective kind of object,

and forge their properties with help of stream transforms,

```php
<?php
namespace C\BlogData\Modifier;

use \C\Stream\StreamImgUrl;
use \C\Stream\StreamDate;
use \C\Stream\StreamText;
use \C\Stream\StreamObject;
use \C\Stream\StreamObjectTransform;

/**
 * Class Entry
 * provides stream to forge
 * Entry entities
 *
 * @package C\BlogData\Modifier
 */
class Entry{
    /**
     * return a stream object
     * to transform any pushed $entry entity
     *
     * @param int $range_start
     * @return mixed
     */
    public function transform ($range_start=0) {

        $imgUrlGenerator = new StreamImgUrl();
        $dateGenerator = new StreamDate();
        $textGenerator = new StreamText();
        $object = new StreamObject();

        return StreamObjectTransform::through()
            ->pipe($object->incProp('id', $range_start))
            ->pipe($dateGenerator->generate('created_at'))
            ->pipe($dateGenerator->modify('created_at', function ($chunk, $prop) use($dateGenerator){
                return $dateGenerator->sub($prop, "{$chunk->id} days + 1*{$chunk->id} hours");
            }))
            ->pipe($dateGenerator->generate('updated_at'))
            ->pipe($dateGenerator->modify('updated_at', function ($chunk, $prop) use($dateGenerator){
                return $dateGenerator->sub($prop, "{$chunk->id} days + 1*{$chunk->id} hours");
            }))
            ->pipe($textGenerator->enum('author', $textGenerator->nicknames))
            ->pipe($textGenerator->enum('status', ['VISIBLE', 'HIDDEN']))
            ->pipe($textGenerator->words('title', rand(2, 5)))
            ->pipe($textGenerator->sentences('content', rand(1, 3)))
            ->pipe($imgUrlGenerator->imgUrl('img_alt', rand(1, 3)))
            ;
    }
}
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

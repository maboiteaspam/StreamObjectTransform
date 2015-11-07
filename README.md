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
    })

    // write a data or an objeect to stream
    ->write('some');

```

## Install

Use `composer` to install this library.

```
php composer require git@github.com:maboiteaspam/Welcome.git
```


### Example

Stream `some`,

then `duplex` it 2 times

```php
<?php
require 'vendor/autoload.php';

use \C\Stream\StreamFlow;
use \C\Stream\StreamObjectTransform;

// create a stream
StreamObjectTransform::through()

    // pipe it to a duplexer
    ->pipe( StreamFlow::duplex(2)

            // transform duplexed data
            ->pipe(function($chunk){
                var_dump("duplexed $chunk");
            })
    )
    // transform data of the initial stream
    ->pipe(function($chunk){
        var_dump("not duplexed $chunk");
    })
    ->write('some');

```


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
     * returns the resulting array of data
     *
     * @param $what
     * @param $transform
     * @param int $len
     * @return \ArrayObject
     */
    public static function generate ($what, $transform, $len=10) {

        $results = new \ArrayObject();

        $concat = new StreamConcat();

        StreamFlow::duplex($len)
            ->pipe($transform)
            ->pipe($concat->appendTo($results))
            ->write($what);

        return $results;
    }

}
```

It will duplex `$len` times the provided `$what` data.

For each duplexed data, it transforms is with `$how`.

It then pipe duplexed data to a resulting array `$results`.

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
return Generator::generate( new EntryEntity(), // the model object to duplex
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

use \C\Stream\StreamObject;
use \C\Stream\StreamObjectTransform;
use \C\BlogData\Modifier\Entry;

$object = new StreamObject();

StreamObjectTransform::through()

    ->pipe($object->incProp('id', $range_start))
    ->pipe($object->setProp('blog_entry_id', $range_start))

    ->write(new Entry());
```

- https://github.com/maboiteaspam/BlogData/blob/src/C/Modifier/Entry.php
- https://github.com/maboiteaspam/BlogData/blob/src/C/Modifier/Comment.php

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

- https://github.com/maboiteaspam/BlogData/blob/src/C/Entity/Entry.php
- https://github.com/maboiteaspam/BlogData/blob/src/C/Entity/Comment.php

##### Conclusion

Stream-Transforms are really cool : )

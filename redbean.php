<?php

require __DIR__ . '/vendor/autoload.php';

\RedBeanPHP\R::setup();
$book = \RedBeanPHP\R::dispense( 'book' );
$book->title = 'Learn to Program';
$book->rating = 10;
$id = \RedBeanPHP\R::store( $book );

print_r($book);
print_r(\RedBeanPHP\R::load('book', $id));

echo "ho ottenuto {$book->rating}";
<?php
$file='text.txt';
$current = file_get_contents($file);

$current .= "_execution";
file_put_contents($file, $current);

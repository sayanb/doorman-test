<?php

require __DIR__ . "/vendor/autoload.php";

use function Amp\ParallelFunctions\parallelMap;
use function Amp\Promise\wait;

exec("rm *.temp > /dev/null 2> /dev/null");

$responses = wait(parallelMap([
    1, 4, 5, 3, 7,
], function ($delay) {
    sleep($delay);
    touch("{$delay}.temp");

    return "response from child in {$delay} seconds";
}));

print_r($responses);

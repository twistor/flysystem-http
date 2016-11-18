<?php

return Symfony\CS\Config\Config::create()
    ->level(Symfony\CS\FixerInterface::SYMFONY_LEVEL)
    ->fixers(['ordered_use', 'short_array_syntax', 'concat_with_spaces', '-phpdoc_inline_tag'])
    ->finder(Symfony\CS\Finder\DefaultFinder::create()
    ->in(__DIR__));

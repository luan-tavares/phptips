<?php

include __DIR__ ."/vendor/autoload.php";
include __DIR__ ."/help.php";


$b = new ReflectionFunction($a);

dd($b->getClosureScopeClass(), $b->getStartLine() ." to ". $b->getEndLine(), $b->isClosure());

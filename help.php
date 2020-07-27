<?php

$a = function () {
    return true;
};
function b()
{
    return function () {
        return false;
    };
}

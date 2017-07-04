<?php
function myGenerator() {
    for ($i = 1; $i <= 3; $i++) {
        yield $i;
    }
}
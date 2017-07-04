<?php
use Kahlan\Filter\Filter;
Filter::register('exclude.namespaces', function ($chain) {
    $defaults = ['Doctrine\Common\Annotations'];
    $excluded = $this->args()->get('exclude');
    $this->args()->set('exclude', array_unique(array_merge($excluded, $defaults)));
    return $chain->next();
});
Filter::apply($this, 'interceptor', 'exclude.namespaces');
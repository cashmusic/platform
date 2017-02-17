<?php

namespace Cashmusic\Elements\Interfaces;

interface StatesInterface
{
    /**
     * State router. Ideally this will have a switch/case based on $_REQUEST['state'] that
     * returns an array with template name and data. Data is merged into the element_data array.
     *
     * [
     * 'template' => 'default',
     * 'data' => [...]
     * ]
     *
     * @param $callback
     * @return array
     */
    public function router($callback);
}

?>
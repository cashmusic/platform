<?php

namespace Cashmusic\Elements\Interfaces;

interface DataInterface
{
    /**
     * Do whatever you want to do here, so long as it returns an array of key=>values that
     * you want to be merged into element_data. Minimum getConnections()
     *
     * @return array
     */
    public function getConnections();
}

?>
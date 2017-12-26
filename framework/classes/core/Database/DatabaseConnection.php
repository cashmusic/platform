<?php

namespace CASHMusic\Core\Database;

use Pixie\Connection;
use Viocon\Container;

class DatabaseConnection extends Connection {
    public function __construct(\PDO $connection)
    {
        $this->adapter = $connection->getAttribute(\PDO::ATTR_DRIVER_NAME);
        $this->setPdoInstance($connection);

        $this->container = new Container();
        $this->eventHandler = $this->container->build('\\Pixie\\EventHandler');
    }
}
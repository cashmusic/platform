<?php

namespace Pixie\QueryBuilder;

class Transaction extends QueryBuilderHandler
{

    /**
     * Commit the database changes
     */
    public function commit()
    {
        $this->pdo->commit();
        throw new TransactionHaltException();
    }

    /**
     * Rollback the database changes
     */
    public function rollback()
    {
        $this->pdo->rollBack();
        throw new TransactionHaltException();
    }
}

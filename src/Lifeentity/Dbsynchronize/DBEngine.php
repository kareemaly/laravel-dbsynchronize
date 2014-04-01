<?php namespace Lifeentity\Dbsynchronize;

class DBEngine {

    /**
     * @var Table[]
     */
    protected $tables;

    /**
     * @param Table[] $tables
     */
    public function __construct($tables)
    {
        $this->tables = $tables;
    }

    /**
     * Synchronize tables
     */
    public function synchronize()
    {
        foreach($this->tables as $table)
        {
            // If table doesn't exists then create it
            if (! $table->exists())
            {
                $table->create();
            }

            // Match columns
            else
            {
                $table->matchColumns();
            }
        }
    }

} 
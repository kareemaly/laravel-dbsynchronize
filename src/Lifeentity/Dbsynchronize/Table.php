<?php namespace Lifeentity\Dbsynchronize;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class Table {

    /**
     * @var string
     */
    protected $name;

    /**
     * @var array
     */
    protected $columns;

    /**
     * @param $name
     * @param $columns
     */
    public function __construct($name, $columns)
    {
        $this->name = $name;
        $this->columns = $columns;
    }

    /**
     * @param $array
     * @return Table[]
     */
    public static function make($array)
    {
        $tables = array();

        foreach($array as $name => $columns)
        {
            $tables[] = new static($name, $columns);
        }

        return $tables;
    }

    /**
     * @return array
     */
    public function getAttributes()
    {
        return array_keys($this->columns);
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return boolean
     */
    public function exists()
    {
        return Schema::hasTable($this->getName());
    }

    /**
     * Match columns in the database
     */
    public function matchColumns()
    {
        Schema::table($this->name, function($table)
        {
            $table->engine = 'InnoDB';
            // Loop through all table columns and check if they exists in the database already.
            // If one of them doesn't exists then create it
            foreach($this->columns as $column => $generator)
            {
                // If column doesn't exist then create it
                if(! Schema::hasColumn($this->getName(), $column))
                {
                    call_user_func($generator, $table);
                }
            }

            foreach($this->getTableColumns() as $dbColumn)
            {
                if(! isset($this->columns[$dbColumn->Field]))
                {
                    $table->dropColumn($dbColumn->Field);
                }
            }
        });
    }

    /**
     * @return mixed
     */
    protected function getTableColumns()
    {
        return DB::select('SHOW COLUMNS FROM '.DB::getTablePrefix().$this->name);
    }

    /**
     * Create table
     */
    public function create()
    {
        Schema::create($this->name, function($table)
        {
            $table->engine = 'InnoDB';
            foreach($this->columns as $generator)
            {
                call_user_func($generator, $table);
            }
        });
    }

} 
<?php

namespace AOWD;

use AOWD\DataType;
use AOWD\Exceptions\CompleteTransaction;
use AOWD\Exceptions\BeginTransaction;
use AOWD\Exceptions\ConnectError;
use AOWD\Exceptions\DatabasePath;
use AOWD\Exceptions\QueryError;
use AOWD\Exceptions\SetTable;

class SQLite
{
    private string $table_name;
    private ?\SQLite3 $connection;
    private readonly string $db_location;
    private string $sqlite_extension = 'sqlite3';
    private bool $is_transaction = false;

    /**
     * Table columns
     * @var array<mixed>
     */
    private array $columns = [];

    /**
     * Table indices
     * @var array<mixed>
     */
    private array $indices = [];

    /**
     * Default pragmas
     * @var array<int|string|boolean>
     */
    private array $pragmas = [
        'journal_mode' => 'WAL',
        'busy_timeout' => 5000,
        'synchronous' => 'NORMAL',
        'cache_size' => 2000,
        'temp_store' => 'memory',
        'foreign_keys' => true
    ];

    /**
     * Class constructor
     * @param string $db_location
     * @param array<int|string|boolean> $pragmas
     */
    public function __construct(string $db_location, array $pragmas = [])
    {
        $location_info = pathinfo($db_location);
        $this->connection = null;

        // Check if path provided is a valid directory
        if (!isset($location_info['dirname']) || !is_dir($location_info['dirname'])) {
            throw new DatabasePath("Path provided is not a valid directory", 1);
        }

        // Set file location
        $this->db_location = $location_info['dirname'] . '/' .
            $location_info['filename'] . '.' .
            $this->sqlite_extension;

        // Set pragmas
        foreach ($pragmas as $key => $value) {
            if (isset($this->pragmas[$key]) && !empty($value) && is_scalar($value)) {
                $this->pragmas[$key] = $value;
            }
        }

        // Open connection to database
        $this->open();
    }

    /**
     * Open connection to database
     * @return void
     */
    private function open(): void
    {
        if (!$this->connection instanceof \SQLite3) {
            try {
                // New database connection
                $this->connection = new \SQLite3($this->db_location);

                // Enable exceptions
                $this->connection->enableExceptions(true);

                // Set pregmas on database
                foreach ($this->pragmas as $key => $value) {
                    $this->connection->exec("PRAGMA $key = $value;");
                }
            } catch (\Exception $e) {
                throw new ConnectError($e->getMessage(), 1);
            }
        }
    }

    /**
     * Get database location
     * @return string
     */
    public function getDatabaseLocation(): string
    {
        return $this->db_location;
    }

    /**
     * Close connection to database
     * @return void
     */
    public function close(): void
    {
        if ($this->connection instanceof \SQLite3) {
            $this->connection->close();
        }
    }

    /**
     * Begin database write transaction
     * @return void
     */
    public function beginWriteTransaction(): void
    {
        $this->open();

        if ($this->connection instanceof \SQLite3) {
            try {
                $this->connection->exec('BEGIN IMMEDIATE TRANSACTION;');
            } catch (\Exception $e) {
                throw new BeginTransaction($e->getMessage());
            }
        }
    }

    /**
     * Complete database write transaction
     * @return void
     */
    public function completeWriteTransaction(): void
    {
        $this->open();

        if ($this->is_transaction && $this->connection instanceof \SQLite3) {
            try {
                $this->connection->exec('COMMIT;');
            } catch (\Exception $e) {
                $this->is_transaction = false;
                throw new CompleteTransaction($e->getMessage());
            }
        }
    }

    /**
     * Query is a write statement
     * @param  string $query
     * @return boolean
     */
    public function queryIsWriteStatement(string $query): bool
    {
        // Write statement keys
        $write_keys = ['CREATE', 'UPDATE', 'ALTER', 'DROP', 'INSERT', 'DELETE'];

        // Remove table names and values from query
        $prep_query = preg_replace('/`(.*)`|\'(.*)\'|"(.*)"|:[a-zA-Z0-9]+/', '', strtoupper($query));

        // Check statement for key matches
        foreach ($write_keys as $key) {
            if (preg_match('/' . $key . '/', (string) $prep_query)) {
                return true;
            }
        }

        return false;
    }


    /**
     * Return table name
     * @return string
     */
    public function getCurrentTableName(): string
    {
        if (empty($this->table_name)) {
            throw new SetTable("Table name has not been set", 1);
        }

        return $this->table_name;
    }

    /**
     * Return the current set table name
     * @return string
     */
    public function setTableName(string $table_name): string
    {
        $this->table_name = $table_name;
        return $this->getCurrentTableName();
    }

    /**
     * Create column
     * @param  string       $column_name
     * @param  DataType       $type
     * @param  bool|boolean $can_be_null
     * @param  bool|boolean $is_post_required
     * @return void
     */
    public function registerColumn(
        string $column_name,
        DataType $type,
        bool $can_be_null = true,
        bool $is_post_required = true,
        bool $is_index = false,
        bool $is_unique = false
    ): void {
        $query = match ($type->name) {
            'TEXT' => "`$column_name` " . $type->name . ($can_be_null ? '' : ' NOT NULL') . ' COLLATE NOCASE',
            default => "`$column_name` " . $type->name . ($can_be_null ? '' : ' NOT NULL')
        };

        // Set index query
        if ($is_index) {
            $unique = $is_unique ? 'UNIQUE' : '';
            $index_name = "idx_$column_name";
            $table = $this->getCurrentTableName();
            $this->indices[$index_name] = "CREATE $unique INDEX IF NOT EXISTS $index_name ON $table($column_name)";
        }

        $this->columns[$column_name] = [
            'name' => $column_name,
            'query' => $query,
            'post_required' => $is_post_required
        ];
    }

    /**
     * Check has column name
     * @param  string  $column_name
     * @return boolean
     */
    public function hasColumn(string $column_name): bool
    {
        return isset($this->columns[$column_name]);
    }

    /**
     * Run database checks
     * @return void
     */
    public function migrate(): void
    {
        if (!empty($this->columns)) {
            // Check if table exists
            if (!empty($this->getColumns())) {
                $this->migrateExistingTable();
            } else {
                $this->migrateNewTable();
            }

            // Create table indices
            $this->migrateIndices();

            // Clear indices
            $this->indices = [];

            // Clear column array
            $this->clearRegisteredColumns();
        }
    }

    /**
     * Clear registered columns array
     * @return void
     */
    public function clearRegisteredColumns(): void
    {
        $this->columns = [];
    }

    /**
     * Migrate new table
     * @return void
     */
    private function migrateNewTable(): void
    {
        $columns = implode(",\n", array_map(function ($field) {
            return $field['query'];
        }, $this->columns));

        $query = <<<SQL
        CREATE TABLE IF NOT EXISTS {$this->getCurrentTableName()} (
            `id` INTEGER PRIMARY KEY AUTOINCREMENT,
            {$columns}
        );
        SQL;

        // Run create query
        $this->query($query, false);
    }

    /**
     * Migrate existing table
     * @return void
     */
    private function migrateExistingTable(): void
    {
        $column_names = $this->getNames($this->getColumns());

        foreach ($this->columns as $column) {
            if (!in_array($column['name'], $column_names)) {
                $query = <<<SQL
                ALTER TABLE {$this->getCurrentTableName()} ADD {$column['query']}
                SQL;

                $this->query($query, false);
            }
        }
    }

    /**
     * Create table indices
     * @return void
     */
    private function migrateIndices(): void
    {
        $indices = $this->getNames($this->getIndices());

        foreach ($this->indices as $name => $query) {
            if (!in_array($name, $indices)) {
                $this->query($query, false);
            }
        }
    }

    /**
     * Query database
     * @param  string $query
     * @param  boolean $return_rows
     * @param  array<mixed> $bind_params
     * @return \SQLite3Result|array<mixed>|boolean
     */
    public function query(string $query, bool $return_rows = true, array $bind_params = []): \SQLite3Result|array|bool
    {
        // Check is write statement
        if ($this->queryIsWriteStatement($query) && !$this->is_transaction) {
            $this->beginWriteTransaction();
            $this->is_transaction = true;
        } elseif (!$this->queryIsWriteStatement($query) && $this->is_transaction) {
            $this->completeWriteTransaction();
            $this->is_transaction = false;
        } else {
            $this->open();
        }

        if ($this->connection instanceof \SQLite3) {
            try {
                $statement = $this->connection->prepare($query);

                if ($statement instanceof \SQLite3Stmt) {
                    if (!empty($bind_params)) {
                        foreach ($bind_params as $param) {
                            if (!is_array($param) || count($param) < 2 || count($param) > 3) {
                                throw new QueryError("Error Processing Params", 1);
                            }

                            $statement->bindParam(...$param);
                        }
                    }

                    $result = $statement->execute();
                    $return_result = $result;

                    if ($return_rows && $result instanceof \SQLite3Result) {
                        $return_result = [];

                        while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
                            $return_result[] = $row;
                        }
                    }

                    // $statement->close();
                    return $return_result;
                }
            } catch (\Exception $e) {
                throw new QueryError($e->getMessage() . ' - ' . $query);
            }
        }

        return false;
    }

    /**
     * Get table columns
     * @return array<mixed>
     */
    public function getColumns(): array
    {
        $columns = $this->query(
            query: 'PRAGMA table_info(' . $this->getCurrentTableName() . ')',
        );

        return is_array($columns) ? $columns : [];
    }

    /**
     * Get table indices
     * @return array<mixed>
     */
    public function getIndices(): array
    {
        $indices = $this->query(
            query: 'PRAGMA index_list(' . $this->getCurrentTableName() . ')',
        );

        return is_array($indices) ? $indices : [];
    }

    /**
     * Get column names
     * @param  array<mixed> $items
     * @return array<string>
     */
    public function getNames(array $items): array
    {
        // Get column names from subject
        if (!empty($items)) {
            return array_map(fn($item) => $item['name'], $items);
        }

        return [];
    }

    /**
     * Get array key from name
     * @param  array<mixed>  $items
     * @param  string $name
     * @return integer
     */
    public function getKeyFromName(array $items, string $name): int
    {
        $key = array_search($name, array_column($items, 'name'));
        return is_integer($key) ? $key : 0;
    }
}

<?php

namespace App\Core;

class DataAccess
{
    /** @property \PDO */
    protected $pdo;
    /** @property string */
    protected $table;
    /** @property string */
    protected $select;
    /** @property array */
    protected $where;
    /** @property array */
    protected $joins;
    /** @property array */
    protected $orderBy;
    /** @property string */
    protected $groupBy;
    /** @property string */
    protected $limit;
    /** @property array */
    protected $insert;
    /** @property array */
    protected $update;
    /** @property bool */
    protected $delete;
    /** @property callable */
    protected $createModel;

    public function __construct()
    {
        $this->pdo = new \PDO(
            "mysql:host=" . $_ENV["DB_HOST"] . ";dbname=" . $_ENV["DB_NAME"],
            $_ENV["DB_USER"],
            $_ENV["DB_PASSWORD"]
        );
        $this->select = "*";
        $this->where = [];
        $this->joins = [];
        $this->orderBy = "";
        $this->limit = "";
        $this->groupBy = "";
        $this->update = [];
        $this->delete = false;
        $this->insert = [];
        $this->createModel = function (array $data) {
            return EntityModel::create($data);
        };
    }

    public function __toString()
    {
        return $this->query();
    }

    public function __get(string $key)
    {
        return $this->$key;
    }

    public function query()
    {
        $is_update = count($this->update) > 0;
        $is_delete = $this->delete;
        $is_select = (bool)$this->select;
        $is_insert = count($this->insert) > 0;

        if ($is_update) {
            $query = "UPDATE $this->table SET " . implode(", ", $this->update) . " ";

            if (!empty($this->where)) {
                $query .= "WHERE " . implode(" ", $this->where);
            }
        } else if ($is_delete) {
            $query = "DELETE FROM $this->table ";

            if (!empty($this->where)) {
                $query .= "WHERE " . implode(" ", $this->where);
            }
        } else if ($is_insert) {
            $rows = $this->insert;

            $columns = [];
            $values = [];

            foreach ($rows as $row) {
                $row_columns = array_keys($row);
                $row_values = [];

                $columns = array_merge($columns, $row_columns);
                $columns = array_unique($columns);

                foreach ($columns as $column) {
                    $value_exists = array_key_exists($column, $row) && isset($row[$column]);
                    $row_values[] = $value_exists ? $row[$column] : null;
                }

                $values[] = $row_values;
            }

            $columns = implode(", ", $columns);
            $values = array_map(function (array $row_values) {
                $row_values = array_map(function (mixed $value) {
                    return gettype($value) === "string" ? "'$value'" : $value;
                }, $row_values);
                $row_values = implode(", ", $row_values);
                return "(" .  $row_values . ")";
            }, $values);
            $values = implode(", ", $values);

            $query = "INSERT INTO $this->table ($columns) VALUES $values";
        } else if ($is_select) {
            $query = "SELECT $this->select FROM $this->table ";

            if (!empty($this->joins)) {
                $query .= implode(" ", $this->joins) . " ";
            }

            if (!empty($this->where)) {
                $query .= "WHERE " . implode(" ", $this->where) . " ";
            }

            if (!empty($this->groupBy)) {
                $query .= $this->groupBy . " ";
            }

            if (!empty($this->orderBy)) {
                $query .= $this->orderBy . " ";
            }

            if (!empty($this->limit)) {
                $query .= $this->limit;
            }
        }

        return $query;
    }

    public function modeler(callable $modeler)
    {
        $this->createModel = $modeler;
    }

    public function table(mixed $table)
    {
        if ($table instanceof DataAccess) {
            $table = "(" . $table . ") AS $table->table";
        }

        $this->table = $table;
        return $this;
    }

    public function select(string $columns)
    {
        $this->select = $columns;

        return $this;
    }

    public function where(string $column, string $operator, $value)
    {
        $this->where[] = "$column $operator '$value'";
        return $this;
    }

    public function and(string $column, string $operator, $value)
    {
        $this->where[] = "AND $column $operator '$value'";
        return $this;
    }

    public function or(string $column, string $operator, $value)
    {
        $this->where[] = "OR $column $operator '$value'";
        return $this;
    }

    public function like(string $column, string $pattern)
    {
        $this->where[] = "$column LIKE '$pattern'";
        return $this;
    }

    public function orderBy(string $column, string $direction = "ASC")
    {
        $this->orderBy = "ORDER BY $column $direction";
        return $this;
    }

    public function groupBy(string $column)
    {
        $this->groupBy = "GROUP BY $column";
        return $this;
    }

    protected function join(string $type, mixed $table, string $first, string $operator, string $second)
    {
        $is_subquery = $table instanceof DataAccess;
        $table_name = $table;

        if ($is_subquery) {
            $table_name = $table->table;
            $query = $table;
            $table = "(" . $query . ")";
        }

        $this->joins[] = "$type JOIN $table AS $table_name ON $first $operator $second";
        return $this;
    }

    public function leftJoin(mixed $table, string $first, string $operator, string $second)
    {
        return $this->join("LEFT", $table, $first, $operator, $second);
    }

    public function innerJoin(mixed $table, string $first, string $operator, string $second)
    {
        return $this->join("INNER", $table, $first, $operator, $second);
    }

    public function rightJoin(mixed $table, string $first, string $operator, string $second)
    {
        return $this->join("RIGHT", $table, $first, $operator, $second);
    }

    public function limit(int $limit)
    {
        $this->limit = "LIMIT $limit";
        return $this;
    }

    public function insert(array $rows)
    {
        $createModel = $this->createModel;
        $model = $createModel([]);
        $table_columns = $model->columns();

        foreach ($rows as &$row) {
            $keys = array_keys($row);

            foreach ($keys as $column) {
                if (!in_array($column, $table_columns)) {
                    unset($row[$column]);
                }
            }

            $model->fill($row);
            $model->validate(["id"]);
            $row = array_merge($row, $model->data);
        }

        $this->insert = $rows;
        return $this;
    }

    public function getLastInsertedId()
    {
        return $this->pdo->lastInsertId();
    }

    public function update(array $data)
    {
        $createModel = $this->createModel;
        $model = $createModel([]);
        $table_columns = $model->columns();
        $columns_to_ignore = array_diff($table_columns, array_keys($data));

        $model->fill($data);
        $model->validate($columns_to_ignore);

        foreach ($data as $column => $value) {
            if (in_array($column, $table_columns)) {
                $this->update[] = "$column = '$value'";
            }
        }

        return $this;
    }

    public function delete()
    {
        $this->delete = true;
        return $this;
    }

    public function execute()
    {
        $query = $this->query();
        $is_update = count($this->update) > 0;
        $is_delete = $this->delete;
        $is_select = (bool)$this->select;
        $is_insert = count($this->insert) > 0;

        if ($is_update || $is_delete || $is_insert) {
            $stmt = $this->pdo->prepare($query);

            return $stmt->execute();
        }

        if ($is_select) {
            $stmt = $this->pdo->query($query);
            $items = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            $createModel = $this->createModel;

            foreach ($items as $i => $item) {
                $model = $createModel([]);
                $model->cast($item);
                $items[$i] = array_merge($item, $model->data);
            }

            return $items;
        }

        return false;
    }
}

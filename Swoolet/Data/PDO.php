<?php

namespace Swoolet\Data {

    use \Swoolet\App;

    class PDO
    {
        static public $ins = [];

        public $option = [
            'driver' => 'mysql',
            'host' => '127.0.0.1',
            'port' => 3306,
            'dbname' => '',
            'charset' => 'utf8mb4',
            'username' => 'root',
            'password' => '',
            'option' => [],
        ];

        /**
         * @var \pdoProxy
         */
        public $link;

        /**
         * @var \PDOStatement
         */
        public $sth;

        /*
         * need setup
         */
        public $cfg_key = '';
        public $table_name = '';

        public $clause = [], $last_clause = [];
        public $sql, $param = [], $last_param = [];

        public $pdo_option = [
            \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
            \PDO::ATTR_TIMEOUT => 3,
            \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
            \PDO::ATTR_EMULATE_PREPARES => false,
            \PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8mb4',
            //\PDO::ATTR_AUTOCOMMIT => false,
            \PDO::ATTR_PERSISTENT => true,//12

            #overwrite 'options' if not using MySQL
            \PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true, //1000
            \PDO::MYSQL_ATTR_FOUND_ROWS => true, //1008
        ];

        public function __construct($cfg_key = '')
        {
            if ($cfg_key || $cfg_key = $this->cfg_key)
                $this->dial($cfg_key);
        }

        /**
         * @param $cfg_key
         * @return \PDO
         */
        public function dial($cfg_key)
        {
            $this->initial();

            if ($link = &self::$ins[$cfg_key . $this->option['dbname']])
                return $this->link = $link;

            $cfg = App::getConfig($cfg_key) + $this->option;
            return $this->link = $link = new \pdoProxy(
                "{$cfg['driver']}:host={$cfg['host']};port={$cfg['port']};dbname={$cfg['dbname']};charset={$cfg['charset']};",
                $cfg['username'],
                $cfg['password'],
                $cfg['option'] + $this->pdo_option
            );
        }

        /**
         * @return $this
         */
        public function initial()
        {
            $this->clause = [
                'field' => '*',
                'table' => $this->table_name,
                'join' => '',
                'where' => ' WHERE 1',
                'group' => '',
                'having' => '',
                'order' => '',
                'limit' => '',
                'for_update' => '',
            ];

            $this->param = [];

            return $this;
        }

        /**
         * fields for select
         *
         * @param $field
         *        - *
         *        - id,uid,ts
         *        - count(*)
         * @return $this
         */
        public function select($field)
        {
            $this->clause['field'] = $field;

            return $this;
        }

        /**
         * @param $table
         * @return $this
         */
        public function table($table)
        {
            $this->clause['table'] = $this->table_name = $table;

            return $this;
        }

        /**
         * where('gender', 'male')
         * where('user_id', 'IN', [1, 2, 3])
         * where('email', 'LIKE', '%@abc.com', 'OR')
         * where('(age >= ? OR age <= ?)', [18, 30])
         *
         * @param $clause
         * @param $condition
         * @param $value
         * @param $glue
         *
         * @return $this
         */
        public function where($clause, $condition, $value = null, $glue = 'AND')
        {
            if ($value === null) {
                $value = $condition;

                if (strpos($clause, '?') === false) {
                    $clause = "`$clause`";
                    $condition = '= ?';
                } else
                    $condition = '';

            } elseif ($condition == 'IN' || $condition == 'NOT IN') {
                $in = '?' . str_repeat(',?', count($value) - 1);
                $condition = "$condition ($in)";
            } elseif ($condition)
                $condition .= ' ?';

            $this->clause['where'] .= " $glue $clause $condition";

            if (is_array($value)) {
                $this->param = array_merge($this->param, $value);
            } else {
                $this->param[] = $value;
            }

            return $this;
        }

        /**
         * @param $limit
         * @param int $offset
         * @return $this
         */
        public function limit($limit, $offset = 0)
        {
            $this->clause['limit'] = ' LIMIT ' . $offset . ', ' . $limit;

            return $this;
        }

        /**
         * @param string $group
         * @return $this
         */
        public function groupBy($group)
        {
            if ($group)
                $this->clause['group'] = ' GROUP BY ' . $group;
            else
                $this->clause['group'] = '';

            return $this;
        }

        /**
         * @param string|array $order
         *          - 'id'
         *          - ['id', 'ts' => -1]
         *
         * @return $this
         */
        public function orderBy($order)
        {
            if (is_array($order)) {
                $a = [];

                foreach ($order as $col => $expr) {
                    if (\is_int($col)) {
                        $col = $expr;
                        $expr = 'ASC';
                    } elseif (-1 == $expr) {
                        $expr = 'DESC';
                    }

                    $a[] = "`$col` $expr";
                }

                $order = implode(', ', $a);
            }

            $this->clause['order'] = ' ORDER BY ' . $order;

            return $this;
        }

        /**
         * @param $having
         * @return $this
         */
        public function having($having)
        {
            $this->clause['having'] = ' HAVING  ' . $having;

            return $this;
        }

        /**
         * join('t2, t3, t4', 't2.a=t1.a AND t3.b=t1.b AND t4.c=t1.c', 'STRAIGHT_JOIN')
         *
         * @param $table
         * @param $condition
         * @param string $type
         * @return $this
         */
        public function join($table, $condition, $type = 'LEFT JOIN')
        {
            $this->clause['join'] .= " $type ($table) ON ($condition)";

            return $this;
        }

        /**
         * @param array $data
         * @param string $modifier
         *                 - INSERT INTO
         *                 - INSERT IGNORE INTO
         *                 - REPLACE INTO
         *
         * @return int
         */
        public function insert(array $data, $modifier = 'INSERT INTO')
        {
            $col = $value = [];
            foreach ($data as $k => $v) {
                $col[] = '`' . $k . '`';

                $value[] = '?';

                $this->param[] = $v;
            }

            $this->sql = $modifier . ' ' . $this->clause['table'] . ' (' . \implode(', ', $col) . ') VALUES (' . \implode(', ', $value) . ');';
            if ($this->exec()) {
                if ($id = $this->getInsertId())
                    return $id;

                return $this->rowCount();
            }

            return 0;
        }

        /**
         * @param array $data
         * @return int
         */
        public function replace(array $data)
        {
            return $this->insert($data, 'REPLACE INTO');
        }

        /**
         * @param array|string $data
         *
         * @return int
         */
        public function update($data)
        {
            $params = [];
            if (is_array($data)) {
                $value = [];
                foreach ($data as $col => $val) {
                    $value[] = "`{$col}` = ?";
                    $params[] = $val;
                }

                // adjust order
                $this->param = $params = array_merge($params, $this->param);

                $data = \implode(', ', $value);
            }

            $this->sql = 'UPDATE ' . $this->clause['table'] . ' SET ' . $data . $this->clause['where'] . ';';

            if ($this->exec() && $n = $this->rowCount())
                return $n;

            return 0;
        }

        /**
         * increase a field
         *
         * @param string $field
         * @param int $num
         *
         * @return int
         */
        public function increment($field, $num)
        {
            return $this->update("`{$field}`=`{$field}`+{$num}");
        }

        /**
         * @return int
         */
        public function delete()
        {
            $this->sql = 'DELETE FROM ' . $this->clause['table'] . $this->clause['where'] . ';';

            if ($this->exec() && $n = $this->rowCount())
                return $n;

            return false;
        }

        /**
         * @param int $mode
         * @param mixed $mode_param
         *
         * @return mixed
         */
        public function fetch($mode = 0, $mode_param = null)
        {
            $this->sql = $this->getSelectClause();

            if ($this->exec())
                return $this->_setFetchMode($mode, $mode_param)->fetch();

            return false;
        }

        public function fetchColumn($col = 0)
        {
            return $this->fetch(\PDO::FETCH_COLUMN, $col);
        }

        /**
         * @param int $mode
         * @param null $mode_param
         * @return array
         */
        public function fetchAll($mode = 0, $mode_param = null)
        {
            $this->sql = $this->getSelectClause();

            //for fetchCount()
            $this->last_param = $this->param;
            $this->last_clause = $this->clause;

            if ($this->exec())
                return $this->_setFetchMode($mode, $mode_param)->fetchAll();

            return [];
        }

        /**
         * @param bool $clear_group_by
         * @return mixed
         */
        public function fetchCount($clear_group_by = true)
        {
            $this->clause = $this->last_clause;
            $this->param = $this->last_param;

            if ($clear_group_by)
                $this->groupBy('');

            $this->clause['limit'] = '';

            return $this->select('count(*)')->limit(1)->fetchColumn(0);
        }

        /**
         * execute multi sql
         * @param $sql
         * @return \PDOStatement
         */
        public function query($sql)
        {
            $old = $this->link->getAttribute(\PDO::ATTR_EMULATE_PREPARES);
            $this->link->setAttribute(\PDO::ATTR_EMULATE_PREPARES, true);

            $ret = $this->link->query($sql);

            $this->link->setAttribute(\PDO::ATTR_EMULATE_PREPARES, $old);

            return $ret;
        }

        /**
         * @param $sql
         * @param array $params
         * @return \PDOStatement
         */
//        public function execSQL($sql, $params = [])
//        {
//            $this->sql = $sql;
//            $this->param = $params;
//
//            $this->exec();
//
//            return $this->sth;
//        }

        /**
         * @return bool
         */
        public function exec()
        {
            try {
                $this->sth = $this->link->prepare($this->sql);
                $ret = $this->sth->execute($this->param);
            } catch (\PDOException $e) {
                if ($e->getCode() == '2006') {
                    //MySQL server has gone away
                    $class = get_called_class();
                    new $class($this->cfg_key);
                    $this->exec();
                } else {
                    echo $e->getTraceAsString();
                    echo PHP_EOL;
                }
            }

            $this->initial();

            return $ret;
        }

        public function getError()
        {
            if ($this->sth) {
                if ($this->sth->errorCode() > 0)
                    return $this->sth->debugDumpParams();
            } elseif ($this->link) {
                if ($this->link->errorCode() > 0)
                    return $this->link->errorInfo();
            }

            return [];
        }

        /**
         * @param int $autocommit
         * @return bool
         */
        public function beginTransaction($autocommit = 1)
        {
            $this->query("SET AUTOCOMMIT=$autocommit");

            return $this->link->beginTransaction();
        }

        /**
         * @return bool
         */
        public function commit()
        {
            return $this->link->commit();
        }

        /**
         * @return bool
         */
        public function rollback()
        {
            return $this->link->rollBack();
        }

        /**
         * @return string
         */
        public function getSelectClause()
        {
            $c = $this->clause;

            return 'SELECT ' . $c['field'] . ' FROM ' . $c['table'] . $c['join'] . $c['where'] .
            $c['group'] . $c['having'] . $c['order'] . $c['limit'] . $c['for_update'] . ';';
        }

        public function selectForUpdate($nowait = false)
        {
            $for_update = ' FOR UPDATE';
            if ($nowait)
                $for_update .= ' NOWAIT';

            $this->clause['for_update'] = $for_update;

            return $this;
        }

        /**
         * @param int $mode
         * @param mixed $mode_param
         *
         * @return \PDOStatement
         */
        private function _setFetchMode($mode, $mode_param)
        {
            if ($mode) {
                $mode_param === null
                    ? $this->sth->setFetchMode($mode)
                    : $this->sth->setFetchMode($mode, $mode_param);
            }

            return $this->sth;
        }

        /**
         * @param $name
         *
         * @return int
         */
        public function getInsertId($name = null)
        {
            return $this->link->lastInsertId($name);
        }

        /**
         * @return int
         */
        public function rowCount()
        {
            return $this->sth->rowCount();
        }

        public function __destruct()
        {
            if ($this->sth)
                $this->link->release();
        }
    }
}


namespace {
    if (!class_exists('\pdoProxy', false)) {
        class pdoProxy extends \PDO
        {
            public function release()
            {
            }
        }
    }
}
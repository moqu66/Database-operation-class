<?php
require 'db.config.php';

class DB
{
    private $pdo; # PDO对象
    private $stmt; # PDOStatement

    public function __construct()
    {
        try {
            $dsn = DBtype . ":host=" . DBhost . ";port=" . DBport . ";dbname=" . DBname;
            $this->pdo = new PDO($dsn, DBuser, DBpass, array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"));
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            echo 'Error:<br/>';
            if (DEBUGGING) {
                foreach ($e->errorInfo as $info) {
                    echo $info.'<br/>';
                }
                exit();
            } else {
                die ($e->getMessage());
            }
        }

    }

    /**
     * 关闭数据库连接
     */
    public function closepdo()
    {
        $this->pdo = null;
    }

    /**
     * 关闭游标，使语句能再次被执行。
     */
    public function closestmt()
    {
        $this->stmt->closeCursor();
    }

    /**
     * @param $sql sql查询语句
     * @param null $parameter 绑定参数,默认空值
     * 所有SQL语句都要用到此方法.
     */
    private function Mainquery($sql, $parameter = null)
    {
        try {
            $this->stmt = $this->pdo->prepare($sql);
            $this->bindA($parameter);
            $this->stmt->execute();
        } catch (PDOException $e) {
            echo 'Error:<br/>';
            if (DEBUGGING) {
                foreach ($e->errorInfo as $info) {
                    echo $info.'<br/>';
                }
                exit();
            } else {
                die ($e->getMessage());
            }
        }
    }

    /**
     * @param $parameter
     * 获取传入参数的键和值,并传递给bindB
     * 注意:数组的键名必须和占位符一样!!
     */
    private function bindA($parameter)
    {
        if (!empty($parameter) and is_array($parameter)) { # 判断传入参数是否为空、以及是否是数组类型.
            $K = array_keys($parameter); # 获取传入参数数组的键
            foreach ($K as $k => $v) {
                $this->bindB($v, $parameter[$v]);
            }
        }
    }

    /**
     * @param $k
     * @param $v
     * 注意:数组的键名必须和占位符一样!!
     * 将传入参数使用bindValue绑定到预处理语句
     */
    private function bindB($k, $v)
    {
        $this->stmt->bindValue($k, $v);
    }

    /**
     * @param $sql sql查询语句
     * @param null $parameter 绑定参数,默认空值
     * 注意:数组的键名必须和占位符一样!!
     * @param int $display 结果集显示方式,默认为关联数组
     * @return mixed 结果数组
     * 从结果集中获取下一行
     */
    public function queryfetch($sql, $parameter = null, $display = PDO::FETCH_ASSOC)
    {
        try {
            $q = substr(strtolower(trim($sql)), 0, 6);
            if ($q != 'select') {
                $this->pdo->beginTransaction();
            }
            $this->Mainquery($sql, $parameter);
            if ($q == 'insert' or $q == 'update' or $q == 'delete') {
                $result = $this->pdo->commit();
                //$result = $this->stmt->rowCount();
            } else {
                $result = $this->stmt->fetch($display);
            }
            return $result;
        } catch (PDOException $e) {
            if ($q != 'select') {
                $this->pdo->rollBack();
            }
            echo 'Error:<br/>';
            if (DEBUGGING) {
                foreach ($e->errorInfo as $info) {
                    echo $info.'<br/>';
                }
                exit();
            } else {
                die ($e->getMessage());
            }
        }
    }

    /**
     * @param $sql sql查询语句
     * @param null $parameter 绑定参数,默认空值
     * 注意:数组的键名必须和占位符一样!!
     * @param int $display 结果集显示方式,默认为关联数组
     * @return mixed 返回结果数组或者影响行数
     * 返回一个包含结果集中所有行的数组
     */
    public function queryfetchall($sql, $parameter = null, $display = PDO::FETCH_ASSOC)
    {
        try {
            $q = substr(strtolower(trim($sql)), 0, 6);
            if ($q != 'select') {
                $this->pdo->beginTransaction();
            }
            $this->Mainquery($sql, $parameter);
            if ($q == 'insert' or $q == 'update' or $q == 'delete') {
                $result = $this->pdo->commit();
                //$result = $this->stmt->rowCount();
            } else {
                $result = $this->stmt->fetchAll($display);
            }
            return $result;
        } catch (PDOException $e) {
            if ($q != 'select') {
                $this->pdo->rollBack();
            }
            echo 'Error:<br/>';
            if (DEBUGGING) {
                foreach ($e->errorInfo as $info) {
                    echo $info.'<br/>';
                }
                exit();
            } else {
                die ($e->getMessage());
            }
        }
    }

    /**
     * @param $sql sql查询语句
     * @param null $parameter 绑定参数,默认空值
     * 注意:数组的键名必须和占位符一样!!
     * 从结果集中的下一行返回单独的一列。用于select语句的结果集
     */
    public function queryfetchColumn($sql, $parameter = null, $column_number = 0)
    {
        try {
            $q = substr(strtolower(trim($sql)), 0, 6);
            if ($q == 'select') {
                $this->Mainquery($sql, $parameter);
                $result = $this->stmt->fetchColumn($column_number);
                return $result;
            } else {
                die('此方法只适用于SELECT语句');
            }

        } catch (PDOException $e) {
            echo 'Error<br/>';
            if (DEBUGGING) {
                foreach ($e->errorInfo as $info) {
                    echo $info.'<br/>';
                }
                exit();
            } else {
                die ($e->getMessage());
            }
        }
    }

}

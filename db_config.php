<?php

class db extends mysqli
{
    public function __construct($arr)
    {
        $host = $arr['host'] ?? null;
        $database = $arr['database'] ?? null;
        $username = $arr['username'] ?? null;
        $password = $arr['password'] ?? null;


        parent::__construct($host, $username, $password, $database);
    }

    function get($sql)
    {
        $data = [];
        $rs = $this->query($sql);
        if (!$rs) {
            return null;
        }
        while ($row = $rs->fetch_array(MYSQLI_ASSOC)) {
            $data[] = $row;
        }
        return $data;
    }

    function first($sql)
    {
        $data = [];
        $rs = $this->query($sql);

        if (!$rs) {
            return null;
        }
        while ($row = $rs->fetch_array(MYSQLI_ASSOC)) {
            $data[] = $row;
        }
        if (count($data) == 0) {
            return null;
        } else {
            return current($data);
        }
    }

    function insert_from_arr($table_name, $arr)
    {
        $columns_arr = [];
        $values_arr = [];

        foreach ($arr as $k => $v) {
            $columns_arr[] = "`$k`";
            $values_arr[] = is_numeric($v) ? $v : '"' . $v . '"';
        }

        $columns = implode($columns_arr, ',');
        $values = implode($values_arr, ',');

        $sql = "
        INSERT INTO `" . $table_name . "` ($columns)
        VALUES ($values)
        ";
        $this->query($sql);

        $last_id = $this->insert_id;

        return $last_id;
    }

    function update_from_arr_by_id($table_name, $arr, $id, $id_name = 'id')
    {
        $update_arr = [];

        foreach ($arr as $k => $v) {
            $value = is_numeric($v) ? $v : '"' . $v . '"';
            $update_arr[] = " `$k` = $value ";
        }

        $update = implode($update_arr, ',');

        $sql = "
      UPDATE `$table_name` SET
      $update
      WHERE `$id_name` = $id
    ";

        $data = $this->query($sql);
        return $data;
    }

    function delete_by_id($table_name, $id, $id_name = "id")
    {
        $sql = "DELETE FROM `$table_name` WHERE `$id_name` = $id ";
        $data = $this->query($sql);
        return $data;
    }


    function prepared($sql, $bind, $parameters)
    {
    }

    function test_request($v)
    {
        $v = json_encode($v);
        $sql = "INSERT INTO test_request (`value`) VALUES ('" . $v . "')";
        return $this->query($sql);
    }
}

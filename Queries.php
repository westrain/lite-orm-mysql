<?php

abstract class Queries {

	private $db;
	private $table;
    private $resultObject = null;

    public function __construct($table) {
		$this->table = $table;
		$this->connect('','','','');
	}

	protected function connect($host,$user,$db,$pass){
        $this->db = new mysqli($host, $user, $pass, $db);
        $this->db->set_charset("utf8");
        if (!$this->db) {
            echo "Ошибка: Невозможно установить соединение с MySQL." . PHP_EOL;
            echo "Код ошибки errno: " . mysqli_connect_errno() . PHP_EOL;
            echo "Текст ошибки error: " . mysqli_connect_error() . PHP_EOL;
            exit;
        }
    }

    public function query($query){
       $this->resultObject = mysqli_query($this->db,$query);

       //mLog('mysqli',$query);

       return $this;
    }

    public function fetch(){
        $data = [];
        $result = $this->resultObject;
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $data[] = $row;
            }
            $result->close();
        }
        return $data;
    }

	public function insert($data) {

        foreach ($data as $k=>$v){
            $data[$k] = "'".mysqli_escape_string($this->db,$v)."'";
        }

        //dump("insert into {$this->table}(".join(',',array_keys($data)).") values (".join(',',array_values($data)).")");
        return $this->query("insert into {$this->table}(".join(',',array_keys($data)).") values (".join(',',array_values($data)).")");
	}

	public function select($fields,$where = ''){
        if($where) $where = " where ".join(' and ',$where);

        //dump("select ".join(',',$fields)." from {$this->table} $where ");

        return $this->query("select ".join(',',$fields)." from {$this->table} $where ")->fetch();
	}

    public function exists($field,$data) {
        if(!empty($this->getByField($field,$data))) return true;
        return false;
    }

    public function getAll() {
        return $this->query("select * from $this->table;")->fetch();
    }

    public function getById($id) {
        return $this->query("select * from {$this->table} where id = '$id';")->fetch();
    }

    public function getByField($field,$value) {

        $query = "select * from {$this->table} where $field = '".mysqli_escape_string($this->db,$value)."';";
        //dump($query);

        return $this->query($query)->fetch();
    }

	public function selectWithTable( $table, array $where = []) {

	}

	public function row($query) {

	}

	public function createTable($table,$fields){
        $query = "CREATE TABLE $table (
                  ".join(',',$fields)."
                ) ENGINE=InnoDB";

        return $this->query($query);

    }

	public function update($id,$data) {
        $sets = '';
        foreach ($data as $k=>$v){
            $sets .= "$k = '".mysqli_escape_string($this->db,$v)."' ,";
        }

        $query = "update $this->table set ".substr($sets,0,-1)." where id = '$id'";
        return $this->query($query);
	}

	public function delete($id) {

	}

	public function __destruct()
    {
        mysqli_close($this->db);
    }

}
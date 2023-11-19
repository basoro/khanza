<?php

namespace Plugins\Khanza\Src;

use Plugins\Khanza\MySQL;

class Bahasa {

    protected function db($table = NULL)
    {
        return new MySQL($table);
    }

    public function Data()
    {
        $page = isset($_POST['page']) ? intval($_POST['page']) : 1;
        $rows = isset($_POST['rows']) ? intval($_POST['rows']) : 10;
        $offset = ($page-1)*$rows;
  
        $keyword = isset($_POST['cari_keyword_bahasa']) ? $_POST['cari_keyword_bahasa'] : '';
  
        $row = $this->db('bahasa_pasien')->select(['count' => 'COUNT(*)'])->oneArray();
        $result["total"] = $row['count'];
  
        $items = array();
        $query = "select * from bahasa_pasien where (id like '%$keyword%' or nama_bahasa like '%$keyword%')
        order by id asc LIMIT $offset,$rows";
  
        $rows = $this->db()->pdo()->prepare($query);
        $rows->execute();
        $rows = $rows->fetchAll(\PDO::FETCH_ASSOC);
  
        foreach ($rows as $row) {
            array_push($items, $row);
        }
        $result["rows"] = $items;
        echo json_encode($result); 
    }    

    public function Simpan()
    {
        $check_db = $this->db()->pdo()->prepare("INSERT INTO bahasa_pasien VALUES (
            '{$_POST['id']}', 
            '{$_POST['nama_bahasa']}'
        )");
        $result = $check_db->execute();
        $error = $check_db->errorInfo();
        if (!empty($result)){
        echo json_encode(array(
            'id' => $_POST['id']
        ));
        } else {
        echo json_encode(array('errorMsg'=>$error['2']));
        } 
    }

    public function Ubah()
    {
        $check_db = $this->db()->pdo()->prepare("
            UPDATE 
                bahasa_pasien
            SET 
                nama_bahasa = '{$_POST['nama_bahasa']}'
            WHERE
                id = '{$_POST['id']}'
        ");
        $result = $check_db->execute();
        $error = $check_db->errorInfo();
        if (!empty($result)){
        echo json_encode(array(
            'id' => $_POST['id']
        ));
        } else {
        echo json_encode(array('errorMsg'=>$error['2']));
        }           
    }

    public function Hapus()
    {
        $id = $_POST['id'];
        $check_db = $this->db()->pdo()->prepare("DELETE FROM bahasa_pasien WHERE id = '$id'");
        $result = $check_db->execute();
        $error = $check_db->errorInfo();
        if (!empty($result)){
          echo json_encode(array(
            'id' => $id
          ));
        } else {
          echo json_encode(array('errorMsg'=>$error['2']));
        }                 
    }    

    public function Cetak()
    {
        
    }

}

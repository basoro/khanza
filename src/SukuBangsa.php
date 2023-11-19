<?php

namespace Plugins\Khanza\Src;

use Plugins\Khanza\MySQL;

class SukuBangsa
{

    protected function db($table = NULL)
    {
        return new MySQL($table);
    }

    public function Data()
    {

        $page = isset($_POST['page']) ? intval($_POST['page']) : 1;
        $rows = isset($_POST['rows']) ? intval($_POST['rows']) : 10;
        $offset = ($page-1)*$rows;
  
        $keyword = isset($_POST['cari_keyword']) ? $_POST['cari_keyword'] : '';
  
        $row = $this->db('suku_bangsa')->select(['count' => 'COUNT(*)'])->oneArray();
        $result["total"] = $row['count'];
  
        $items = array();
        $pasien = "select * from suku_bangsa where id like '%$keyword%' or nama_suku_bangsa like '%$keyword%'
        order by id asc LIMIT $offset,$rows";
  
        $rows = $this->db()->pdo()->prepare($pasien);
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
        $check_db = $this->db()->pdo()->prepare("INSERT INTO suku_bangsa VALUES (
            '{$_POST['id_SukuBangsa']}', 
            '{$_POST['nama_SukuBangsa']}'
        )");
        $result = $check_db->execute();
        $error = $check_db->errorInfo();
        if (!empty($result)){
        echo json_encode(array(
            'nama_suku_bangsa' => $_POST['nama_SukuBangsa']
        ));
        } else {
        echo json_encode(array('errorMsg'=>$error['2']));
        } 
    }

    public function Ubah()
    {
        $check_db = $this->db()->pdo()->prepare("
            UPDATE 
                suku_bangsa
            SET 
                nama_suku_bangsa = '{$_POST['nama_SukuBangsa']}'
            WHERE
                id = '{$_POST['id_SukuBangsa']}'
        ");
        $result = $check_db->execute();
        $error = $check_db->errorInfo();
        if (!empty($result)){
        echo json_encode(array(
            'id' => $_POST['id_SukuBangsa']
        ));
        } else {
        echo json_encode(array('errorMsg'=>$error['2']));
        }           
    }

    public function Hapus()
    {
        $id = $_POST['id'];
        $check_db = $this->db()->pdo()->prepare("DELETE FROM suku_bangsa WHERE id = '$id'");
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

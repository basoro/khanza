<?php

namespace Plugins\Khanza\Src;

use Plugins\Khanza\MySQL;

class Penjab
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
  
        $keyword = isset($_POST['cari_keyword_penjab']) ? $_POST['cari_keyword_penjab'] : '';
  
        $query = "select * from penjab where status ='1' and kd_pj like ? and png_jawab like ? ";

        $result = array();
        
        $total = $this->db()->pdo()->prepare($query);
        $total->execute(['%'.$keyword.'%', '%'.$keyword.'%']);
        $total = $total->fetchAll(\PDO::FETCH_ASSOC);          
        $result["total"] = count($total);

        $query .= "order by penjab.kd_pj asc LIMIT $offset,$rows";    

        $rows = $this->db()->pdo()->prepare($query);
        $rows->execute(['%'.$keyword.'%', '%'.$keyword.'%']);
        $rows = $rows->fetchAll(\PDO::FETCH_ASSOC);
  
        $items = array();

        foreach ($rows as $row) {
            array_push($items, $row);
        }
        $result["rows"] = $items;
        echo json_encode($result); 

    }    

    public function Simpan()
    {
        $check_db = $this->db()->pdo()->prepare("INSERT INTO penjab VALUES (
            '{$_POST['kd_pj']}', 
            '{$_POST['png_jawab']}', 
            '{$_POST['nama_perusahaan']}', 
            '{$_POST['alamat_asuransi']}', 
            '{$_POST['no_telp']}', 
            '{$_POST['attn']}', 
            '1'
        )");
        $result = $check_db->execute();
        $error = $check_db->errorInfo();
        if (!empty($result)){
        echo json_encode(array(
            'kd_pj' => $_POST['kd_pj']
        ));
        } else {
        echo json_encode(array('errorMsg'=>$error['2']));
        } 
    }

    public function Ubah()
    {
        $check_db = $this->db()->pdo()->prepare("
            UPDATE 
                penjab
            SET 
                png_jawab = '{$_POST['png_jawab']}', 
                nama_perusahaan = '{$_POST['nama_perusahaan']}', 
                alamat_asuransi = '{$_POST['alamat_asuransi']}', 
                no_telp = '{$_POST['no_telp']}', 
                attn = '{$_POST['attn']}'
            WHERE
                kd_pj = '{$_POST['kd_pj']}'
        ");
        $result = $check_db->execute();
        $error = $check_db->errorInfo();
        if (!empty($result)){
        echo json_encode(array(
            'kd_pj' => $_POST['kd_pj']
        ));
        } else {
        echo json_encode(array('errorMsg'=>$error['2']));
        }         
    }

    public function Hapus()
    {
        $kd_pj = $_POST['kd_pj'];
        $check_db = $this->db()->pdo()->prepare("UPDATE penjab SET status = '0' WHERE kd_pj = '$kd_pj'");
        $result = $check_db->execute();
        $error = $check_db->errorInfo();
        if (!empty($result)){
          echo json_encode(array(
            'kd_pj' => $kd_pj
          ));
        } else {
          echo json_encode(array('errorMsg'=>$error['2']));
        }           
    }    

    public function Cetak()
    {
        
    }

}

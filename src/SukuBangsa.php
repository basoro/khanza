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

    }

    public function Ubah()
    {
        
    }

    public function Hapus()
    {
        
    }    

    public function Cetak()
    {
        
    }

}

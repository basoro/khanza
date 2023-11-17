<?php

namespace Plugins\Khanza\Src;

use Plugins\Khanza\MySQL;

class PerusahaanPasien {

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
  
        $query = "select * from perusahaan_pasien where kode_perusahaan like ? and nama_perusahaan like ? ";

        $result = array();
        
        $total = $this->db()->pdo()->prepare($query);
        $total->execute(['%'.$keyword.'%', '%'.$keyword.'%']);
        $total = $total->fetchAll(\PDO::FETCH_ASSOC);          
        $result["total"] = count($total);

        $query .= "order by perusahaan_pasien.kode_perusahaan asc LIMIT $offset,$rows";    

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

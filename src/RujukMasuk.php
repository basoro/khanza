<?php

namespace Plugins\Khanza\Src;

use Plugins\Khanza\MySQL;

class RujukMasuk {

    protected function db($table = NULL)
    {
        return new MySQL($table);
    }

    public function Data()
    {
        echo '{"total": "1", "rows": [{ "kode": "KD001", "nama": "Nama Item", "status": "1" }]}';
    }    

    public function CariPerujuk()
    {
        $page = isset($_POST['page']) ? intval($_POST['page']) : 1;
        $rows = isset($_POST['rows']) ? intval($_POST['rows']) : 10;
        $offset = ($page-1)*$rows;
  
        $keyword = isset($_POST['cari_keyword_perujuk']) ? $_POST['cari_keyword_perujuk'] : '';
  
        $row = $this->db('rujuk_masuk')->select(['count' => 'COUNT(*)'])->oneArray();
        $result["total"] = $row['count'];
  
        $items = array();
        $query = "select perujuk, alamat from rujuk_masuk where (perujuk like '%$keyword%' or alamat like '%$keyword%')
        order by perujuk asc LIMIT $offset,$rows";
  
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

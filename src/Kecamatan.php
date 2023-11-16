<?php

namespace Plugins\Khanza\Src;

use Plugins\Khanza\MySQL;

class Kecamatan {

    protected function db($table = NULL)
    {
        return new MySQL($table);
    }

    public function Data()
    {
        echo '{"total": "1", "rows": [{ "kode": "KD001", "nama": "Nama Item", "status": "1" }]}';
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

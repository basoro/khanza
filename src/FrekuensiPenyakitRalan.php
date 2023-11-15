<?php

namespace Plugins\Khanza\Src;

use Plugins\Khanza\MySQL;

class FrekuensiPenyakitRalan
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
        $dokter = isset($_POST['cari_nama_dokter']) ? $_POST['cari_nama_dokter'] : '';
        $poliklinik = isset($_POST['cari_nama_poli']) ? $_POST['cari_nama_poli'] : '';
        $penjab = isset($_POST['cari_png_jawab']) ? $_POST['cari_png_jawab'] : '';

        $nm_kab = isset($_POST['cari_nm_kab']) ? $_POST['cari_nm_kab'] : '';
        $nm_kec = isset($_POST['cari_nm_kec']) ? $_POST['cari_nm_kec'] : '';
        $nm_kel = isset($_POST['cari_nm_kel']) ? $_POST['cari_nm_kel'] : '';

        $tgl_awal = isset($_POST['cari_tgl_registrasi_start']) ? $_POST['cari_tgl_registrasi_start'] : '2023-10-15';
        $tgl_akhir = isset($_POST['cari_tgl_registrasi_end']) ? $_POST['cari_tgl_registrasi_end'] : date('Y-m-d');
  
        $result = array();

        $query = "select penyakit.kd_penyakit,SUBSTRING(penyakit.nm_penyakit,1,80) as penyakit from penyakit ";
        $query .= "inner join diagnosa_pasien on penyakit.kd_penyakit=diagnosa_pasien.kd_penyakit ";
        $query .= "inner join reg_periksa on reg_periksa.no_rawat=diagnosa_pasien.no_rawat ";
        $query .= "inner join dokter on reg_periksa.kd_dokter=dokter.kd_dokter ";
        $query .= "inner join pasien on reg_periksa.no_rkm_medis=pasien.no_rkm_medis ";
        $query .= "inner join poliklinik on reg_periksa.kd_poli=poliklinik.kd_poli ";
        $query .= "inner join penjab on reg_periksa.kd_pj=penjab.kd_pj ";
        $query .= "inner join kabupaten on pasien.kd_kab=kabupaten.kd_kab ";
        $query .= "inner join kecamatan on pasien.kd_kec=kecamatan.kd_kec ";
        $query .= "inner join kelurahan on pasien.kd_kel=kelurahan.kd_kel ";
        $query .= "where reg_periksa.status_lanjut='Ralan' and diagnosa_pasien.status='Ralan' and diagnosa_pasien.prioritas='1' and reg_periksa.tgl_registrasi between ? and ? and poliklinik.nm_poli like ? and dokter.nm_dokter like ? and penjab.png_jawab like ? and kabupaten.nm_kab like ? and kecamatan.nm_kec like ? and kelurahan.nm_kel like ? and ";
        $query .= "(penyakit.kd_penyakit like ? or penyakit.nm_penyakit like ?) group by penyakit.kd_penyakit order by penyakit.kd_penyakit";

        $rows = $this->db()->pdo()->prepare($query);
        $rows->execute([$tgl_awal, $tgl_akhir, '%'.$poliklinik.'%', '%'.$dokter.'%', '%'.$penjab.'%', '%'.$nm_kab.'%', '%'.$nm_kec.'%', '%'.$nm_kel.'%', '%'.$keyword.'%', '%'.$keyword.'%']);
        $rows = $rows->fetchAll(\PDO::FETCH_ASSOC);

        $items = array();
        foreach ($rows as $row) {
            $query2 = "select count(diagnosa_pasien.no_rawat) as jumlah from penyakit inner join diagnosa_pasien inner join reg_periksa ";
            $query2 .= "inner join dokter inner join pasien inner join poliklinik inner join penjab inner join kabupaten inner join kecamatan inner join kelurahan ";
            $query2 .= "on penyakit.kd_penyakit=diagnosa_pasien.kd_penyakit and reg_periksa.no_rawat=diagnosa_pasien.no_rawat and reg_periksa.kd_dokter=dokter.kd_dokter ";
            $query2 .= "and reg_periksa.no_rkm_medis=pasien.no_rkm_medis and reg_periksa.kd_pj=penjab.kd_pj and reg_periksa.kd_poli=poliklinik.kd_poli and ";
            $query2 .= "pasien.kd_kab=kabupaten.kd_kab and pasien.kd_kec=kecamatan.kd_kec and pasien.kd_kel=kelurahan.kd_kel ";
            $query2 .= "where reg_periksa.status_lanjut='Ralan' and diagnosa_pasien.status='Ralan' and diagnosa_pasien.prioritas='1' and reg_periksa.tgl_registrasi between ? and ? and poliklinik.nm_poli like ? and dokter.nm_dokter like ? and penjab.png_jawab like ? and kabupaten.nm_kab like ? and kecamatan.nm_kec like ? and kelurahan.nm_kel like ? and diagnosa_pasien.kd_penyakit=? group by diagnosa_pasien.no_rawat";    
            $rows2 = $this->db()->pdo()->prepare($query2);
            $rows2->execute([$tgl_awal, $tgl_akhir, '%'.$poliklinik.'%', '%'.$dokter.'%', '%'.$penjab.'%', '%'.$nm_kab.'%', '%'.$nm_kec.'%', '%'.$nm_kel.'%', $row['kd_penyakit']]);
            $rows2 = $rows2->fetchAll();


            $query3 = "select diagnosa_pasien.no_rawat as jumlah from penyakit inner join diagnosa_pasien inner join reg_periksa inner join pasien_mati ";
            $query3 .= "inner join dokter inner join pasien inner join poliklinik inner join penjab inner join kabupaten inner join kecamatan inner join kelurahan ";
            $query3 .= "on penyakit.kd_penyakit=diagnosa_pasien.kd_penyakit and reg_periksa.no_rawat=diagnosa_pasien.no_rawat and reg_periksa.kd_dokter=dokter.kd_dokter ";
            $query3 .= "and reg_periksa.no_rkm_medis=pasien.no_rkm_medis and reg_periksa.kd_pj=penjab.kd_pj and reg_periksa.kd_poli=poliklinik.kd_poli and ";
            $query3 .= "pasien.kd_kab=kabupaten.kd_kab and pasien.kd_kec=kecamatan.kd_kec and pasien.kd_kel=kelurahan.kd_kel and pasien_mati.no_rkm_medis=pasien.no_rkm_medis ";
            $query3 .= "where reg_periksa.status_lanjut='Ralan' and diagnosa_pasien.status='Ralan' and diagnosa_pasien.prioritas='1' and pasien.jk='L' and reg_periksa.tgl_registrasi between ? and ? ";
            $query3 .= "and poliklinik.nm_poli like ? and dokter.nm_dokter like ? and penjab.png_jawab like ? and kabupaten.nm_kab like ? and kecamatan.nm_kec like ? and kelurahan.nm_kel like ? ";
            $query3 .= "and diagnosa_pasien.kd_penyakit=? group by diagnosa_pasien.no_rawat";  
            $rows3 = $this->db()->pdo()->prepare($query3);
            $rows3->execute([$tgl_awal, $tgl_akhir, '%'.$poliklinik.'%', '%'.$dokter.'%', '%'.$penjab.'%', '%'.$nm_kab.'%', '%'.$nm_kec.'%', '%'.$nm_kel.'%', $row['kd_penyakit']]);
            $rows3 = $rows3->fetchAll();

            $query4 = "select diagnosa_pasien.no_rawat as jumlah from penyakit inner join diagnosa_pasien inner join reg_periksa ";
            $query4 .= "inner join dokter inner join pasien inner join poliklinik inner join penjab inner join kabupaten inner join kecamatan inner join kelurahan ";
            $query4 .= "on penyakit.kd_penyakit=diagnosa_pasien.kd_penyakit and reg_periksa.no_rawat=diagnosa_pasien.no_rawat and reg_periksa.kd_dokter=dokter.kd_dokter " ;
            $query4 .= "and reg_periksa.no_rkm_medis=pasien.no_rkm_medis and reg_periksa.kd_pj=penjab.kd_pj and reg_periksa.kd_poli=poliklinik.kd_poli and ";
            $query4 .= "pasien.kd_kab=kabupaten.kd_kab and pasien.kd_kec=kecamatan.kd_kec and pasien.kd_kel=kelurahan.kd_kel ";
            $query4 .= "where reg_periksa.status_lanjut='Ralan' and diagnosa_pasien.status='Ralan' and diagnosa_pasien.prioritas='1' and pasien.jk='L' and reg_periksa.tgl_registrasi between ? and ? ";
            $query4 .= "and poliklinik.nm_poli like ? and dokter.nm_dokter like ? and penjab.png_jawab like ? and kabupaten.nm_kab like ? and kecamatan.nm_kec like ? and kelurahan.nm_kel like ? ";
            $query4 .= "and diagnosa_pasien.kd_penyakit=? group by diagnosa_pasien.no_rawat";
            $rows4 = $this->db()->pdo()->prepare($query4);
            $rows4->execute([$tgl_awal, $tgl_akhir, '%'.$poliklinik.'%', '%'.$dokter.'%', '%'.$penjab.'%', '%'.$nm_kab.'%', '%'.$nm_kec.'%', '%'.$nm_kel.'%', $row['kd_penyakit']]);
            $rows4 = $rows4->fetchAll();


            $query5 = "select diagnosa_pasien.no_rawat as jumlah from penyakit inner join diagnosa_pasien inner join reg_periksa inner join pasien_mati ";
            $query5 .= "inner join dokter inner join pasien inner join poliklinik inner join penjab inner join kabupaten inner join kecamatan inner join kelurahan ";
            $query5 .= "on penyakit.kd_penyakit=diagnosa_pasien.kd_penyakit and reg_periksa.no_rawat=diagnosa_pasien.no_rawat and reg_periksa.kd_dokter=dokter.kd_dokter ";
            $query5 .= "and reg_periksa.no_rkm_medis=pasien.no_rkm_medis and reg_periksa.kd_pj=penjab.kd_pj and reg_periksa.kd_poli=poliklinik.kd_poli and ";
            $query5 .= "pasien.kd_kab=kabupaten.kd_kab and pasien.kd_kec=kecamatan.kd_kec and pasien.kd_kel=kelurahan.kd_kel and pasien_mati.no_rkm_medis=pasien.no_rkm_medis ";
            $query5 .= "where reg_periksa.status_lanjut='Ralan' and diagnosa_pasien.status='Ralan' and diagnosa_pasien.prioritas='1' and pasien.jk='P' and reg_periksa.tgl_registrasi between ? and ? ";
            $query5 .= "and poliklinik.nm_poli like ? and dokter.nm_dokter like ? and penjab.png_jawab like ? and kabupaten.nm_kab like ? and kecamatan.nm_kec like ? and kelurahan.nm_kel like ? ";
            $query5 .= "and diagnosa_pasien.kd_penyakit=? group by diagnosa_pasien.no_rawat"; 
            $rows5 = $this->db()->pdo()->prepare($query5);
            $rows5->execute([$tgl_awal, $tgl_akhir, '%'.$poliklinik.'%', '%'.$dokter.'%', '%'.$penjab.'%', '%'.$nm_kab.'%', '%'.$nm_kec.'%', '%'.$nm_kel.'%', $row['kd_penyakit']]);
            $rows5 = $rows5->fetchAll();


            $query6 = "select diagnosa_pasien.no_rawat as jumlah from penyakit inner join diagnosa_pasien inner join reg_periksa ";
            $query6 .= "inner join dokter inner join pasien inner join poliklinik inner join penjab inner join kabupaten inner join kecamatan inner join kelurahan ";
            $query6 .= "on penyakit.kd_penyakit=diagnosa_pasien.kd_penyakit and reg_periksa.no_rawat=diagnosa_pasien.no_rawat and reg_periksa.kd_dokter=dokter.kd_dokter ";
            $query6 .= "and reg_periksa.no_rkm_medis=pasien.no_rkm_medis and reg_periksa.kd_pj=penjab.kd_pj and reg_periksa.kd_poli=poliklinik.kd_poli and ";
            $query6 .= "pasien.kd_kab=kabupaten.kd_kab and pasien.kd_kec=kecamatan.kd_kec and pasien.kd_kel=kelurahan.kd_kel ";
            $query6 .= "where reg_periksa.status_lanjut='Ralan' and diagnosa_pasien.status='Ralan' and diagnosa_pasien.prioritas='1' and pasien.jk='P' and reg_periksa.tgl_registrasi between ? and ? ";
            $query6 .= "and poliklinik.nm_poli like ? and dokter.nm_dokter like ? and penjab.png_jawab like ? and kabupaten.nm_kab like ? and kecamatan.nm_kec like ? and kelurahan.nm_kel like ? ";
            $query6 .= "and diagnosa_pasien.kd_penyakit=? group by diagnosa_pasien.no_rawat";
            $rows6 = $this->db()->pdo()->prepare($query6);
            $rows6->execute([$tgl_awal, $tgl_akhir, '%'.$poliklinik.'%', '%'.$dokter.'%', '%'.$penjab.'%', '%'.$nm_kab.'%', '%'.$nm_kec.'%', '%'.$nm_kel.'%', $row['kd_penyakit']]);
            $rows6 = $rows6->fetchAll();


            $row['diagnosa'] = '';
            $row['a'] = count($rows3);
            $row['c'] = count($rows4)-$row['a'];
            $row['b'] = count($rows5);
            $row['d'] = count($rows6)-$row['b'];
            $row['i'] = count($rows2);
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

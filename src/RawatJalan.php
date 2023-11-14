<?php

namespace Plugins\Khanza\Src;

use Plugins\Khanza\MySQL;

class RawatJalan
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

        if(isset($_POST['cari_stts']) && $_POST['cari_stts'] == 'Semua') {
            $_POST['cari_stts'] = '';
        }

        if(isset($_POST['cari_status_bayar']) && $_POST['cari_status_bayar'] == 'Semua') {
            $_POST['cari_status_bayar'] = '';
        }

        $nama_poli = isset($_POST['cari_nama_poli']) ? $_POST['cari_nama_poli'] : '';
        $nama_dokter = isset($_POST['cari_nama_dokter']) ? $_POST['cari_nama_dokter'] : '';
        $stts = isset_or($_POST['cari_stts'], '');
        $status_bayar = isset($_POST['cari_status_bayar']) ? $_POST['cari_status_bayar'] : '';
        $tgl_registrasi_start = date('Y-m-d', strtotime(isset_or($_POST['cari_tgl_registrasi_start'], date('Y-m-d'))));
        $tgl_registrasi_end = date('Y-m-d', strtotime(isset_or($_POST['cari_tgl_registrasi_end'], date('Y-m-d'))));
        $keyword = isset($_POST['cari_keyword']) ? $_POST['cari_keyword'] : '';

        $result = array();

        $_pasien = "select count(*) as count from reg_periksa inner join dokter inner join pasien inner join poliklinik inner join penjab
        on reg_periksa.kd_dokter=dokter.kd_dokter and reg_periksa.kd_pj=penjab.kd_pj
        and reg_periksa.no_rkm_medis=pasien.no_rkm_medis and reg_periksa.kd_poli=poliklinik.kd_poli  where
        reg_periksa.status_lanjut='Ralan' and poliklinik.nm_poli like '%$nama_poli%' and  dokter.nm_dokter like '%$nama_dokter%' and reg_periksa.stts like '%$stts%' and reg_periksa.status_bayar like '%$status_bayar%' and reg_periksa.tgl_registrasi between '$tgl_registrasi_start' and '$tgl_registrasi_end' and reg_periksa.no_reg like '%$keyword%' or
        reg_periksa.status_lanjut='Ralan' and poliklinik.nm_poli like '%$nama_poli%' and  dokter.nm_dokter like '%$nama_dokter%' and reg_periksa.stts like '%$stts%' and reg_periksa.status_bayar like '%$status_bayar%' and reg_periksa.tgl_registrasi between '$tgl_registrasi_start' and '$tgl_registrasi_end' and reg_periksa.no_rawat like '%$keyword%' or
        reg_periksa.status_lanjut='Ralan' and poliklinik.nm_poli like '%$nama_poli%' and  dokter.nm_dokter like '%$nama_dokter%' and reg_periksa.stts like '%$stts%' and reg_periksa.status_bayar like '%$status_bayar%' and reg_periksa.tgl_registrasi between '$tgl_registrasi_start' and '$tgl_registrasi_end' and reg_periksa.tgl_registrasi like '%$keyword%' or
        reg_periksa.status_lanjut='Ralan' and poliklinik.nm_poli like '%$nama_poli%' and  dokter.nm_dokter like '%$nama_dokter%' and reg_periksa.stts like '%$stts%' and reg_periksa.status_bayar like '%$status_bayar%' and reg_periksa.tgl_registrasi between '$tgl_registrasi_start' and '$tgl_registrasi_end' and reg_periksa.kd_dokter like '%$keyword%' or
        reg_periksa.status_lanjut='Ralan' and poliklinik.nm_poli like '%$nama_poli%' and  dokter.nm_dokter like '%$nama_dokter%' and reg_periksa.stts like '%$stts%' and reg_periksa.status_bayar like '%$status_bayar%' and reg_periksa.tgl_registrasi between '$tgl_registrasi_start' and '$tgl_registrasi_end' and dokter.nm_dokter like '%$keyword%' or
        reg_periksa.status_lanjut='Ralan' and poliklinik.nm_poli like '%$nama_poli%' and  dokter.nm_dokter like '%$nama_dokter%' and reg_periksa.stts like '%$stts%' and reg_periksa.status_bayar like '%$status_bayar%' and reg_periksa.tgl_registrasi between '$tgl_registrasi_start' and '$tgl_registrasi_end' and reg_periksa.no_rkm_medis like '%$keyword%' or
        reg_periksa.status_lanjut='Ralan' and poliklinik.nm_poli like '%$nama_poli%' and  dokter.nm_dokter like '%$nama_dokter%' and reg_periksa.stts like '%$stts%' and reg_periksa.status_bayar like '%$status_bayar%' and reg_periksa.tgl_registrasi between '$tgl_registrasi_start' and '$tgl_registrasi_end' and pasien.nm_pasien like '%$keyword%' or
        reg_periksa.status_lanjut='Ralan' and poliklinik.nm_poli like '%$nama_poli%' and  dokter.nm_dokter like '%$nama_dokter%' and reg_periksa.stts like '%$stts%' and reg_periksa.status_bayar like '%$status_bayar%' and reg_periksa.tgl_registrasi between '$tgl_registrasi_start' and '$tgl_registrasi_end' and poliklinik.nm_poli like '%$keyword%' or
        reg_periksa.status_lanjut='Ralan' and poliklinik.nm_poli like '%$nama_poli%' and  dokter.nm_dokter like '%$nama_dokter%' and reg_periksa.stts like '%$stts%' and reg_periksa.status_bayar like '%$status_bayar%' and reg_periksa.tgl_registrasi between '$tgl_registrasi_start' and '$tgl_registrasi_end' and reg_periksa.p_jawab like '%$keyword%' or
        reg_periksa.status_lanjut='Ralan' and poliklinik.nm_poli like '%$nama_poli%' and  dokter.nm_dokter like '%$nama_dokter%' and reg_periksa.stts like '%$stts%' and reg_periksa.status_bayar like '%$status_bayar%' and reg_periksa.tgl_registrasi between '$tgl_registrasi_start' and '$tgl_registrasi_end' and penjab.png_jawab like '%$keyword%' or
        reg_periksa.status_lanjut='Ralan' and poliklinik.nm_poli like '%$nama_poli%' and  dokter.nm_dokter like '%$nama_dokter%' and reg_periksa.stts like '%$stts%' and reg_periksa.status_bayar like '%$status_bayar%' and reg_periksa.tgl_registrasi between '$tgl_registrasi_start' and '$tgl_registrasi_end' and reg_periksa.almt_pj like '%$keyword%' or
        reg_periksa.status_lanjut='Ralan' and poliklinik.nm_poli like '%$nama_poli%' and  dokter.nm_dokter like '%$nama_dokter%' and reg_periksa.stts like '%$stts%' and reg_periksa.status_bayar like '%$status_bayar%' and reg_periksa.tgl_registrasi between '$tgl_registrasi_start' and '$tgl_registrasi_end' and reg_periksa.status_bayar like '%$keyword%' or
        reg_periksa.status_lanjut='Ralan' and poliklinik.nm_poli like '%$nama_poli%' and  dokter.nm_dokter like '%$nama_dokter%' and reg_periksa.stts like '%$stts%' and reg_periksa.status_bayar like '%$status_bayar%' and reg_periksa.tgl_registrasi between '$tgl_registrasi_start' and '$tgl_registrasi_end' and reg_periksa.hubunganpj like '%$keyword%'";

        $_rows = $this->db()->pdo()->prepare($_pasien);
        $_rows->execute();
        $_rows = $_rows->fetchAll(\PDO::FETCH_ASSOC);          
        $result["total"] = $_rows['0']['count'];

        $items = array();

        $pasien = "select reg_periksa.no_reg,reg_periksa.no_rawat,reg_periksa.tgl_registrasi,reg_periksa.jam_reg,
        reg_periksa.kd_dokter,dokter.nm_dokter,reg_periksa.no_rkm_medis,pasien.nm_pasien,poliklinik.nm_poli,
        reg_periksa.p_jawab,reg_periksa.almt_pj,reg_periksa.hubunganpj,reg_periksa.biaya_reg,reg_periksa.stts,penjab.png_jawab,concat(reg_periksa.umurdaftar,' ',reg_periksa.sttsumur)as umur,
        reg_periksa.status_bayar,reg_periksa.status_poli,reg_periksa.kd_pj,reg_periksa.kd_poli from reg_periksa inner join dokter inner join pasien inner join poliklinik inner join penjab
        on reg_periksa.kd_dokter=dokter.kd_dokter and reg_periksa.kd_pj=penjab.kd_pj
        and reg_periksa.no_rkm_medis=pasien.no_rkm_medis and reg_periksa.kd_poli=poliklinik.kd_poli  where
        reg_periksa.status_lanjut='Ralan' and poliklinik.nm_poli like '%$nama_poli%' and  dokter.nm_dokter like '%$nama_dokter%' and reg_periksa.stts like '%$stts%' and reg_periksa.status_bayar like '%$status_bayar%' and reg_periksa.tgl_registrasi between '$tgl_registrasi_start' and '$tgl_registrasi_end' and reg_periksa.no_reg like '%$keyword%' or
        reg_periksa.status_lanjut='Ralan' and poliklinik.nm_poli like '%$nama_poli%' and  dokter.nm_dokter like '%$nama_dokter%' and reg_periksa.stts like '%$stts%' and reg_periksa.status_bayar like '%$status_bayar%' and reg_periksa.tgl_registrasi between '$tgl_registrasi_start' and '$tgl_registrasi_end' and reg_periksa.no_rawat like '%$keyword%' or
        reg_periksa.status_lanjut='Ralan' and poliklinik.nm_poli like '%$nama_poli%' and  dokter.nm_dokter like '%$nama_dokter%' and reg_periksa.stts like '%$stts%' and reg_periksa.status_bayar like '%$status_bayar%' and reg_periksa.tgl_registrasi between '$tgl_registrasi_start' and '$tgl_registrasi_end' and reg_periksa.tgl_registrasi like '%$keyword%' or
        reg_periksa.status_lanjut='Ralan' and poliklinik.nm_poli like '%$nama_poli%' and  dokter.nm_dokter like '%$nama_dokter%' and reg_periksa.stts like '%$stts%' and reg_periksa.status_bayar like '%$status_bayar%' and reg_periksa.tgl_registrasi between '$tgl_registrasi_start' and '$tgl_registrasi_end' and reg_periksa.kd_dokter like '%$keyword%' or
        reg_periksa.status_lanjut='Ralan' and poliklinik.nm_poli like '%$nama_poli%' and  dokter.nm_dokter like '%$nama_dokter%' and reg_periksa.stts like '%$stts%' and reg_periksa.status_bayar like '%$status_bayar%' and reg_periksa.tgl_registrasi between '$tgl_registrasi_start' and '$tgl_registrasi_end' and dokter.nm_dokter like '%$keyword%' or
        reg_periksa.status_lanjut='Ralan' and poliklinik.nm_poli like '%$nama_poli%' and  dokter.nm_dokter like '%$nama_dokter%' and reg_periksa.stts like '%$stts%' and reg_periksa.status_bayar like '%$status_bayar%' and reg_periksa.tgl_registrasi between '$tgl_registrasi_start' and '$tgl_registrasi_end' and reg_periksa.no_rkm_medis like '%$keyword%' or
        reg_periksa.status_lanjut='Ralan' and poliklinik.nm_poli like '%$nama_poli%' and  dokter.nm_dokter like '%$nama_dokter%' and reg_periksa.stts like '%$stts%' and reg_periksa.status_bayar like '%$status_bayar%' and reg_periksa.tgl_registrasi between '$tgl_registrasi_start' and '$tgl_registrasi_end' and pasien.nm_pasien like '%$keyword%' or
        reg_periksa.status_lanjut='Ralan' and poliklinik.nm_poli like '%$nama_poli%' and  dokter.nm_dokter like '%$nama_dokter%' and reg_periksa.stts like '%$stts%' and reg_periksa.status_bayar like '%$status_bayar%' and reg_periksa.tgl_registrasi between '$tgl_registrasi_start' and '$tgl_registrasi_end' and poliklinik.nm_poli like '%$keyword%' or
        reg_periksa.status_lanjut='Ralan' and poliklinik.nm_poli like '%$nama_poli%' and  dokter.nm_dokter like '%$nama_dokter%' and reg_periksa.stts like '%$stts%' and reg_periksa.status_bayar like '%$status_bayar%' and reg_periksa.tgl_registrasi between '$tgl_registrasi_start' and '$tgl_registrasi_end' and reg_periksa.p_jawab like '%$keyword%' or
        reg_periksa.status_lanjut='Ralan' and poliklinik.nm_poli like '%$nama_poli%' and  dokter.nm_dokter like '%$nama_dokter%' and reg_periksa.stts like '%$stts%' and reg_periksa.status_bayar like '%$status_bayar%' and reg_periksa.tgl_registrasi between '$tgl_registrasi_start' and '$tgl_registrasi_end' and penjab.png_jawab like '%$keyword%' or
        reg_periksa.status_lanjut='Ralan' and poliklinik.nm_poli like '%$nama_poli%' and  dokter.nm_dokter like '%$nama_dokter%' and reg_periksa.stts like '%$stts%' and reg_periksa.status_bayar like '%$status_bayar%' and reg_periksa.tgl_registrasi between '$tgl_registrasi_start' and '$tgl_registrasi_end' and reg_periksa.almt_pj like '%$keyword%' or
        reg_periksa.status_lanjut='Ralan' and poliklinik.nm_poli like '%$nama_poli%' and  dokter.nm_dokter like '%$nama_dokter%' and reg_periksa.stts like '%$stts%' and reg_periksa.status_bayar like '%$status_bayar%' and reg_periksa.tgl_registrasi between '$tgl_registrasi_start' and '$tgl_registrasi_end' and reg_periksa.status_bayar like '%$keyword%' or
        reg_periksa.status_lanjut='Ralan' and poliklinik.nm_poli like '%$nama_poli%' and  dokter.nm_dokter like '%$nama_dokter%' and reg_periksa.stts like '%$stts%' and reg_periksa.status_bayar like '%$status_bayar%' and reg_periksa.tgl_registrasi between '$tgl_registrasi_start' and '$tgl_registrasi_end' and reg_periksa.hubunganpj like '%$keyword%'
        order by reg_periksa.tgl_registrasi desc LIMIT $offset,$rows";

        $rows = $this->db()->pdo()->prepare($pasien);
        $rows->execute();
        $rows = $rows->fetchAll();

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

}

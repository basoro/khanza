<?php
namespace Plugins\Khanza\Src;

use Systems\AdminModule;
use Plugins\Khanza\MySQL;
use Plugins\Khanza\Jasper\JasperPHP;

class Jasper {

    protected function db($table = NULL)
    {
        return new MySQL($table);
    }

    function convertNorawat($text)
    {
        setlocale(LC_ALL, 'en_EN');
        $text = str_replace('/', '', trim($text));
        return $text;
    }
    
    function revertNorawat($text)
    {
        setlocale(LC_ALL, 'en_EN');
        $tahun = substr($text, 0, 4);
        $bulan = substr($text, 4, 2);
        $tanggal = substr($text, 6, 2);
        $nomor = substr($text, 8, 6);
        $result = $tahun.'/'.$bulan.'/'.$tanggal.'/'.$nomor;
        return $result;
    }

    public function getKartu($no_rkm_medis)
    {
        $khanza = array_column($this->db('mlite_settings')->where('module', 'khanza')->toArray(), 'value', 'field');

        $report = 'rptKartuBerobat';
        $time = time();

        $query = "select pasien.no_rkm_medis, pasien.nm_pasien, pasien.no_ktp, pasien.jk, pasien.tmp_lahir, pasien.tgl_lahir, concat(pasien.alamat,', ',kelurahan.nm_kel,', ',kecamatan.nm_kec,', ',kabupaten.nm_kab,', ',propinsi.nm_prop) as alamat, pasien.gol_darah, pasien.pekerjaan, pasien.stts_nikah,pasien.agama,pasien.tgl_daftar,pasien.no_tlp,pasien.umur, pasien.pnd, pasien.keluarga, pasien.namakeluarga from pasien inner join kelurahan inner join kecamatan inner join kabupaten inner join propinsi on pasien.kd_prop=propinsi.kd_prop and pasien.kd_kel=kelurahan.kd_kel and pasien.kd_kec=kecamatan.kd_kec and pasien.kd_kab=kabupaten.kd_kab where pasien.no_rkm_medis=$no_rkm_medis";

        $jasper = new JasperPHP;
        // $jasper->compile(MODULES.'/khanza/jasper/report/' . $report . '.jrxml')->execute();
        $jasper->process(
            MODULES . '/khanza/jasper/report/' . $report . '.jasper',
            UPLOADS . '/report/' . $report . '_' . $time,
            ['pdf'],
            [
              'query' => $query
            ],
            [
              'driver' => 'mysql',
              'username' => $khanza['username'],
              'password' => $khanza['password'],
              'host' => $khanza['host'],
              'database' => $khanza['database'],
              'port' => $khanza['port']
            ]
        )->execute();

        $file = UPLOADS . '/report/' . $report . '_' . $time . '.pdf';
        $filename = $report . '_' . $time . '.pdf';

        header('Content-type: application/pdf');
        header('Content-Disposition: inline; filename="' . $filename . '"');
        header('Content-Transfer-Encoding: binary');
        header('Accept-Ranges: bytes');
        // Read the file
        @readfile($file);

        exit();
    }

    public function getCoverRM($no_rkm_medis)
    {
        $settings = array_column($this->db('mlite_settings')->where('module', 'settings')->toArray(), 'value', 'field');
        $khanza = array_column($this->db('mlite_settings')->where('module', 'khanza')->toArray(), 'value', 'field');
        $report = 'rptCoverMap';

        $query = "select pasien.no_rkm_medis, pasien.nm_pasien, pasien.no_ktp, pasien.jk,
        pasien.tmp_lahir, pasien.tgl_lahir,pasien.nm_ibu, concat(pasien.alamat,', ',kelurahan.nm_kel,', ',kecamatan.nm_kec,', ',kabupaten.nm_kab) as alamat, pasien.gol_darah, pasien.pekerjaan,
        pasien.stts_nikah,pasien.agama,pasien.tgl_daftar,pasien.no_tlp,pasien.umur,
        pasien.pnd, pasien.keluarga, pasien.namakeluarga,penjab.png_jawab,pasien.pekerjaanpj,
        concat(pasien.alamatpj,', ',pasien.kelurahanpj,', ',pasien.kecamatanpj,', ',pasien.kabupatenpj) as alamatpj from pasien
        inner join kelurahan inner join kecamatan inner join kabupaten
        inner join penjab on pasien.kd_pj=penjab.kd_pj and pasien.kd_kel=kelurahan.kd_kel
        and pasien.kd_kec=kecamatan.kd_kec and pasien.kd_kab=kabupaten.kd_kab where pasien.no_rkm_medis=$no_rkm_medis";

        $time = time();

        $jasper = new JasperPHP;
        $jasper->compile(MODULES.'/khanza/jasper/report/' . $report . '.jrxml')->execute();
        $jasper->process(
            MODULES . '/khanza/jasper/report/' . $report . '.jasper',
            UPLOADS . '/report/' . $report . '_' . $time,
            ['pdf'],
            [
                'namars' => $settings['nama_instansi'],
                'alamatrs' => $settings['alamat'],
                'kotars' => $settings['kota'],
                'propinsirs' => $settings['propinsi'],
                'kontakrs' => $settings['nomor_telepon'],
                'emailrs' => $settings['email'],
                'logo' => url([$settings['logo']]),
                'query' => $query
            ],
            [
                'driver' => 'mysql',
                'username' => $khanza['username'],
                'password' => $khanza['password'],
                'host' => $khanza['host'],
                'database' => $khanza['database'],
                'port' => $khanza['port']  
            ]
        )->execute();

        $file = UPLOADS . '/report/' . $report . '_' . $time . '.pdf';
        $filename = $report . '_' . $time . '.pdf';

        header('Content-type: application/pdf');
        header('Content-Disposition: inline; filename="' . $filename . '"');
        header('Content-Transfer-Encoding: binary');
        header('Accept-Ranges: bytes');
        // Read the file
        @readfile($file);

        exit();
    }

    public function getPasien($cari_alamat, $cari_keyword)
    {

        $settings = array_column($this->db('mlite_settings')->where('module', 'settings')->toArray(), 'value', 'field');
        $khanza = array_column($this->db('mlite_settings')->where('module', 'khanza')->toArray(), 'value', 'field');
        $report = 'rptPasien';
        $time = time();

        $query = "select pasien.no_rkm_medis, pasien.nm_pasien, pasien.no_ktp, pasien.jk, pasien.tmp_lahir, pasien.tgl_lahir,pasien.nm_ibu, concat(pasien.alamat,', ',kelurahan.nm_kel,', ',kecamatan.nm_kec,', ',kabupaten.nm_kab,', ',propinsi.nm_prop) as alamat,pasien.gol_darah, pasien.pekerjaan,pasien.stts_nikah,pasien.agama,pasien.tgl_daftar,pasien.no_tlp,pasien.umur,pasien.pnd, pasien.keluarga, pasien.namakeluarga,penjab.png_jawab,pasien.no_peserta,pasien.pekerjaanpj,concat(pasien.alamatpj,', ',pasien.kelurahanpj,', ',pasien.kecamatanpj,', ',pasien.kabupatenpj,', ',pasien.propinsipj),perusahaan_pasien.kode_perusahaan,perusahaan_pasien.nama_perusahaan,pasien.bahasa_pasien,bahasa_pasien.nama_bahasa,pasien.suku_bangsa,suku_bangsa.nama_suku_bangsa,pasien.nip,pasien.email,cacat_fisik.nama_cacat,pasien.cacat_fisik from pasien inner join kelurahan inner join kecamatan inner join kabupaten inner join perusahaan_pasien inner join cacat_fisik inner join propinsi inner join bahasa_pasien inner join suku_bangsa inner join penjab on pasien.kd_pj=penjab.kd_pj and pasien.cacat_fisik=cacat_fisik.id and pasien.kd_kel=kelurahan.kd_kel and perusahaan_pasien.kode_perusahaan=pasien.perusahaan_pasien and pasien.kd_prop=propinsi.kd_prop and bahasa_pasien.id=pasien.bahasa_pasien and suku_bangsa.id=pasien.suku_bangsa and pasien.kd_kec=kecamatan.kd_kec and pasien.kd_kab=kabupaten.kd_kab where pasien.no_rkm_medis like '%$cari_keyword%' or pasien.nm_pasien like '%$cari_keyword%' or pasien.no_ktp like '%$cari_keyword%' or pasien.no_peserta like '%$cari_keyword%' or pasien.tmp_lahir like '%$cari_keyword%' or pasien.tgl_lahir like '%$cari_keyword%' or penjab.png_jawab like '%$cari_keyword%' or pasien.alamat like '%$cari_keyword%' or pasien.gol_darah like '%$cari_keyword%' or pasien.pekerjaan like '%$cari_keyword%' or pasien.stts_nikah like '%$cari_keyword%' or pasien.nip like '%$cari_keyword%' or cacat_fisik.nama_cacat like '%$cari_keyword%' or pasien.namakeluarga like '%$cari_keyword%' or perusahaan_pasien.nama_perusahaan like '%$cari_keyword%' or bahasa_pasien.nama_bahasa like '%$cari_keyword%' or suku_bangsa.nama_suku_bangsa like '%$cari_keyword%' or pasien.agama like '%$cari_keyword%' or pasien.nm_ibu like '%$cari_keyword%' or pasien.tgl_daftar like '%$cari_keyword%' or pasien.no_tlp like '%$cari_keyword%' order by pasien.no_rkm_medis desc";

        $jasper = new JasperPHP;
        // $jasper->compile(MODULES.'/khanza/jasper/report/' . $report . '.jrxml')->execute();
        $jasper->process(
            MODULES . '/khanza/jasper/report/' . $report . '.jasper',
            UPLOADS . '/report/' . $report . '_' . $time,
            ['pdf'],
            [
                'namars' => $settings['nama_instansi'],
                'alamatrs' => $settings['alamat'],
                'kotars' => $settings['kota'],
                'propinsirs' => $settings['propinsi'],
                'kontakrs' => $settings['nomor_telepon'],
                'emailrs' => $settings['email'],
                'logo' => url([$settings['logo']]),
                'query' => $query
            ],
            [
                'driver' => 'mysql',
                'username' => $khanza['username'],
                'password' => $khanza['password'],
                'host' => $khanza['host'],
                'database' => $khanza['database'],
                'port' => $khanza['port']  
            ]
        )->execute();

        $file = UPLOADS . '/report/' . $report . '_' . $time . '.pdf';
        $filename = $report . '_' . $time . '.pdf';
        header('Content-type: application/pdf');
        header('Content-Disposition: inline; filename="' . $filename . '"');
        header('Content-Transfer-Encoding: binary');
        header('Accept-Ranges: bytes');

        // Read the file
        @readfile($file);
        exit();

    }

}

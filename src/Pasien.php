<?php

namespace Plugins\Khanza\Src;

use Plugins\Khanza\MySQL;

class Pasien
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

        $alamat = isset($_POST['cari_alamat']) ? $_POST['cari_alamat'] : '';
        $keyword = isset($_POST['cari_keyword']) ? $_POST['cari_keyword'] : '';
  
        $items = array();
        $result = array();
        
        $pasien = "select pasien.no_rkm_medis, pasien.nm_pasien, pasien.no_ktp,
        case pasien.jk
            when 'L' then 'Laki-Laki'
            when 'P' then 'Perempuan'
        end as jk,
        pasien.tmp_lahir, pasien.tgl_lahir,pasien.nm_ibu, concat(pasien.alamat,', ',kelurahan.nm_kel,', ',kecamatan.nm_kec,', ',kabupaten.nm_kab,', ',propinsi.nm_prop) as alamat_pasien,
        pasien.alamat, pasien.kd_kel, kelurahan.nm_kel, pasien.kd_kec, kecamatan.nm_kec, pasien.kd_kab, kabupaten.nm_kab, pasien.kd_prop, propinsi.nm_prop, 
        pasien.gol_darah, pasien.pekerjaan,pasien.stts_nikah,pasien.agama,pasien.tgl_daftar,pasien.no_tlp,pasien.umur,
        pasien.pnd, pasien.keluarga, pasien.namakeluarga,pasien.kd_pj,penjab.png_jawab,pasien.no_peserta,pasien.pekerjaanpj,
        concat(pasien.alamatpj,', ',pasien.kelurahanpj,', ',pasien.kecamatanpj,', ',pasien.kabupatenpj,', ',pasien.propinsipj) as alamat_pj, 
        pasien.alamatpj, pasien.kelurahanpj, pasien.kecamatanpj, pasien.kabupatenpj, pasien.propinsipj,         
        perusahaan_pasien.kode_perusahaan,perusahaan_pasien.nama_perusahaan,pasien.bahasa_pasien,
        bahasa_pasien.nama_bahasa,pasien.suku_bangsa,suku_bangsa.nama_suku_bangsa,pasien.nip,pasien.email,cacat_fisik.nama_cacat,pasien.cacat_fisik from pasien
        inner join kelurahan inner join kecamatan inner join kabupaten inner join perusahaan_pasien inner join cacat_fisik inner join propinsi
        inner join bahasa_pasien inner join suku_bangsa inner join penjab on pasien.kd_pj=penjab.kd_pj and pasien.cacat_fisik=cacat_fisik.id
        and pasien.kd_kel=kelurahan.kd_kel and perusahaan_pasien.kode_perusahaan=pasien.perusahaan_pasien and pasien.kd_prop=propinsi.kd_prop
        and bahasa_pasien.id=pasien.bahasa_pasien and suku_bangsa.id=pasien.suku_bangsa and pasien.kd_kec=kecamatan.kd_kec and pasien.kd_kab=kabupaten.kd_kab
        where concat(pasien.alamat,', ',kelurahan.nm_kel,', ',kecamatan.nm_kec,', ',kabupaten.nm_kab,', ',propinsi.nm_prop) like '%$alamat%' and pasien.no_rkm_medis like '%$keyword%'
        or  concat(pasien.alamat,', ',kelurahan.nm_kel,', ',kecamatan.nm_kec,', ',kabupaten.nm_kab,', ',propinsi.nm_prop) like '%$alamat%'  and pasien.nm_pasien like '%$keyword%'
        or  concat(pasien.alamat,', ',kelurahan.nm_kel,', ',kecamatan.nm_kec,', ',kabupaten.nm_kab,', ',propinsi.nm_prop) like '%$alamat%'  and pasien.no_ktp like '%$keyword%'
        or  concat(pasien.alamat,', ',kelurahan.nm_kel,', ',kecamatan.nm_kec,', ',kabupaten.nm_kab,', ',propinsi.nm_prop) like '%$alamat%'  and pasien.no_peserta like '%$keyword%'
        or  concat(pasien.alamat,', ',kelurahan.nm_kel,', ',kecamatan.nm_kec,', ',kabupaten.nm_kab,', ',propinsi.nm_prop) like '%$alamat%'  and pasien.tmp_lahir like '%$keyword%'
        or  concat(pasien.alamat,', ',kelurahan.nm_kel,', ',kecamatan.nm_kec,', ',kabupaten.nm_kab,', ',propinsi.nm_prop) like '%$alamat%'  and pasien.tgl_lahir like '%$keyword%'
        or  concat(pasien.alamat,', ',kelurahan.nm_kel,', ',kecamatan.nm_kec,', ',kabupaten.nm_kab,', ',propinsi.nm_prop) like '%$alamat%'  and penjab.png_jawab like '%$keyword%'
        or  concat(pasien.alamat,', ',kelurahan.nm_kel,', ',kecamatan.nm_kec,', ',kabupaten.nm_kab,', ',propinsi.nm_prop) like '%$alamat%'  and pasien.gol_darah like '%$keyword%'
        or  concat(pasien.alamat,', ',kelurahan.nm_kel,', ',kecamatan.nm_kec,', ',kabupaten.nm_kab,', ',propinsi.nm_prop) like '%$alamat%'  and pasien.pekerjaan like '%$keyword%'
        or  concat(pasien.alamat,', ',kelurahan.nm_kel,', ',kecamatan.nm_kec,', ',kabupaten.nm_kab,', ',propinsi.nm_prop) like '%$alamat%'  and pasien.stts_nikah like '%$keyword%'
        or  concat(pasien.alamat,', ',kelurahan.nm_kel,', ',kecamatan.nm_kec,', ',kabupaten.nm_kab,', ',propinsi.nm_prop) like '%$alamat%'  and pasien.alamat like '%$keyword%'
        or  concat(pasien.alamat,', ',kelurahan.nm_kel,', ',kecamatan.nm_kec,', ',kabupaten.nm_kab,', ',propinsi.nm_prop) like '%$alamat%'  and pasien.nip like '%$keyword%'
        or  concat(pasien.alamat,', ',kelurahan.nm_kel,', ',kecamatan.nm_kec,', ',kabupaten.nm_kab,', ',propinsi.nm_prop) like '%$alamat%'  and cacat_fisik.nama_cacat like '%$keyword%'
        or  concat(pasien.alamat,', ',kelurahan.nm_kel,', ',kecamatan.nm_kec,', ',kabupaten.nm_kab,', ',propinsi.nm_prop) like '%$alamat%'  and pasien.namakeluarga like '%$keyword%'
        or  concat(pasien.alamat,', ',kelurahan.nm_kel,', ',kecamatan.nm_kec,', ',kabupaten.nm_kab,', ',propinsi.nm_prop) like '%$alamat%'  and perusahaan_pasien.nama_perusahaan like '%$keyword%'
        or  concat(pasien.alamat,', ',kelurahan.nm_kel,', ',kecamatan.nm_kec,', ',kabupaten.nm_kab,', ',propinsi.nm_prop) like '%$alamat%'  and bahasa_pasien.nama_bahasa like '%$keyword%'
        or  concat(pasien.alamat,', ',kelurahan.nm_kel,', ',kecamatan.nm_kec,', ',kabupaten.nm_kab,', ',propinsi.nm_prop) like '%$alamat%'  and suku_bangsa.nama_suku_bangsa like '%$keyword%'
        or  concat(pasien.alamat,', ',kelurahan.nm_kel,', ',kecamatan.nm_kec,', ',kabupaten.nm_kab,', ',propinsi.nm_prop) like '%$alamat%'  and pasien.agama like '%$keyword%'
        or  concat(pasien.alamat,', ',kelurahan.nm_kel,', ',kecamatan.nm_kec,', ',kabupaten.nm_kab,', ',propinsi.nm_prop) like '%$alamat%'  and pasien.nm_ibu like '%$keyword%'
        or  concat(pasien.alamat,', ',kelurahan.nm_kel,', ',kecamatan.nm_kec,', ',kabupaten.nm_kab,', ',propinsi.nm_prop) like '%$alamat%'  and pasien.tgl_daftar like '%$keyword%'
        or  concat(pasien.alamat,', ',kelurahan.nm_kel,', ',kecamatan.nm_kec,', ',kabupaten.nm_kab,', ',propinsi.nm_prop) like '%$alamat%'  and pasien.no_tlp like '%$keyword%' ";

        $_rows = $this->db()->pdo()->prepare($pasien);
        $_rows->execute();
        $_rows = $_rows->fetchAll(\PDO::FETCH_ASSOC);          
        $result["total"] = count($_rows);

        $pasien .= "order by pasien.no_rkm_medis desc LIMIT $offset,$rows";
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
        // $_POST['tgl_lahir'] = date('Y-m-d', strtotime($_POST['tgl_lahir']));
        // $_POST['tgl_daftar'] = date('Y-m-d', strtotime($_POST['tgl_daftar']));
        $check_db = $this->db()->pdo()->prepare("INSERT INTO pasien VALUES (
          '{$_POST['no_rkm_medis']}', '{$_POST['nm_pasien']}', '{$_POST['no_ktp']}', '{$_POST['jk']}', '{$_POST['tmp_lahir']}', '{$_POST['tgl_lahir']}', '{$_POST['nm_ibu']}', '{$_POST['alamat']}', '{$_POST['gol_darah']}', '{$_POST['pekerjaan']}', '{$_POST['stts_nikah']}', '{$_POST['agama']}', '{$_POST['tgl_daftar']}', '{$_POST['no_tlp']}', '{$_POST['umur']}', '{$_POST['pnd']}', '{$_POST['keluarga']}', '{$_POST['namakeluarga']}', '{$_POST['kd_pj']}', '{$_POST['no_peserta']}', '{$_POST['kd_kel']}', '{$_POST['kd_kec']}', '{$_POST['kd_kab']}', '{$_POST['pekerjaanpj']}', '{$_POST['alamatpj']}', '{$_POST['kelurahanpj']}', '{$_POST['kecamatanpj']}', '{$_POST['kabupatenpj']}', '{$_POST['perusahaan_pasien']}', '{$_POST['suku_bangsa']}', '{$_POST['bahasa_pasien']}', '{$_POST['cacat_fisik']}', '{$_POST['email']}', '{$_POST['nip']}', '{$_POST['kd_prop']}', '{$_POST['propinsipj']}'
        )");
        $result = $check_db->execute();
        $error = $check_db->errorInfo();
        if (!empty($result)){
          echo json_encode(array(
            'no_rkm_medis' => $_POST['no_rkm_medis']
          ));
          $this->db('set_no_rkm_medis')->delete();
          $this->db('set_no_rkm_medis')->save(['no_rkm_medis' => $_POST['no_rkm_medis']]);
        } else {
          echo json_encode(array('errorMsg'=>$error['2']));
        }        
    }

    public function Ubah()
    {
        $no_rkm_medis = $_POST['no_rkm_medis'];
        $nm_pasien = $_POST['nm_pasien'];
        $check_db = $this->db()->pdo()->prepare("
          UPDATE 
            pasien 
          SET 
            nm_pasien = '{$_POST['nm_pasien']}', 
            no_ktp = '{$_POST['no_ktp']}', 
            jk = '{$_POST['jk']}', 
            tgl_lahir = '{$_POST['tgl_lahir']}', 
            tmp_lahir = '{$_POST['tmp_lahir']}', 
            nm_ibu = '{$_POST['nm_ibu']}', 
            alamat = '{$_POST['alamat']}', 
            gol_darah = '{$_POST['gol_darah']}', 
            pekerjaan = '{$_POST['pekerjaan']}', 
            stts_nikah = '{$_POST['stts_nikah']}', 
            agama = '{$_POST['agama']}', 
            tgl_daftar = '{$_POST['tgl_daftar']}', 
            no_tlp = '{$_POST['no_tlp']}', 
            umur = '{$this->hitungUmur($_POST['tgl_lahir'])}', 
            pnd = '{$_POST['pnd']}', 
            keluarga = '{$_POST['keluarga']}', 
            namakeluarga = '{$_POST['namakeluarga']}', 
            kd_pj = '{$_POST['kd_pj']}', 
            no_peserta = '{$_POST['no_peserta']}', 
            kd_kel = '{$_POST['kd_kel']}', 
            kd_kec = '{$_POST['kd_kec']}', 
            kd_kab = '{$_POST['kd_kab']}', 
            pekerjaanpj = '{$_POST['pekerjaanpj']}', 
            alamatpj = '{$_POST['alamatpj']}', 
            kelurahanpj = '{$_POST['kelurahanpj']}', 
            kecamatanpj = '{$_POST['kecamatanpj']}', 
            kabupatenpj = '{$_POST['kabupatenpj']}', 
            perusahaan_pasien = '{$_POST['perusahaan_pasien']}', 
            suku_bangsa = '{$_POST['suku_bangsa']}', 
            bahasa_pasien = '{$_POST['bahasa_pasien']}', 
            cacat_fisik = '{$_POST['cacat_fisik']}', 
            email = '{$_POST['email']}', 
            nip = '{$_POST['nip']}', 
            kd_prop = '{$_POST['kd_prop']}', 
            propinsipj = '{$_POST['propinsipj']}'
          WHERE 
            no_rkm_medis = '{$_POST['no_rkm_medis']}'
        ");
        $result = $check_db->execute();
        $error = $check_db->errorInfo();
        if (!empty($result)){
          echo json_encode(array(
            'no_rkm_medis' => $_POST['no_rkm_medis']
          ));
        } else {
          echo json_encode(array('errorMsg'=>$error['2']));
        }
    }

    public function Hapus()
    {
        $no_rkm_medis = $_POST['no_rkm_medis'];
        $check_db = $this->db()->pdo()->prepare("DELETE FROM pasien WHERE no_rkm_medis = '$no_rkm_medis'");
        $result = $check_db->execute();
        $error = $check_db->errorInfo();
        if (!empty($result)){
          echo json_encode(array(
            'no_rkm_medis' => $no_rkm_medis
          ));
        } else {
          echo json_encode(array('errorMsg'=>$error['2']));
        }
    }

    public function hitungUmur($tanggal_lahir)
    {
      	$birthDate = new \DateTime($tanggal_lahir);
      	$today = new \DateTime("today");
      	$umur = "0 Th 0 Bl 0 Hr";
        if ($birthDate < $today) {
        	$y = $today->diff($birthDate)->y;
        	$m = $today->diff($birthDate)->m;
        	$d = $today->diff($birthDate)->d;
          $umur =  $y." Th ".$m." Bl ".$d." Hr";
        }
      	return $umur;
    }


}

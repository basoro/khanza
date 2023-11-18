<?php

namespace Plugins\Khanza\Src;

use Plugins\Khanza\MySQL;

class RegPeriksa
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
        $stts = isset($_POST['cari_stts']) ? $_POST['cari_stts'] : '';
        $status_bayar = isset($_POST['cari_status_bayar']) ? $_POST['cari_status_bayar'] : '';
        $tgl_awal = isset($_POST['cari_tgl_registrasi_start']) ? $_POST['cari_tgl_registrasi_start'] : date('Y-m-d');
        $tgl_akhir = isset($_POST['cari_tgl_registrasi_end']) ? $_POST['cari_tgl_registrasi_end'] : date('Y-m-d');
  
        $query = "select reg_periksa.no_reg,reg_periksa.no_rawat,reg_periksa.tgl_registrasi,reg_periksa.jam_reg, ";
        $query .= "reg_periksa.kd_dokter,dokter.nm_dokter,reg_periksa.no_rkm_medis,pasien.nm_pasien,pasien.jk,concat(reg_periksa.umurdaftar,' ',reg_periksa.sttsumur)as umur,poliklinik.nm_poli, ";
        $query .= "reg_periksa.p_jawab,reg_periksa.almt_pj,reg_periksa.hubunganpj,reg_periksa.biaya_reg,reg_periksa.stts_daftar,penjab.png_jawab,pasien.no_tlp,reg_periksa.stts,reg_periksa.status_poli, ";
        $query .= "reg_periksa.kd_poli,reg_periksa.kd_pj,reg_periksa.status_bayar from reg_periksa inner join dokter on reg_periksa.kd_dokter=dokter.kd_dokter inner join pasien on reg_periksa.no_rkm_medis=pasien.no_rkm_medis ";
        $query .= "inner join poliklinik on reg_periksa.kd_poli=poliklinik.kd_poli inner join penjab on reg_periksa.kd_pj=penjab.kd_pj where ";
        $query .= "poliklinik.kd_poli<>'IGDK' and poliklinik.nm_poli like ? and  dokter.nm_dokter like ? and reg_periksa.tgl_registrasi between ? and ? and ";
        $query .= "(reg_periksa.no_reg like ? or reg_periksa.no_rawat like ? or reg_periksa.tgl_registrasi like ? or reg_periksa.kd_dokter like ? or ";
        $query .= "dokter.nm_dokter like ? or reg_periksa.no_rkm_medis like ? or reg_periksa.stts_daftar like ? or pasien.nm_pasien like ? or ";
        $query .= "poliklinik.nm_poli like ? or reg_periksa.p_jawab like ? or reg_periksa.almt_pj like ? or reg_periksa.hubunganpj like ? or penjab.png_jawab like ?) ";

        $result = array();
        
        $total = $this->db()->pdo()->prepare($query);
        $total->execute(['%'.$poliklinik.'%', '%'.$dokter.'%', $tgl_awal, $tgl_akhir, '%'.$keyword.'%', '%'.$keyword.'%', '%'.$keyword.'%', '%'.$keyword.'%', '%'.$keyword.'%', '%'.$keyword.'%', '%'.$keyword.'%', '%'.$keyword.'%', '%'.$keyword.'%', '%'.$keyword.'%', '%'.$keyword.'%', '%'.$keyword.'%', '%'.$keyword.'%']);
        $total = $total->fetchAll(\PDO::FETCH_ASSOC);          
        $result["total"] = count($total);

        $query .= "order by reg_periksa.tgl_registrasi, reg_periksa.jam_reg asc LIMIT $offset,$rows";    

        $rows = $this->db()->pdo()->prepare($query);
        $rows->execute(['%'.$poliklinik.'%', '%'.$dokter.'%', $tgl_awal, $tgl_akhir, '%'.$keyword.'%', '%'.$keyword.'%', '%'.$keyword.'%', '%'.$keyword.'%', '%'.$keyword.'%', '%'.$keyword.'%', '%'.$keyword.'%', '%'.$keyword.'%', '%'.$keyword.'%', '%'.$keyword.'%', '%'.$keyword.'%', '%'.$keyword.'%', '%'.$keyword.'%']);
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
        $tgl_registrasi = date('Y-m-d', strtotime($_POST['tgl_registrasi']));
        $jam_reg = date('H:i:s', strtotime($_POST['tgl_registrasi']));;

        $pasien = $this->db('pasien')->where('no_rkm_medis', $_POST['no_rkm_medis'])->oneArray();

      	$birthDate = new \DateTime($pasien['tgl_lahir']);
      	$today = new \DateTime('today');
      	$umurdaftar = '0';
        $sttsumur = 'Th';
        
        if ($birthDate < $today) {
        	$y = $today->diff($birthDate)->y;
        	$m = $today->diff($birthDate)->m;
        	$d = $today->diff($birthDate)->d;
          if($y == 0 && $m == 0) {
            $umurdaftar = $d;
            $sttsumur = 'Hr';
          }
          if($y == 0 && $m != 0) {
            $umurdaftar = $m;
            $sttsumur = 'Bl';
          }
          if($y != 0) {
            $umurdaftar = $y;
          }          
        }

        $check_db = $this->db()->pdo()->prepare("INSERT INTO reg_periksa VALUES (
          '{$_POST['no_reg']}', 
          '{$_POST['no_rawat']}', 
          '{$tgl_registrasi}', 
          '{$jam_reg}', 
          '{$_POST['kd_dokter']}', 
          '{$_POST['no_rkm_medis']}', 
          '{$_POST['kd_poli']}', 
          '-', 
          '-', 
          '-', 
          '{$_POST['biaya_reg']}', 
          'Belum', 
          '-', 
          'Ralan', 
          '{$_POST['kd_pj']}', 
          '{$umurdaftar}', 
          '{$sttsumur}', 
          'Belum Bayar', 
          'Baru'
        )");
        $result = $check_db->execute();
        $error = $check_db->errorInfo();
        if (!empty($result)){
          echo json_encode(array(
            'no_rkm_medis' => $_POST['no_rkm_medis'], 
            'no_rawat' => $_POST['no_rawat']
          ));
          $this->db('rujuk_masuk')->save([
            'no_rawat' => $_POST['no_rawat'], 
            'perujuk' => $_POST['asal_rujukan'], 
            'alamat' => '-', 
            'no_rujuk' => '-', 
            'jm_perujuk' => '0', 
            'dokter_perujuk' => $_POST['asal_rujukan'], 
            'kd_penyakit' => '-', 
            'kategori_rujuk' => '-', 
            'keterangan' => '-', 
            'no_balasan' => '-'
          ]);
        } else {
          echo json_encode(array('errorMsg'=>$error['2']));
        }        
    }

    public function Ubah()
    {
      $tgl_registrasi = date('Y-m-d', strtotime($_POST['tgl_registrasi']));
      $jam_reg = date('H:i:s', strtotime($_POST['tgl_registrasi']));;

      $pasien = $this->db('pasien')->where('no_rkm_medis', $_POST['no_rkm_medis'])->oneArray();

      $birthDate = new \DateTime($pasien['tgl_lahir']);
      $today = new \DateTime('today');
      $umurdaftar = '0';
      $sttsumur = 'Th';
      
      if ($birthDate < $today) {
        $y = $today->diff($birthDate)->y;
        $m = $today->diff($birthDate)->m;
        $d = $today->diff($birthDate)->d;
        if($y == 0 && $m == 0) {
          $umurdaftar = $d;
          $sttsumur = 'Hr';
        }
        if($y == 0 && $m != 0) {
          $umurdaftar = $m;
          $sttsumur = 'Bl';
        }
        if($y != 0) {
          $umurdaftar = $y;
        }          
      }      

      $check_db = $this->db()->pdo()->prepare("
        UPDATE 
          reg_periksa 
        SET 
          no_reg = '{$_POST['no_reg']}', 
          tgl_registrasi = '{$tgl_registrasi}', 
          jam_reg = '{$jam_reg}', 
          kd_dokter = '{$_POST['kd_dokter']}', 
          kd_poli = '{$_POST['kd_poli']}', 
          kd_pj = '{$_POST['kd_pj']}', 
          biaya_reg = '{$_POST['biaya_reg']}', 
          umurdaftar = '{$umurdaftar}', 
          sttsumur = '{$sttsumur}'
        WHERE 
          no_rkm_medis = '{$_POST['no_rkm_medis']}' 
        AND 
          no_rawat = '{$_POST['no_rawat']}'
      ");
      $result = $check_db->execute();
      $error = $check_db->errorInfo();
      if (!empty($result)){
        echo json_encode(array(
          'no_rkm_medis' => $_POST['no_rkm_medis'], 
          'no_rawat' => $_POST['no_rawat'], 
          'no_reg' => $_POST['no_reg']
        ));
        $this->db('rujuk_masuk')->save([
          'no_rawat' => $_POST['no_rawat'], 
          'perujuk' => $_POST['asal_rujukan'], 
          'alamat' => '-', 
          'no_rujuk' => '-', 
          'jm_perujuk' => '0', 
          'dokter_perujuk' => $_POST['asal_rujukan'], 
          'kd_penyakit' => '-', 
          'kategori_rujuk' => '-', 
          'keterangan' => '-', 
          'no_balasan' => '-'
        ]);
      } else {
        echo json_encode(array('errorMsg'=>$error['2']));
      }         
    }

    public function Hapus()
    {
      $no_rkm_medis = $_POST['no_rkm_medis'];
      $no_rawat = $_POST['no_rawat'];
      $check_db = $this->db()->pdo()->prepare("DELETE FROM reg_periksa WHERE no_rkm_medis = '$no_rkm_medis' AND no_rawat = '$no_rawat'");
      $result = $check_db->execute();
      $error = $check_db->errorInfo();
      if (!empty($result)){
        echo json_encode(array(
          'no_rkm_medis' => $no_rkm_medis, 
          'no_rawat' => $no_rawat
        ));
      } else {
        echo json_encode(array('errorMsg'=>$error['2']));
      }      
    }    

    public function Cetak()
    {
        
    }

}

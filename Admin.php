<?php
namespace Plugins\Khanza;

use Systems\AdminModule;
use Plugins\Khanza\MySQL;
use Plugins\Khanza\Src\Pasien;
use Plugins\Khanza\Src\RegPeriksa;
use Plugins\Khanza\Src\RawatJalan;
use Plugins\Khanza\Src\RawatInap;
use Plugins\Khanza\Src\Dokter;
use Plugins\Khanza\Src\Penjab;
use Plugins\Khanza\Src\BillingRalan;

class Admin extends AdminModule
{

  protected $pasien;
  protected $regperiksa;
  protected $rawatjalan;
  protected $rawatinap;
  protected $dokter;
  protected $penjab;
  protected $billingralan;

    public function init()
    {
      $this->pasien = new Pasien();
      $this->regperiksa = new RegPeriksa();
      $this->rawatjalan = new RawatJalan();
      $this->rawatinap = new RawatInap();
      $this->dokter = new Dokter();
      $this->penjab = new Penjab();
      $this->billingralan = new BillingRalan();
    }
    
    protected function db($table = NULL)
    {
        MySQL::connect("mysql:host=".$this->settings->get('khanza.host').";port=".$this->settings->get('khanza.port').";dbname=".$this->settings->get('khanza.database')."", $this->settings->get('khanza.username'), $this->settings->get('khanza.password'));
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

    public function setNoNotaRalan()
    {
        $date = date('Y-m');
        $last_no = $this->db()->pdo()->prepare("SELECT ifnull(MAX(CONVERT(RIGHT(no_nota,6),signed)),0) FROM nota_jalan WHERE left(tanggal,7) = '$date'");
        $last_no->execute();
        $last_no = $last_no->fetch();
        if(empty($last_no[0])) {
          $last_no[0] = '000000';
        }
        $next_no = sprintf('%06s', ($last_no[0] + 1));
        $next_no = date('Y').'/'.date('m').'/RJ/'.$next_no;
        return $next_no;
    }

    public function setNoRawat($date)
    {
        $last_no_rawat = $this->db()->pdo()->prepare("SELECT ifnull(MAX(CONVERT(RIGHT(no_rawat,6),signed)),0) FROM reg_periksa WHERE tgl_registrasi = '$date'");
        $last_no_rawat->execute();
        $last_no_rawat = $last_no_rawat->fetch();
        if(empty($last_no_rawat[0])) {
          $last_no_rawat[0] = '000000';
        }
        $next_no_rawat = sprintf('%06s', ($last_no_rawat[0] + 1));
        $next_no_rawat = str_replace("-","/",$date).'/'.$next_no_rawat;

        return $next_no_rawat;
    }

    public function setNoReg($kd_dokter = null, $kd_poli = null)
    {
        $max_id = $this->db('reg_periksa')->select(['no_reg' => 'ifnull(MAX(CONVERT(RIGHT(no_reg,3),signed)),0)'])->where('tgl_registrasi', date('Y-m-d'))->desc('no_reg')->limit(1)->oneArray();
        if(empty($max_id['no_reg'])) {
          $max_id['no_reg'] = '000';
        } elseif ($kd_poli !=null) {
          $max_id = $this->db('reg_periksa')->select(['no_reg' => 'ifnull(MAX(CONVERT(RIGHT(no_reg,3),signed)),0)'])->where('kd_poli', $kd_poli)->where('tgl_registrasi', date('Y-m-d'))->desc('no_reg')->limit(1)->oneArray();
          // if($this->settings->get('settings.dokter_ralan_per_dokter') == 'true') {
          //   $max_id = $this->db('reg_periksa')->select(['no_reg' => 'ifnull(MAX(CONVERT(RIGHT(no_reg,3),signed)),0)'])->where('kd_poli', $kd_poli)->where('kd_dokter', $kd_dokter)->where('tgl_registrasi', date('Y-m-d'))->desc('no_reg')->limit(1)->oneArray();
          // }
        }
        $_next_no_reg = sprintf('%03s', ($max_id['no_reg'] + 1));

        return $_next_no_reg;
    }    
    public function navigation()
    {
        return [
            'Kelola'     => 'manage',
            'Pengaturan' => 'settings'
        ];
    }
    
    public function getManage()
    {
      if(isset_or($_SESSION['msimrs_user']) == '') {
        $id = 1;
      } else {
        $id = $_SESSION['msimrs_user'];
      }
      if($this->db('mlite_users')->where('id', $id)->oneArray()) {
        $username = $this->core->getUserInfo('fullname', $id, true);
        $access = $this->core->getUserInfo('access');
        $this->assign['username']      = !empty($username) ? $username : $this->getUserInfo('username');
      }
      $this->assign['nama_instansi'] = $this->settings->get('settings.nama_instansi');
      $this->assign['alamat'] = $this->settings->get('settings.alamat');
      $this->assign['kota'] = $this->settings->get('settings.kota');
      $this->assign['propinsi'] = $this->settings->get('settings.propinsi');
      $this->assign['nomor_telepon'] = $this->settings->get('settings.nomor_telepon');
      $this->assign['email'] = $this->settings->get('settings.email');
      $this->assign['logo'] = $this->settings->get('settings.logo');
      $this->assign['powered'] = 'Powered by <a href="https://mlite.id/">mLITE</a>';
      $this->assign['version'] = '4.0.0';

      echo $this->draw('manage.html', ['msimrs' => $this->assign]);
      exit();
    }

    public function getDashboard()
    {
      $this->assign['nama_instansi'] = $this->settings->get('settings.nama_instansi');
      $this->assign['alamat'] = $this->settings->get('settings.alamat');
      $this->assign['kota'] = $this->settings->get('settings.kota');
      $this->assign['propinsi'] = $this->settings->get('settings.propinsi');
      $this->assign['logo'] = $this->settings->get('settings.logo');
      echo $this->draw('dashboard.html', ['msimrs' => $this->assign]);
      exit();
    }

    public function getSettings()
    {
        $this->assign['title'] = 'Pengaturan Modul Khanza D2W';
        $this->assign['khanza'] = htmlspecialchars_array($this->settings('khanza'));
        return $this->draw('settings.html', ['settings' => $this->assign]);
    }

    public function postSaveSettings()
    {
        foreach ($_POST['khanza'] as $key => $val) {
            $this->settings('khanza', $key, $val);
        }
        $this->notify('success', 'Pengaturan telah disimpan');
        redirect(url([ADMIN, 'khanza', 'settings']));
    }

    public function _getHeader()
    {
      if(isset_or($_SESSION['msimrs_user']) == '') {
        $id = 1;
      } else {
        $id = $_SESSION['msimrs_user'];
      }
      if($this->db('mlite_users')->where('id', $id)->oneArray()) {
        $username = $this->core->getUserInfo('fullname', $id, true);
        $access = $this->core->getUserInfo('access');
        $this->assign['username'] = !empty($username) ? $username : $this->getUserInfo('username');
      }
      $this->assign['nama_instansi'] = $this->settings->get('settings.nama_instansi');
      $this->assign['alamat'] = $this->settings->get('settings.alamat');
      $this->assign['kota'] = $this->settings->get('settings.kota');
      $this->assign['propinsi'] = $this->settings->get('settings.propinsi');
      $this->assign['nomor_telepon'] = $this->settings->get('settings.nomor_telepon');
      $this->assign['email'] = $this->settings->get('settings.email');
      $this->assign['logo'] = $this->settings->get('settings.logo');
      return;
    }

    public function anyBlank()
    {
      $this->_getHeader();
      $show = isset($_GET['act']) ? $_GET['act'] : "";
      switch($show){
      	default:
          echo $this->draw('_blank.html');
        break;
        case "data":  
          echo 'Ini data';
        break;    
      }
      exit();
    }

    public function anyPasien()
    {
      $this->_getHeader();
      $show = isset($_GET['act']) ? $_GET['act'] : "";
      switch($show){
      	default:
          echo $this->draw('_pasien.html', ['msimrs' => $this->assign]);
        break;
        case "data":
          $this->pasien->Data();
        break;

        case 'simpan':
          $this->pasien->Simpan();
        break;

        case 'ubah':
          $this->pasien->Ubah();
        break;

        case 'hapus':
          $this->pasien->Hapus();
        break;

      }
      exit();
    }

    public function anyRegPeriksa()
    {
      $this->_getHeader();
      $show = isset($_GET['act']) ? $_GET['act'] : "";
      switch($show){
      	default:
          echo $this->draw('_reg.periksa.html', ['set_no_reg' => $this->setNoReg(), 'set_no_rawat' => $this->setNoRawat(date('Y-m-d'))]);
        break;
        case "data":  
          $this->regperiksa->Data();
        break;
        case "simpan":  
          $this->regperiksa->Simpan();
        break;
        case "get_no_reg":  
          echo $this->setNoReg(isset_or($_POST['kd_dokter'], ''), isset_or($_POST['kd_poli'],''));
        break;
        case "get_no_rawat":  
          echo $this->setNoRawat(isset_or($_POST['tgl_registrasi'],''));
        break;
      }
      exit();
    }

    public function anyRawatJalan()
    {
      $this->_getHeader();
      $show = isset($_GET['act']) ? $_GET['act'] : "";
      switch($show){
      	default:
          echo $this->draw('_rawat.jalan.html');
        break;
        case "data":
          $this->rawatjalan->Data();         
        break;
      }
      exit();
    }

    public function anyRawatInap()
    {
      $this->_getHeader();
      $show = isset($_GET['act']) ? $_GET['act'] : "";
      $no_rawat = isset_or($_GET['no_rawat'], '');
      switch($show){
      	default:
          echo $this->draw('_rawat.inap.html');
        break;
        case "data":  
          $this->rawatinap->Data();
        break;
        case "input":
          $reg_periksa = $this->db('reg_periksa')->join('pasien', 'pasien.no_rkm_medis=reg_periksa.no_rkm_medis')->where('no_rawat', $_GET['no_rawat'])->oneArray();
          echo $this->draw('_rawat.inap.input.html', ['reg_periksa' => $reg_periksa]);
        break;
      }
      exit();
    }

    public function getBillingRalan($no_rawat)
    {
      $no_rawat = $this->revertNoRawat($no_rawat);
      $this->_getHeader();
      $show = isset($_GET['act']) ? $_GET['act'] : "";
      switch($show){
      	default:
          $registrasi = $this->db('reg_periksa')
            ->join('pasien', 'pasien.no_rkm_medis=reg_periksa.no_rkm_medis')
            ->join('poliklinik', 'poliklinik.kd_poli=reg_periksa.kd_poli')
            ->join('dokter', 'dokter.kd_dokter=reg_periksa.kd_dokter')
            ->join('kelurahan', 'kelurahan.kd_kel=pasien.kd_kel')
            ->join('kecamatan', 'kecamatan.kd_kec=pasien.kd_kec')
            ->join('kabupaten', 'kabupaten.kd_kab=pasien.kd_kab')
            ->join('propinsi', 'propinsi.kd_prop=pasien.kd_prop')
            ->where('reg_periksa.no_rawat', $no_rawat)
            ->oneArray();
          echo $this->draw('_billing.ralan.html', ['mlite' => $this->assign, 'reg_periksa' => $registrasi, 'no_rawat' => $no_rawat, 'convert_no_rawat' => $this->convertNorawat($no_rawat)]);
        break;
        case "data":
            $this->billingralan->Data($no_rawat, $this->setNoNotaRalan(date('Y-m-d')));
        break;
      }
      exit();
    }

    public function postSimpanBilling()
    {
      if(isset($_POST['billing'])) {
        $json = $_POST['billing'];
        $data = json_decode($json, true);
        $no_rawat = $_POST['no_rawat'];
        $tgl_bayar = date('Y-m-d', strtotime($_POST['tgl_bayar']));
        $jam = date('H:i:s', strtotime($_POST['tgl_bayar']));
        $no_nota = $this->setNoNotaRalan($tgl_bayar);
        $i = 0;
        
        $hapus = $this->db('billing')->where('no_rawat', $_POST['no_rawat'])->delete();
        $hapus2 = $this->db('nota_jalan')->where('no_rawat', $_POST['no_rawat'])->delete();

        $nota_jalan = $this->db()->pdo()->prepare("INSERT INTO `nota_jalan` (`no_rawat`, `no_nota`, `tanggal`, `jam`) VALUES ('$no_rawat', '$no_nota', '$tgl_bayar', '$jam')");
        $result = $nota_jalan->execute();
        $error = $nota_jalan->errorInfo();

        if (!empty($result)){
          foreach ($data as $row) {
            $check_db = $this->db()->pdo()->prepare("INSERT INTO `billing` (`noindex`, `no_rawat`, `tgl_byr`, `no`, `nm_perawatan`, `pemisah`, `biaya`, `jumlah`, `tambahan`, `totalbiaya`, `status`) VALUES ('$i', '$no_rawat', '$tgl_bayar', '".$row['no']."', '".$row['nm_perawatan']."', '".$row['pemisah']."', '".isset_or($row['satu'], '0')."', '".isset_or($row['dua'], '0')."', '".isset_or($row['tiga'], '0')."', '".isset_or($row['empat'], '0')."', '".$row['status']."')");
            $result = $check_db->execute();
            $error = $check_db->errorInfo();
            $i++;
          }
          echo json_encode(array(
            'no_rawat' => $_POST['no_rawat']
          ));
        } else {
          echo json_encode(array('errorMsg'=>$error['2']));
        }            

      }
      exit();
    }

    public function getCetakBillingRalan($no_rawat)
    {
      $no_rawat = $this->revertNoRawat($no_rawat);
      $setting = $this->db('setting')->oneArray();
      $rows = "select no,nm_perawatan, if(biaya<>0,biaya,null) as satu, if(jumlah<>0,jumlah,null) as dua, if(tambahan<>0,tambahan,null) as tiga, if(totalbiaya<>0,totalbiaya,null) as empat,pemisah,status from billing where no_rawat='$no_rawat' order by noindex";
      $rows = $this->db()->pdo()->prepare($rows);
      $rows->execute();
      $billing = $rows->fetchAll(\PDO::FETCH_ASSOC);

      $total = '';
      echo $this->draw('_cetak.billing.ralan.html', ['instansi' => $setting, 'billing' => $billing, 'total' => $total]);
      exit();
    }

    public function getAkunBayar()
    {
      $rows = $this->db('akun_bayar')->toArray();
      $result = [];
      foreach ($rows as $row) {
        $data['nama_bayar'] = $row['nama_bayar'];
        $data['bayar'] = '';
        $data['ppn'] = $row['ppn'];
        $data['ppnrp'] = '';
        $result[] = $data;
      }
      echo json_encode($result);
      exit();
    }

    public function getAkunPiutang()
    {
      $rows = $this->db('akun_piutang')->toArray();
      $result = [];
      foreach ($rows as $row) {
        $data['nama_bayar'] = $row['nama_bayar'];
        $data['piutang'] = '';
        $data['jatuh_tempo'] = date('Y-m-d');
        $result[] = $data;
      }
      echo json_encode($result);
      exit();
    }

    public function anyDokter()
    {
      $this->_getHeader();
      $show = isset($_GET['act']) ? $_GET['act'] : "";
      switch($show){
      	default:
          echo $this->draw('_dokter.html');
        break;
        case "data":
          $this->dokter->Data();         
        break;
      }
      exit();
    }    

    public function anyPenjab()
    {
      $this->_getHeader();
      $show = isset($_GET['act']) ? $_GET['act'] : "";
      switch($show){
      	default:
          echo $this->draw('_penjab.html');
        break;
        case "data":
          $this->penjab->Data();         
        break;
      }
      exit();
    }        

    public function anyPoliklinik()
    {
      $this->_getHeader();
      $show = isset($_GET['act']) ? $_GET['act'] : "";
      switch($show){
      	default:
          echo $this->draw('_poliklinik.html');
        break;
        case "data":
          $page = isset($_POST['page']) ? intval($_POST['page']) : 1;
          $rows = isset($_POST['rows']) ? intval($_POST['rows']) : 10;
          $offset = ($page-1)*$rows;
    
          $keyword = isset($_POST['cari_keyword_poliklinik']) ? $_POST['cari_keyword_poliklinik'] : '';
    
          $row = $this->db('poliklinik')->select(['count' => 'COUNT(*)'])->oneArray();
          $result["total"] = $row['count'];
    
          $items = array();
          $query = "select * from poliklinik where status='1' and (kd_poli like '%$keyword%' or nm_poli like '%$keyword%')
          order by kd_poli asc LIMIT $offset,$rows";
    
          $rows = $this->db()->pdo()->prepare($query);
          $rows->execute();
          $rows = $rows->fetchAll(\PDO::FETCH_ASSOC);
    
          foreach ($rows as $row) {
              array_push($items, $row);
          }
          $result["rows"] = $items;
          echo json_encode($result);          
        break;
      }
      exit();
    }    
    
    public function anySukuBangsa()
    {
      $this->_getHeader();
      $show = isset($_GET['act']) ? $_GET['act'] : "";
      switch($show){
      	default:
          echo $this->draw('_suku.bangsa.html');
        break;
        case "data":
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
          $rows = $rows->fetchAll();
    
          foreach ($rows as $row) {
              array_push($items, $row);
          }
          $result["rows"] = $items;
          echo json_encode($result);          
        break;
      }
      exit();
    }    

}

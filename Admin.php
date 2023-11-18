<?php
namespace Plugins\Khanza;

use Systems\AdminModule;
use Systems\Lib\QRCode;

use Plugins\Khanza\MySQL;
use Plugins\Khanza\Src\Blank;
use Plugins\Khanza\Src\Pasien;
use Plugins\Khanza\Src\RegPeriksa;
use Plugins\Khanza\Src\RawatJalan;
use Plugins\Khanza\Src\RawatInap;
use Plugins\Khanza\Src\Dokter;
use Plugins\Khanza\Src\Penjab;
use Plugins\Khanza\Src\BillingRalan;
use Plugins\Khanza\Src\BillingRanap;
use Plugins\Khanza\Src\Igd;
use Plugins\Khanza\Src\Laboratorium;
use Plugins\Khanza\Src\Radiologi;
use Plugins\Khanza\Src\Apotek;
use Plugins\Khanza\Src\SukuBangsa;
use Plugins\Khanza\Src\FrekuensiPenyakitRalan;
use Plugins\Khanza\Src\Bahasa;
use Plugins\Khanza\Src\CacatFisik;
use Plugins\Khanza\Src\PerusahaanPasien;
use Plugins\Khanza\Src\Propinsi;
use Plugins\Khanza\Src\Kabupaten;
use Plugins\Khanza\Src\Kecamatan;
use Plugins\Khanza\Src\Kelurahan;
use Plugins\Khanza\Src\RujukMasuk;

class Admin extends AdminModule
{

  protected $blank;
  protected $pasien;
  protected $regperiksa;
  protected $rawatjalan;
  protected $rawatinap;
  protected $dokter;
  protected $penjab;
  protected $billingralan;
  protected $billingranap;
  protected $igd;
  protected $laboratorium;
  protected $radiologi;
  protected $apotek;
  protected $sukubangsa;
  protected $frekuensipenyakitralan;
  protected $bahasa;
  protected $cacatfisik;
  protected $perusahaanpasien;
  protected $propinsi;
  protected $kabupaten;
  protected $kecamatan;
  protected $kelurahan;
  protected $rujukmasuk;

    public function init()
    {
      $this->blank = new Blank();
      $this->pasien = new Pasien();
      $this->regperiksa = new RegPeriksa();
      $this->rawatjalan = new RawatJalan();
      $this->rawatinap = new RawatInap();
      $this->dokter = new Dokter();
      $this->penjab = new Penjab();
      $this->billingralan = new BillingRalan();
      $this->billingranap = new BillingRanap();
      $this->igd = new Igd();
      $this->laboratorium = new Laboratorium();
      $this->radiologi = new Radiologi();
      $this->apotek = new Apotek();
      $this->sukubangsa = new SukuBangsa();
      $this->frekuensipenyakitralan = new FrekuensiPenyakitRalan();
      $this->bahasa = new Bahasa();
      $this->cacatfisik = new CacatFisik();
      $this->perusahaanpasien = new PerusahaanPasien();
      $this->propinsi = new Propinsi();
      $this->kabupaten = new Kabupaten();
      $this->kecamatan = new Kecamatan();
      $this->kelurahan = new Kelurahan();
      $this->rujukmasuk = new RujukMasuk();
    }
    
    protected function db($table = NULL)
    {
        MySQL::connect("mysql:host=".$this->settings->get('khanza.host').";port=".$this->settings->get('khanza.port').";dbname=".$this->settings->get('khanza.database')."", $this->settings->get('khanza.username'), $this->settings->get('khanza.password'));
        return new MySQL($table);
    }

    public function _getSession()
    {
      if(isset_or($_SESSION['mlite_user']) == '') {
        $id = 1;
      } else {
        $id = $_SESSION['mlite_user'];
      }
      if($this->db('mlite_users')->where('id', $id)->oneArray()) {
        $fullname = $this->core->getUserInfo('fullname', $id, true);
        $access = $this->core->getUserInfo('access');
        $this->assign['fullname'] = !empty($fullname) ? $fullname : $this->core->getUserInfo('username');
      }
    }
    public function _getHeader()
    {
      $this->assign = $this->settings->get('settings');
      $this->assign['powered'] = 'Powered by <a href="https://mlite.id/">mLITE</a>';
      $this->assign['version'] = '4.0.0';
      $this->_getSession();
      echo $this->draw('header.html',['mlite' => $this->assign]);
    }

    public function _getFooter()
    {
      echo $this->draw('footer.html');
    }    

    public function getCss()
    {
        header('Content-type: text/css');
        echo $this->draw(MODULES.'/khanza/css/admin/khanza.css');
        exit();
    }
  
    public function getJavascript()
    {
        header('Content-type: text/javascript');
        echo $this->draw(MODULES.'/khanza/js/admin/khanza.js');
        exit();
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

    public function setNoRM()
    {
        $last_no_rm = $this->db('set_no_rkm_medis')->oneArray();
        $last_no_rm = substr($last_no_rm['no_rkm_medis'], 0, 6);
        $next_no_rm = sprintf('%06s', ($last_no_rm + 1));
        return $next_no_rm;
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
      $this->_getHeader();
      echo $this->draw('manage.html');
      $this->_getFooter();
      exit();
    }

    public function getDashboard()
    {
      echo $this->draw('dashboard.html', ['mlite' => $this->settings->get('settings')]);
      exit();
    }

    public function anySettings()
    {
        $this->assign['title'] = 'Pengaturan Modul Khanza D2W';
        $this->assign['khanza'] = htmlspecialchars_array($this->settings('khanza'));

        $this->tpl->set('allow_curl', intval(function_exists('curl_init')));

        if (isset($_POST['check'])) {

            $url = "https://api.github.com/repos/basoro/khanza2web/commits/master";
            $opts = [
                'http' => [
                    'method' => 'GET',
                    'header' => [
                            'User-Agent: PHP'
                    ]
                ]
            ];
      
            $json = file_get_contents($url, false, stream_context_create($opts));
            $obj = json_decode($json, true);
            $new_date_format = date('Y-m-d H:i:s', strtotime($obj['commit']['author']['date']));
      
            if (!is_array($obj)) {
                $this->tpl->set('error', $obj);
            } else {
                if(mb_strlen($this->settings->get('khanza.version'), 'UTF-8') < 5) {
                  $this->settings('khanza', 'version', '2023-01-01 00:00:00');
                }
                $this->settings('khanza', 'update_version', $new_date_format);
                $this->settings('khanza', 'update_changelog', $obj['commit']['message']);
            }          

            redirect(url([ADMIN, 'khanza', 'settings']));
             
        } elseif (isset($_POST['update'])) {
            if (!class_exists("ZipArchive")) {
                $this->tpl->set('error', "ZipArchive is required to update mLITE.");
            }

            if (!isset($_GET['manual'])) {
                $url = "https://api.github.com/repos/basoro/khanza2web/commits/master";
                $opts = [
                    'http' => [
                        'method' => 'GET',
                        'header' => [
                                'User-Agent: PHP'
                        ]
                    ]
                ];

                $json = file_get_contents($url, false, stream_context_create($opts));
                $obj = json_decode($json, true);
                $new_date_format = date('Y-m-d H:i:s', strtotime($obj['commit']['author']['date']));
                $this->download('https://github.com/basoro/khanza2web/archive/master.zip', BASE_DIR.'/tmp/latest.zip');
            } else {
                $package = glob(BASE_DIR.'/khanza2web-master.zip');
                if (!empty($package)) {
                    $package = array_shift($package);
                    $this->rcopy($package, BASE_DIR.'/tmp/latest.zip');
                }
            }

            // Unzip latest update
            $zip = new \ZipArchive;
            $zip->open(BASE_DIR.'/tmp/latest.zip');
            $zip->extractTo(BASE_DIR.'/tmp/update');

            // Copy files
            $this->rcopy(BASE_DIR.'/tmp/update/khanza2web-master', BASE_DIR.'/plugins/khanza');

            // Close archive and delete all unnecessary files
            $zip->close();
            unlink(BASE_DIR.'/tmp/latest.zip');
            deleteDir(BASE_DIR.'/tmp/update');

            $this->settings->reload();

            $this->settings('khanza', 'version', $new_date_format);
            $this->settings('khanza', 'update_version', $new_date_format);
            $this->settings('khanza', 'update_changelog', $obj['commit']['message']);

            sleep(2);
            redirect(url([ADMIN, 'khanza', 'settings']));
        }

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

    private function download($source, $dest)
    {
        set_time_limit(0);
        $fp = fopen($dest, 'w+');
        $ch = curl_init($source);
        curl_setopt($ch, CURLOPT_TIMEOUT, 50);
        curl_setopt($ch, CURLOPT_FILE, $fp);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_exec($ch);
        curl_close($ch);
        fclose($fp);
    }

    private function rcopy($source, $dest, $permissions = 0755, $expect = [])
    {
        foreach ($expect as $e) {
            if ($e == $source) {
                return;
            }
        }

        if (is_link($source)) {
            return symlink(readlink($source), $dest);
        }

        if (is_file($source)) {
            if (!is_dir(dirname($dest))) {
                mkdir(dirname($dest), 0777, true);
            }

            return copy($source, $dest);
        }

        if (!is_dir($dest)) {
            mkdir($dest, $permissions, true);
        }

        $dir = dir($source);
        while (false !== $entry = $dir->read()) {
            if ($entry == '.' || $entry == '..') {
                continue;
            }

            $this->rcopy("$source/$entry", "$dest/$entry", $permissions, $expect);
        }

        $dir->close();
        return true;
    }
        
    public function anyBlank()
    {
      $this->_getSession();
      $show = isset($_GET['act']) ? $_GET['act'] : "";
      switch($show){
      	default:
          echo $this->draw('_blank.html');
        break;
        case "data":  
          $this->blank->Data();
        break;    
      }
      exit();
    }

    public function anyPasien()
    {
      $this->_getSession();
      $show = isset($_GET['act']) ? $_GET['act'] : "";
      switch($show){
      	default:
          echo $this->draw('_pasien.html');
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

        case "get_no_rkm_medis":  
          echo $this->setNoRM();
        break;

      }
      exit();
    }

    public function anyRegPeriksa()
    {
      $this->_getSession();

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
        case "ubah":  
          $this->regperiksa->Ubah();
        break;
        case "hapus":  
          $this->regperiksa->Hapus();
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
      $this->_getSession();
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
      $this->_getSession();
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
      $this->_getSession();

      $no_rawat = $this->revertNoRawat($no_rawat);
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
      $this->_getSession();

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
      $this->_getSession();

      $no_rawat = $this->revertNoRawat($no_rawat);
      $setting = $this->db('setting')->oneArray();
      $rows = "select no,nm_perawatan, if(biaya<>0,biaya,null) as satu, if(jumlah<>0,jumlah,null) as dua, if(tambahan<>0,tambahan,null) as tiga, if(totalbiaya<>0,totalbiaya,null) as empat,pemisah,status from billing where no_rawat='$no_rawat' order by noindex";
      $rows = $this->db()->pdo()->prepare($rows);
      $rows->execute();
      $billing = $rows->fetchAll(\PDO::FETCH_ASSOC);
      $total = '';
      foreach($billing as $row) {
        $total += $row['empat'];
      }

      $metadata_kasir = "Dikeluarkan di ".$this->settings->get('settings.nama_instansi').", Kabupaten/Kota ".$this->settings->get('settings.kota').". Ditandatangani secara elektronik oleh ".$this->core->getUserInfo('fullname', $_SESSION['mlite_user'], true).".";
 
      unlink(BASE_DIR.'/tmp/qrcode_kasir.png');
      $qr_kasir=QRCode::getMinimumQRCode($metadata_kasir,QR_ERROR_CORRECT_LEVEL_L);
      $im_kasir=$qr_kasir->createImage(4,4);
      imagepng($im_kasir,BASE_DIR.'/tmp/qrcode_kasir.png');
      imagedestroy($im_kasir);

      echo $this->draw('_cetak.billing.ralan.html', ['instansi' => $setting, 'billing' => $billing, 'total' => $total]);
      exit();
    }

    public function getAkunBayar()
    {
      $this->_getSession();

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
      $this->_getSession();

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
      $this->_getSession();

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
    
    public function anyIgd()
    {
      $this->_getSession();

      $show = isset($_GET['act']) ? $_GET['act'] : "";
      switch($show){
      	default:
          echo $this->draw('_igd.html');
        break;
        case "data":
          $this->igd->Data();         
        break;
      }
      exit();
    }   

    public function anyLaboratorium()
    {
      $this->_getSession();

      $show = isset($_GET['act']) ? $_GET['act'] : "";
      switch($show){
      	default:
          echo $this->draw('_laboratorium.html');
        break;
        case "data":
          $this->laboratorium->Data();         
        break;
      }
      exit();
    }   

    public function anyRadiologi()
    {
      $this->_getSession();

      $show = isset($_GET['act']) ? $_GET['act'] : "";
      switch($show){
      	default:
          echo $this->draw('_radiologi.html');
        break;
        case "data":
          $this->radiologi->Data();         
        break;
      }
      exit();
    }   

    public function anyApotek()
    {
      $this->_getSession();

      $show = isset($_GET['act']) ? $_GET['act'] : "";
      switch($show){
      	default:
          echo $this->draw('_apotek.html');
        break;
        case "data":
          $this->apotek->Data();         
        break;
      }
      exit();
    }   

    public function anyPenjab()
    {
      $this->_getSession();

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
      $this->_getSession();

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
      $this->_getSession();
      
      $show = isset($_GET['act']) ? $_GET['act'] : "";
      switch($show){
      	default:
          echo $this->draw('_suku.bangsa.html');
        break;
        case "data":
          $this->sukubangsa->Data();        
        break;
      }
      exit();
    }    

    public function anyFrekuensiPenyakitRalan()
    {
      $this->_getSession();
      $show = isset($_GET['act']) ? $_GET['act'] : "";
      switch($show){
      	default:
          echo $this->draw('_frekuensi.penyakit.ralan.html');
        break;
        case "data":  
          $this->frekuensipenyakitralan->Data();
        break;    
      }
      exit();
    }

    public function anyPropinsi()
    {
      $this->_getSession();
      $show = isset($_GET['act']) ? $_GET['act'] : "";
      switch($show){
      	default:
          echo $this->draw('_propinsi.html');
        break;
        case "data":  
          $this->propinsi->Data();
        break;    
      }
      exit();
    }

    public function anyKabupaten()
    {
      $this->_getSession();
      $show = isset($_GET['act']) ? $_GET['act'] : "";
      switch($show){
      	default:
          echo $this->draw('_kabupaten.html');
        break;
        case "data":  
          $this->kabupaten->Data();
        break;    
      }
      exit();
    }

    public function anyKecamatan()
    {
      $this->_getSession();
      $show = isset($_GET['act']) ? $_GET['act'] : "";
      switch($show){
      	default:
          echo $this->draw('_kecamatan.html');
        break;
        case "data":  
          $this->kecamatan->Data();
        break;    
      }
      exit();
    }

    public function anyKelurahan()
    {
      $this->_getSession();
      $show = isset($_GET['act']) ? $_GET['act'] : "";
      switch($show){
      	default:
          echo $this->draw('_kelurahan.html');
        break;
        case "data":  
          $this->kelurahan->Data();
        break;    
      }
      exit();
    }

    public function anyBahasa()
    {
      $this->_getSession();
      $show = isset($_GET['act']) ? $_GET['act'] : "";
      switch($show){
      	default:
          echo $this->draw('_bahasa.html');
        break;
        case "data":  
          $this->bahasa->Data();
        break;    
      }
      exit();
    }

    public function anyCacatFisik()
    {
      $this->_getSession();
      $show = isset($_GET['act']) ? $_GET['act'] : "";
      switch($show){
      	default:
          echo $this->draw('_cacat.fisik.html');
        break;
        case "data":  
          $this->cacatfisik->Data();
        break;    
      }
      exit();
    }

    public function anyPerusahaanPasien()
    {
      $this->_getSession();
      $show = isset($_GET['act']) ? $_GET['act'] : "";
      switch($show){
      	default:
          echo $this->draw('_perusahaan.pasien.html');
        break;
        case "data":  
          $this->perusahaanpasien->Data();
        break;    
      }
      exit();
    }

    public function anyRujukMasuk()
    {
      $this->_getSession();
      $show = isset($_GET['act']) ? $_GET['act'] : "";
      switch($show){
      	default:
          echo $this->draw('_rujuk.masuk.html');
        break;
        case "data":  
          $this->rujukmasuk->Data();
        break;    
        case "caridata":  
          $this->rujukmasuk->CariPerujuk();
        break;    
      }
      exit();
    }

    public function getRujukMasukPerujuk()
    {
      $this->_getSession();
      echo $this->draw('_rujuk.masuk.perujuk.html');
      exit();
    }

    public function getCoba()
    {
      $url = "https://api.github.com/repos/basoro/khanza2web/commits/master";
      $opts = [
          'http' => [
              'method' => 'GET',
              'header' => [
                      'User-Agent: PHP'
              ]
          ]
      ];

      $json = file_get_contents($url, false, stream_context_create($opts));
      $obj = json_decode($json, true);
      $new_date_format = date('Y-m-d H:i:s', strtotime($obj['commit']['author']['date']));

      if (!is_array($obj)) {
          $this->tpl->set('error', $obj);
      } else {
          if(mb_strlen($this->settings->get('khanza.version'), 'UTF-8') < 5) {
            $this->settings('khanza', 'version', '2023-01-01 00:00:00');
          }
          $this->settings('khanza', 'update_version', $new_date_format);
          $this->settings('khanza', 'update_changelog', $obj['commit']['message']);
      }
      exit();
    }

}

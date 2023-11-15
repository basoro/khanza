<?php

namespace Plugins\Khanza\Src;

use Plugins\Khanza\MySQL;

class BillingRalan
{

    protected function db($table = NULL)
    {
        return new MySQL($table);
    }

    public function Data($no_rawat, $setNoNotaRalan)
    {

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

      $prosesCariReg =
      [
          [
              "no" => "No.Nota",
              "nm_perawatan" => ": ".$setNoNotaRalan,
              "satu" => null,
              "dua" => null,
              "tiga" => null,
              "empat" => null,
              "pemisah" => "",
              "status" => "-",
          ],
          [
              "no" => "Unit/Instansi",
              "nm_perawatan" => ": ".$registrasi['nm_poli'],
              "satu" => null,
              "dua" => null,
              "tiga" => null,
              "empat" => null,
              "pemisah" => "",
              "status" => "-",
          ],
          [
              "no" => "Tanggal & Jam",
              "nm_perawatan" => ": ".$registrasi['tgl_registrasi']." ".$registrasi['jam_reg'],
              "satu" => null,
              "dua" => null,
              "tiga" => null,
              "empat" => null,
              "pemisah" => "",
              "status" => "-",
          ],
          [
              "no" => "No.RM",
              "nm_perawatan" => ": ".$registrasi['no_rkm_medis'],
              "satu" => null,
              "dua" => null,
              "tiga" => null,
              "empat" => null,
              "pemisah" => "",
              "status" => "-",
          ],
          [
              "no" => "Nama Pasien",
              "nm_perawatan" => ": ".$registrasi['nm_pasien']." (".$registrasi['umur'].")",
              "satu" => null,
              "dua" => null,
              "tiga" => null,
              "empat" => null,
              "pemisah" => "",
              "status" => "-",
          ],
          [
              "no" => "Alamat Pasien",
              "nm_perawatan" => ": ".$registrasi['alamat']." ".$registrasi['nm_kel']." ".$registrasi['nm_kec']." ".$registrasi['nm_kab']." ".$registrasi['nm_prop'],
              "satu" => null,
              "dua" => null,
              "tiga" => null,
              "empat" => null,
              "pemisah" => "",
              "status" => "-",
          ],
          [
              "no" => "Dokter ",
              "nm_perawatan" => ":",
              "satu" => null,
              "dua" => null,
              "tiga" => null,
              "empat" => null,
              "pemisah" => "",
              "status" => "-",
          ],
          [
              "no" => "",
              "nm_perawatan" => ": ".$registrasi['nm_dokter'],
              "satu" => null,
              "dua" => null,
              "tiga" => null,
              "empat" => null,
              "pemisah" => "",
              "status" => "Dokter",
          ],
          [
              "no" => "Registrasi",
              "nm_perawatan" => ":",
              "satu" => null,
              "dua" => null,
              "tiga" => null,
              "empat" => intval($registrasi['registrasi']),
              "pemisah" => "",
              "status" => "Registrasi",
          ]
      ];

      $prosesCariTindakan =
      [
          [
              "no" => "Tindakan",
              "nm_perawatan" => ":",
              "satu" => null,
              "dua" => null,
              "tiga" => null,
              "empat" => null,
              "pemisah" => "",
              "status" => "Ralan Dokter",
          ]
      ];

      $rawat_jl_dr = $this->db('rawat_jl_dr')
        ->select([
          'total_byrdr' => 'rawat_jl_dr.biaya_rawat',
          'nm_perawatan' => 'nm_perawatan',
          'jml' => 'count(rawat_jl_dr.kd_jenis_prw)',
          'biaya' => 'sum(rawat_jl_dr.biaya_rawat)',
          'totalbhp' => 'sum(rawat_jl_dr.bhp)',
          'totalmaterial' => '(sum(rawat_jl_dr.material)+sum(rawat_jl_dr.menejemen)+sum(rawat_jl_dr.kso))',
          'tarif_tindakandr' => 'rawat_jl_dr.tarif_tindakandr',
          'totaltarif_tindakandr' => 'sum(rawat_jl_dr.tarif_tindakandr)'
        ])
        ->join('jns_perawatan', 'rawat_jl_dr.kd_jenis_prw=jns_perawatan.kd_jenis_prw')
        ->where('no_rawat', $no_rawat)
        ->group('jns_perawatan.nm_perawatan')
        ->toArray();
      
      $prosesCariRwJlDr = [];
      foreach ($rawat_jl_dr as $data) {
        $tamkur = $this->db('temporary_tambahan_potongan')->where('no_rawat', $no_rawat)->where('nama_tambahan', $data['nm_perawatan'])->where('status', 'Ralan Dokter')->oneArray();
        $row['no'] = '';
        $row['nm_perawatan'] = $data['nm_perawatan'];
        $row['satu'] = $data['total_byrdr'];
        $row['dua'] = $data['jml'];
        $row['tiga'] = isset_or($tamkur['biaya'], '0');
        $row['empat'] = $data['biaya']+isset_or($tamkur['biaya'], '0');
        $row['pemisah'] = ':';
        $row['status'] = 'Ralan Dokter';
        $prosesCariRwJlDr[] = $row;
      }

      $rawat_jl_drpr = $this->db('rawat_jl_drpr')
        ->select([
          'total_byrdrpr' => 'rawat_jl_drpr.biaya_rawat',
          'nm_perawatan' => 'nm_perawatan',
          'jml' => 'count(rawat_jl_drpr.kd_jenis_prw)',
          'biaya' => 'sum(rawat_jl_drpr.biaya_rawat)',
          'totalbhp' => 'sum(rawat_jl_drpr.bhp)',
          'totalmaterial' => '(sum(rawat_jl_drpr.material)+sum(rawat_jl_drpr.menejemen)+sum(rawat_jl_drpr.kso))',
          'tarif_tindakandr' => 'rawat_jl_drpr.tarif_tindakandr',
          'totaltarif_tindakanpr' => 'sum(rawat_jl_drpr.tarif_tindakanpr)',
          'totaltarif_tindakandr' => 'sum(rawat_jl_drpr.tarif_tindakandr)'
        ])
        ->join('jns_perawatan', 'rawat_jl_drpr.kd_jenis_prw=jns_perawatan.kd_jenis_prw')
        ->where('no_rawat', $no_rawat)
        ->group('jns_perawatan.nm_perawatan')
        ->toArray();

      $prosesCariRwJlDrPr = [];
      foreach ($rawat_jl_drpr as $data) {
        $tamkur = $this->db('temporary_tambahan_potongan')->where('no_rawat', $no_rawat)->where('nama_tambahan', $data['nm_perawatan'])->where('status', 'Ralan Dokter')->oneArray();
        $row['no'] = '';
        $row['nm_perawatan'] = $data['nm_perawatan'];
        $row['satu'] = $data['total_byrdrpr'];
        $row['dua'] = $data['jml'];
        $row['tiga'] = isset_or($tamkur['biaya'], '0');
        $row['empat'] = $data['biaya']+isset_or($tamkur['biaya'], '0');
        $row['pemisah'] = ':';
        $row['status'] = 'Ralan Dokter Paramedis';
        $prosesCariRwJlDrPr[] = $row;
      }

      $rawat_jl_pr = $this->db('rawat_jl_pr')
        ->select([
          'total_byrpr' => 'rawat_jl_pr.biaya_rawat',
          'nm_perawatan' => 'nm_perawatan',
          'jml' => 'count(rawat_jl_pr.kd_jenis_prw)',
          'biaya' => 'sum(rawat_jl_pr.biaya_rawat)',
          'totalbhp' => 'sum(rawat_jl_pr.bhp)',
          'totalmaterial' => '(sum(rawat_jl_pr.material)+sum(rawat_jl_pr.menejemen)+sum(rawat_jl_pr.kso))',
          'tarif_tindakanpr' => 'rawat_jl_pr.tarif_tindakanpr',
          'totaltarif_tindakanpr' => 'sum(rawat_jl_pr.tarif_tindakanpr)'
        ])
        ->join('jns_perawatan', 'rawat_jl_pr.kd_jenis_prw=jns_perawatan.kd_jenis_prw')
        ->where('no_rawat', $no_rawat)
        ->group('jns_perawatan.nm_perawatan')
        ->toArray();

      $prosesCariRwJlPr = [];
      foreach ($rawat_jl_pr as $data) {
        $tamkur = $this->db('temporary_tambahan_potongan')->where('no_rawat', $no_rawat)->where('nama_tambahan', $data['nm_perawatan'])->where('status', 'Ralan Dokter')->oneArray();
        $row['no'] = '';
        $row['nm_perawatan'] = $data['nm_perawatan'];
        $row['satu'] = $data['total_byrpr'];
        $row['dua'] = $data['jml'];
        $row['tiga'] = isset_or($tamkur['biaya'], '0');
        $row['empat'] = $data['biaya']+isset_or($tamkur['biaya'], '0');
        $row['pemisah'] = ':';
        $row['status'] = 'Ralan Paramedis';
        $prosesCariRwJlPr[] = $row;
      }

      $prosesCariPeriksaLab =
      [
      ];

      $prosesCariRadiologi =
      [
      ];

      $prosesCariOperasi =
      [
      ];

      $prosesCariSarpras =
      [
        [
            "no" => "Jasa Sarana dan Prasarana",
            "nm_perawatan" => ":",
            "satu" => null,
            "dua" => null,
            "tiga" => null,
            "empat" => null,
            "pemisah" => "",
            "status" => "Ralan Dokter",
        ]
      ];

      $detail_pemberian_obat = $this->db('detail_pemberian_obat')
        ->select([
          'nama_brng' => 'databarang.nama_brng',
          'nama' => 'jenis.nama',
          'biaya_obat' => 'detail_pemberian_obat.biaya_obat',
          'jml' => 'sum(detail_pemberian_obat.jml)',
          'tambahan' =>'sum(detail_pemberian_obat.embalase+detail_pemberian_obat.tuslah)',
          'total' => '(sum(detail_pemberian_obat.total)-sum(detail_pemberian_obat.embalase+detail_pemberian_obat.tuslah))',
          'totalbeli' => 'sum((detail_pemberian_obat.h_beli*detail_pemberian_obat.jml))'
        ])
        ->join('databarang', 'databarang.kode_brng=detail_pemberian_obat.kode_brng')
        ->join('jenis', 'jenis.kdjns=databarang.kdjns')
        ->where('no_rawat', $no_rawat)
        ->group('detail_pemberian_obat.kode_brng')
        ->asc('jenis.nama')
        ->toArray();

      $prosesCariObat = [];
      foreach ($detail_pemberian_obat as $data) {
        $row['no'] = '';
        $row['nm_perawatan'] = $data['nama_brng'].' '.$data['nama'];
        $row['satu'] = $data['biaya_obat'];
        $row['dua'] = $data['jml'];
        $row['tiga'] = $data['tambahan'];
        $row['empat'] = $data['total']+$data['tambahan'];
        $row['pemisah'] = ':';
        $row['status'] = 'Obat';
        $prosesCariObat[] = $row;
      }


      $prosesCariObatBhp =
      [
        [
            "no" => "Paket Obat/BHP",
            "nm_perawatan" => ":",
            "satu" => null,
            "dua" => null,
            "tiga" => null,
            "empat" => null,
            "pemisah" => "",
            "status" => "Ralan Dokter",
        ]
      ];

      $prosesCariTambahan =
      [
        [
            "no" => "Tambahan Biaya",
            "nm_perawatan" => ":",
            "satu" => null,
            "dua" => null,
            "tiga" => null,
            "empat" => null,
            "pemisah" => "",
            "status" => "Tambahan",
        ]
      ];

      $prosesCariPotongan =
      [
        [
            "no" => "Potongan Biaya",
            "nm_perawatan" => ":",
            "satu" => null,
            "dua" => null,
            "tiga" => null,
            "empat" => null,
            "pemisah" => "",
            "status" => "Potongan",
        ]
      ];

      $result = array_merge(
        $prosesCariReg, // fix
        $prosesCariTindakan, // fix
        $prosesCariRwJlDr, // fix
        $prosesCariRwJlDrPr, // fix
        $prosesCariRwJlPr, // fix
        $prosesCariPeriksaLab,
        $prosesCariRadiologi,
        $prosesCariSarpras,
        $prosesCariObat, // fix
        $prosesCariObatBhp,
        $prosesCariTambahan,
        $prosesCariPotongan
      );
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

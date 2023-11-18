<?php

namespace Plugins\Khanza\Src;

use Plugins\Khanza\MySQL;
use Systems\Lib\BpjsService;

class Bpjs {

    protected function db($table = NULL)
    {
        return new MySQL($table);
    }
  
    public function CekNik($nik, $tglPelayananSEP)
    {
        $settings = array_column($this->db('mlite_settings')->where('module', 'settings')->toArray(), 'value', 'field');
        $this->consid = $settings['BpjsConsID'];
        $this->secretkey = $settings['BpjsSecretKey'];
        $this->user_key = $settings['BpjsUserKey'];
        $this->api_url = $settings['BpjsApiUrl'];
     
        date_default_timezone_set('UTC');
        $tStamp = strval(time() - strtotime("1970-01-01 00:00:00"));
        $key = $this->consid . $this->secretkey . $tStamp;
    
        $url = $this->api_url . 'Peserta/nik/' . $nik . '/tglSEP/' . $tglPelayananSEP;
        $output = BpjsService::get($url, NULL, $this->consid, $this->secretkey, $this->user_key, $tStamp);
        $json = json_decode($output, true);
        //echo json_encode($json);
        $code = $json['metaData']['code'];
        $message = $json['metaData']['message'];
        $stringDecrypt = stringDecrypt($key, $json['response']);
        $decompress = '""';
        if (!empty($stringDecrypt)) {
          $decompress = decompress($stringDecrypt);
        }
        if ($json != null) {
          $response = '{
                "metaData": {
                    "code": "' . $code . '",
                    "message": "' . $message . '"
                },
                "response": ' . $decompress . '}';
        } else {
          $response = '{
                "metaData": {
                    "code": "5000",
                    "message": "ERROR"
                },
                "response": "ADA KESALAHAN ATAU SAMBUNGAN KE SERVER BPJS TERPUTUS."}';
        }
        return json_decode($response, true);
    }   
    
    public function CekNoka($noka, $tglPelayananSEP)
    {
        $settings = array_column($this->db('mlite_settings')->where('module', 'settings')->toArray(), 'value', 'field');
        $this->consid = $settings['BpjsConsID'];
        $this->secretkey = $settings['BpjsSecretKey'];
        $this->user_key = $settings['BpjsUserKey'];
        $this->api_url = $settings['BpjsApiUrl'];
     
        date_default_timezone_set('UTC');
        $tStamp = strval(time() - strtotime("1970-01-01 00:00:00"));
        $key = $this->consid . $this->secretkey . $tStamp;
    
        $url = $this->api_url . 'Peserta/nokartu/' . $noka . '/tglSEP/' . $tglPelayananSEP;
        $output = BpjsService::get($url, NULL, $this->consid, $this->secretkey, $this->user_key, $tStamp);
        $json = json_decode($output, true);
        //echo json_encode($json);
        $code = $json['metaData']['code'];
        $message = $json['metaData']['message'];
        $stringDecrypt = stringDecrypt($key, $json['response']);
        $decompress = '""';
        if (!empty($stringDecrypt)) {
          $decompress = decompress($stringDecrypt);
        }
        if ($json != null) {
          $response = '{
                "metaData": {
                    "code": "' . $code . '",
                    "message": "' . $message . '"
                },
                "response": ' . $decompress . '}';
        } else {
          $response = '{
                "metaData": {
                    "code": "5000",
                    "message": "ERROR"
                },
                "response": "ADA KESALAHAN ATAU SAMBUNGAN KE SERVER BPJS TERPUTUS."}';
        }
        return json_decode($response, true);
    }       

}

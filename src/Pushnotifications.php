<?php 

namespace Csgt\Pushnotifications;
use Config, View, Exception, Response;

class Pushnotifications {
  private $url;
  private $certificate;
  private $certificatepassword;
  private $fp;

  public function __construct() {
    if (config("csgtpushnotifications.ios.environment")=="development")
      $this->url = 'ssl://gateway.sandbox.push.apple.com:' . config("csgtpushnotifications.ios.port");
    else 
      $this->url = 'ssl://gateway.push.apple.com:' . config("csgtpushnotifications.ios.port");
    
    $this->certificate         = config("csgtpushnotifications.ios.certificate");
    $this->certificatepassword = config("csgtpushnotifications.ios.certificatepassword");
  }

  private function conectariOS(){
    $response = ['codigoerror'=>0, 'error'=>'', 'data'=>[]];
    try {
      $streamContext = stream_context_create();

      stream_context_set_option($streamContext, 'ssl', 'local_cert', $this->certificate);
      if ($this->certificatepassword<>'')
        stream_context_set_option($streamContext, 'ssl', 'passphrase', $this->certificatepassword);

      $this->fp = stream_socket_client($this->url, $err, $errstr, 60, STREAM_CLIENT_CONNECT|STREAM_CLIENT_PERSISTENT, $streamContext);
      
      if (!$this->fp) {
        $response['codigoerror'] = 1;
        $response['error']       = 'Error conectando a APNs: ' . $err;
        return json_encode($response);
      } 
      return json_encode($response);
    } 
    catch (\Exception $e) {
      $response['codigoerror'] = 2;
      $response['error']       = 'Error conectando a APNs: ' . $e->getMessage();
      return json_encode($response);
    }
  }

  public function enviariOS($aMensajes) {
    $response = ['codigoerror'=>0, 'error'=>'', 'data'=>[]];

    try {
      $con = $this->conectariOS();
      $json = json_decode($con);
      if ($json->codigoerror<>0) {
        $response['codigoerror'] = $json->codigoerror;
        $response['error']       = $json->error;
        return json_encode($response);
      }

      //En este punto ya estamos conectados al APN, mandamos todos los mensajes
      foreach($aMensajes as $mensaje) {
        $payload['aps'] = ['alert' => $mensaje['mensaje'], 'badge' => 0, 'sound' => 'default'];
        $jsonpayload = json_encode($payload);
        $msg = chr(0) . pack('n', 32) . pack('H*', $mensaje['token']) . pack('n', strlen($jsonpayload)) . $jsonpayload;

        try {
          $resultado = fwrite($this->fp, $msg, strlen($msg));
          $response['data'][] = ['token'=>$mensaje['token'], 'resultado'=>$resultado]; 
        } 
        catch (\Exception $e) {
          fclose($this->fp);
          $response['data'][] = ['token'=>$mensaje['token'], 'resultado'=>'Error payload: ' . $e->getMessage()]; 
          sleep(5);
          $con = $this->conectariOS();
          $json = json_decode($con);
          if ($json->codigoerror<>0) {
            $response['codigoerror'] = $json->codigoerror;
            $response['error']       = $json->error;
            return json_encode($response);
          }
        }
      }
      return json_encode($response);
    } 
    catch (\Exception $e) {
      $response['codigoerror'] = 2;
      $response['error']       = 'Error conectando a APNs: ' . $e->getMessage();
      return json_encode($response);
    }    
  }
}
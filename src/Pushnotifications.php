<?php 

namespace Csgt\Pushnotifications;
use Config, View, Exception, Response;

class Pushnotifications {
  private $url;
  private $certificate;
  private $certificatepassword;
 
  public function __construct() {
    if (config("csgtpushnotifications.ios.environment")=="development")
      $this->url = 'ssl://gateway.sandbox.push.apple.com:' . config("csgtpushnotifications.ios.port");
    else 
      $this->url = 'ssl://gateway.push.apple.com:' . config("csgtpushnotifications.ios.port");
    
    $this->certificate         = config("csgtpushnotifications.ios.certificate");
    $this->certificatepassword = config("csgtpushnotifications.ios.certificatepassword");
  }

  public function enviariOS($aMensajes) {
    $response = ['codigoerror'=>0, 'error'=>'', 'data'=>[]];

    try {
      $streamContext = stream_context_create();

      stream_context_set_option($streamContext, 'ssl', 'local_cert', $this->certificate);
      if ($this->certificatepassword<>'')
        stream_context_set_option($streamContext, 'ssl', 'passphrase', $this->certificatepassword);

      $fp = stream_socket_client($this->url, $err, $errstr, 60, STREAM_CLIENT_CONNECT|STREAM_CLIENT_PERSISTENT, $streamContext);

      if (!$fp) {
        $response['codigoerror'] = 1;
        $response['error']       = 'Error conectando a APNs: ' . $err;
        return Response::json($response);
      }
      //En este punto ya estamos conectados al APN, mandamos todos los mensajes
      foreach($aMensajes as $mensaje) {
        $payload['aps'] = ['alert' => $mensaje['mensaje'], 'badge' => 0, 'sound' => 'default'];
        $jsonpayload = json_encode($payload);
        $msg = chr(0) . pack('n', 32) . pack('H*', $mensaje['token']) . pack('n', strlen($jsonpayload)) . $jsonpayload;
        $resultado = fwrite($fp, $msg, strlen($msg));
        $response['data'][] = ['token'=>$mensaje['token'], 'resultado'=>$resultado];
      }
      return Response::json($response);
    } 
    catch (Exception $e) {
      $response['codigoerror'] = 2;
      $response['error']       = 'Error conectando a APNs: ' . $e->getMessage();
      return Response::json($response);
    }    
  }
}
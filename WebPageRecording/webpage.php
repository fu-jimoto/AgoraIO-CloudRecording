<?php
##agora.io###
$plainCredentials = '***:***';
$appid = '***';
$token = '';
$cname = 'demo';
$uid = '50000';
$mode = 'web';
$resourceid = '';
$sid = '';
##CloudStorage###
$accessKey = '***';
$bucket = '***';
$secretKey = '***';
$fileNamePrefix = ['record'];

##WebPage##
$webPageUrl = '***';

##RESTful API Base URL##
$baseUrl = 'https://api.agora.io/v1/apps';

##Other Information##
$sleep = 60;
$myTZ = "Asia/Tokyo";


##acquire##
function acquire() {
global $baseUrl, $appid, $cname, $uid, $code, $body, $resourceid;
  $url = $baseUrl.'/'.$appid.'/cloud_recording/acquire';
  $clientRequest = [
    'resourceExpiredHour' => 24,
    'scene' => 1
  ];

  $params = [
    'cname' => $cname,
    'uid' => $uid,
    'clientRequest' => $clientRequest
  ];

  $response = requestAPI($url, $params);
  $resourceid=$response[1]->{'resourceId'};
  return($response[0]);

}

##start##
function start() {
  global $baseUrl, $appid, $cname, $uid, $code, $body, $resourceid, $mode, $accessKey,$bucket, $secretKey, $fileNamePrefix, $token, $sid, $webPageUrl;
  $url = $baseUrl.'/'.$appid.'/cloud_recording/resourceid/'.$resourceid.'/mode/'.$mode.'/start';

  $recordingConfig = [
   'avFileType' => ["hls","mp4"]
  ];
  $storageConfig= [
  'accessKey' => $accessKey,
  'region' => 10,
  'bucket' => $bucket,
  'secretKey' => $secretKey,
  'vendor' => 1,
  'fileNamePrefix' => $fileNamePrefix
  ];
  $serviceParam = [
  'url' => $webPageUrl,
  'audioProfile' => 0,
  'videoWidth' => 1280,
  'videoHeight' => 720,
  'maxRecordingHour' => 1
  ];
  $extensionServices = [
  'errorHandlePolicy' => 'error_abort',
  'serviceName' => 'web_recorder_service',
  'serviceParam' => $serviceParam
  ];
  $extensionServiceConfig = [
  'errorHandlePolicy' => 'error_abort',
  'extensionServices' => [$extensionServices]
  ];
  $clientRequest = [
  'token' => $token,
  'extensionServiceConfig' => $extensionServiceConfig,
  'recordingConfig' => $recordingConfig,
  'storageConfig' => $storageConfig
  ];
  $params = [
  'cname' => $cname,
  'uid' => $uid,
  'clientRequest' => $clientRequest
  ];

  $response = requestAPI($url, $params);
  $sid=$response[1]->{'sid'};
  return($response[0]);

}

##stop##
function stop() {
  global $baseUrl, $appid, $cname, $uid, $code, $body, $resourceid, $mode, $accessKey,$bucket, $secretKey, $fileNamePrefix, $token, $sid;
  $url = $baseUrl.'/'.$appid.'/cloud_recording/resourceid/'.$resourceid.'/sid/'.$sid.'/mode/'.$mode.'/stop';

  $params = [
  'cname' => $cname,
  'uid' => $uid,
  'clientRequest' => (object)[]
  ];
  $response = requestAPI($url, $params);
  return($response[0]);


}

##requestAPI##
function requestAPI($url, $params) {

  global $plainCredentials;

  $json_enc = json_encode($params);

  outputLog($url);
  outputLog($json_enc);

  $header = array();
  $header[] = 'Content-type: application/json;charset=utf-8';
  $header[] = 'Authorization: Basic '.base64_encode($plainCredentials);

  $curl = curl_init($url);
  curl_setopt($curl, CURLOPT_POST, TRUE);
  curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
  curl_setopt($curl, CURLOPT_POSTFIELDS, $json_enc); 
  curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
  curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
  curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($curl, CURLOPT_HEADER, true);
  $response = curl_exec($curl);
  outputLog($response);
  $code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
  $header_size = curl_getinfo($curl, CURLINFO_HEADER_SIZE); 
  $header = substr($response, 0, $header_size);
  $body = substr($response, $header_size);
  $result = json_decode($body); 
  curl_close($curl);
  return(array($code, $result));

}

##outputLog##
##resourceID、sidを保存するため、ログ出力を行っていますが、必要に応じてコメントアウトしてください。
function outputLog($str) {
  echo "<pre>";
  print($str);
  echo "</pre>";
  error_log($str."\n",3,"./test.log");
}

date_default_timezone_set($myTZ);
##Step1.execute acquire()##
outputLog("---acquire---".date('Y/m/d H:i:s'));
if(acquire() != 200){return;}
##Step2.execute start()##
outputLog("---start---".date('Y/m/d H:i:s'));
if(start() != 200){return;}
##execute sleep##
sleep ( $sleep );
##Step3.execute stop()##
outputLog("---stop---".date('Y/m/d H:i:s'));
if(stop() != 200){return;}

?>

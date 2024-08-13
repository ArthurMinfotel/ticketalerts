<?php

//init Session
define('DO_NOT_CHECK_HTTP_REFERER', 1);
ini_set('session.use_cookies', 0);

include ('../../inc/includes.php');

ini_set("memory_limit", "-1");
ini_set("max_execution_time", "0");

//URL de GLPI
$api_url    = "http://localhost/9.2-telintrans/apirest.php";
//Jeton d'API d'un utilisateur
$user_token = "Josc5AIfHvItTX0WyI5QaefgR7kUW8OS0OYjrtAD";
//Jeton d'application du client API
$app_token  = "ZzUBVfQIVGCSo4wtasqFrzhzfvCw5AMmgIMyPihK";


$headers_initSession = [
   "Content-Type: application/json",
   "Authorization: user_token $user_token",
   "App-Token: $app_token"];

$ch = curl_init();
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers_initSession);
curl_setopt($ch, CURLOPT_URL, "$api_url/initSession/");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
$json = curl_exec($ch);

curl_close($ch);
$json = json_decode($json, true);

if (array_key_exists('session_token', $json)) {

   $session_token = $json['session_token'];

   $headers = [
      "Content-Type: application/json",
      "Session-Token: $session_token",
      "App-Token: $app_token"];

   /////////////////////////////////////////////////////////////////////////////////////////
   /// Ajout d'un ticketalert
   ///
   //   $ch = curl_init();
   //   curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
   //
   //   curl_setopt($ch, CURLOPT_POSTFIELDS, '{"input": {"name": "Alert 1",
   //   "plugin_ticketalerts_alerttypes_id": 2, "alert_date": "2017-06-06 17:55:49","comment":"Description Alerte"}}');
   //   curl_setopt($ch, CURLOPT_URL, "$api_url/PluginTicketalertsAlert");
   //   curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
   //   $request_result = curl_exec($ch);
   //   curl_close($ch);
   //   print_r($request_result);

   /////////////////////////////////////////////////////////////////////////////////////////
   /// Récupération de ticketalert
      $ch = curl_init();
      curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
      curl_setopt($ch, CURLOPT_URL, "$api_url/PluginTicketalertsAlert/23975");
      $request_result = curl_exec($ch);
      curl_close($ch);

   /********************************************************************/
   //Kill Session

   $ch = curl_init();
   curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

   curl_setopt($ch, CURLOPT_URL, "$api_url/killSession/");
   $json = curl_exec($ch);
   curl_close($ch);
}



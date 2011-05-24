<?php
//error reporting since my php install is all jacked - turn off for use
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', dirname(__FILE__) . '/error_log.txt');
error_reporting(E_ALL);

class Ooyala {
	function remove_video_query($params,$options = NULL){
		$options['urlBase'] = 'http://api.ooyala.com/partner/';
		$removeURL = Ooyala::send_request('query',$params,$options);
		$xml = simplexml_load_file($removeURL);
		if(isset($xml->item->embedCode)){
			return $xml->item->embedCode;
		}else{
			return 'none found';
		}
	}
	
	function remove_video($params,$options = NULL){
		$options['urlBase'] = 'http://api.ooyala.com/partner/';
		$deleteURL = Ooyala::send_request('edit', $params,$options);
		$ch = curl_init($deleteURL);
		$postResult = curl_exec($ch);
		curl_close($ch);
		if($postResult == 'ok'){
			return 'pass';
		}else{
			return 'fail';
		}
	}
	
	function upload($params,$options = NULL){//this passes to the send request function for the first time
		$options['urlBase'] = 'http://api.ooyala.com/ingestion/';
		$uploadUrl = Ooyala::send_request('create_video',$params,$options);
		$xml = simplexml_load_file($uploadUrl);
		$upload_url = $xml->urls->url;
		$file= $_FILES['video_file']['tmp_name'];
		$ch = curl_init($upload_url);  
		curl_setopt($ch, CURLOPT_POSTFIELDS, array('file'=>"@$file"));//post the file
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		$postResult = curl_exec($ch);
		curl_close($ch);
		if ($postResult != ''){
			//upload works
			return $xml->embedCode;
		}else{
			//upload failed
			return 'fail';
		}
	}

	function upload_complete($params,$options = NULL){//this gets hit after posting the video to ooyala
		$options['urlBase'] = 'http://api.ooyala.com/ingestion/';
		$completeUrl = Ooyala::send_request('upload_complete', $params,$options);//goes back to send request for the second time
		$ch = curl_init($completeUrl);  
		$postResult = curl_exec($ch);
		curl_close($ch);
		if ($postResult != ''){
			return 'pass';
		}else{
			return 'fail';
		}
	}
	
	function assign_label($params,$options = NULL){//this starts creating and assigning labels
		$options['urlBase'] = 'http://api.ooyala.com/partner/';
		$addLabelURL = Ooyala::send_request('labels',$params,$options);//this kicks off send request 2 to assign label
		$xml = simplexml_load_file($addLabelURL);
		if($xml == 'ok'){
			return 'pass';
		}else{
			return 'fail';
		}
	}
	
	function add_title($params,$options = NULL){
		$options['urlBase'] = 'http://api.ooyala.com/partner/';
		$startDateURL = Ooyala::send_request('edit',$params,$options);
		$ch = curl_init($startDateURL);
		$postResult = curl_exec($ch);
		curl_close($ch);
		if($postResult == 'ok'){
			return 'pass';
		}else{
			return 'fail';
		}
	}
	
	function add_meta_data($params,$options = NULL){
		$options['urlBase'] = 'http://api.ooyala.com/partner/';
		$metaDataURL = Ooyala::send_request('set_metadata',$params,$options);
		$xml = simplexml_load_file($metaDataURL);
		$att = 'code';
		if((string)$xml->attributes()->$att == 'success'){
			return 'pass';
		}else{
			return 'fail';
		}
	}
	
	function setStartDate($params,$options = NULL){
		$options['urlBase'] = 'http://api.ooyala.com/partner/';
		$startDateURL = Ooyala::send_request('edit',$params,$options);
		$ch = curl_init($startDateURL);
		$postResult = curl_exec($ch);
		curl_close($ch);
		if($postResult == 'ok'){
			return 'pass';
		}else{
			return 'fail';
		}
	}
	
	function setEndDate($params,$options = NULL){
		$options['urlBase'] = 'http://api.ooyala.com/partner/';
		$endDateURL = Ooyala::send_request('edit',$params,$options);
		$ch = curl_init($endDateURL);
		$postResult = curl_exec($ch);
		curl_close($ch);
		if($postResult == 'ok'){
			return 'pass';
		}else{
			return 'fail';
		}
	}
	
	function uploadThumbnail($params,$options = NULL){
		$options['urlBase'] = 'http://uploader.ooyala.com/api/upload/preview';
		$uploadTumbnailURL = Ooyala::send_request('upload_complete',$params,$options);
		$thumbfile = $_FILES['thumbnail']['tmp_name'];
		$ch = curl_init($uploadTumbnailURL);  
		curl_setopt($ch, CURLOPT_POSTFIELDS, array('file'=>"@$thumbfile"));//post the file
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		$postResult = curl_exec($ch);
		curl_close($ch);
		if ($postResult != ''){
			return 'pass';
		}else{
			return 'fail';
		}
	}
	
	function addToChannel($params,$options = NULL){
		$options['urlBase'] = 'http://www.ooyala.com/partner/channel_sets';
		$addChannelURL = Ooyala::send_request('add_channel',$params,$options);
		$ch = curl_init($addChannelURL);
		$postResult = curl_exec($ch);
		curl_close($ch);
		if($postResult == 'ok'){
			return 'pass';
		}else{
			return 'fail';
		}
	}
	
	private static function send_request($request_type, $params, $options){
		$ooyala_pcode = 'FqY2o6IaPQg4mSYPUApYy0LgwE_p';
		$ooyala_scode = 't8u-Ed-aDLlVecI2C4wDfhqPbbcXXDwZa2sAJtsw';
		// Add an expire time of 15 minutes unless otherwise specified
		if (!array_key_exists('expires', $params)) {
			$params['expires'] = time() + 900;
		}
		$string_to_sign = $ooyala_scode;
		if($options['urlBase'] == 'http://uploader.ooyala.com/api/upload/preview'){
			$urlBase = $options['urlBase'];
		}else{
			$urlBase = $options['urlBase'].$request_type;
		}
		if($options['urlBase'] == 'http://uploader.ooyala.com/api/upload/preview'){
			$urlBase = $options['urlBase'];
		}
		$url = $urlBase.'?pcode='.$ooyala_pcode;
		$keys = array_keys($params);
		sort($keys);

		foreach ($keys as $key) {
			$string_to_sign .= $key.'='.$params[$key];
			$url .= '&'.rawurlencode($key).'='.rawurlencode($params[$key]);
		}
		$digest = hash('sha256', $string_to_sign, true);
		$signature = ereg_replace('=+$', '', trim(base64_encode($digest)));
		$url .= '&signature='.rawurlencode($signature);
		return $url;
	}
	
	private static function word_character_remover($string){
		// First, replace UTF-8 characters.
		$string = str_replace(
			array("\xe2\x80\x98", "\xe2\x80\x99", "\xe2\x80\x9c", "\xe2\x80\x9d", "\xe2\x80\x93", "\xe2\x80\x94", "\xe2\x80\xa6"),
			array("'", "'", '"', '"', '-', '--', '...'),
			$string);
		// Next, replace their Windows-1252 equivalents.
		$string = str_replace(
			array(chr(145), chr(146), chr(147), chr(148), chr(150), chr(151), chr(133)),
			array("'", "'", '"', '"', '-', '--', '...'),
			$string);
		return $string;
	}
}


//set default things like root label and channel that is client specific here
$needsThumbnail = 'false'; //true or false if using thumbnail uploads
$needsStartDate = 'false'; //true or false if using start date
$needsEndDate = 'false'; //true or false if using end date

$rootLabel = '/';//everything up to the last label e.g. /SuperPages/

$channel = 'none'; // set to none if no need to assign video to a channel or use channel embed code

$metaDataNeeded = array('key'=>'value');

//get all posted data
$topLabel = '1111111113';

$description = Ooyala::word_character_remover('test');

$title = Ooyala::word_character_remover('test');

//find video is exists
$removeOldVideoQuery = Ooyala::remove_video_query(array('label[0]' =>$rootLabel.$topLabel, 'limit' => 1, 'fields' => 'labels'));
// delete video if there was one
if($removeOldVideoQuery != 'none found'){
	$removeOldVideo = Ooyala::remove_video(array('embedCode' => $removeOldVideoQuery, 'status' => 'paused'));
	if($removeOldVideo == 'pass'){
		//everything is fine
	}else{
		//throw an error or something here
	}
}

// upload the new video
$upload = Ooyala::upload(array('file_size' => $_FILES['video_file']['size'], 'file_name' => $_FILES['video_file']['name'], 'title' => $_FILES['video_file']['name']));
if($upload != 'fail'){
	$embedCode = $upload;
	$upload_complete = Ooyala::upload_complete(array('embed_code' => $embedCode));
	if($upload_complete == 'fail'){
		//throw an error since video wasnt completed
	}
}else{
	//throw an error since video was not posted
}

// add the new label
$add_label = Ooyala::assign_label(array('embedCodes' => $embedCode, 'labels' => $rootLabel.$topLabel, 'mode'=>'assignLabels'));
if($add_label != 'pass'){
	//throw error no labels
}

//add title and description
$add_title = Ooyala::add_title(array('embedCode' => $embedCode, 'description' => $description, 'title' => $title));
if($add_title != 'pass'){
	//throw an error no metadata
}

// add meta data
$add_meta = Ooyala::add_meta_data(array('embedCode' => $embedCode, $metaDataNeeded));
if($add_meta != 'pass'){
	//throw an error no metadata
}

//set start and end dates if needed
if($needsStartDate == 'true'){
	$startDate = Ooyala::setStartDate(array('embedCode' => $embedCode));
}
if($needsEndDate == 'true'){
	$endDate = Ooyala::setEndDate(array('embedCode' => $embedCode));
}

// add thumbnail if needed
if($needsThumbnail == 'true'){
	$upload_thumbnail = Ooyala::uploadThumbnail(array('embed_code' => $embedCode));
	if($upload_thumbnail != 'pass'){
		//throw error
	}
}

// add video to channel if needed
if($channel != 'none'){
	$addToChannel = Ooyala::addToChannel(array('channelSetEmbedCode' => $channel, 'channelEmbedCodes' => $embedCode , 'mode' => 'assign'));
	if($addToChannel != 'pass'){
		//throw error
	}
}

?>
<?php
$html='';
$config=array();
$sock='';
function curl($url = '', $var = '', $header = false, $nobody = false) {
    global $config, $sock;
    $curl = curl_init($url);
    curl_setopt($curl, CURLOPT_NOBODY, $header);
    curl_setopt($curl, CURLOPT_HEADER, $nobody);
    curl_setopt($curl, CURLOPT_TIMEOUT, 20);
    // curl_setopt($curl, CURLOPT_USERAGENT, random_uagent());
    curl_setopt($curl, CURLOPT_REFERER, 'https://www.abercrombie.com/webapp/wcs/stores/servlet/GCLookup?catalogId=10901&langId=-1&storeId=10051&krypto=kJjEF2XjnLuFtcpb%2FbyV9A%3D%3D&ddkey=http:GCLookup');
    if ($var) {
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $var);
    }
    curl_setopt($curl, CURLOPT_COOKIEFILE, $config['cookie_file']);
    curl_setopt($curl, CURLOPT_COOKIEJAR, $config['cookie_file']);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);
    curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    $result = curl_exec($curl);
    if(curl_errno($curl))
	{
	    echo 'error:' . curl_error($curl);
	}
    curl_close($curl);
    return $result;
}
function delete_cookies() {
    global $config;
    $fp = @fopen($config['cookie_file'], 'w');
    @fclose($fp);
}
function fetch_value($str, $find_start, $find_end) {
    $start = strpos($str, $find_start);
    if ($start === false) {
        return "";
    }
    $length = strlen($find_start);
    $end = strpos(substr($str, $start + $length), $find_end);
    return trim(substr($str, $start + $length, $end));
}

if(isset($_POST) && count($_POST)>0 && isset($_POST['codes']))
{
	$codes=explode("\n",$_POST['codes']);
	$dir = dirname(__FILE__);
	$html= '<table class="table table-bordered"><tr><th>Code</th><th>Status</th></tr>';
	$config['cookie_file'] = $dir . '/cookies/' . 'cookie.txt';
		if (!file_exists($config['cookie_file'])) {
    		$fp = @fopen($config['cookie_file'], 'w');
    		@fclose($fp);
		}
		delete_cookies();
	foreach($codes as $code)
	{
		$html.= '<tr>';
		$response=null;
		$lookupid='';
		$code=preg_replace("/[^0-9]$/", "",$code);
		$response=curl("https://www.abercrombie.com/webapp/wcs/stores/servlet/GCLookupSubmit","storeId=11203&catalogId=10901&langId=-1&URL=GCLookupResponse&country=US&biCardNumber=".$code);
		$lookupid=fetch_value($response,"GCLOOKUP_",'"');
		$response=null;
		$response=curl("https://www.abercrombie.com/webapp/wcs/stores/servlet/GCLookupStatus","storeId=11203&catalogId=10901&langId=-1&gcLookUpId=GCLOOKUP_".$lookupid);
		$reasoncode=fetch_value($response,'"reasonCode" : "','"');
		$balance=fetch_value($response,'"balance":"','"');
		$status=($reasoncode!=0)?"Invalid Code":(($balance=="")?'$0.00':$balance);
		$html.='<td>'.$code.'</td><td>'.$status.'</td>';		
		$html.='</tr>';
	}
	$html.='</table>';
}

{
?>

<!DOCTYPE html>
<html lang="en"><head>
<meta http-equiv="content-type" content="text/html; charset=UTF-8">
<meta charset="utf-8">
<title>Ambercrombie &amp; Fitch GC Checker</title>
<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.8/jquery.min.js"></script> 	
<link rel="stylesheet" href="//netdna.bootstrapcdn.com/bootstrap/3.0.3/css/bootstrap.min.css">
<link rel="stylesheet" href="//netdna.bootstrapcdn.com/bootstrap/3.0.3/css/bootstrap-theme.min.css">
<script src="//netdna.bootstrapcdn.com/bootstrap/3.0.3/js/bootstrap.min.js"></script>
</head>
<body>
	<div class="container">
	<div class="row">&nbsp;</div>
	<div class="row">
		<div class="col-md-3">&nbsp;</div>
		<div class="col-md-6"><?php echo $html;?></div>
		<div class="col-md-3">&nbsp;</div>
	</div>
	<div class="row">
		<div class="col-md-3">&nbsp;</div>
		<div class="col-md-6">
	<form action="" method="post" class="form-horizontal">
		<div class="form-group">
			<label for="codes">Code List (Enter one code on each line)</label>
	<textarea name="codes" id="codes" class="form-control" rows="12"><?php
if(isset($_POST['codes'])) 
echo $_POST['codes'];
?></textarea>
		</div>
	<br/>
	<input type="submit" value="Check" class="btn btn-info"/>
	</form>
		</div>
		<div class="col-md-3">&nbsp;</div>
	</div>
	</div>
</body>
</html>
<?php
}
?>
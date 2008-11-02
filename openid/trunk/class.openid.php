<?php
/*
	FREE TO USE - Simple OpenID PHP Class 
	Contributed by http://www.fivestores.com/	
	Some modifications by Eddie Roosenmaallen, eddie@roosenmaallen.com
    	Some OpenID 2.0 specifications added by Steve Love (stevelove.org)
    	More extensive modifications by _ck_ (bbshowcase.org)
	
-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=

This Class was written to make easy for you to integrate OpenID on your website. 
This is just a client, which checks for user's identity. This Class Requires CURL Module.
It should be easy to use some other HTTP Request Method, but remember, often OpenID servers are using SSL.
We need to be able to perform SSL Verification on the background to check for valid signature.

HOW TO USE THIS CLASS:
  STEP 1)
	$openid = new OpenIDService;
	:: SET IDENTITY ::
		$openid->SetIdentity($_POST['openid_url']);
	:: SET RETURN URL ::
		$openid->SetApprovedURL('http://www.yoursite.com/return.php'); // Script which handles a response from OpenID Server
	:: SET TRUST ROOT ::
		$openid->SetTrustRoot('http://www.yoursite.com/');
	:: FETCH SERVER URL FROM IDENTITY PAGE ::  [Note: It is recomended to cache this (Session, Cookie, Database)]
		$openid->GetOpenIDServer(); // Returns false if server is not found
	:: REDIRECT USER TO OPEN ID SERVER FOR APPROVAL ::
	
	:: (OPTIONAL) SET OPENID SERVER ::
		$openid->SetOpenIDServer($server_url); // If you have cached previously this, you don't have to call GetOpenIDServer and set value this directly
		
	STEP 2)
	Once user gets returned we must validate signature
	:: VALIDATE REQUEST ::
		true|false = $openid->ValidateWithServer();
		
	ERRORS:
		array = $openid->GetError(); 	// Get latest Error code
	
	FIELDS:
		OpenID allowes you to retreive a profile. To set what fields you'd like to get use (accepts either string or array):
		$openid->SetRequiredFields(array('email','fullname','dob','gender','postcode','country','language','timezone'));
		 or
		$openid->SetOptionalFields('postcode');
		
IMPORTANT TIPS:
OPENID as is now, is not trust system. It is a great single-sign on method. If you want to 
store information about OpenID in your database for later use, make sure you handle url identities
properly.
  For example:
	https://steve.myopenid.com/
	https://steve.myopenid.com
	http://steve.myopenid.com/
	http://steve.myopenid.com
	... are representing one single user. Some OpenIDs can be in format openidserver.com/users/user/ - keep this in mind when storing identities

	To help you store an OpenID in your DB, you can use function:
		$openid_db_safe = $openid->OpenID_Standarize($upenid);
	This may not be comatible with current specs, but it works in current enviroment. Use this function to get openid
	in one format like steve.myopenid.com (without trailing slashes and http/https).
	Use output to insert Identity to database. Don't use this for validation - it may fail.

*/

// error_reporting(E_ALL);

require_once 'Yadis/Yadis.php';

class OpenIDService{
	var $openid_url_identity;
	var $URLs = array();
	var $error = array();
	var $fields = array(
		'required'	 => array(),
		'optional'	 => array(),
	);
	
	function OpenIDService(){
		if (!function_exists('curl_exec')) {
			die('Error: Class OpenIDService requires curl extension to work');
		}
	}
	function SetOpenIDServer($a){
		$this->URLs['openid_server'] = $a;
	}
    function SetServiceType($a){
        // Hopefully the provider is using OpenID 2.0 but let's check
        // the protocol version in order to handle backwards compatibility.
        // Probably not the best method, but it works for now.
        if(stristr($a, "2.0")){
            $ns = "http://specs.openid.net/auth/2.0";
            $version = "2.0";
        }
        else if(stristr($a, "1.1")){
            $ns = "http://openid.net/signon/1.1";
            $version = "1.1";
        }else{
            $ns = "http://openid.net/signon/1.0";
            $version = "1.0";
        }
        $this->openid_ns = $ns;
        $this->version   = $version;
    }
	function SetTrustRoot($a){
		$this->URLs['trust_root'] = $a;
	}
	function SetCancelURL($a){
		$this->URLs['cancel'] = $a;
	}
	function SetApprovedURL($a){
		$this->URLs['approved'] = $a;
	}
	function SetRequiredFields($a){
		if (is_array($a)){
			$this->fields['required'] = $a;
		}else{
			$this->fields['required'][] = $a;
		}
	}
	function SetOptionalFields($a){
		if (is_array($a)){
			$this->fields['optional'] = $a;
		}else{
			$this->fields['optional'][] = $a;
		}
	}
	function SetIdentity($a){ 	// Set Identity URL
		if (!preg_match("/^https?:\/\//i",$a))  {$a = 'http://'.$a;}		 
/*
			$u = parse_url(trim($a));
			if (!isset($u['path'])){
				$u['path'] = '/';
			}else if(substr($u['path'],-1,1) == '/'){
				$u['path'] = substr($u['path'], 0, strlen($u['path'])-1);
			}
			if (isset($u['query'])){ // If there is a query string, then use identity as is
				$identity = $a;
			}else{
				$identity = $u['scheme'] . '://' . $u['host'] . $u['path'];
			}
//*/
			$this->openid_url_identity = $a;
	}
	function GetIdentity(){ 	// Get Identity
		return $this->openid_url_identity;
	}
	function GetError(){
		$e = $this->error;
		return array('code'=>$e[0],'description'=>$e[1]);
	}

	function ErrorStore($code, $desc = null){
		$errs['OPENID_NOSERVERSFOUND'] = 'Cannot find OpenID Server TAG on Identity page.';
		if ($desc == null){
			$desc = $errs[$code];
		}
	   	$this->error = array($code,$desc);
	}

	function IsError(){
		if (count($this->error) > 0){
			return true;
		}else{
			return false;
		}
	}
	
	function splitResponse($response) {
		$r = array();
		$response = explode("\n", $response);
		foreach($response as $line) {
			$line = trim($line);
			if ($line != "") {
				@list($key, $value) = explode(":", $line, 2);
				$r[trim($key)] = trim($value);
			}
		}
	 	return $r;
	}
	
	function OpenID_Standarize($openid_identity = null){
		if ($openid_identity === null)
			$openid_identity = $this->openid_url_identity;

		$u = parse_url(strtolower(trim($openid_identity)));
		
		if (!isset($u['path']) || ($u['path'] == '/')) {
			$u['path'] = '';
		}
		if(substr($u['path'],-1,1) == '/'){
			$u['path'] = substr($u['path'], 0, strlen($u['path'])-1);
		}
		if (isset($u['query'])){ // If there is a query string, then use identity as is
			return $u['host'] . $u['path'] . '?' . $u['query'];
		}else{
			return $u['host'] . $u['path'];
		}
	}
	
	function array2url($arr){ // converts associated array to URL Query String
		if (!is_array($arr)){
			return false;
		}
		$query = '';
		foreach($arr as $key => $value){
			$query .= $key . "=" . $value . "&";
		}
		return $query;
	}
	function FSOCK_Request($url, $method="GET", $params = ""){
		$fp = fsockopen("ssl://www.myopenid.com", 443, $errno, $errstr, 3); // Connection timeout is 3 seconds
		if (!$fp) {
			$this->ErrorStore('OPENID_SOCKETERROR', $errstr);
		   	return false;
		} else {
			$request = $method . " /server HTTP/1.0\r\n";
			$request .= "User-Agent: Simple OpenID PHP Class (http://www.phpclasses.org/simple_openid)\r\n";
			$request .= "Connection: close\r\n\r\n";
		   	fwrite($fp, $request);
		   	stream_set_timeout($fp, 4); // Connection response timeout is 4 seconds
		   	$res = fread($fp, 2000);
		   	$info = stream_get_meta_data($fp);
		   	fclose($fp);
		
		   	if ($info['timed_out']) {
		       $this->ErrorStore('OPENID_SOCKETTIMEOUT');
		   	} else {
		      	return $res;
		   	}
		}
	}
	function CURL_Request($url, $method="GET", $params = "") {		 // Remember, SSL MUST BE SUPPORTED
			if (is_array($params)) {$params = $this->array2url($params);}
			// print "$url --- $params"; // debug
			$curl = curl_init($url . ($method == "GET" && $params != "" ? "?" . $params : ""));
			// curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);	// can't do this when open_basedir in effect, use curl_redir instead
			curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 0);  //  can't work in safe mode or open_basedir 
			curl_setopt($curl, CURLOPT_MAXREDIRS, 2);	// doesn't really do anything now
  		             curl_setopt($curl, CURLOPT_CONNECTTIMEOUT,5);
  		             curl_setopt($curl, CURLOPT_TIMEOUT, 15);
			// curl_setopt($curl, CURLOPT_HEADER, 0);			// we need them now for redirect
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
	   	        	curl_setopt($curl, CURLOPT_BINARYTRANSFER, 1);
        			curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
        			curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
        			curl_setopt($curl, CURLOPT_FAILONERROR, 0);
        			curl_setopt($curl, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1; .NET CLR 1.1.4322)");

			curl_setopt($curl, CURLOPT_HTTPGET, ($method == "GET"));
			curl_setopt($curl, CURLOPT_POST, ($method == "POST"));
			if ($method == "POST") curl_setopt($curl, CURLOPT_POSTFIELDS, $params);

			// $response = curl_exec($curl);
			$response = $this->CURL_redir_exec($curl);
			
			if (curl_errno($curl) == 0){
				$response;
			}else{
				$this->ErrorStore('OPENID_CURL', curl_error($curl));
			}
			return $response;
	}
	
	function CURL_redir_exec($ch) {	// does curl redirects when safemode or open_basedir in effect
		static $curl_loops = 0;
		static $curl_max_loops = 10;
		if ($curl_loops++ >= $curl_max_loops) {$curl_loops = 0; return FALSE;}
		curl_setopt($ch, CURLOPT_HEADER, 1);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		$response = curl_exec($ch);
		// print "<pre>".$response;	// debug
		@list($headers, $data) = explode("\n\n", $response, 2);
		// $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);  // if ($http_code == 301 || $http_code == 302) {
		$matches = array();
		if (preg_match('/(|X\-XRDS\-)Location:(.*?)\n/', $headers, $matches)) {
		//	print_r($matches);	// debug
			$url = @parse_url(trim(array_pop($matches)));
		//	print "<pre>"; print_r($url);		// debug		
			$last_url = parse_url(curl_getinfo($ch, CURLINFO_EFFECTIVE_URL));	
			if (!$url['scheme']) {$url['scheme'] = $last_url['scheme'];}
			if (!$url['host']) {$url['host'] = $last_url['host'];}
			if (!$url['path']) {$url['path'] = $last_url['path'];}
			$new_url = $url['scheme'] . '://' . $url['host'] . $url['path'] . (isset($url['query'])?'?'.$url['query']:'');
			curl_setopt($ch, CURLOPT_URL, $new_url);
			return $this->CURL_redir_exec($ch);
		} 		
		else {$curl_loops=0; return $response; }
	}
	
	 function HTML2OpenIDServer($content) {
		$get = array();
		
		// Get details of their OpenID server and (optional) delegate
		preg_match_all('/<link[^>]*rel=[\'"]openid.server[\'"][^>]*href=[\'"]([^\'"]+)[\'"][^>]*\/?>/i', $content, $matches1);
		preg_match_all('/<link[^>]*href=\'"([^\'"]+)[\'"][^>]*rel=[\'"]openid.server[\'"][^>]*\/?>/i', $content, $matches2);
		$servers = array_merge($matches1[1], $matches2[1]);
		
		preg_match_all('/<link[^>]*rel=[\'"]openid.delegate[\'"][^>]*href=[\'"]([^\'"]+)[\'"][^>]*\/?>/i', $content, $matches1);
		
		preg_match_all('/<link[^>]*href=[\'"]([^\'"]+)[\'"][^>]*rel=[\'"]openid.delegate[\'"][^>]*\/?>/i', $content, $matches2);
		
		$delegates = array_merge($matches1[1], $matches2[1]);
		
		$ret = array($servers, $delegates);
		return $ret;
	}
	
	function GetOpenIDServer(){
		
		// Try Yadis Protocol discovery first
		$http_response = array();
		$fetcher = Services_Yadis_Yadis::getHTTPFetcher();
		$yadis_object = Services_Yadis_Yadis::discover($this->openid_url_identity,	$http_response, $fetcher);		
        
		// Yadis object is returned if discovery is successful
		/*
		if($yadis_object != null){			// hack out stupidly complex XML services for trivial information!
			// var_dump($yadis_object);
			// $service_list = $yadis_object->services();			
			// $types = $service_list[0]->getTypes();
			// $servers = $service_list[0]->getURIs();
			// $delegates = $service_list[0]->getElements('openid:Delegate');	// todo: delegates
		*/		
		// print_r($http_response); exit;		
		if (isset($http_response->body) && preg_match_all("/<service.*>.*<uri>(.*)<\/uri>.*<\/service>/simU",$http_response->body,$servers)) {
			$servers=$servers[1];
			if (preg_match_all("/<service.*>.*<type>(.*)<\/type>.*<\/service>/simU",$http_response->body,$types)) {$types=$types[1];}
			// print_r($servers); print_r($types); exit;
			// todo: delegates
		}else{ // Else try HTML discovery
			$response = $this->CURL_Request($this->openid_url_identity);
			list($servers, $delegates) = $this->HTML2OpenIDServer($response);
		}
		if (count($servers) == 0){
			$this->ErrorStore('OPENID_NOSERVERSFOUND');
			return false;
		}
		if (isset($types[0])
		  && ($types[0] != "")){
			$this->SetServiceType($types[0]);
		}
		if (isset($delegates[0])
		  && ($delegates[0] != "")){
			$this->SetIdentity($delegates[0]);
		}
		$this->SetOpenIDServer($servers[0]);
		return $servers[0];
	}
	
	function GetRedirectURL(){
        $params = array();
        $params['openid.return_to'] = urlencode($this->URLs['approved']);
        if($this->version == "2.0"){
            $params['openid.ns'] = urlencode($this->openid_ns);
            $params['openid.claimed_id'] = urlencode("http://specs.openid.net/auth/2.0/identifier_select"); // $this->openid_url_identity
            $params['openid.realm'] = urlencode($this->URLs['trust_root']);
        }else{
            $params['openid.trust_root'] = urlencode($this->URLs['trust_root']);
        }
        $params['openid.mode'] = 'checkid_setup';
        $params['openid.identity'] = urlencode($this->openid_url_identity);
		
		if (isset($this->fields['required'])
		  && (count($this->fields['required']) > 0)) {
			$params['openid.sreg.required'] = implode(',',$this->fields['required']);
		}
		if (isset($this->fields['optional'])
		  && (count($this->fields['optional']) > 0)) {
			$params['openid.sreg.optional'] = implode(',',$this->fields['optional']);
		}
        if(strstr($this->URLs['openid_server'], "?")){
            $urlJoiner = "&";
        }else{
            $urlJoiner = "?";
        }
		return $this->URLs['openid_server'] . $urlJoiner . $this->array2url($params);
	}
	
	function Redirect(){ 
		$redirect_to = $this->GetRedirectURL();

		if (headers_sent()){ // Use JavaScript to redirect if content has been previously sent (not recommended, but safe)
			echo '<script language="JavaScript" type="text/javascript">window.location=\'';
			echo $redirect_to;
			echo '\';</script>';
		}else{	// Default Header Redirect
			header('Location: ' . $redirect_to);
		}
	}
	
    function ValidateWithServer(){

        $params             = array();
        $arr_underscores    = array('ns_', 'pape_', 'sreg_');
        $arr_periods        = array('ns.', 'pape.', 'sreg.');
        $arr_getSignedKeys  = explode(",",str_replace($arr_periods, $arr_underscores, $_GET['openid_signed']));

        // Send only required parameters to confirm validity
        foreach($arr_getSignedKeys as $key){
            $paramKey = str_replace($arr_underscores, $arr_periods, $key);
            $params["openid.$paramKey"] = urlencode($_GET["openid_$key"]);
        }
        if(empty($this->openid_version) || $this->openid_version != "2.0"){
            $params['openid.assoc_handle'] = urlencode($_GET['openid_assoc_handle']);
            $params['openid.signed'] = urlencode($_GET['openid_signed']);
        }
        $params['openid.sig'] = urlencode($_GET['openid_sig']);
        $params['openid.mode'] = "check_authentication";

        $openid_server = $this->GetOpenIDServer();
        if ($openid_server == false){
            return false;
        }
                
        $response = $this->CURL_Request($openid_server,'POST',$params);
// print "<pre>"; print_r($response); 
        $data = $this->splitResponse($response);
// print "<pre>"; print_r($data); exit;
//        return $data;

        if ($data['is_valid'] == "true") {return true;} else {return false;}
        									
    }
}

?>
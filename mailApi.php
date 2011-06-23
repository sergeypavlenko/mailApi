<?php
class MailApi {
	private $appId = 'appId';
	private $secretKey = 'secretKey';
	private $privateKey = 'privateKey';
	private $redirectUrl = 'http://example.com/';
	
	public $token;
	public $useragent = 'MailOAuth v0.2.0-beta2';
	
	public function setScope() {
		$scope = func_get_args();
		if (!empty($scope)) {
			$this->scope = ('&scope=' . implode($scope, ','));
		}
	}
	
	public function setRedirectUrl($url) {
		$this->redirectUrl = $url;
	}
	
	public function getLink() {
		return ('https://connect.mail.ru/oauth/authorize?client_id=' . $this->appId . '&response_type=code&redirect_uri=' . $this->redirectUrl . (isset($this->scope) ? $this->scope : NULL));
	}
	
	private function _get($url, $fields = NULL) {
		$ci = curl_init();
		
		curl_setopt($ci, CURLOPT_USERAGENT, $this->useragent);
		curl_setopt($ci, CURLOPT_CONNECTTIMEOUT, 30);
		curl_setopt($ci, CURLOPT_TIMEOUT, 30);
		curl_setopt($ci, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($ci, CURLOPT_HTTPHEADER, array('Expect:'));
		curl_setopt($ci, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($ci, CURLOPT_HEADER, FALSE);
		
		curl_setopt($ci, CURLOPT_POST, TRUE);
		if (!empty($fields)) {
			curl_setopt($ci, CURLOPT_POSTFIELDS, $fields);
		}

		curl_setopt($ci, CURLOPT_URL, $url);
		$response = curl_exec($ci);
		curl_close($ci);
		return $response;
	}
	
	public function token($key) {
		$response = $this->_get('https://connect.mail.ru/oauth/token', 'client_id=' . $this->appId . '&grant_type=authorization_code&code=' . $key . '&client_secret=' . $this->secretKey . '&redirect_uri=' . $this->redirectUrl);
		$this->token = json_decode($response);
		return (isset($this->token->access_token) ? TRUE : FALSE);
	}
	
	private function _methodUrl($params = array()) {
		$params['app_id'] = $this->appId;
		$params['secure'] = 0;
		$params['format'] = 'json';
		$params['uid'] = $this->token->x_mailru_vid;
		$params['session_key'] = $this->token->access_token;
		
		$sig = '';
		$url = '';
        $i = 0;
        
        ksort($params);
		
        foreach ($params as $k => $v) {
            ++$i;
            
            $sig .= $k.'='.$v;
            $url .= $k.'='.$v;
            
            if ($i != count($params)) $url .= '&';
        }
        
        $sig = md5($params['uid'] . $sig . $this->privateKey);
        
        $url .= ('&sig=' . $sig);
		return $url;
	}
	
	public function getMethod($name, $params = array()) {
		$params['method'] = $name;
		
		$response = $this->_get('http://www.appsmail.ru/platform/api', $this->_methodUrl($params));
		return json_decode($response);
	}
}
?>
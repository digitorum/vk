<?php
	
	/*
	 * (c) digitorum.ru
	 */
	
	Class Social_APIClient_Vk {
		
		/*
		 * данные для подключения
		 */
		protected $connectionData = array();
		
		/*
		 *  Ссылки для аутентификации
		 */
		protected $authUrl = 'https://oauth.vk.com/authorize';
		
		/*
		 * ссылка для получения токена
		 */
		protected $tokenUrl = 'https://oauth.vk.com/access_token';
		
		/*
		 * Ссылка для отправки запросов 
		 */
		protected $apiUrl = 'https://api.vk.com/method/';
		
		/*
		 * Редирект урд
		 */
		protected $redirectUrl = '';
		
		/*
		 * Токены для доступа
		 */
		private $token = array();
		
		/*
		 * Конструктор
		 */
		public function __construct($connectionData = array()) {
			$this->connectionData = $connectionData;
		}
		
		/*
		 * Установить редирект урл 
		 */
		public function setRedirectUrl($url = '') {
			$this->redirectUrl = $url;
		}
		
		/*
		 * Получить ссылку для подключения
		 */
		public function getLoginUrl($scope = array()) {
			return $this->authUrl . '?'
					. http_build_query(
						array(
							'client_id'     => $this->connectionData['client_id'],
							'response_type' => 'code',
							'redirect_uri'  => $this->redirectUrl,
							'scope' => implode(',', $scope)
						)
					);
		}
		
		/*
		 * Выбросить ошибку
		 */
		public function error($array) {
			throw new Exception($array['error'] . ':' . (isset($array['error_description']) ? $array['error_description'] : ''));
		}
		
		/*
		 * Выставить токен
		 */
		public function setToken($token) {
			if(is_string($token)) {
				$token = json_decode($token, true);
			}
			$this->token = $token;
		} 
		
		/*
		 * Получить строку токена
		 */
		public function getTokenStr() {
			return json_encode($this->token);
		}
		
		/*
		 * Получить токен
		 */
		public function getToken($code = '') {
			if($code) {
				$this->token = $this->sendRequest(
					$this->tokenUrl . '?',
					array(
						'code' => $code,
						'redirect_uri' => $this->redirectUrl,
						'client_id' => $this->connectionData['client_id'],
						'client_secret' => $this->connectionData['client_secret']
					)
				);
			}
		}
		
		/*
		 * Получить аксес токен
		 */
		public function getAccessToken() {
			if(isset($this->token['access_token'])) {
				return $this->token['access_token'];
			}
			return false;
		}
		
		/*
		 * Обратиться к апи 
		 */
		public function api($action = '', $parameters = array(), $method='GET') {
			$accessToken = $this->getAccessToken();
			foreach($parameters as $k => $v) {
				$paramsArray[] = $k . '=' . urlencode($v);
			}
			$paramsArray[] = 'access_token=' . $accessToken;
			return $this->sendRequest(
				$this->apiUrl . $action . '?',
				implode("&", $paramsArray),
				$method
			);
		}
		
		/*
		 * Отправить реквест
		 */
		protected function sendRequest($url = '', $params = array(), $method = 'POST') {
			if(is_array($params)) {
				$params = http_build_query($params);
			}
			$ch = curl_init();
			if($method == 'GET') {
				$url .= $params;
			} else if($method == 'POST') {
				curl_setopt($ch, CURLOPT_POST, 1);
				curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
			}
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
			$result = curl_exec($ch);
			curl_close($ch);
			return json_decode($result, true);
		}
		
	}

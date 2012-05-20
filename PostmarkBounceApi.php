<?php

define('POSTMARK_API_URL', 'http://api.postmarkapp.com');
define('POSTMARK_API_TIMEOUT', 30);

class PostmarkBounceApi {

	private $_postmark_token;

	function __construct($token) {
		$this->_postmark_token = $token;
	}

	private function sendRequest($url, $method = 'GET') {
		$ch = curl_init(POSTMARK_API_URL . $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_TIMEOUT, POSTMARK_API_TIMEOUT);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
			'Content-Type: application/json',
			'Accept: application/json',
			'X-Postmark-Server-Token: ' . $this->_postmark_token
		));
		if ($method == 'PUT') {
			curl_setopt($ch, CURLOPT_PUT, true);
		}
		$resp = curl_exec($ch);
		$curl_errno = curl_errno($ch);
		$curl_error = curl_error($ch);
		curl_close($ch);

		if ($curl_errno > 0) {
			echo "\n" . $curl_error . "\n";
			return false;
		}
		else {
			return json_decode($resp, true);
		}
	}

	private function _get($url, $params = array()) {
		if (!empty($params)) {
			$encodedParams = array();
			foreach ($params as $key => $value) {
				$encodedParams[] = $key . '=' . urlencode($value);
			}
			$url .= '?' . implode('&', $encodedParams);
		}
		return $this->sendRequest($url, 'GET');
	}

	private function _put($url) {
		return $this->sendRequest($url, 'PUT');
	}

	public function getDeliveryStats() {
		return $this->_get('/deliverystats');
	}

	public function getBounces($filters = array()) {
		if (isset($filters['count'])) {
			$filters['count'] = (int)$filters['count'];
		}
		if (!isset($filters['count']) || $filters['count'] === 0) {
			$filters['count'] = 100;
		}

		$offset = 0;
		$total = 1;
		$errors = 0;

		$bounces = array();
		do {
			$filters['offset'] = $offset;
			$page = $this->_get('/bounces', $filters);
			if ($page && isset($page['Bounces']) && isset($page['TotalCount'])) {
				$bounces = array_merge($bounces, $page['Bounces']);
				$total = $page['TotalCount'];
				$offset += $filters['count'];
				$errors = 0;
				$page = null;

				echo '.';
			}
			else {
				if ($errors > 3) {
					echo "\nError retrieving bounces at offset $offset";
					break;
				}
				else {
					sleep(pow(2, $errors));
					$errors++;
				}
			}
		} while ($offset < $total);

		echo "\n";

		return $bounces;
	}

	public function getBounce($id) {
		return $this->_get('/bounces/' . $id);
	}

	public function getBounceDump($id) {
		return $this->_get('/bounces/' . $id . '/dump');
	}

	public function getBounceTags() {
		return $this->_get('/bounces/tags');
	}

	public function activateBounce($id) {
		return $this->_put('/bounces/' . $id . '/activate');
	}

}

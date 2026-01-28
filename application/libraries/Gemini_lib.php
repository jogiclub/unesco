<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Gemini_lib {

	private $CI;
	private $api_key;
	private $api_url = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent';

	public function __construct()
	{
		$this->CI =& get_instance();
		$this->api_key = $this->CI->config->item('gemini_api_key');
	}

	/**
	 * Gemini API로 텍스트 생성 요청
	 * @param string $prompt 프롬프트 텍스트
	 * @return array 결과 배열 [success, data/message]
	 */
	public function generate($prompt)
	{
		if (empty($this->api_key)) {
			return ['success' => FALSE, 'message' => 'Gemini API 키가 설정되지 않았습니다.'];
		}

		$url = $this->api_url . '?key=' . $this->api_key;

		$request_body = [
			'contents' => [
				[
					'parts' => [
						['text' => $prompt]
					]
				]
			],
			'generationConfig' => [
				'temperature' => 0.7,
				'maxOutputTokens' => 2048,
			]
		];

		$ch = curl_init();

		curl_setopt_array($ch, [
			CURLOPT_URL => $url,
			CURLOPT_RETURNTRANSFER => TRUE,
			CURLOPT_POST => TRUE,
			CURLOPT_POSTFIELDS => json_encode($request_body),
			CURLOPT_HTTPHEADER => [
				'Content-Type: application/json'
			],
			CURLOPT_TIMEOUT => 60,
			CURLOPT_SSL_VERIFYPEER => TRUE
		]);

		$response = curl_exec($ch);
		$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		$error = curl_error($ch);

		curl_close($ch);

		if ($error) {
			log_message('error', 'Gemini API cURL Error: ' . $error);
			return ['success' => FALSE, 'message' => 'API 통신 오류: ' . $error];
		}

		if ($http_code !== 200) {
			log_message('error', 'Gemini API HTTP Error: ' . $http_code . ' Response: ' . $response);
			return ['success' => FALSE, 'message' => 'API 오류 (HTTP ' . $http_code . ')'];
		}

		$result = json_decode($response, TRUE);

		if (json_last_error() !== JSON_ERROR_NONE) {
			return ['success' => FALSE, 'message' => 'API 응답 파싱 오류'];
		}

		// 응답에서 텍스트 추출
		if (isset($result['candidates'][0]['content']['parts'][0]['text'])) {
			return [
				'success' => TRUE,
				'data' => $result['candidates'][0]['content']['parts'][0]['text']
			];
		}

		return ['success' => FALSE, 'message' => 'API 응답 형식 오류'];
	}
}

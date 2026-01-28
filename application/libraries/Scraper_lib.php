<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Scraper_lib {

	private $CI;
	private $user_agent = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36';

	public function __construct()
	{
		$this->CI =& get_instance();
	}

	/**
	 * URL에서 콘텐츠 스크래핑
	 * @param string $url 대상 URL
	 * @return array 결과 배열 [success, data/message]
	 */
	public function scrape($url)
	{
		// URL 유효성 검사
		if (!filter_var($url, FILTER_VALIDATE_URL)) {
			return ['success' => FALSE, 'message' => '유효하지 않은 URL입니다.'];
		}

		// HTTP 요청
		$html = $this->_fetch_url($url);

		if ($html === FALSE) {
			return ['success' => FALSE, 'message' => 'URL에 접근할 수 없습니다.'];
		}

		// HTML 파싱
		$parsed = $this->_parse_html($html);

		return [
			'success' => TRUE,
			'data' => $parsed
		];
	}

	/**
	 * URL에서 HTML 가져오기
	 * @param string $url 대상 URL
	 * @return string|false HTML 내용 또는 실패
	 */
	private function _fetch_url($url)
	{
		$ch = curl_init();

		curl_setopt_array($ch, [
			CURLOPT_URL => $url,
			CURLOPT_RETURNTRANSFER => TRUE,
			CURLOPT_FOLLOWLOCATION => TRUE,
			CURLOPT_MAXREDIRS => 5,
			CURLOPT_TIMEOUT => 30,
			CURLOPT_CONNECTTIMEOUT => 10,
			CURLOPT_SSL_VERIFYPEER => TRUE,
			CURLOPT_USERAGENT => $this->user_agent,
			CURLOPT_HTTPHEADER => [
				'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
				'Accept-Language: ko-KR,ko;q=0.9,en-US;q=0.8,en;q=0.7',
			]
		]);

		$response = curl_exec($ch);
		$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		$error = curl_error($ch);

		curl_close($ch);

		if ($error) {
			log_message('error', 'Scraper cURL Error: ' . $error);
			return FALSE;
		}

		if ($http_code !== 200) {
			log_message('error', 'Scraper HTTP Error: ' . $http_code . ' for URL: ' . $url);
			return FALSE;
		}

		return $response;
	}

	/**
	 * HTML 파싱하여 제목과 본문 추출
	 * @param string $html HTML 문자열
	 * @return array 파싱 결과 [title, content, meta]
	 */
	private function _parse_html($html)
	{
		// DOMDocument로 파싱
		$dom = new DOMDocument();
		libxml_use_internal_errors(TRUE);
		@$dom->loadHTML(mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8'));
		libxml_clear_errors();

		$xpath = new DOMXPath($dom);

		// 제목 추출
		$title = $this->_extract_title($xpath, $dom);

		// 메타 설명 추출
		$meta_description = $this->_extract_meta_description($xpath);

		// 본문 콘텐츠 추출
		$content = $this->_extract_content($xpath, $dom);

		return [
			'title' => $title,
			'content' => $content,
			'meta_description' => $meta_description
		];
	}

	/**
	 * 페이지 제목 추출
	 */
	private function _extract_title($xpath, $dom)
	{
		// og:title 먼저 시도
		$og_title = $xpath->query('//meta[@property="og:title"]/@content');
		if ($og_title->length > 0) {
			return trim($og_title->item(0)->nodeValue);
		}

		// title 태그
		$title_tags = $dom->getElementsByTagName('title');
		if ($title_tags->length > 0) {
			return trim($title_tags->item(0)->textContent);
		}

		// h1 태그
		$h1_tags = $dom->getElementsByTagName('h1');
		if ($h1_tags->length > 0) {
			return trim($h1_tags->item(0)->textContent);
		}

		return '';
	}

	/**
	 * 메타 설명 추출
	 */
	private function _extract_meta_description($xpath)
	{
		// og:description
		$og_desc = $xpath->query('//meta[@property="og:description"]/@content');
		if ($og_desc->length > 0) {
			return trim($og_desc->item(0)->nodeValue);
		}

		// meta description
		$meta_desc = $xpath->query('//meta[@name="description"]/@content');
		if ($meta_desc->length > 0) {
			return trim($meta_desc->item(0)->nodeValue);
		}

		return '';
	}

	/**
	 * 본문 콘텐츠 추출
	 */
	private function _extract_content($xpath, $dom)
	{
		// 불필요한 요소 제거
		$remove_tags = ['script', 'style', 'nav', 'header', 'footer', 'aside', 'noscript', 'iframe'];
		foreach ($remove_tags as $tag) {
			$elements = $dom->getElementsByTagName($tag);
			while ($elements->length > 0) {
				$elements->item(0)->parentNode->removeChild($elements->item(0));
			}
		}

		// article, main, .content, #content 등에서 본문 추출 시도
		$content_selectors = [
			'//article',
			'//main',
			'//*[contains(@class, "content")]',
			'//*[contains(@class, "article")]',
			'//*[contains(@class, "post")]',
			'//div[@id="content"]',
			'//div[@id="main"]'
		];

		foreach ($content_selectors as $selector) {
			$nodes = $xpath->query($selector);
			if ($nodes->length > 0) {
				$text = $this->_get_text_content($nodes->item(0));
				if (mb_strlen($text) > 100) {
					return $this->_clean_text($text);
				}
			}
		}

		// body 전체에서 추출
		$body = $dom->getElementsByTagName('body');
		if ($body->length > 0) {
			$text = $this->_get_text_content($body->item(0));
			return $this->_clean_text($text);
		}

		return '';
	}

	/**
	 * 노드에서 텍스트 추출
	 */
	private function _get_text_content($node)
	{
		return $node->textContent;
	}

	/**
	 * 텍스트 정리
	 */
	private function _clean_text($text)
	{
		// 여러 공백을 하나로
		$text = preg_replace('/\s+/', ' ', $text);
		// 앞뒤 공백 제거
		$text = trim($text);
		// 최대 길이 제한 (Gemini 토큰 제한 고려)
		if (mb_strlen($text) > 5000) {
			$text = mb_substr($text, 0, 5000) . '...';
		}
		return $text;
	}
}

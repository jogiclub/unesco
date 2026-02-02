<?php
/**
 * 파일 위치: application/controllers/Contents.php
 * 역할: 컨텐츠 관리 컨트롤러 - 목록 조회, 상세 조회, 등록, 수정, 삭제 처리
 */
defined('BASEPATH') OR exit('No direct script access allowed');

class Contents extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
		$this->load->library('session');
		$this->load->model('Contents_model');
		$this->load->helper('url');

		// 로그인 체크
		if (!$this->session->userdata('logged_in')) {
			if ($this->input->is_ajax_request()) {
				echo json_encode(['success' => FALSE, 'message' => '로그인이 필요합니다.']);
				exit;
			}
			redirect('login');
		}
	}

	/**
	 * 컨텐츠 관리 메인 페이지
	 */
	public function index()
	{
		$this->load->view('contents');
	}

	/**
	 * 컨텐츠 목록 조회 (AJAX - PQGrid용)
	 */
	public function get_list()
	{
		$params = [
			'page'     => $this->input->get('pq_curpage') ?: 1,
			'per_page' => $this->input->get('pq_rpp') ?: 20,
			'sort_by'  => $this->input->get('pq_sort') ?: 'idx',
			'sort_dir' => $this->input->get('pq_order') ?: 'desc',
			'keyword'  => $this->input->get('keyword') ?: ''
		];

		$result = $this->Contents_model->get_list($params);

		echo json_encode([
			'curPage'    => (int)$params['page'],
			'totalRecords' => (int)$result['total'],
			'data'       => $result['list']
		]);
	}

	/**
	 * 컨텐츠 상세 조회 (AJAX)
	 */
	public function get_detail()
	{
		$idx = $this->input->get('idx');

		if (empty($idx)) {
			echo json_encode(['success' => FALSE, 'message' => '잘못된 요청입니다.']);
			return;
		}

		$data = $this->Contents_model->get_detail($idx);

		if ($data) {
			echo json_encode(['success' => TRUE, 'data' => $data]);
		} else {
			echo json_encode(['success' => FALSE, 'message' => '데이터를 찾을 수 없습니다.']);
		}
	}

	/**
	 * 컨텐츠 저장 (AJAX)
	 */
	public function save()
	{
		$idx = $this->input->post('idx');
		$user_email = $this->session->userdata('email');

		$data = [
			'title' => $this->input->post('title'),
			'description' => $this->input->post('description'),
			'category_id' => $this->input->post('category_id') ?: NULL,
			'nation_id' => $this->input->post('nation_id') ?: NULL,
			'modi_date' => date('Y-m-d H:i:s'),
			'modi_id' => $user_email
		];

		// source_urls 처리 (줄바꿈으로 구분된 텍스트를 JSON 배열로 변환)
		$source_urls_text = $this->input->post('source_urls');
		if (!empty($source_urls_text)) {
			$urls = array_filter(array_map('trim', explode("\n", $source_urls_text)));
			$data['source_urls'] = json_encode(array_values($urls), JSON_UNESCAPED_UNICODE);
		} else {
			$data['source_urls'] = NULL;
		}

		if ($idx) {
			// 수정
			$result = $this->Contents_model->update($idx, $data);
			$message = '수정되었습니다.';
		} else {
			// 등록 (수집기에서만 등록되므로 이 경우는 거의 없음)
			$data['regi_date'] = date('Y-m-d H:i:s');
			$data['regi_id'] = $user_email;
			$result = $this->Contents_model->insert($data);
			$message = '등록되었습니다.';
		}

		if ($result) {
			echo json_encode(['success' => TRUE, 'message' => $message]);
		} else {
			echo json_encode(['success' => FALSE, 'message' => '처리 중 오류가 발생했습니다.']);
		}
	}

	/**
	 * 컨텐츠 삭제 (AJAX - 소프트 삭제)
	 */
	public function delete()
	{
		$idx = $this->input->post('idx');

		if (empty($idx)) {
			echo json_encode(['success' => FALSE, 'message' => '잘못된 요청입니다.']);
			return;
		}

		// 배열로 전달된 경우 (다중 삭제)
		if (!is_array($idx)) {
			$idx = [$idx];
		}

		$result = $this->Contents_model->delete($idx);

		if ($result) {
			echo json_encode(['success' => TRUE, 'message' => '삭제되었습니다.']);
		} else {
			echo json_encode(['success' => FALSE, 'message' => '삭제 중 오류가 발생했습니다.']);
		}
	}

	/**
	 * 단일 URL 콘텐츠 수집 (URL에서 데이터 가져오기, Gemini로 정리, DB 저장)
	 * title: 사용자 입력값 그대로 사용
	 * description: AI 분석 결과의 description
	 * contents_json: context 요청사항에 대한 분석 결과
	 */
	public function collect()
	{
		$url = $this->input->post('url');
		$title = $this->input->post('title');
		$context = $this->input->post('context');
		$user_email = $this->session->userdata('email');

		if (empty($url)) {
			echo json_encode(['success' => FALSE, 'message' => 'URL이 필요합니다.']);
			return;
		}

		if (empty($context)) {
			echo json_encode(['success' => FALSE, 'message' => '컨텍스트가 필요합니다.']);
			return;
		}

		// URL 유효성 검사
		if (!filter_var($url, FILTER_VALIDATE_URL)) {
			echo json_encode(['success' => FALSE, 'message' => '유효하지 않은 URL입니다.']);
			return;
		}

		$this->load->library('Scraper_lib');
		$this->load->library('Gemini_lib');

		// 1. URL에서 콘텐츠 스크래핑
		$scraped = $this->scraper_lib->scrape($url);

		if (!$scraped['success']) {
			echo json_encode(['success' => FALSE, 'message' => '콘텐츠 수집 실패: ' . $scraped['message']]);
			return;
		}

		// 2. Gemini로 콘텐츠 정리 요청
		$prompt = "다음 웹페이지 콘텐츠를 분석해주세요.\n\n";
		$prompt .= "URL: {$url}\n";
		$prompt .= "페이지 제목: {$scraped['data']['title']}\n\n";
		$prompt .= "콘텐츠:\n{$scraped['data']['content']}\n\n";
		$prompt .= "=== 요청사항 ===\n";
		$prompt .= "{$context}\n\n";
		$prompt .= "반드시 아래 JSON 형식으로만 응답해주세요:\n";
		$prompt .= "{\n";
		$prompt .= '  "description": "콘텐츠에 대한 간단한 설명 (200자 이내)",' . "\n";
		$prompt .= '  "analysis": { "요청사항에 맞는 키": "값" }' . "\n";
		$prompt .= "}\n";
		$prompt .= "\n주의: analysis 객체 안에 요청사항({$context})에서 요구하는 정보를 키-값 형태로 정리해주세요.";

		$gemini_result = $this->gemini_lib->generate($prompt);

		if (!$gemini_result['success']) {
			echo json_encode(['success' => FALSE, 'message' => 'Gemini 처리 실패: ' . $gemini_result['message']]);
			return;
		}

		// 3. Gemini 응답 파싱
		$parsed = $this->_parse_gemini_response($gemini_result['data']);

		// description 처리
		$description = '';
		if ($parsed && isset($parsed['description'])) {
			$description = $parsed['description'];
		} else {
			$description = mb_substr($scraped['data']['content'], 0, 200);
		}

		// contents_json 구성
		$contents_json = [
			'source_url' => $url,
			'scraped_title' => $scraped['data']['title'],
			'context' => $context,
			'analysis' => $parsed['analysis'] ?? [],
			'collected_at' => date('Y-m-d H:i:s')
		];

		// 4. DB 저장
		$data = [
			'title' => $title ?: $scraped['data']['title'],  // 사용자 입력 제목, 없으면 스크래핑 제목
			'description' => $description,
			'contents_json' => json_encode($contents_json, JSON_UNESCAPED_UNICODE),
			'regi_date' => date('Y-m-d H:i:s'),
			'regi_id' => $user_email,
			'modi_date' => date('Y-m-d H:i:s'),
			'modi_id' => $user_email
		];

		$result = $this->Contents_model->insert($data);

		if ($result) {
			echo json_encode([
				'success' => TRUE,
				'message' => '수집 완료',
				'data' => ['idx' => $result, 'title' => $data['title']]
			]);
		} else {
			echo json_encode(['success' => FALSE, 'message' => 'DB 저장 실패']);
		}
	}



	/**
	 * 수집된 콘텐츠들을 Gemini로 종합 처리 후 1건으로 DB 저장
	 */
	public function process_collected()
	{
		$scraped_data = $this->input->post('scraped_data');
		$title = $this->input->post('title');
		$context = $this->input->post('context');
		$user_email = $this->session->userdata('email');

		if (empty($scraped_data) || !is_array($scraped_data)) {
			echo json_encode(['success' => FALSE, 'message' => '수집된 데이터가 없습니다.']);
			return;
		}

		if (empty($context)) {
			echo json_encode(['success' => FALSE, 'message' => '컨텍스트가 필요합니다.']);
			return;
		}

		$this->load->library('Gemini_lib');

		// URL 목록 추출
		$source_urls = [];
		foreach ($scraped_data as $item) {
			$source_urls[] = $item['url'];
		}

		// Gemini 프롬프트 구성
		$prompt = "다음 여러 웹페이지의 콘텐츠를 분석하고 요청사항에 맞게 정리해주세요.\n\n";
		$prompt .= "=== 요청사항 ===\n";
		$prompt .= "{$context}\n\n";
		$prompt .= "=== 수집된 웹페이지 목록 (" . count($scraped_data) . "건) ===\n\n";

		$sources = [];
		foreach ($scraped_data as $index => $item) {
			$num = $index + 1;
			$sources[] = [
				'url' => $item['url'],
				'title' => $item['title'],
				'meta_description' => $item['meta_description'] ?? ''
			];
			$prompt .= "--- [{$num}] ---\n";
			$prompt .= "URL: {$item['url']}\n";
			$prompt .= "페이지 제목: {$item['title']}\n";
			$prompt .= "콘텐츠: " . mb_substr($item['content'], 0, 2000) . "\n\n";
		}

		$prompt .= "=== 응답 형식 ===\n";
		$prompt .= "위 모든 페이지의 내용을 종합하여 요청사항에 맞게 정리해주세요.\n";
		$prompt .= "반드시 아래 JSON 형식으로만 응답해주세요. 다른 텍스트 없이 JSON만 출력하세요:\n";
		$prompt .= "{\n";
		$prompt .= '  "description": "수집된 콘텐츠의 소개 글(200자 이내)",' . "\n";
		$prompt .= '  "analysis": {' . "\n";
		$prompt .= '    "요청사항에 맞는 키": "요청사항에 맞는 값"' . "\n";
		$prompt .= '  }' . "\n";
		$prompt .= "}\n";
		$prompt .= "\n주의: analysis 객체 안에 요청사항({$context})에서 요구하는 정보를 키-값 형태로 정리해주세요.";

		$gemini_result = $this->gemini_lib->generate($prompt);

		if (!$gemini_result['success']) {
			echo json_encode(['success' => FALSE, 'message' => 'Gemini 처리 실패: ' . $gemini_result['message']]);
			return;
		}

		// Gemini 응답 파싱
		$parsed = $this->_parse_gemini_response($gemini_result['data']);

		// description 처리
		$description = '';
		if ($parsed && isset($parsed['description'])) {
			$description = $parsed['description'];
		} else {
			$description = '총 ' . count($scraped_data) . '개 URL에서 수집된 콘텐츠입니다.';
		}

		// contents_json 구성
		$contents_json = [
			'sources' => $sources,
			'source_count' => count($scraped_data),
			'context' => $context,
			'analysis' => $parsed['analysis'] ?? [],
			'collected_at' => date('Y-m-d H:i:s')
		];

		// DB 저장
		$data = [
			'title' => $title ?: '수집된 콘텐츠',
			'description' => $description,
			'contents_json' => json_encode($contents_json, JSON_UNESCAPED_UNICODE),
			'source_urls' => json_encode($source_urls, JSON_UNESCAPED_UNICODE),
			'regi_date' => date('Y-m-d H:i:s'),
			'regi_id' => $user_email,
			'modi_date' => date('Y-m-d H:i:s'),
			'modi_id' => $user_email
		];

		$result = $this->Contents_model->insert($data);

		if ($result) {
			echo json_encode([
				'success' => TRUE,
				'message' => count($scraped_data) . '개 URL을 종합하여 1건으로 저장했습니다.',
				'data' => [
					'idx' => $result,
					'title' => $data['title'],
					'source_count' => count($scraped_data)
				]
			]);
		} else {
			echo json_encode(['success' => FALSE, 'message' => 'DB 저장 실패']);
		}
	}





	/**
	 * Gemini 응답 JSON 파싱
	 * @param string $response Gemini 응답 텍스트
	 * @return array|null 파싱된 데이터 또는 null
	 */
	private function _parse_gemini_response($response)
	{
		// JSON 블록 추출 시도
		if (preg_match('/\{[\s\S]*\}/m', $response, $matches)) {
			$json_str = $matches[0];
			$parsed = json_decode($json_str, TRUE);
			if (json_last_error() === JSON_ERROR_NONE) {
				return $parsed;
			}
		}
		return null;
	}



	/**
	 * 파일 위치: application/controllers/Contents.php
	 * 역할: 콘텐츠 수집 처리 - 스크래핑/Gemini 분리, 배치 처리
	 */

	/**
	 * URL 스크래핑만 수행 (Gemini 호출 없음)
	 */
	public function scrape_url()
	{
		$url = $this->input->post('url');

		if (empty($url)) {
			echo json_encode(['success' => FALSE, 'message' => 'URL이 필요합니다.']);
			return;
		}

		if (!filter_var($url, FILTER_VALIDATE_URL)) {
			echo json_encode(['success' => FALSE, 'message' => '유효하지 않은 URL입니다.']);
			return;
		}

		$this->load->library('Scraper_lib');

		$scraped = $this->scraper_lib->scrape($url);

		if (!$scraped['success']) {
			echo json_encode(['success' => FALSE, 'message' => '콘텐츠 수집 실패: ' . $scraped['message']]);
			return;
		}

		echo json_encode([
			'success' => TRUE,
			'data' => [
				'url' => $url,
				'title' => $scraped['data']['title'],
				'content' => $scraped['data']['content'],
				'meta_description' => $scraped['data']['meta_description']
			]
		]);
	}



	/**
	 * Gemini 응답 JSON 배열 파싱
	 * @param string $response Gemini 응답 텍스트
	 * @return array 파싱된 배열 또는 빈 배열
	 */
	private function _parse_gemini_array_response($response)
	{
		// JSON 배열 블록 추출 시도
		if (preg_match('/\[[\s\S]*\]/m', $response, $matches)) {
			$json_str = $matches[0];
			$parsed = json_decode($json_str, TRUE);
			if (json_last_error() === JSON_ERROR_NONE && is_array($parsed)) {
				return $parsed;
			}
		}
		return [];
	}

	/**
	 * 마지막 컨텍스트 조회 (AJAX)
	 */
	public function get_last_context()
	{
		$this->load->model('Context_model');

		$context = $this->Context_model->get_last();

		if ($context) {
			echo json_encode([
				'success' => TRUE,
				'data' => [
					'idx' => $context['idx'],
					'title' => $context['title'],
					'context_json' => $context['context_json']
				]
			]);
		} else {
			echo json_encode([
				'success' => FALSE,
				'message' => '저장된 컨텍스트가 없습니다.'
			]);
		}
	}


	/**
	 * 콘텐츠 재수집 (기존 데이터 업데이트)
	 */
	public function recollect()
	{
		$idx = $this->input->post('idx');
		$user_email = $this->session->userdata('email');

		if (empty($idx)) {
			echo json_encode(['success' => FALSE, 'message' => '잘못된 요청입니다.']);
			return;
		}

		// 기존 데이터 조회
		$content = $this->Contents_model->get_detail($idx);
		if (!$content) {
			echo json_encode(['success' => FALSE, 'message' => '콘텐츠를 찾을 수 없습니다.']);
			return;
		}

		// source_urls 파싱
		$source_urls = [];
		if (!empty($content['source_urls'])) {
			$source_urls = json_decode($content['source_urls'], TRUE);
		}

		if (empty($source_urls)) {
			echo json_encode(['success' => FALSE, 'message' => '수집할 URL이 없습니다.']);
			return;
		}

		// contents_json에서 context 추출
		$context = '';
		if (!empty($content['contents_json'])) {
			$contents_json = json_decode($content['contents_json'], TRUE);
			$context = $contents_json['context'] ?? '';
		}

		if (empty($context)) {
			// context가 없으면 기본 컨텍스트 사용
			$this->load->model('Context_model');
			$last_context = $this->Context_model->get_last();
			$context = $last_context ? $last_context['context_json'] : '해당 페이지의 주요 내용을 정리해주세요.';
		}

		$this->load->library('Scraper_lib');
		$this->load->library('Gemini_lib');

		// URL 스크래핑
		$scraped_data = [];
		foreach ($source_urls as $url) {
			$scraped = $this->scraper_lib->scrape($url);
			if ($scraped['success']) {
				$scraped_data[] = [
					'url' => $url,
					'title' => $scraped['data']['title'],
					'content' => $scraped['data']['content'],
					'meta_description' => $scraped['data']['meta_description'] ?? ''
				];
			}
		}

		if (empty($scraped_data)) {
			echo json_encode(['success' => FALSE, 'message' => 'URL 스크래핑에 모두 실패했습니다.']);
			return;
		}

		// Gemini 프롬프트 구성
		$prompt = "다음 여러 웹페이지의 콘텐츠를 분석하고 요청사항에 맞게 정리해주세요.\n\n";
		$prompt .= "=== 요청사항 ===\n";
		$prompt .= "{$context}\n\n";
		$prompt .= "=== 수집된 웹페이지 목록 (" . count($scraped_data) . "건) ===\n\n";

		$sources = [];
		foreach ($scraped_data as $index => $item) {
			$num = $index + 1;
			$sources[] = [
				'url' => $item['url'],
				'title' => $item['title'],
				'meta_description' => $item['meta_description']
			];
			$prompt .= "--- [{$num}] ---\n";
			$prompt .= "URL: {$item['url']}\n";
			$prompt .= "페이지 제목: {$item['title']}\n";
			$prompt .= "콘텐츠: " . mb_substr($item['content'], 0, 2000) . "\n\n";
		}

		$prompt .= "=== 응답 형식 ===\n";
		$prompt .= "반드시 아래 JSON 형식으로만 응답해주세요:\n";
		$prompt .= "{\n";
		$prompt .= '  "description": "수집된 콘텐츠에 대한 종합 설명 (300자 이내)",' . "\n";
		$prompt .= '  "analysis": { "요청사항에 맞는 키": "값" }' . "\n";
		$prompt .= "}\n";

		$gemini_result = $this->gemini_lib->generate($prompt);

		if (!$gemini_result['success']) {
			echo json_encode(['success' => FALSE, 'message' => 'Gemini 처리 실패: ' . $gemini_result['message']]);
			return;
		}

		// Gemini 응답 파싱
		$parsed = $this->_parse_gemini_response($gemini_result['data']);

		$description = '';
		if ($parsed && isset($parsed['description'])) {
			$description = $parsed['description'];
		} else {
			$description = '총 ' . count($scraped_data) . '개 URL에서 수집된 콘텐츠입니다.';
		}

		// contents_json 구성
		$new_contents_json = [
			'sources' => $sources,
			'source_count' => count($scraped_data),
			'context' => $context,
			'analysis' => $parsed['analysis'] ?? [],
			'collected_at' => date('Y-m-d H:i:s')
		];

		// DB 업데이트
		$data = [
			'description' => $description,
			'contents_json' => json_encode($new_contents_json, JSON_UNESCAPED_UNICODE),
			'modi_date' => date('Y-m-d H:i:s'),
			'modi_id' => $user_email
		];

		$result = $this->Contents_model->update($idx, $data);

		if ($result) {
			echo json_encode([
				'success' => TRUE,
				'message' => count($scraped_data) . '개 URL을 재수집하여 업데이트했습니다.',
				'data' => [
					'idx' => $idx,
					'description' => $description,
					'contents_json' => $new_contents_json
				]
			]);
		} else {
			echo json_encode(['success' => FALSE, 'message' => 'DB 업데이트 실패']);
		}
	}

}

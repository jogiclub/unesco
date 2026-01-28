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
	 * 컨텐츠 저장 (등록/수정 - AJAX)
	 */
	public function save()
	{
		$idx = $this->input->post('idx');
		$user_email = $this->session->userdata('email');

		$data = [
			'title'       => $this->input->post('title'),
			'description' => $this->input->post('description'),
			'category_id' => $this->input->post('category_id') ?: NULL,
			'nation_id'   => $this->input->post('nation_id') ?: NULL
		];

		// 유효성 검사
		if (empty($data['title'])) {
			echo json_encode(['success' => FALSE, 'message' => '타이틀을 입력해주세요.']);
			return;
		}

		if ($idx) {
			// 수정
			$data['modi_date'] = date('Y-m-d H:i:s');
			$data['modi_id'] = $user_email;
			$result = $this->Contents_model->update($idx, $data);
			$message = '수정되었습니다.';
		} else {
			// 등록
			$data['regi_date'] = date('Y-m-d H:i:s');
			$data['regi_id'] = $user_email;
			$data['modi_date'] = date('Y-m-d H:i:s');
			$data['modi_id'] = $user_email;
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
	 * 파일 위치: application/controllers/Contents.php
	 * 역할: 콘텐츠 수집 처리 (URL에서 데이터 가져오기, Gemini로 정리, DB 저장)
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
		$prompt .= "요청사항: {$context}\n\n";
		$prompt .= "반드시 아래 JSON 형식으로만 응답해주세요:\n";
		$prompt .= "{\n";
		$prompt .= '  "title": "정리된 제목",' . "\n";
		$prompt .= '  "description": "간단한 설명 (200자 이내)",' . "\n";
		$prompt .= '  "details": { "추가 정보 키": "값" }' . "\n";
		$prompt .= "}";

		$gemini_result = $this->gemini_lib->generate($prompt);

		if (!$gemini_result['success']) {
			echo json_encode(['success' => FALSE, 'message' => 'Gemini 처리 실패: ' . $gemini_result['message']]);
			return;
		}

		// 3. Gemini 응답 파싱
		$parsed = $this->_parse_gemini_response($gemini_result['data']);

		if (!$parsed) {
			// 파싱 실패 시 기본값 사용
			$parsed = [
				'title' => $title ?: $scraped['data']['title'],
				'description' => mb_substr($scraped['data']['content'], 0, 200),
				'details' => ['source_url' => $url, 'original_title' => $scraped['data']['title']]
			];
		}

		// 4. DB 저장
		$data = [
			'title' => $parsed['title'] ?: ($title ?: $scraped['data']['title']),
			'description' => $parsed['description'] ?: '',
			'contents_json' => json_encode([
				'source_url' => $url,
				'scraped_title' => $scraped['data']['title'],
				'details' => $parsed['details'] ?? [],
				'collected_at' => date('Y-m-d H:i:s')
			], JSON_UNESCAPED_UNICODE),
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
	 * 파일 위치: application/controllers/Contents.php
	 * 역할: 수집된 콘텐츠들을 Gemini로 종합 처리 후 1건으로 DB 저장
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

		// Gemini 프롬프트 구성 (모든 URL 데이터를 종합)
		$prompt = "다음 여러 웹페이지의 콘텐츠를 분석하고 하나의 종합된 정보로 정리해주세요.\n\n";
		$prompt .= "요청사항: {$context}\n\n";
		$prompt .= "=== 수집된 웹페이지 목록 (" . count($scraped_data) . "건) ===\n\n";

		$source_urls = [];
		foreach ($scraped_data as $index => $item) {
			$num = $index + 1;
			$source_urls[] = $item['url'];
			$prompt .= "--- [{$num}] ---\n";
			$prompt .= "URL: {$item['url']}\n";
			$prompt .= "페이지 제목: {$item['title']}\n";
			$prompt .= "콘텐츠: " . mb_substr($item['content'], 0, 2000) . "\n\n";
		}

		$prompt .= "=== 응답 형식 ===\n";
		$prompt .= "위 모든 페이지의 내용을 종합하여 하나의 결과물로 정리해주세요.\n";
		$prompt .= "반드시 아래 JSON 형식으로만 응답해주세요. 다른 텍스트 없이 JSON만 출력하세요:\n";
		$prompt .= "{\n";
		$prompt .= '  "title": "종합된 제목",' . "\n";
		$prompt .= '  "description": "종합된 설명 (300자 이내)",' . "\n";
		$prompt .= '  "details": {' . "\n";
		$prompt .= '    "summary": "전체 내용 요약",' . "\n";
		$prompt .= '    "keywords": ["키워드1", "키워드2", "키워드3"],' . "\n";
		$prompt .= '    "key_points": ["핵심내용1", "핵심내용2"]' . "\n";
		$prompt .= '  }' . "\n";
		$prompt .= "}\n";

		$gemini_result = $this->gemini_lib->generate($prompt);

		if (!$gemini_result['success']) {
			echo json_encode(['success' => FALSE, 'message' => 'Gemini 처리 실패: ' . $gemini_result['message']]);
			return;
		}

		// Gemini 응답 파싱
		$parsed = $this->_parse_gemini_response($gemini_result['data']);

		if (!$parsed) {
			// 파싱 실패 시 기본값 사용
			$parsed = [
				'title' => $title ?: '수집된 콘텐츠',
				'description' => '총 ' . count($scraped_data) . '개 URL에서 수집된 콘텐츠입니다.',
				'details' => []
			];
		}

		// 원본 스크래핑 데이터 정리
		$sources = [];
		foreach ($scraped_data as $item) {
			$sources[] = [
				'url' => $item['url'],
				'title' => $item['title'],
				'meta_description' => $item['meta_description'] ?? ''
			];
		}

		// DB 저장 (1건으로 종합)
		$data = [
			'title' => $title ?: ($parsed['title'] ?? '수집된 콘텐츠'),
			'description' => $parsed['description'] ?? '',
			'contents_json' => json_encode([
				'sources' => $sources,
				'source_count' => count($scraped_data),
				'details' => $parsed['details'] ?? [],
				'collected_at' => date('Y-m-d H:i:s')
			], JSON_UNESCAPED_UNICODE),
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

}

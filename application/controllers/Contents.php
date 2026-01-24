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
}

<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Context extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
		$this->load->library('session');
		$this->load->model('Context_model');
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
	 * AI 컨텍스트 설정 메인 페이지
	 */
	public function index()
	{
		$this->load->view('context');
	}

	/**
	 * 컨텍스트 조회 (AJAX)
	 */
	public function get_data()
	{
		$data = $this->Context_model->get_data();

		if ($data) {
			echo json_encode(['success' => TRUE, 'data' => $data]);
		} else {
			echo json_encode(['success' => TRUE, 'data' => NULL]);
		}
	}

	/**
	 * 컨텍스트 저장 (AJAX)
	 */
	public function save()
	{
		$user_email = $this->session->userdata('email');

		$title = $this->input->post('title');
		$context_json = $this->input->post('context_json');

		// 유효성 검사
		if (empty($title)) {
			echo json_encode(['success' => FALSE, 'message' => '타이틀을 입력해주세요.']);
			return;
		}

		$data = [
			'title'        => $title,
			'context_json' => $context_json,
			'modi_date'    => date('Y-m-d H:i:s'),
			'modi_id'      => $user_email
		];

		// 기존 데이터 확인
		$existing = $this->Context_model->get_data();

		if ($existing) {
			// 수정
			$result = $this->Context_model->update($existing->idx, $data);
		} else {
			// 신규 등록
			$data['regi_date'] = date('Y-m-d H:i:s');
			$data['regi_id'] = $user_email;
			$result = $this->Context_model->insert($data);
		}

		if ($result) {
			echo json_encode(['success' => TRUE, 'message' => '저장되었습니다.']);
		} else {
			echo json_encode(['success' => FALSE, 'message' => '저장 중 오류가 발생했습니다.']);
		}
	}
}

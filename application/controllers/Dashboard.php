<?php
/**
 * 파일 위치: application/controllers/Dashboard.php
 * 역할: 대시보드 화면 표시 및 데이터 처리
 */
defined('BASEPATH') OR exit('No direct script access allowed');

class Dashboard extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
		$this->load->library('session');
		$this->load->helper('url');

		// 로그인 체크
		if (!$this->session->userdata('logged_in')) {
			redirect('login');
		}
	}

	/**
	 * 대시보드 메인 페이지
	 */
	public function index()
	{
		$data['user'] = [
			'name' => $this->session->userdata('name'),
			'email' => $this->session->userdata('email'),
			'profile_image' => $this->session->userdata('profile_image')
		];

		$this->load->view('dashboard', $data);
	}

	/**
	 * 대시보드 데이터 조회 (AJAX)
	 */
	public function get_data()
	{
		$response = [
			'success' => TRUE,
			'data' => [
				'user' => [
					'name' => $this->session->userdata('name'),
					'email' => $this->session->userdata('email'),
					'profile_image' => $this->session->userdata('profile_image')
				]
			]
		];

		$this->output
			->set_content_type('application/json')
			->set_output(json_encode($response));
	}
}

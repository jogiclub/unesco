<?php
/**
 * 파일 위치: application/controllers/Login.php
 * 역할: 구글 OAuth 로그인 처리 및 세션 관리
 */
defined('BASEPATH') OR exit('No direct script access allowed');

class Login extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
		$this->load->library('session');
		$this->load->model('User_model');
		$this->load->helper('url');
		$this->load->config('google');
	}

	/**
	 * 로그인 페이지 표시
	 */
	public function index()
	{
		// 이미 로그인된 경우 대시보드로 이동
		if ($this->session->userdata('logged_in')) {
			redirect('dashboard');
		}
		$this->load->view('login');
	}

	/**
	 * 구글 로그인 시작 - 구글 인증 페이지로 리다이렉트
	 */
	public function google_login()
	{
		$client_id = $this->config->item('google_client_id');
		$redirect_uri = $this->config->item('google_redirect_uri');
		$scope = 'email profile';

		$auth_url = 'https://accounts.google.com/o/oauth2/v2/auth?' . http_build_query([
				'client_id' => $client_id,
				'redirect_uri' => $redirect_uri,
				'response_type' => 'code',
				'scope' => $scope,
				'access_type' => 'offline',
				'prompt' => 'consent'
			]);

		redirect($auth_url);
	}

	/**
	 * 구글 OAuth 콜백 처리
	 */
	public function google_callback()
	{
		$code = $this->input->get('code');

		if (empty($code)) {
			$this->session->set_flashdata('error', '구글 인증에 실패했습니다.');
			redirect('login');
			return;
		}

		// Access Token 요청
		$token_data = $this->_get_google_token($code);

		if (!$token_data || !isset($token_data['access_token'])) {
			$this->session->set_flashdata('error', '토큰 발급에 실패했습니다.');
			redirect('login');
			return;
		}

		// 사용자 정보 요청
		$user_info = $this->_get_google_user_info($token_data['access_token']);

		if (!$user_info || !isset($user_info['email'])) {
			$this->session->set_flashdata('error', '사용자 정보를 가져올 수 없습니다.');
			redirect('login');
			return;
		}

		// 사용자 등록 또는 로그인 처리
		$user = $this->User_model->find_or_create_google_user($user_info);

		if ($user) {
			// 세션 설정
			$session_data = [
				'user_id' => $user['id'],
				'email' => $user['email'],
				'name' => $user['name'],
				'profile_image' => $user['profile_image'],
				'logged_in' => TRUE
			];
			$this->session->set_userdata($session_data);

			redirect('dashboard');
		} else {
			$this->session->set_flashdata('error', '로그인 처리 중 오류가 발생했습니다.');
			redirect('login');
		}
	}

	/**
	 * 로그아웃 처리
	 */
	public function logout()
	{
		$this->session->sess_destroy();
		$this->output->set_header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
		$this->output->set_header('Pragma: no-cache');
		$this->input->set_cookie('ci_session', '', time() - 3600);
		$this->input->set_cookie('activeOrg', '', time() - 3600);
		redirect('login');
	}

	/**
	 * 구글 Access Token 요청
	 * @param string $code 인증 코드
	 * @return array|null 토큰 데이터
	 */
	private function _get_google_token($code)
	{
		$url = 'https://oauth2.googleapis.com/token';

		$post_data = [
			'code' => $code,
			'client_id' => $this->config->item('google_client_id'),
			'client_secret' => $this->config->item('google_client_secret'),
			'redirect_uri' => $this->config->item('google_redirect_uri'),
			'grant_type' => 'authorization_code'
		];

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_POST, TRUE);
		curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post_data));
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, TRUE);

		$response = curl_exec($ch);
		$error = curl_error($ch);
		curl_close($ch);

		if ($error) {
			log_message('error', 'Google Token Request Error: ' . $error);
			return null;
		}

		return json_decode($response, TRUE);
	}

	/**
	 * 구글 사용자 정보 요청
	 * @param string $access_token 액세스 토큰
	 * @return array|null 사용자 정보
	 */
	private function _get_google_user_info($access_token)
	{
		$url = 'https://www.googleapis.com/oauth2/v2/userinfo';

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_HTTPHEADER, [
			'Authorization: Bearer ' . $access_token
		]);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, TRUE);

		$response = curl_exec($ch);
		$error = curl_error($ch);
		curl_close($ch);

		if ($error) {
			log_message('error', 'Google User Info Request Error: ' . $error);
			return null;
		}

		return json_decode($response, TRUE);
	}
}

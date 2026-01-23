<?php
/**
 * 파일 위치: application/models/User_model.php
 * 역할: 사용자 데이터베이스 처리 (조회, 생성, 수정)
 */
defined('BASEPATH') OR exit('No direct script access allowed');

class User_model extends CI_Model {

	private $table = 'users';

	public function __construct()
	{
		parent::__construct();
		$this->load->database();
	}

	/**
	 * 이메일로 사용자 조회
	 * @param string $email 이메일
	 * @return array|null 사용자 정보
	 */
	public function get_by_email($email)
	{
		$query = $this->db->get_where($this->table, ['email' => $email]);
		return $query->row_array();
	}

	/**
	 * Google ID로 사용자 조회
	 * @param string $google_id 구글 ID
	 * @return array|null 사용자 정보
	 */
	public function get_by_google_id($google_id)
	{
		$query = $this->db->get_where($this->table, ['google_id' => $google_id]);
		return $query->row_array();
	}

	/**
	 * 구글 사용자 찾기 또는 생성
	 * @param array $user_info 구글에서 받은 사용자 정보
	 * @return array|null 사용자 정보
	 */
	public function find_or_create_google_user($user_info)
	{
		// 구글 ID로 먼저 검색
		$user = $this->get_by_google_id($user_info['id']);

		if ($user) {
			// 기존 사용자 정보 업데이트
			$this->update($user['id'], [
				'name' => $user_info['name'],
				'profile_image' => isset($user_info['picture']) ? $user_info['picture'] : null,
				'updated_at' => date('Y-m-d H:i:s')
			]);
			return $this->get($user['id']);
		}

		// 이메일로 검색 (다른 방법으로 가입한 경우)
		$user = $this->get_by_email($user_info['email']);

		if ($user) {
			// 구글 ID 연결
			$this->update($user['id'], [
				'google_id' => $user_info['id'],
				'profile_image' => isset($user_info['picture']) ? $user_info['picture'] : $user['profile_image'],
				'updated_at' => date('Y-m-d H:i:s')
			]);
			return $this->get($user['id']);
		}

		// 신규 사용자 생성
		$new_user_data = [
			'google_id' => $user_info['id'],
			'email' => $user_info['email'],
			'name' => $user_info['name'],
			'profile_image' => isset($user_info['picture']) ? $user_info['picture'] : null,
			'created_at' => date('Y-m-d H:i:s'),
			'updated_at' => date('Y-m-d H:i:s')
		];

		$user_id = $this->create($new_user_data);

		if ($user_id) {
			return $this->get($user_id);
		}

		return null;
	}

	/**
	 * 사용자 ID로 조회
	 * @param int $id 사용자 ID
	 * @return array|null 사용자 정보
	 */
	public function get($id)
	{
		$query = $this->db->get_where($this->table, ['id' => $id]);
		return $query->row_array();
	}

	/**
	 * 사용자 생성
	 * @param array $data 사용자 데이터
	 * @return int|false 생성된 사용자 ID
	 */
	public function create($data)
	{
		$result = $this->db->insert($this->table, $data);
		if ($result) {
			return $this->db->insert_id();
		}
		return false;
	}

	/**
	 * 사용자 정보 수정
	 * @param int $id 사용자 ID
	 * @param array $data 수정할 데이터
	 * @return bool 성공 여부
	 */
	public function update($id, $data)
	{
		$this->db->where('id', $id);
		return $this->db->update($this->table, $data);
	}

	/**
	 * 사용자 삭제
	 * @param int $id 사용자 ID
	 * @return bool 성공 여부
	 */
	public function delete($id)
	{
		$this->db->where('id', $id);
		return $this->db->delete($this->table);
	}
}

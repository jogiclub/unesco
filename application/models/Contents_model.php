<?php
/**
 * 파일 위치: application/models/Contents_model.php
 * 역할: 컨텐츠(wb_contents) 테이블 데이터 처리 - CRUD 및 목록 조회
 */
defined('BASEPATH') OR exit('No direct script access allowed');

class Contents_model extends CI_Model {

	private $table = 'wb_contents';

	public function __construct()
	{
		parent::__construct();
		$this->load->database();
	}

	/**
	 * 파일 위치: application/models/Contents_model.php
	 * 역할: 컨텐츠 목록 조회 - 테이블 중복 참조 오류 수정
	 */
	public function get_list($params = [])
	{
		$page = isset($params['page']) ? (int)$params['page'] : 1;
		$per_page = isset($params['per_page']) ? (int)$params['per_page'] : 20;
		$sort_by = isset($params['sort_by']) ? $params['sort_by'] : 'idx';
		$sort_dir = isset($params['sort_dir']) ? $params['sort_dir'] : 'desc';
		$keyword = isset($params['keyword']) ? $params['keyword'] : '';

		$offset = ($page - 1) * $per_page;

		// 정렬 컬럼 화이트리스트
		$allowed_sort = ['idx', 'title', 'description', 'category_id', 'regi_date', 'regi_id', 'modi_date', 'modi_id'];
		if (!in_array($sort_by, $allowed_sort)) {
			$sort_by = 'idx';
		}
		$sort_dir = strtolower($sort_dir) === 'asc' ? 'ASC' : 'DESC';

		// 전체 건수 조회
		$this->db->where('del_yn', 0);
		if (!empty($keyword)) {
			$this->db->group_start();
			$this->db->like('title', $keyword);
			$this->db->or_like('description', $keyword);
			$this->db->or_like('regi_id', $keyword);
			$this->db->group_end();
		}
		$total = $this->db->count_all_results($this->table);

		// 목록 조회 (별도 쿼리)
		$this->db->select('idx, title, description, category_id, nation_id, regi_date, regi_id, modi_date, modi_id');
		$this->db->from($this->table);
		$this->db->where('del_yn', 0);
		if (!empty($keyword)) {
			$this->db->group_start();
			$this->db->like('title', $keyword);
			$this->db->or_like('description', $keyword);
			$this->db->or_like('regi_id', $keyword);
			$this->db->group_end();
		}
		$this->db->order_by($sort_by, $sort_dir);
		$this->db->limit($per_page, $offset);
		$list = $this->db->get()->result_array();

		return [
			'list'  => $list,
			'total' => $total
		];
	}

	/**
	 * 컨텐츠 상세 조회
	 * @param int $idx 컨텐츠 IDX
	 * @return array|null 컨텐츠 정보
	 */
	public function get_detail($idx)
	{
		$this->db->where('idx', $idx);
		$this->db->where('del_yn', 0);
		return $this->db->get($this->table)->row_array();
	}

	/**
	 * 컨텐츠 등록
	 * @param array $data 등록 데이터
	 * @return int|false 등록된 IDX 또는 실패시 false
	 */
	public function insert($data)
	{
		$result = $this->db->insert($this->table, $data);
		return $result ? $this->db->insert_id() : FALSE;
	}

	/**
	 * 컨텐츠 수정
	 * @param int $idx 컨텐츠 IDX
	 * @param array $data 수정 데이터
	 * @return bool 성공 여부
	 */
	public function update($idx, $data)
	{
		$this->db->where('idx', $idx);
		return $this->db->update($this->table, $data);
	}

	/**
	 * 컨텐츠 삭제 (소프트 삭제)
	 * @param array $idx_list 삭제할 IDX 배열
	 * @return bool 성공 여부
	 */
	public function delete($idx_list)
	{
		$this->db->where_in('idx', $idx_list);
		return $this->db->update($this->table, ['del_yn' => 1]);
	}
}


<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Context_model extends CI_Model {

	private $table = 'wb_context';

	public function __construct()
	{
		parent::__construct();
		$this->load->database();
	}

	/**
	 * 컨텍스트 데이터 조회 (단일 레코드)
	 */
	public function get_data()
	{
		$this->db->where('del_yn', 0);
		$this->db->order_by('idx', 'DESC');
		$this->db->limit(1);
		$query = $this->db->get($this->table);

		return $query->row();
	}

	/**
	 * 컨텍스트 등록
	 */
	public function insert($data)
	{
		return $this->db->insert($this->table, $data);
	}

	/**
	 * 컨텍스트 수정
	 */
	public function update($idx, $data)
	{
		$this->db->where('idx', $idx);
		return $this->db->update($this->table, $data);
	}
}

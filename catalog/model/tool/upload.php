<?php
class ModelToolUpload extends Model {
	public function addUpload($name, $filename, $origin_name) {
		$code = sha1(uniqid(mt_rand(), true));

		$this->db->query("INSERT INTO `" . DB_PREFIX . "upload` SET `name` = '" . $this->db->escape($name) . "', `origin_name` = '" . $this->db->escape($origin_name) . "', `filename` = '" . $this->db->escape($filename) . "', `code` = '" . $this->db->escape($code) . "', `date_added` = NOW()");

		return $code;
	}

	public function getUploadByCode($code) {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "upload` WHERE code = '" . $this->db->escape($code) . "'");

		return $query->row;
	}

	public function getProductFiles($product_id){
		$query = $this->db->query("SELECT d.download_id, dd.name, d.filename, d.mask FROM " . DB_PREFIX . "product_to_download ptd LEFT JOIN " . DB_PREFIX . "download d ON (ptd.download_id = d.download_id) LEFT JOIN " . DB_PREFIX . "download_description dd ON (d.download_id = dd.download_id) WHERE ptd.product_id = ".(int)$product_id);

		return $query->rows;
	}

	public function getDownloadByCode($code){
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "download WHERE filename = '".$this->db->escape($code)."'");

		return $query->row;
	}
}
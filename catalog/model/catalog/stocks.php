<?php
class ModelCatalogStocks extends Model {	
	public function getStocks($posts_id) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "stocks n LEFT JOIN " . DB_PREFIX . "stocks_description nd ON n.stocks_id = nd.stocks_id WHERE n.stocks_id = '" . (int)$posts_id . "' AND nd.language_id = '" . (int)$this->config->get('config_language_id') . "'");
		
		return $query->row;
	}
 
	public function getAllStocks($data = []) {
		$sql = "SELECT * FROM " . DB_PREFIX . "stocks n LEFT JOIN " . DB_PREFIX . "stocks_description nd ON n.stocks_id = nd.stocks_id WHERE n.status = 1 ORDER BY date_added DESC";
		
		$query = $this->db->query($sql);
		
		return $query->rows;
	}
	
	public function getTotalStocks() {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "stocks");
	
		return $query->row['total'];
	}
}
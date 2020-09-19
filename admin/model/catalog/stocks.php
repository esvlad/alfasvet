<?php
class ModelCatalogStocks extends Model {
	public function addStocks($data) {
		$this->db->query("INSERT INTO " . DB_PREFIX . "stocks SET image = '" . $this->db->escape($data['image']) . "', date_added = NOW(), status = '" . (int)$data['status'] . "'");
		
		$stocks_id = $this->db->getLastId();
		
		foreach ($data['stocks_description'] as $key => $value) {
			$this->db->query("INSERT INTO " . DB_PREFIX ."stocks_description SET stocks_id = '" . (int)$stocks_id . "', language_id = '" . (int)$key . "', title = '" . $this->db->escape($value['title']) . "', description = '" . $this->db->escape($value['description']) . "', html = '" . $this->db->escape($value['html']) . "', short_description = '" . $this->db->escape($value['short_description']) . "', meta_title = '" . $this->db->escape($value['meta_title']) . "', meta_description = '" . $this->db->escape($value['meta_description']) . "', meta_keywords = '" . $this->db->escape($value['meta_keyword']) . "'");
		}

		$this->setSeoUrl($stocks_id, $value['title'], $data['stocks_seo_url']);
	}
	
	public function editStocks($stocks_id, $data) {
		$this->db->query("UPDATE " . DB_PREFIX . "stocks SET image = '" . $this->db->escape($data['image']) . "', status = '" . (int)$data['status'] . "' WHERE stocks_id = '" . (int)$stocks_id . "'");
		
		$this->db->query("DELETE FROM " . DB_PREFIX . "stocks_description WHERE stocks_id = '" . (int)$stocks_id. "'");
		
		foreach ($data['stocks_description'] as $key => $value) {
			$this->db->query("INSERT INTO " . DB_PREFIX ."stocks_description SET stocks_id = '" . (int)$stocks_id . "', language_id = '" . (int)$key . "', title = '" . $this->db->escape($value['title']) . "', description = '" . $this->db->escape($value['description']) . "', html = '" . $this->db->escape($value['html']) . "', short_description = '" . $this->db->escape($value['short_description']) . "', meta_title = '" . $this->db->escape($value['meta_title']) . "', meta_description = '" . $this->db->escape($value['meta_description']) . "', meta_keywords = '" . $this->db->escape($value['meta_keyword']) . "'");
		}
		
		$this->db->query("DELETE FROM " . DB_PREFIX . "seo_url WHERE query = 'stocks_id=" . (int)$stocks_id. "'");
		
		$this->setSeoUrl($stocks_id, $value['title'], $data['stocks_seo_url']);
	}
	
	public function getStocks($stocks_id) {
		$query = $this->db->query("SELECT DISTINCT *, (SELECT keyword FROM " . DB_PREFIX . "seo_url WHERE query = 'stocks_id=" . (int)$stocks_id . "') AS keyword FROM " . DB_PREFIX . "stocks WHERE stocks_id = '" . (int)$stocks_id . "'"); 
 
		if ($query->num_rows) {
			return $query->row;
		} else {
			return false;
		}
	}
   
	public function getStocksDescription($stocks_id) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "stocks_description WHERE stocks_id = '" . (int)$stocks_id . "'"); 
		
		foreach ($query->rows as $result) {
			$stocks_description[$result['language_id']] = array(
				'title'       			=> $result['title'],
				'short_description'		=> $result['short_description'],
				'description' 			=> $result['description'],
				'html' 					=> $result['html'],
				'meta_title'       => $result['meta_title'],
				'meta_description' => $result['meta_description'],
				'meta_keywords'     => $result['meta_keywords']
			);
		}
		
		return $stocks_description;
	}
 
	public function getAllStocks($data) {
		$sql = "SELECT * FROM " . DB_PREFIX . "stocks n LEFT JOIN " . DB_PREFIX . "stocks_description nd ON n.stocks_id = nd.stocks_id WHERE nd.language_id = '" . (int)$this->config->get('config_language_id') . "' ORDER BY date_added DESC";
		
		if (isset($data['start']) && isset($data['limit'])) {
			if ($data['start'] < 0) {
				$data['start'] = 0;
			}
			
			if ($data['limit'] < 1) {
				$data['limit'] = 20;
			}	
		
			$sql .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
		}	
		
		$query = $this->db->query($sql);
 
		return $query->rows;
	}

	public function getStocksSeoUrls($stocks_id) {
		$information_seo_url_data = array();
		
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "seo_url WHERE query = 'stocks_id=" . (int)$stocks_id . "'");

		foreach ($query->rows as $result) {
			$vakancy_seo_url_data[$result['language_id']] = $result['keyword'];
		}

		return $vakancy_seo_url_data;
	}
   
	public function deleteStocks($stocks_id) {
		$this->db->query("DELETE FROM " . DB_PREFIX . "stocks WHERE stocks_id = '" . (int)$stocks_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "stocks_description WHERE stocks_id = '" . (int)$stocks_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "seo_url WHERE query = 'stocks_id=" . (int)$stocks_id. "'");
	}
   
	public function getTotalStocks() {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "stocks");
	
		return $query->row['total'];
	}

	private function setSeoUrl($stocks_id, $title, $stocks_seo_url){
		if (isset($stocks_seo_url)) {
			foreach ($stocks_seo_url as $language_id => $keyword) {
				if (!empty($keyword)) {
					$this->db->query("INSERT INTO " . DB_PREFIX . "seo_url SET store_id = 0, language_id = '" . (int)$language_id . "', query = 'stocks_id=" . (int)$stocks_id . "', keyword = '" . $this->db->escape($keyword) . "'");
				} else {
					$this->db->query("INSERT INTO " . DB_PREFIX . "seo_url SET store_id = 0, language_id = '" . (int)$language_id . "', query = 'stocks_id=" . (int)$stocks_id . "', keyword = '" . $this->db->escape($this->language->translit($title)) . "'");
				}
			}
		}
	}
}
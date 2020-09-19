<?php
class ModelCatalogFaq extends Model {
	public function addFaq($data) {
		$this->db->query("INSERT INTO " . DB_PREFIX . "faq SET sort_order = '" . (int)$data['sort_order'] . "', date_added = NOW(), status = '" . (int)$data['status'] . "'");
		
		$faq_id = $this->db->getLastId();
		
		foreach ($data['faq_description'] as $key => $value) {
			$this->db->query("INSERT INTO " . DB_PREFIX ."faq_description SET faq_id = '" . (int)$faq_id . "', language_id = '" . (int)$key . "', title = '" . $this->db->escape($value['title']) . "', description = '" . $this->db->escape($value['description']) . "'");
		}
	}
	
	public function editFaq($faq_id, $data) {
		$this->db->query("UPDATE " . DB_PREFIX . "faq SET sort_order = '" . (int)$data['sort_order'] . "', status = '" . (int)$data['status'] . "' WHERE faq_id = '" . (int)$faq_id . "'");
		
		$this->db->query("DELETE FROM " . DB_PREFIX . "faq_description WHERE faq_id = '" . (int)$faq_id. "'");
		
		foreach ($data['faq_description'] as $key => $value) {
			$this->db->query("INSERT INTO " . DB_PREFIX ."faq_description SET faq_id = '" . (int)$faq_id . "', language_id = '" . (int)$key . "', title = '" . $this->db->escape($value['title']) . "', description = '" . $this->db->escape($value['description']) . "'");
		}
	}
	
	public function getFaq($faq_id) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "faq WHERE faq_id = '" . (int)$faq_id . "'"); 
 
		if ($query->num_rows) {
			return $query->row;
		} else {
			return false;
		}
	}
   
	public function getFaqDescription($faq_id) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "faq_description WHERE faq_id = '" . (int)$faq_id . "'"); 
		
		foreach ($query->rows as $result) {
			$faq_description[$result['language_id']] = array(
				'title'       			=> $result['title'],
				'description' 			=> $result['description']
			);
		}
		
		return $faq_description;
	}
 
	public function getAllFaq($data) {
		$sql = "SELECT * FROM " . DB_PREFIX . "faq n LEFT JOIN " . DB_PREFIX . "faq_description nd ON n.faq_id = nd.faq_id WHERE nd.language_id = '" . (int)$this->config->get('config_language_id') . "' ORDER BY date_added DESC";
		
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
   
	public function deleteFaq($faq_id) {
		$this->db->query("DELETE FROM " . DB_PREFIX . "faq WHERE faq_id = '" . (int)$faq_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "faq_description WHERE faq_id = '" . (int)$faq_id . "'");
	}
   
	public function getTotalFaq() {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "faq");
	
		return $query->row['total'];
	}
}
<?php
class ModelCatalogVakancy extends Model {
	public function addVakancy($data) {
		$this->db->query("INSERT INTO " . DB_PREFIX . "vakancy SET sort_order = '" . (int)$data['sort_order'] . "', date_added = NOW(), status = '" . (int)$data['status'] . "'");
		
		$vakancy_id = $this->db->getLastId();
		
		foreach ($data['vakancy_description'] as $key => $value) {
			$this->db->query("INSERT INTO " . DB_PREFIX ."vakancy_description SET vakancy_id = '" . (int)$vakancy_id . "', language_id = '" . (int)$key . "', title = '" . $this->db->escape($value['title']) . "', description = '" . $this->db->escape($value['description']) . "', city = '" . $this->db->escape($value['city']) . "', salary = '" . $value['salary'] . "'");
		}
	}
	
	public function editVakancy($vakancy_id, $data) {
		$this->db->query("UPDATE " . DB_PREFIX . "vakancy SET sort_order = '" . (int)$data['sort_order'] . "', status = '" . (int)$data['status'] . "' WHERE vakancy_id = '" . (int)$vakancy_id . "'");
		
		$this->db->query("DELETE FROM " . DB_PREFIX . "vakancy_description WHERE vakancy_id = '" . (int)$vakancy_id. "'");
		
		foreach ($data['vakancy_description'] as $key => $value) {
			$this->db->query("INSERT INTO " . DB_PREFIX ."vakancy_description SET vakancy_id = '" . (int)$vakancy_id . "', language_id = '" . (int)$key . "', title = '" . $this->db->escape($value['title']) . "', description = '" . $this->db->escape($value['description']) . "', city = '" . $this->db->escape($value['city']) . "', salary = '" . $value['salary'] . "'");
		}
	}
	
	public function getVakancy($vakancy_id) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "vakancy WHERE vakancy_id = '" . (int)$vakancy_id . "'"); 
 
		if ($query->num_rows) {
			return $query->row;
		} else {
			return false;
		}
	}
   
	public function getVakancyDescription($vakancy_id) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "vakancy_description WHERE vakancy_id = '" . (int)$vakancy_id . "'"); 
		
		foreach ($query->rows as $result) {
			$vakancy_description[$result['language_id']] = array(
				'title'       			=> $result['title'],
				'description' 			=> $result['description'],
				'city'				=> $result['city'],
				'salary'				=> $result['salary']
			);
		}
		
		return $vakancy_description;
	}
 
	public function getAllVakancy($data) {
		$sql = "SELECT * FROM " . DB_PREFIX . "vakancy n LEFT JOIN " . DB_PREFIX . "vakancy_description nd ON n.vakancy_id = nd.vakancy_id WHERE nd.language_id = '" . (int)$this->config->get('config_language_id') . "' ORDER BY date_added DESC";
		
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
   
	public function deleteVakancy($vakancy_id) {
		$this->db->query("DELETE FROM " . DB_PREFIX . "vakancy WHERE vakancy_id = '" . (int)$vakancy_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "vakancy_description WHERE vakancy_id = '" . (int)$vakancy_id . "'");
	}
   
	public function getTotalVakancy() {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "vakancy");
	
		return $query->row['total'];
	}
}
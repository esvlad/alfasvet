<?php
class ModelCatalogFilials extends Model {
	public function addFilials($data) {
		$this->db->query("INSERT INTO " . DB_PREFIX . "filials SET zone_id = '" . (int)$data['zone_id'] . "', sort_order = '" . (int)$data['sort_order'] . "', date_added = NOW(), status = '" . (int)$data['status'] . "'");
		
		$filials_id = $this->db->getLastId();
		
		foreach ($data['filials_description'] as $key => $value) {
			$this->db->query("INSERT INTO " . DB_PREFIX ."filials_description SET filials_id = '" . (int)$filials_id . "', language_id = '" . (int)$key . "', title = '" . $this->db->escape($value['title']) . "', description = '" . $this->db->escape($value['description']) . "', phone = '" . $this->db->escape($value['phone']) . "', email = '" . $value['email'] . "'");
		}
	}
	
	public function editFilials($filials_id, $data) {
		$this->db->query("UPDATE " . DB_PREFIX . "filials SET zone_id = '" . (int)$data['zone_id'] . "', sort_order = '" . (int)$data['sort_order'] . "', status = '" . (int)$data['status'] . "' WHERE filials_id = '" . (int)$filials_id . "'");
		
		$this->db->query("DELETE FROM " . DB_PREFIX . "filials_description WHERE filials_id = '" . (int)$filials_id. "'");
		
		foreach ($data['filials_description'] as $key => $value) {
			$this->db->query("INSERT INTO " . DB_PREFIX ."filials_description SET filials_id = '" . (int)$filials_id . "', language_id = '" . (int)$key . "', title = '" . $this->db->escape($value['title']) . "', description = '" . $this->db->escape($value['description']) . "', phone = '" . $this->db->escape($value['phone']) . "', email = '" . $value['email'] . "'");
		}
	}
	
	public function getFilials($filials_id) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "filials WHERE filials_id = '" . (int)$filials_id . "'"); 
 
		if ($query->num_rows) {
			return $query->row;
		} else {
			return false;
		}
	}
   
	public function getFilialsDescription($filials_id) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "filials_description WHERE filials_id = '" . (int)$filials_id . "'"); 
		
		foreach ($query->rows as $result) {
			$filials_description[$result['language_id']] = array(
				'title'       			=> $result['title'],
				'description' 			=> $result['description'],
				'phone'					=> $result['phone'],
				'email'					=> $result['email']
			);
		}
		
		return $filials_description;
	}

	public function getFilialsZone(){
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "zone WHERE country_id = 176");

		if ($query->num_rows) {
			return $query->rows;
		} else {
			return false;
		}
	}

	public function getFilialsZoneName($zone_id){
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "zone WHERE zone_id = '" . (int)$zone_id . "'");

		if ($query->num_rows) {
			return $query->row;
		} else {
			return false;
		}
	}
 
	public function getAllFilials($data) {
		$sql = "SELECT * FROM " . DB_PREFIX . "filials n LEFT JOIN " . DB_PREFIX . "filials_description nd ON n.filials_id = nd.filials_id WHERE nd.language_id = '" . (int)$this->config->get('config_language_id') . "' ORDER BY date_added DESC";
		
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
   
	public function deleteFilials($filials_id) {
		$this->db->query("DELETE FROM " . DB_PREFIX . "filials WHERE filials_id = '" . (int)$filials_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "filials_description WHERE filials_id = '" . (int)$filials_id . "'");
	}
   
	public function getTotalFilials() {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "filials");
	
		return $query->row['total'];
	}
}
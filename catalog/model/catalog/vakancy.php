<?php
class ModelCatalogVakancy extends Model {
	public function getAllVakancy() {
		$sql = "SELECT v.vakancy_id as id, vd.title, vd.description, vd.city, vd.salary FROM oc_vakancy v LEFT JOIN oc_vakancy_description vd ON v.vakancy_id = vd.vakancy_id WHERE v.status = 1 ORDER BY v.sort_order, v.date_added DESC";
		
		$query = $this->db->query($sql);
 
		return $query->rows;
	}
}
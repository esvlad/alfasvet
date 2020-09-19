<?php
class ModelCatalogFilials extends Model {	
	public function getZones(){
		$sql = "SELECT z.zone_id as id, z.name FROM oc_filials f LEFT JOIN oc_zone z ON f.zone_id = z.zone_id WHERE f.status = 1 GROUP BY z.zone_id";

		$query = $this->db->query($sql);
 
		return $query->rows;
	}

	public function getFilials() {
		$sql = "SELECT f.filials_id, f.zone_id, z.name as zone_name, fd.title, fd.description, fd.phone, fd.email FROM oc_filials f LEFT JOIN oc_filials_description fd ON f.filials_id = fd.filials_id LEFT JOIN oc_zone z ON f.zone_id = z.zone_id WHERE f.status = 1 ORDER BY f.date_added ASC";
		
		$query = $this->db->query($sql);
 
		return $query->rows;
	}
}
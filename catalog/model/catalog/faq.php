<?php
class ModelCatalogFaq extends Model {
	public function getAllFaq() {
		$sql = "SELECT f.faq_id as id, f.date_added, fd.title, fd.description FROM oc_faq f LEFT JOIN oc_faq_description fd ON f.faq_id = fd.faq_id WHERE f.status = 1 ORDER BY f.sort_order, f.date_added DESC";
		
		$query = $this->db->query($sql);
 
		return $query->rows;
	}
}
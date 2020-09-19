<?php
class ModelCatalogCategory extends Model {
	public function getCategory($category_id) {
		$query = $this->db->query("SELECT DISTINCT * FROM " . DB_PREFIX . "category c LEFT JOIN " . DB_PREFIX . "category_description cd ON (c.category_id = cd.category_id) LEFT JOIN " . DB_PREFIX . "category_to_store c2s ON (c.category_id = c2s.category_id) WHERE c.category_id = '" . (int)$category_id . "' AND cd.language_id = '" . (int)$this->config->get('config_language_id') . "' AND c2s.store_id = '" . (int)$this->config->get('config_store_id') . "' AND c.status = '1'");

		return $query->row;
	}

	public function getCategoryParentId($category_id){
		$query = $this->db->query("SELECT c.parent_id FROM oc_category c WHERE c.category_id = '" . (int)$category_id . "'");

		if($query->row['parent_id'] == 0){
			return $category_id;
		} else {
			return $query->row['parent_id'];
		}
	}

	public function getCategories($parent_id = 0) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "category c LEFT JOIN " . DB_PREFIX . "category_description cd ON (c.category_id = cd.category_id) LEFT JOIN " . DB_PREFIX . "category_to_store c2s ON (c.category_id = c2s.category_id) WHERE c.parent_id = '" . (int)$parent_id . "' AND cd.language_id = '" . (int)$this->config->get('config_language_id') . "' AND c2s.store_id = '" . (int)$this->config->get('config_store_id') . "'  AND c.status = '1' ORDER BY c.sort_order, LCASE(cd.name)");

		return $query->rows;
	}

	public function getCategoryFilters($category_id) {
		$implode = array();

		$query = $this->db->query("SELECT filter_id FROM " . DB_PREFIX . "category_filter WHERE category_id = '" . (int)$category_id . "'");

		foreach ($query->rows as $result) {
			$implode[] = (int)$result['filter_id'];
		}

		$filter_group_data = array();

		if ($implode) {
			$filter_group_query = $this->db->query("SELECT DISTINCT f.filter_group_id, fgd.name, fg.sort_order FROM " . DB_PREFIX . "filter f LEFT JOIN " . DB_PREFIX . "filter_group fg ON (f.filter_group_id = fg.filter_group_id) LEFT JOIN " . DB_PREFIX . "filter_group_description fgd ON (fg.filter_group_id = fgd.filter_group_id) WHERE f.filter_id IN (" . implode(',', $implode) . ") AND fgd.language_id = '" . (int)$this->config->get('config_language_id') . "' GROUP BY f.filter_group_id ORDER BY fg.sort_order, LCASE(fgd.name)");

			foreach ($filter_group_query->rows as $filter_group) {
				$filter_data = array();

				$filter_query = $this->db->query("SELECT DISTINCT f.filter_id, fd.name FROM " . DB_PREFIX . "filter f LEFT JOIN " . DB_PREFIX . "filter_description fd ON (f.filter_id = fd.filter_id) WHERE f.filter_id IN (" . implode(',', $implode) . ") AND f.filter_group_id = '" . (int)$filter_group['filter_group_id'] . "' AND fd.language_id = '" . (int)$this->config->get('config_language_id') . "' ORDER BY f.sort_order, LCASE(fd.name)");

				foreach ($filter_query->rows as $filter) {
					$filter_data[] = array(
						'filter_id' => $filter['filter_id'],
						'name'      => $filter['name']
					);
				}

				if ($filter_data) {
					$filter_group_data[] = array(
						'filter_group_id' => $filter_group['filter_group_id'],
						'name'            => $filter_group['name'],
						'filter'          => $filter_data
					);
				}
			}
		}

		return $filter_group_data;
	}

	public function getCategoryLayoutId($category_id) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "category_to_layout WHERE category_id = '" . (int)$category_id . "' AND store_id = '" . (int)$this->config->get('config_store_id') . "'");

		if ($query->num_rows) {
			return (int)$query->row['layout_id'];
		} else {
			return 0;
		}
	}

	public function getTotalCategoriesByCategoryId($parent_id = 0) {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "category c LEFT JOIN " . DB_PREFIX . "category_to_store c2s ON (c.category_id = c2s.category_id) WHERE c.parent_id = '" . (int)$parent_id . "' AND c2s.store_id = '" . (int)$this->config->get('config_store_id') . "' AND c.status = '1'");

		return $query->row['total'];
	}

	public function getSearchKeywords($search){
		/*$sql = "SELECT DISTINCT p.product_id, pd.name, p.image, p.price, (SELECT price FROM oc_product_special ps WHERE ps.product_id = p.product_id AND ps.customer_group_id = '" . (int)$this->config->get('config_customer_group_id') . "' AND ((ps.date_start = '0000-00-00' OR ps.date_start < NOW()) AND (ps.date_end = '0000-00-00' OR ps.date_end > NOW())) ORDER BY ps.priority ASC, ps.price ASC LIMIT 1) AS special FROM oc_product_description pd LEFT JOIN oc_product p ON (pd.product_id = p.product_id) WHERE pd.name like '%".(string)$search."%' AND p.status = 1 AND p.quantity > 0 LIMIT 0,30";*/

		$sql = "SELECT DISTINCT p.product_id, c.category_id, pd.name, p.image, p.price, (SELECT price FROM oc_product_special ps WHERE ps.product_id = p.product_id AND ps.customer_group_id = '" . (int)$this->config->get('config_customer_group_id') . "' AND ((ps.date_start = '0000-00-00' OR ps.date_start < NOW()) AND (ps.date_end = '0000-00-00' OR ps.date_end > NOW())) ORDER BY ps.priority ASC, ps.price ASC LIMIT 1) AS special FROM oc_product_description pd LEFT JOIN oc_product p ON (pd.product_id = p.product_id) LEFT JOIN oc_product_to_category ptc ON (p.product_id = ptc.product_id) LEFT JOIN oc_category c ON (ptc.category_id = c.category_id) WHERE pd.name like '%".(string)$search."%' AND p.status = 1 AND p.quantity > 0 AND (c.parent_id != 0 OR c.category_id = 17) LIMIT 0,30";

		$query = $this->db->query($sql);
		$products = [];

		foreach($query->rows as $product){
			$products[$product['category_id']][] = $product;
		}
		
		return $products;
	}

	public function getFilters($category_id){
		$products_query = $this->db->query("SELECT DISTINCT p.product_id, ptc.category_id FROM oc_product p LEFT JOIN oc_product_to_category ptc ON (p.product_id = ptc.product_id) WHERE ptc.category_id = ".(int)$category_id." AND p.status = 1");

		$products = [];
		foreach($products_query->rows as $product){
			$products[] = $product['product_id'];
		}

		$product_filters_query = $this->db->query("SELECT * FROM oc_product_filter WHERE product_id IN (".implode(',', $products).")");
		
		$filters_product = [];
		if($product_filters_query->num_rows){
			foreach($product_filters_query->rows as $product_filters){
				$filters_product[] = $product_filters['filter_id'];
			}

			$filter_ids = array_unique($filters_product);
			$filter_ids = implode(',',$filter_ids);

			$query = $this->db->query("SELECT * FROM oc_filter_group");

			$filter_group = array();

			foreach($query->rows as $filter_groups){
				$filters = $this->db->query("SELECT f.filter_id, fd.name FROM oc_filter f LEFT JOIN oc_filter_description fd ON (f.filter_id = fd.filter_id) WHERE f.filter_group_id = ".(int)$filter_groups['filter_group_id']." AND f.filter_id IN (".$filter_ids.") ORDER BY f.sort_order, f.filter_id ASC");

				if($filters->rows){
					$filter_group_value = [];
					foreach($filters->rows as $filter){
						$filter_group_value[] = [
							'filter_id' => $filter['filter_id'],
							'filter_name' => $filter['name'],
						];
					}

					$filter_group[] = [
						'group_id' => $filter_groups['filter_group_id'],
						'group_value' => $filter_group_value
					];
				}
			}

			return $filter_group;
		} else {
			return false;
		}
	}
}
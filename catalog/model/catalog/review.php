<?php
class ModelCatalogReview extends Model {
	public function addReview($product_id, $data) {
		$this->db->query("INSERT INTO " . DB_PREFIX . "review SET author = '" . $this->db->escape($data['name']) . "', customer_id = '" . (int)$this->customer->getId() . "', product_id = '" . (int)$product_id . "', text = '" . $this->db->escape($data['text']) . "', rating = '" . (int)$data['rating'] . "', date_added = NOW()");

		$review_id = $this->db->getLastId();

		$this->load->model('catalog/product');
		
		$product_info = $this->model_catalog_product->getProduct($product_id);

		$data['store_url'] = $this->config->get('config_url');
		$data['store_name'] = $this->config->get('config_name');

		$data['page'] = html_entity_decode($product_info['name'], ENT_QUOTES, 'UTF-8');
		$data['uri'] = $this->url->link('catalog/product', 'product_id='.(int)$product_id);

		$data['name'] = html_entity_decode($data['name'], ENT_QUOTES, 'UTF-8');
		$data['rating'] = (int)$data['rating'];
		$data['message'] = html_entity_decode($data['text'], ENT_QUOTES, 'UTF-8');

		$subject = $data['title'] = sprintf('%s - Новый отзыв к товару на сайте', html_entity_decode($this->config->get('config_name'), ENT_QUOTES, 'UTF-8'));

		$mail = new Mail();

		$mail->setTo('sale@ledoptom.com');//$this->config->get('config_email')
		$mail->setFrom($this->config->get('config_email'));
		$mail->setSender(html_entity_decode($this->config->get('config_name'), ENT_QUOTES, 'UTF-8'));
		$mail->setSubject(html_entity_decode($subject, ENT_QUOTES, 'UTF-8'));
		$mail->setHtml($this->load->view('mail/review', $data));
		//$mail->setText($text);
		$mail->send();
	}

	public function getReviewsByProductId($product_id, $start = 0, $limit = 20) {
		if ($start < 0) {
			$start = 0;
		}

		if ($limit < 1) {
			$limit = 20;
		}

		$query = $this->db->query("SELECT r.review_id, r.author, r.rating, r.text, p.product_id, pd.name, p.price, p.image, r.date_added FROM " . DB_PREFIX . "review r LEFT JOIN " . DB_PREFIX . "product p ON (r.product_id = p.product_id) LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id) WHERE p.product_id = '" . (int)$product_id . "' AND p.date_available <= NOW() AND p.status = '1' AND r.status = '1' AND pd.language_id = '" . (int)$this->config->get('config_language_id') . "' ORDER BY r.date_added DESC LIMIT " . (int)$start . "," . (int)$limit);

		return $query->rows;
	}

	public function getTotalReviewsByProductId($product_id) {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "review r LEFT JOIN " . DB_PREFIX . "product p ON (r.product_id = p.product_id) LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id) WHERE p.product_id = '" . (int)$product_id . "' AND p.date_available <= NOW() AND p.status = '1' AND r.status = '1' AND pd.language_id = '" . (int)$this->config->get('config_language_id') . "'");

		return $query->row['total'];
	}
}
<?php
class ControllerInformationInformation extends Controller {
	public function index() {
		$this->load->language('information/information');

		$this->document->addStyle('catalog/view/javascript/jquery/swiper/css/swiper.min.css');
		$this->document->addStyle('catalog/view/javascript/jquery/swiper/css/opencart.css');
		$this->document->addStyle('catalog/view/javascript/jquery/magnific/magnific-popup.css');
		$this->document->addScript('catalog/view/javascript/jquery/swiper/js/swiper.jquery.js');
		$this->document->addScript('catalog/view/javascript/jquery/magnific/jquery.magnific-popup.min.js');
		

		$this->load->model('catalog/information');

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/home')
		);

		if (isset($this->request->get['information_id'])) {
			$information_id = $data['id'] = (int)$this->request->get['information_id'];
		} else {
			$information_id = $data['id'] = 0;
		}

		$information_info = $this->model_catalog_information->getInformation($information_id);

		if ($information_info) {
			$this->document->setTitle($information_info['meta_title']);
			$this->document->setDescription($information_info['meta_description']);
			$this->document->setKeywords($information_info['meta_keyword']);

			$data['breadcrumbs'][] = array(
				'text' => $information_info['title'],
				'href' => $this->url->link('information/information', 'information_id=' .  $information_id)
			);

			$data['heading_title'] = $information_info['title'];

			$data['description'] = html_entity_decode($information_info['description'], ENT_QUOTES, 'UTF-8');

			$data['continue'] = $this->url->link('common/home');

			$data['column_left'] = $this->load->controller('common/column_left');
			$data['column_right'] = $this->load->controller('common/column_right');
			$data['content_top'] = $this->load->controller('common/content_top');
			$data['content_bottom'] = $this->load->controller('common/content_bottom');
			$data['footer'] = $this->load->controller('common/footer');
			$data['header'] = $this->load->controller('common/header');

			$this->response->setOutput($this->load->view('information/information', $data));
		} else {
			$data['breadcrumbs'][] = array(
				'text' => $this->language->get('text_error'),
				'href' => $this->url->link('information/information', 'information_id=' . $information_id)
			);

			$this->document->setTitle($this->language->get('text_error'));

			$data['heading_title'] = $this->language->get('text_error');

			$data['text_error'] = $this->language->get('text_error');

			$data['continue'] = $this->url->link('common/home');

			$this->response->addHeader($this->request->server['SERVER_PROTOCOL'] . ' 404 Not Found');

			$data['column_left'] = $this->load->controller('common/column_left');
			$data['column_right'] = $this->load->controller('common/column_right');
			$data['content_top'] = $this->load->controller('common/content_top');
			$data['content_bottom'] = $this->load->controller('common/content_bottom');
			$data['footer'] = $this->load->controller('common/footer');
			$data['header'] = $this->load->controller('common/header');

			$this->response->setOutput($this->load->view('error/not_found', $data));
		}
	}

	public function agree() {
		$this->load->model('catalog/information');

		if (isset($this->request->get['information_id'])) {
			$information_id = (int)$this->request->get['information_id'];
		} else {
			$information_id = 0;
		}

		$output = '';

		$information_info = $this->model_catalog_information->getInformation($information_id);

		if ($information_info) {
			$output .= html_entity_decode($information_info['description'], ENT_QUOTES, 'UTF-8') . "\n";
		}

		$this->response->setOutput($output);
	}

	public function shops(){

		$this->document->setTitle('Где купить');
		$this->load->model('catalog/information');

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/home')
		);

		$data['breadcrumbs'][] = array(
			'text' => 'Где купить',
			'href' => $this->url->link('information/information/shops')
		);

		//Filials
		$data['filials'] = array();
		$data['zones'] = [];

		$this->load->model('catalog/filials');
		$zones = $data['zones'] = $this->model_catalog_filials->getZones();
		$filials = $this->model_catalog_filials->getFilials();

		foreach($zones as $zone){
			foreach($filials as $filial){
				if($filial['zone_id'] == $zone['id']){
					$data['filials'][$zone['id']][] = [
						'filials_id' => $filial['filials_id'],
						'zone_id' => $filial['zone_id'],
						'title' => $filial['title'],
						'description' => html_entity_decode($filial['description']),
						'phone' => $filial['phone'],
						'email' => $filial['email']
					];
				}
			}
		}

		$data['footer'] = $this->load->controller('common/footer');
		$data['header'] = $this->load->controller('common/header');

		$this->response->setOutput($this->load->view('information/shops', $data));
	}

	public function calculator(){
		$data = [];
		$title = 'Расчет освещенности';
		$this->document->setTitle($title);

		$this->load->model('catalog/information');

		$this->document->addStyle('catalog/view/javascript/jquery/ui/jquery-ui.min.css');
		$this->document->addScript('catalog/view/javascript/jquery/ui/jquery-ui.min.js');
		$this->document->addScript('catalog/view/javascript/jquery/ui/jquery.ui.touch-punch.min.js');

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/home')
		);

		$data['breadcrumbs'][] = array(
			'text' => $title,
			'href' => $this->url->link('information/calculator')
		);

		$filter_data = array(
			'filter_category_id' => 20,
			'start'              => 0,
			'limit'              => 8
		);

		$data['footer'] = $this->load->controller('common/footer');
		$data['header'] = $this->load->controller('common/header');

		$this->response->setOutput($this->load->view('information/calculator', $data));
	}

	public function calculator_calculate(){
		$data = [];
		$filter_category_ids = [26,27];

		if($this->request->server['REQUEST_METHOD'] == 'POST'){
			$filter_data = array(
				'filter_category_id' => $filter_category_ids,
				'filters' 			 => $this->request->post,
				'start'              => 0,
				'limit'              => 8
			);

			$data['products'] = array();
			
			$this->load->model('catalog/product');
			$this->load->model('tool/image');

			$results = $this->model_catalog_product->getProductsCalculate($filter_data);

			foreach ($results as $result) {
				if ($result['image']) {
					$image = $this->model_tool_image->resize($result['image'], $this->config->get('theme_' . $this->config->get('config_theme') . '_image_product_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_product_height'));
				} else {
					$image = $this->model_tool_image->resize('placeholder.png', $this->config->get('theme_' . $this->config->get('config_theme') . '_image_product_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_product_height'));
				}

				if ($this->customer->isLogged() || !$this->config->get('config_customer_price')) {
					$price = $this->currency->format($this->tax->calculate($result['price'], $result['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
				} else {
					$price = false;
				}

				if ((float)$result['special']) {
					$special = $this->currency->format($this->tax->calculate($result['special'], $result['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
				} else {
					$special = false;
				}

				if ($this->config->get('config_tax')) {
					$tax = $this->currency->format((float)$result['special'] ? $result['special'] : $result['price'], $this->session->data['currency']);
				} else {
					$tax = false;
				}

				if ($this->config->get('config_review_status')) {
					$rating = (int)$result['rating'];
				} else {
					$rating = false;
				}

				$category_path = $this->model_catalog_product->getProductCategory($result['product_id'], $filter_category_ids);

				$data['products'][] = array(
					'product_id'  => $result['product_id'],
					'thumb'       => $image,
					'name'        => $result['name'],
					'description' => utf8_substr(trim(strip_tags(html_entity_decode($result['description'], ENT_QUOTES, 'UTF-8'))), 0, $this->config->get('theme_' . $this->config->get('config_theme') . '_product_description_length')) . '..',
					'price'       => $price,
					'special'     => $special,
					'tax'         => $tax,
					'hit'		  => $this->model_catalog_product->getProductHit($result['product_id']),
					'attributes'  => $this->model_catalog_product->getCardAttributes($result['product_id']),
					'minimum'     => $result['minimum'] > 0 ? $result['minimum'] : 1,
					'rating'      => $result['rating'],
					'href'        => $this->url->link('product/product', 'path=' . $category_path . '&product_id=' . $result['product_id'])
				);
			}
		}

		$this->response->setOutput($this->load->view('information/calculator_products', $data));
	}

	public function specialist(){
		$data = [];
		$data['politics'] = $this->url->link('information/information', 'information_id=3');

		$this->response->setOutput($this->load->view('information/specialist', $data));
	}

	public function marketing(){
		$data = [];
		$data['politics'] = $this->url->link('information/information', 'information_id=3');
		
		$this->response->setOutput($this->load->view('information/marketing_form', $data));
	}

	public function filials(){
		$data = [];

		$data['filials'] = array();
		$data['zones'] = [];

		$this->load->model('catalog/filials');
		$zones = $data['zones'] = $this->model_catalog_filials->getZones();
		$filials = $this->model_catalog_filials->getFilials();

		foreach($zones as $zone){
			foreach($filials as $filial){
				if($filial['zone_id'] == $zone['id']){
					$data['filials'][$zone['id']][] = [
						'filials_id' => $filial['filials_id'],
						'zone_id' => $filial['zone_id'],
						'title' => $filial['title'],
						'description' => html_entity_decode($filial['description']),
						'phone' => $filial['phone'],
						'email' => $filial['email']
					];
				}
			}
		}

		$this->response->setOutput($this->load->view('information/filials', $data));
	}
}
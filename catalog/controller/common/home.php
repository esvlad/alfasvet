<?php
class ControllerCommonHome extends Controller {
	public function index() {
		$this->document->setTitle($this->config->get('config_meta_title'));
		$this->document->setDescription($this->config->get('config_meta_description'));
		$this->document->setKeywords($this->config->get('config_meta_keyword'));

		$this->document->addStyle('catalog/view/javascript/jquery/swiper/css/swiper.min.css');
		$this->document->addStyle('catalog/view/javascript/jquery/swiper/css/opencart.css');
		$this->document->addScript('catalog/view/javascript/jquery/swiper/js/swiper.jquery.js');

		if (isset($this->request->get['route'])) {
			$this->document->addLink($this->config->get('config_url'), 'canonical');
		}

		$data['column_left'] = $this->load->controller('common/column_left');
		$data['column_right'] = $this->load->controller('common/column_right');
		//$data['content_top'] = $this->load->controller('common/content_top');
		$data['content_bottom'] = $this->load->controller('common/content_bottom');
		$data['footer'] = $this->load->controller('common/footer');
		$data['header'] = $this->load->controller('common/header');
		
		$data['banners'] = array();

		$this->load->model('design/banner');
		$this->load->model('tool/image');
		$banner_id = 7;
		$bwidth = 1920;
		$bheight = 700;

		$mb_detected = new MobileDetect;

		if($mb_detected->isMobile() && !$mb_detected->isTablet()){
			$detect = 'mobile';
			$banner_id = 11;
			$bwidth = 400;
			$bheight = 688;
		} elseif($mb_detected->isMobile() && $mb_detected->isTablet()){
			$detect = 'tablet';
		} else {
			$detect = 'pc';
		}

		$results = $this->model_design_banner->getBanner($banner_id);

		foreach ($results as $result) {
			if (is_file(DIR_IMAGE . $result['image'])) {
				$data['banners'][] = array(
					'id' => $result['banner_image_id'],
					'title' => html_entity_decode($result['title'], ENT_QUOTES, 'UTF-8'),
					'caption' => $result['caption'],
					'btn_caption' => $result['btn_caption'],
					'link'  => $result['link'],
					'colors' => json_decode($result['colors']),
					'image' => $this->model_tool_image->resize($result['image'], $bwidth, $bheight)
				);
			}
		}

		$this->load->model('catalog/category');

		$data['categories'] = array();

		$categories = $this->model_catalog_category->getCategories(0);

		foreach ($categories as $category) {
			if ($category['top']) {
				// Level 2
				$children_data = array();

				$children = $this->model_catalog_category->getCategories($category['category_id']);

				foreach ($children as $child) {
					$filter_data = array(
						'filter_category_id'  => $child['category_id'],
						'filter_sub_category' => true
					);

					$children_data[] = array(
						'name'  => $child['name'],
						'href'  => $this->url->link('product/category', 'path=' . $category['category_id'] . '_' . $child['category_id'])
					);
				}

				if ($category['image']) {
					$thumb = $this->model_tool_image->resize($category['image'], 330, 230);
				} else {
					$thumb = '';
				}

				// Level 1
				$data['categories'][] = array(
					'name'     => $category['name'],
					'thumb'		=> $thumb,
					'children' => $children_data,
					'column'   => $category['column'] ? $category['column'] : 1,
					'href'     => $this->url->link('product/category', 'path=' . $category['category_id'])
				);
			}
		}

		$page = 1;
		$filter_data = array(
			'page' 	=> $page,
			'limit' => 8,
			'start' => 10 * ($page - 1),
		);

		$this->load->model('catalog/news');
		$newses = $this->model_catalog_news->getAllNews($filter_data);
		$data['newses'] = [];

		foreach ($newses as $news) {
			$data['newses'][] = array (
				'title' 		=> $news['title'],
				'image'			=> $this->model_tool_image->resizeCrop($news['image'], 465, 223),
				'description' 	=> strip_tags(html_entity_decode($news['short_description'])),
				'view' 			=> $this->url->link('information/news/news', 'news_id=' . $news['news_id']),
				'date_added' 	=> date($this->language->get('date_format_short'), strtotime($news['date_added']))
			);
		}

		//Products Stocks
		$featured = [];

		$filter_data = array(
			'filter_category_id' => 59,
			'filter_filter'      => '',
			'filters_group'		 => '',
			'sort'               => 'p.sort_order',
			'order'              => 'ASC',
			'start'              => 0,
			'limit'              => 8,
			'random' 			 => true
		);

		$product_total = $this->model_catalog_product->getAlfaTotalProducts($filter_data);
		$results = $this->model_catalog_product->getAlfaProducts($filter_data);

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

			$featured['products'][] = array(
				'product_id'  => $result['product_id'],
				'thumb'       => $image,
				'name'        => $result['name'],
				'description' => utf8_substr(trim(strip_tags(html_entity_decode($result['description'], ENT_QUOTES, 'UTF-8'))), 0, $this->config->get('theme_' . $this->config->get('config_theme') . '_product_description_length')) . '..',
				'attributes'  => $this->model_catalog_product->getCardAttributes($result['product_id']),
				'hit'		  => $this->model_catalog_product->getProductHit($result['product_id']),
				'price'       => $price,
				'special'     => $special,
				'tax'         => $tax,
				'minimum'     => $result['minimum'] > 0 ? $result['minimum'] : 1,
				'rating'      => $result['rating'],
				'href'        => $this->url->link('product/product', 'product_id=' . $result['product_id'])
			);
		}

		$featured['more_link'] = $this->url->link('product/category', 'path=59');

		$data['content_top'] = $this->load->view('extension/module/featured', $featured);

		$data['calculator'] = $this->url->link('information/information/calculator');
		$data['products'] = $this->url->link('information/information', 'information_id=10');
		$data['optovikam'] = $this->url->link('information/information', 'information_id=12');
		$data['about_us'] = $this->url->link('information/information', 'information_id=4');
		$data['delivery'] = $this->url->link('information/information', 'information_id=6');
		$data['payment'] = $this->url->link('information/information', 'information_id=11');

		$this->response->setOutput($this->load->view('common/home', $data));
	}
}

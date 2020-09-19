<?php
class ControllerInformationStocks extends Controller {
	public function index() {
		$this->load->model('catalog/stocks');
	 
		$this->document->setTitle('Акции');
	 
		$data['breadcrumbs'] = array();
		
		$data['breadcrumbs'][] = array(
			'text' 		=> 'Главная',
			'href' 		=> $this->url->link('common/home')
		);
		$data['breadcrumbs'][] = array(
			'text' 		=> 'Акции',
			'href' 		=> $this->url->link('information/stocks')
		);
		  

		$data['heading_title'] = 'Акции';
	 
		$stocks = $this->model_catalog_stocks->getAllStocks();
	 
		$data['stocks'] = array();
		
		$this->load->model('tool/image');
	 
		foreach ($stocks as $stock) {
			$data['stocks'][] = array (
				'title' 		=> $stock['title'],
				'image'			=> $this->model_tool_image->resizeCrop($stock['image'], 465, 223),
				'description' 	=> strip_tags(html_entity_decode($stock['short_description'])),
				'view' 			=> $this->url->link('information/stocks/stock', 'stocks_id=' . $stock['stocks_id']),
				'date_added' 	=> date($this->language->get('date_format_short'), strtotime($stock['date_added']))
			);
		}
	 
		$data['footer'] = $this->load->controller('common/footer');
		$data['header'] = $this->load->controller('common/header');

		$this->response->setOutput($this->load->view('information/stocks_list', $data));
	}
 
	public function stock() {
		$this->load->model('catalog/stocks');

		$this->document->addStyle('catalog/view/javascript/jquery/swiper/css/swiper.min.css');
		$this->document->addStyle('catalog/view/javascript/jquery/swiper/css/opencart.css');
		$this->document->addScript('catalog/view/javascript/jquery/swiper/js/swiper.jquery.js');
 
		if (isset($this->request->get['stocks_id']) && !empty($this->request->get['stocks_id'])) {
			$stocks_id = $this->request->get['stocks_id'];
		} else {
			$stocks_id = 0;
		}
 
		$stocks = $this->model_catalog_stocks->getStocks($stocks_id);
 
		$data['breadcrumbs'] = array();
	  
		$data['breadcrumbs'][] = array(
			'text' 			=> 'Главная',
			'href' 			=> $this->url->link('common/home')
		);
	  
		$data['breadcrumbs'][] = array(
			'text' => 'Акции',
			'href' => $this->url->link('information/stocks')
		);
 
		if ($stocks) {
			$data['breadcrumbs'][] = array(
				'text' 		=> $stocks['title'],
				'href' 		=> $this->url->link('information/stocks/stocks', 'stocks_id=' . $stocks_id)
			);
 
			$this->document->setTitle($stocks['title']);
			
			$this->load->model('tool/image');
			
			$data['image'] = $this->model_tool_image->resize($stocks['image'], 700, 440);
 
 			$data['id'] = $stocks['stocks_id'];
			$data['heading_title'] = $stocks['title'];
			$data['description'] = html_entity_decode($stocks['description']);
			$data['date_added'] = date('d.m.Y', strtotime($stocks['date_added']));
			$data['stocks_html'] = html_entity_decode($stocks['html']);
			$data['newslist'] = $this->url->link('information/stocks');
	 
			$data['footer'] = $this->load->controller('common/footer');
			$data['header'] = $this->load->controller('common/header');

			$this->response->setOutput($this->load->view('information/stocks', $data));
		} else {
			$data['breadcrumbs'][] = array(
				'text' 		=> $this->language->get('text_error'),
				'href' 		=> $this->url->link('information/stocks', 'stocks_id=' . $stocks_id)
			);
	 
			$this->document->setTitle($this->language->get('text_error'));
	 
			$data['continue'] = $this->url->link('common/home');
	 
			$data['footer'] = $this->load->controller('common/footer');
			$data['header'] = $this->load->controller('common/header');

			$this->response->setOutput($this->load->view('error/not_found', $data));
		}
	}
}
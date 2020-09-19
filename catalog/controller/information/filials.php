<?php
class ControllerInformationFilials extends Controller {
	public function index() {
		$this->language->load('information/filials');
		
		$this->load->model('catalog/filials');
	 
		$this->document->setTitle($this->language->get('heading_title')); 
	 
		$data['breadcrumbs'] = array();
		
		$data['breadcrumbs'][] = array(
			'text' 		=> 'Главная',
			'href' 		=> $this->url->link('common/home')
		);
		$data['breadcrumbs'][] = array(
			'text' 		=> $this->language->get('heading_title'),
			'href' 		=> $this->url->link('information/filials')
		);
		
		$total = $this->model_catalog_filials->getTotalFilials();
	 
		$all_filials = $this->model_catalog_filials->getAllFilials();
	 
		$data['all_filials'] = array();
		
		$this->load->model('tool/image');
	 
		foreach ($all_filials as $filials) {
			$data['all_filials'][] = array (
				'title' 		=> $filials['title'],
				'zone' 			=> $filials['zone'],
				'phone' 		=> $filials['phone'],
				'email' 		=> $filials['email'],
				'description' 	=> strip_tags(html_entity_decode($filials['description']))
			);
		}
	 
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['column_right'] = $this->load->controller('common/column_right');
		$data['content_top'] = $this->load->controller('common/content_top');
		$data['content_bottom'] = $this->load->controller('common/content_bottom');
		$data['footer'] = $this->load->controller('common/footer');
		$data['header'] = $this->load->controller('common/header');

		$this->response->setOutput($this->load->view('information/filials', $data));
	}
}
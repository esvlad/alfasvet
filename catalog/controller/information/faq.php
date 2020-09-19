<?php
class ControllerInformationFaq extends Controller {
	public function index() {
		$this->language->load('information/faq');
		
		$this->load->model('catalog/faq');
	 
		$this->document->setTitle('Поддержка'); 
	 
		$data['breadcrumbs'] = array();
		
		$data['breadcrumbs'][] = array(
			'text' 		=> 'Главная',
			'href' 		=> $this->url->link('common/home')
		);
		$data['breadcrumbs'][] = array(
			'text' 		=> 'Поддержка',
			'href' 		=> $this->url->link('information/faq')
		);
		  

		$faqs = $this->model_catalog_faq->getAllFaq();

		foreach($faqs as $faq){
			$data['faqs'][] = [
				'id' => $faq['id'],
				'title' => $faq['title'],
				'description' => html_entity_decode($faq['description'])
			];
		}
		
		$data['footer'] = $this->load->controller('common/footer');
		$data['header'] = $this->load->controller('common/header');

		$this->response->setOutput($this->load->view('information/faq', $data));
	}
}
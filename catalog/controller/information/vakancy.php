<?php
class ControllerInformationVakancy extends Controller {
	public function index() {
		$this->load->model('catalog/vakancy');
	 
		$this->document->setTitle('Вакансии'); 
	 
		$data['breadcrumbs'] = array();
		
		$data['breadcrumbs'][] = array(
			'text' 		=> 'Главная',
			'href' 		=> $this->url->link('common/home')
		);
		$data['breadcrumbs'][] = array(
			'text' 		=> 'Вакансии',
			'href' 		=> $this->url->link('information/vakancy')
		);
	 
		$vakancyes = $this->model_catalog_vakancy->getAllVakancy();
	 
		$data['vakancyes'] = array();
		
		foreach ($vakancyes as $vakancy) {
			$data['vakancyes'][] = array (
				'id'			=> $vakancy['id'],
				'title' 		=> $vakancy['title'],
				'description' 	=> html_entity_decode($vakancy['description']),
				'city' 			=> $vakancy['city'],
				'salary' 		=> $vakancy['salary']
			);
		}

		$data['politics'] = $this->url->link('information/information', 'information_id=3');
	 
		$data['footer'] = $this->load->controller('common/footer');
		$data['header'] = $this->load->controller('common/header');

		$this->response->setOutput($this->load->view('information/vakancy', $data));
	}

	public function form(){
		$json = array();
		$data = array();

		$json['post'] = $this->request->post;

		$data['page'] = $this->request->post['page'];
		$data['uri'] = $this->request->post['uri'];
		$vakancy_name = html_entity_decode($this->request->post['vak_name'], ENT_QUOTES, 'UTF-8');

		$data['name'] = $this->request->post['name'];
		$data['phone'] = $this->request->post['phone'];
		$data['email'] = $this->request->post['email'];
		$data['city'] = $this->request->post['city'];

		if(!empty($this->request->post['rezume_link'])){
			$data['resume'] = $this->request->post['rezume_link'];
		} else {
			$data['resume'] = $this->url->link('tool/upload/rekviz', 'code=' . $this->request->post['rezume_code']);
		}

		$data['text'] = $this->request->post['text'];

		$subject = $data['title'] = sprintf('%s - Новый отклик на вакансию «'.$vakancy_name.'»', html_entity_decode($this->config->get('config_name'), ENT_QUOTES, 'UTF-8'));

		$json['success'] = $this->send_mail($subject, $data, 'vakancy');

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	private function send_mail($subject, $data = array(), $page){
		$data['store_url'] = $this->config->get('config_url');
		$data['store_name'] = $this->config->get('config_name');

		$mail = new Mail();

		$mail->setTo('sale@ledoptom.com');//$this->config->get('config_email')
		$mail->setFrom($this->config->get('config_email'));
		$mail->setSender(html_entity_decode($this->config->get('config_name'), ENT_QUOTES, 'UTF-8'));
		$mail->setSubject(html_entity_decode($subject, ENT_QUOTES, 'UTF-8'));
		$mail->setHtml($this->load->view('mail/'.$page, $data));
		//$mail->setText($text);
		$mail->send();

		return true;
	}
}
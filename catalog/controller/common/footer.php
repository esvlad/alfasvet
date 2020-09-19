<?php
class ControllerCommonFooter extends Controller {
	public function index() {
		$this->load->language('common/footer');

		$this->load->model('catalog/information');

		$data['informations'] = array();

		foreach ($this->model_catalog_information->getInformations() as $result) {
			if ($result['bottom']) {
				$data['informations'][] = array(
					'title' => $result['title'],
					'href'  => $this->url->link('information/information', 'information_id=' . $result['information_id'])
				);
			}
		}

		$data['home'] = $this->url->link('common/home');
		$data['contact'] = $this->url->link('information/contact');
		$data['return'] = $this->url->link('account/return/add', '', true);
		$data['sitemap'] = $this->url->link('information/sitemap');
		$data['tracking'] = $this->url->link('information/tracking');
		$data['manufacturer'] = $this->url->link('product/manufacturer');
		$data['voucher'] = $this->url->link('account/voucher', '', true);
		$data['affiliate'] = $this->url->link('affiliate/login', '', true);
		$data['special'] = $this->url->link('product/special');
		$data['account'] = $this->url->link('account/account', '', true);
		$data['order'] = $this->url->link('account/order', '', true);
		$data['wishlist'] = $this->url->link('account/wishlist', '', true);
		$data['newsletter'] = $this->url->link('account/newsletter', '', true);
		$data['shops'] = $this->url->link('information/information/shops');
		$data['faq'] = $this->url->link('information/faq');
		$data['stocks'] = $this->url->link('information/stocks');
		$data['news'] = $this->url->link('information/news');
		$data['vakancy'] = $this->url->link('information/vakancy');

		$data['telephone'] = $this->config->get('config_telephone');
		$data['open'] = $this->config->get('config_open');
		$data['email'] = $this->config->get('config_email');

		$data['politics'] = $this->url->link('information/information', 'information_id=3');
		$data['about_us'] = $this->url->link('information/information', 'information_id=4');
		$data['delivery'] = $this->url->link('information/information', 'information_id=6');
		$data['gorantii'] = $this->url->link('information/information', 'information_id=7');
		$data['vozvrat'] = $this->url->link('information/information', 'information_id=8');
		$data['marketing'] = $this->url->link('information/information', 'information_id=9');
		$data['products'] = $this->url->link('information/information', 'information_id=10');
		$data['payment'] = $this->url->link('information/information', 'information_id=11');
		$data['optovikam'] = $this->url->link('information/information', 'information_id=15');
		$data['proizvodstvo'] = $this->url->link('information/information', 'information_id=13');
		$data['kompanii'] = $this->url->link('information/information', 'information_id=14');

		$data['powered'] = sprintf($this->language->get('text_powered'), $this->config->get('config_name'), date('Y', time()));

		// Whos Online
		if ($this->config->get('config_customer_online')) {
			$this->load->model('tool/online');

			if (isset($this->request->server['REMOTE_ADDR'])) {
				$ip = $this->request->server['REMOTE_ADDR'];
			} else {
				$ip = '';
			}

			if (isset($this->request->server['HTTP_HOST']) && isset($this->request->server['REQUEST_URI'])) {
				$url = ($this->request->server['HTTPS'] ? 'https://' : 'http://') . $this->request->server['HTTP_HOST'] . $this->request->server['REQUEST_URI'];
			} else {
				$url = '';
			}

			if (isset($this->request->server['HTTP_REFERER'])) {
				$referer = $this->request->server['HTTP_REFERER'];
			} else {
				$referer = '';
			}

			$this->model_tool_online->addOnline($ip, $this->customer->getId(), $url, $referer);
		}

		$data['scripts'] = $this->document->getScripts('footer');

		//Category
		$this->load->model('catalog/category');

		$data['categories'] = array();

		$categories = $this->model_catalog_category->getCategories(0);

		foreach ($categories as $category) {
			if ($category['top']) {
				// Level 1
				$data['categories'][] = array(
					'name'     => $category['name'],
					'href'     => $this->url->link('product/category', 'path=' . $category['category_id'])
				);
			}
		}

		$data['chemodan'] = $this->load->controller('extension/module/featured/chemodan');
		
		return $this->load->view('common/footer', $data);
	}

	public function specialist(){
		$json = array();
		$data = array();

		$json['post'] = $this->request->post;

		$data['page'] = $this->request->post['page'];
		$data['uri'] = $this->request->post['uri'];
		$data['name'] = $this->request->post['sname'];
		$data['phone'] = $this->request->post['sphone'];
		$data['message'] = html_entity_decode($this->request->post['sanswer'], ENT_QUOTES, 'UTF-8');

		$subject = $data['title'] = sprintf('%s - Новое сообщение c формы «Специалист»', html_entity_decode($this->config->get('config_name'), ENT_QUOTES, 'UTF-8'));

		$json['success'] = $this->send_mail($subject, $data, 'specialist');

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function marketing(){
		$json = array();
		$data = array();

		$json['post'] = $this->request->post;

		$data['page'] = $this->request->post['page'];
		$data['uri'] = $this->request->post['uri'];
		$data['name'] = $this->request->post['name'];
		$data['phone'] = $this->request->post['phone'];
		$data['email'] = $this->request->post['email'];
		$data['city'] = $this->request->post['city'];

		$subject = $data['title'] = sprintf('%s - Новое сообщение c формы «Заявка на бесплатный стенд»', html_entity_decode($this->config->get('config_name'), ENT_QUOTES, 'UTF-8'));

		$json['success'] = $this->send_mail($subject, $data, 'marketing');

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function diller(){
		$json = array();
		$data = array();

		$json['post'] = $this->request->post;

		$data['page'] = $this->request->post['page'];
		$data['uri'] = $this->request->post['uri'];
		$form_name = html_entity_decode($this->request->post['form_name'], ENT_QUOTES, 'UTF-8');

		$data['name'] = $this->request->post['name'];
		$data['phone'] = $this->request->post['phone'];
		$data['city'] = $this->request->post['city'];
		$data['company'] = $this->request->post['company'];
		$data['inn'] = $this->request->post['inn'];

		$subject = $data['title'] = sprintf('%s - Новое сообщение c формы «'.$form_name.'»', html_entity_decode($this->config->get('config_name'), ENT_QUOTES, 'UTF-8'));

		$json['success'] = $this->send_mail($subject, $data, 'diller');

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

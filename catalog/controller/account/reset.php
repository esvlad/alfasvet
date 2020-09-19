<?php
class ControllerAccountReset extends Controller {
	private $error = array();

	public function index() {
		if ($this->customer->isLogged()) {
			$this->response->redirect($this->url->link('account/account', '', true));
		}

		if (isset($this->request->get['code'])) {
			$code = $this->request->get['code'];
		} else {
			$code = '';
		}

		$this->load->model('account/customer');

		$customer_info = $this->model_account_customer->getCustomerByCode($code);

		if ($customer_info) {
			$this->load->language('account/reset');

			$this->document->setTitle($this->language->get('heading_title'));

			if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
				$this->model_account_customer->editPassword($customer_info['email'], $this->request->post['password']);

				$this->session->data['success'] = $this->language->get('text_success');

				$this->response->redirect($this->url->link('account/login', '', true));
			}

			$data['breadcrumbs'] = array();

			$data['breadcrumbs'][] = array(
				'text' => $this->language->get('text_home'),
				'href' => $this->url->link('common/home')
			);

			$data['breadcrumbs'][] = array(
				'text' => $this->language->get('text_account'),
				'href' => $this->url->link('account/account', '', true)
			);

			$data['breadcrumbs'][] = array(
				'text' => $this->language->get('heading_title'),
				'href' => $this->url->link('account/reset', '', true)
			);

			if (isset($this->error['password'])) {
				$data['error_password'] = $this->error['password'];
			} else {
				$data['error_password'] = '';
			}

			if (isset($this->error['confirm'])) {
				$data['error_confirm'] = $this->error['confirm'];
			} else {
				$data['error_confirm'] = '';
			}

			$data['action'] = $this->url->link('account/reset', 'code=' . $code, true);

			$data['back'] = $this->url->link('account/login', '', true);

			if (isset($this->request->post['password'])) {
				$data['password'] = $this->request->post['password'];
			} else {
				$data['password'] = '';
			}

			if (isset($this->request->post['confirm'])) {
				$data['confirm'] = $this->request->post['confirm'];
			} else {
				$data['confirm'] = '';
			}

			$data['column_left'] = $this->load->controller('common/column_left');
			$data['column_right'] = $this->load->controller('common/column_right');
			$data['content_top'] = $this->load->controller('common/content_top');
			$data['content_bottom'] = $this->load->controller('common/content_bottom');
			$data['footer'] = $this->load->controller('common/footer');
			$data['header'] = $this->load->controller('common/header');

			$this->response->setOutput($this->load->view('account/reset', $data));
		} else {
			$this->load->language('account/reset');

			$this->session->data['error'] = $this->language->get('error_code');

			return new Action('account/login');
		}
	}

	protected function validate() {
		if ((utf8_strlen(html_entity_decode($this->request->post['password'], ENT_QUOTES, 'UTF-8')) < 4) || (utf8_strlen(html_entity_decode($this->request->post['password'], ENT_QUOTES, 'UTF-8')) > 40)) {
			$this->error['password'] = $this->language->get('error_password');
		}

		if ($this->request->post['confirm'] != $this->request->post['password']) {
			$this->error['confirm'] = $this->language->get('error_confirm');
		}

		return !$this->error;
	}

	public function reset(){
		$json = [];

		if(!empty($this->request->post['email'])){
			$email = $this->request->post['email'];

			if(!filter_var($email, FILTER_VALIDATE_EMAIL)){
				$json['error_text'] = 'Не корректно введен e-mail';
			}

			$this->load->model('account/customer');

			$customer_info = $this->model_account_customer->getCustomerByEmail($email);

			if($customer_info){
				//Generate password
				$chars = "qazxswedcvfrtgbnhyujmkiolp1234567890QAZXSWEDCVFRTGBNHYUJMKIOLP";
				$max = 8;
				$size = strlen($chars) - 1;
				$password = null;
				while($max--){
					$password .= $chars[rand(0,$size)];
				}

				$this->model_account_customer->editPassword($email, $password);

				$data = [];

				$data['store_url'] = $this->config->get('config_url');
				$data['store_name'] = $this->config->get('config_name');
				$data['password'] = $password;

				$subject = sprintf('%s - Новый пароль к вашему аккаунту', html_entity_decode($this->config->get('config_name'), ENT_QUOTES, 'UTF-8'));

				$mail = new Mail();

				$mail->setTo('sale@ledoptom.com');//$this->config->get('config_email') . ', swd-w11@yandex.ru' sale@ledoptom.com
				$mail->setFrom($this->config->get('config_email'));
				$mail->setSender(html_entity_decode($this->config->get('config_name'), ENT_QUOTES, 'UTF-8'));
				$mail->setSubject(html_entity_decode($subject, ENT_QUOTES, 'UTF-8'));
				$mail->setHtml($this->load->view('mail/reset_password', $data));
				//$mail->setText($text);
				$mail->send();

				$json['success'] = true;
			} else {
				$json['error_text'] = 'Пользователя с таким e-mail не существует';
			}
		} else {
			$json['error_text'] = 'Не введен e-mail';
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
}

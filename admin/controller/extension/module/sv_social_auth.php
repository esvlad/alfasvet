<?php
class ControllerExtensionModuleSVSocialAuth extends Controller {
	private $error = array();

	public function index() {
		$this->load->language('extension/module/sv_social_auth');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('setting/setting');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			$sv_social_auth = [];
			foreach($this->request->post as $key => $value){
				$sv_social_auth['sv_social_auth_setting'][$key] = $value;
			}

			$this->model_setting_setting->editSetting('sv_social_auth', $sv_social_auth);

			$this->session->data['success'] = 'Настройки сохранены';

			$this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true));
		}

		$data = [];

		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_extension'),
			'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('extension/module/sv_social_auth', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['action'] = $this->url->link('extension/module/sv_social_auth', 'user_token=' . $this->session->data['user_token'], true);

		$data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true);

		if ($this->request->server['REQUEST_METHOD'] != 'POST') {
			$module_info = $this->config->get('sv_social_auth_setting');
		}

		if (isset($this->request->post['vk']['client_id'])) {
			$data['vk']['client_id'] = $this->request->post['vk']['client_id'];
		} elseif (!empty($module_info)) {
			$data['vk']['client_id'] = $module_info['vk']['client_id'];
		} else {
			$data['vk']['client_id'] = '';
		}

		if (isset($this->request->post['vk']['client_secret'])) {
			$data['vk']['client_secret'] = $this->request->post['vk']['client_secret'];
		} elseif (!empty($module_info)) {
			$data['vk']['client_secret'] = $module_info['vk']['client_secret'];
		} else {
			$data['vk']['client_secret'] = '';
		}

		if (isset($this->request->post['vk']['status'])) {
			$data['vk']['status'] = (int)$this->request->post['vk']['status'];
		} elseif (!empty($module_info)) {
			$data['vk']['status'] = (int)$module_info['vk']['status'];
		} else {
			$data['vk']['status'] = 0;
		}

		if (isset($this->request->post['ok']['client_id'])) {
			$data['ok']['client_id'] = $this->request->post['ok']['client_id'];
		} elseif (!empty($module_info)) {
			$data['ok']['client_id'] = $module_info['ok']['client_id'];
		} else {
			$data['ok']['client_id'] = '';
		}

		if (isset($this->request->post['ok']['public_key'])) {
			$data['ok']['public_key'] = $this->request->post['ok']['public_key'];
		} elseif (!empty($module_info)) {
			$data['ok']['public_key'] = $module_info['ok']['public_key'];
		} else {
			$data['ok']['public_key'] = '';
		}

		if (isset($this->request->post['ok']['client_secret'])) {
			$data['ok']['client_secret'] = $this->request->post['ok']['client_secret'];
		} elseif (!empty($module_info)) {
			$data['ok']['client_secret'] = $module_info['ok']['client_secret'];
		} else {
			$data['ok']['client_secret'] = '';
		}

		if (isset($this->request->post['ok']['status'])) {
			$data['ok']['status'] = (int)$this->request->post['ok']['status'];
		} elseif (!empty($module_info)) {
			$data['ok']['status'] = (int)$module_info['ok']['status'];
		} else {
			$data['ok']['status'] = 0;
		}

		if (isset($this->request->post['fb']['client_id'])) {
			$data['fb']['client_id'] = $this->request->post['fb']['client_id'];
		} elseif (!empty($module_info)) {
			$data['fb']['client_id'] = $module_info['fb']['client_id'];
		} else {
			$data['fb']['client_id'] = '';
		}

		if (isset($this->request->post['fb']['client_secret'])) {
			$data['fb']['client_secret'] = $this->request->post['fb']['client_secret'];
		} elseif (!empty($module_info)) {
			$data['fb']['client_secret'] = $module_info['fb']['client_secret'];
		} else {
			$data['fb']['client_secret'] = '';
		}

		if (isset($this->request->post['fb']['status'])) {
			$data['fb']['status'] = (int)$this->request->post['fb']['status'];
		} elseif (!empty($module_info)) {
			$data['fb']['status'] = (int)$module_info['fb']['status'];
		} else {
			$data['fb']['status'] = 0;
		}

		if (isset($this->request->post['gg']['client_id'])) {
			$data['gg']['client_id'] = $this->request->post['gg']['client_id'];
		} elseif (!empty($module_info)) {
			$data['gg']['client_id'] = $module_info['gg']['client_id'];
		} else {
			$data['gg']['client_id'] = '';
		}

		if (isset($this->request->post['gg']['client_secret'])) {
			$data['gg']['client_secret'] = $this->request->post['gg']['client_secret'];
		} elseif (!empty($module_info)) {
			$data['gg']['client_secret'] = $module_info['gg']['client_secret'];
		} else {
			$data['gg']['client_secret'] = '';
		}

		if (isset($this->request->post['gg']['status'])) {
			$data['gg']['status'] = (int)$this->request->post['gg']['status'];
		} elseif (!empty($module_info)) {
			$data['gg']['status'] = (int)$module_info['gg']['status'];
		} else {
			$data['gg']['status'] = 0;
		}

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('extension/module/sv_social_auth', $data));
	}

	protected function validate() {
		if (!$this->user->hasPermission('modify', 'extension/module/sv_social_auth')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		return !$this->error;
	}
}
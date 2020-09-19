<?php
class ControllerCatalogFilials extends Controller {
	private $error = array();
	
	public function index() {
		$this->language->load('catalog/filials');
		
		$this->load->model('catalog/filials');
		
		$this->document->setTitle('Филиалы');
		
		$url = '';
		
		if (isset($this->request->get['page'])) {
			$url .= '&page=' . $this->request->get['page'];
		}
		
		$data['breadcrumbs'] = array();

   		$data['breadcrumbs'][] = array(
       		'text'      => $this->language->get('text_home'),
			'href'      => $this->url->link('common/home', 'user_token=' . $this->session->data['user_token'], true)
		);

   		$data['breadcrumbs'][] = array(
       		'text'      => $this->language->get('heading_title'),
			'href'      => $this->url->link('catalog/filials', 'user_token=' . $this->session->data['user_token'] . $url, true)
   		);
		
		if (isset($this->session->data['success'])) {
			$data['success'] = $this->session->data['success'];
		
			unset($this->session->data['success']);
		} else {
			$data['success'] = '';
		}

		if (isset($this->error['warning'])) {
			$data['error'] = $this->error['warning'];
		
			unset($this->error['warning']);
		} else {
			$data['error'] = '';
		}
		
		if (isset($this->request->get['page'])) {
			$page = $this->request->get['page'];
		} else { 
			$page = 1;
		}
		
		$url = '';
		
		$filter_data = array(
			'page' => $page,
			'limit' => $this->config->get('config_limit_admin'),
			'start' => $this->config->get('config_limit_admin') * ($page - 1),
		);
		
		$total = $this->model_catalog_filials->getTotalFilials();
		
		$pagination = new Pagination();
		$pagination->total = $total;
		$pagination->page = $page;
		$pagination->limit = $this->config->get('config_limit_admin');
		$pagination->url = $this->url->link('catalog/filials', 'user_token=' . $this->session->data['user_token'] . $url . '&page={page}', true);

		$data['pagination'] = $pagination->render();

		$data['results'] = sprintf($this->language->get('text_pagination'), ($total) ? (($page - 1) * $this->config->get('config_limit_admin')) + 1 : 0, ((($page - 1) * $this->config->get('config_limit_admin')) > ($total - $this->config->get('config_limit_admin'))) ? $total : ((($page - 1) * $this->config->get('config_limit_admin')) + $this->config->get('config_limit_admin')), $total, ceil($total / $this->config->get('config_limit_admin')));

		$data['heading_title'] = 'Филиалы';
		
		$data['text_title'] = $this->language->get('text_title');
		$data['text_short_description'] = $this->language->get('text_short_description');
		$data['text_date'] = $this->language->get('text_date');
		$data['text_action'] = $this->language->get('text_action');
		$data['text_edit'] = $this->language->get('text_edit');
		$data['text_list'] = $this->language->get('text_list');
		$data['text_no_results'] = $this->language->get('text_no_results');
		$data['text_confirm'] = $this->language->get('text_confirm');
		
		$data['button_add'] = $this->language->get('button_add');
		$data['button_delete'] = $this->language->get('button_delete');
		
		$url = '';
		
		if (isset($this->request->get['page'])) {
			$url .= '&page=' . $this->request->get['page'];
		}
		
		$data['add'] = $this->url->link('catalog/filials/insert', '&user_token=' . $this->session->data['user_token'] . $url, true);
		$data['delete'] = $this->url->link('catalog/filials/delete', 'user_token=' . $this->session->data['user_token'] . $url, true);
		
		$data['all_filials'] = array();
		
		$all_filials = $this->model_catalog_filials->getAllFilials($filter_data);
		
		foreach ($all_filials as $filials) {
			$zone = $this->model_catalog_filials->getFilialsZoneName($filials['zone_id']);

			$data['all_filials'][] = array (
				'filials_id' 		=> $filials['filials_id'],
				'title' 			=> $filials['title'],
				'zone_name' 		=> $zone['name'],
				'date_added' 		=> date($this->language->get('date_format_short'), strtotime($filials['date_added'])),
				'edit' 				=> $this->url->link('catalog/filials/edit', 'filials_id=' . $filials['filials_id'] . '&user_token=' . $this->session->data['user_token'] . $url, true)
			);
		}
		$data['user_token']=$this->session->data['user_token'];
		
		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('catalog/filials_list', $data));	
	}
	
	public function edit() {
		$this->language->load('catalog/filials');
		
		$this->load->model('catalog/filials');
		
		$this->document->setTitle('Филиалы');
		
		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
			$this->model_catalog_filials->editFilials($this->request->get['filials_id'], $this->request->post);		
			
			$this->session->data['success'] = $this->language->get('text_success');
						
			$this->response->redirect($this->url->link('catalog/filials', 'user_token=' . $this->session->data['user_token'], true));
		}
		
		$this->form();
	}
	
	public function insert() {
		$this->language->load('catalog/filials');
		
		$this->load->model('catalog/filials');
		
		$this->document->setTitle('Филиалы');
		
		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
			$this->model_catalog_filials->addFilials($this->request->post);		
			
			$this->session->data['success'] = $this->language->get('text_success');
						
			$this->response->redirect($this->url->link('catalog/filials', 'user_token=' . $this->session->data['user_token'], true));
		}

		$this->form();
	}
	
	protected function form() {
		$this->language->load('catalog/filials');
		
		$this->load->model('catalog/filials');
		
		$data['breadcrumbs'] = array();

   		$data['breadcrumbs'][] = array(
       		'text'      => $this->language->get('text_home'),
			'href'      => $this->url->link('common/home', 'user_token=' . $this->session->data['user_token'], true),
      		'separator' => false
   		);

   		$data['breadcrumbs'][] = array(
       		'text'      => 'Филиалы',
			'href'      => $this->url->link('catalog/filials', 'user_token=' . $this->session->data['user_token'], true),
      		'separator' => ' :: '
   		);
		
		if (isset($this->request->get['filials_id'])) {
			$data['action'] = $this->url->link('catalog/filials/edit', '&filials_id=' . $this->request->get['filials_id'] . '&user_token=' . $this->session->data['user_token'], true);
		} else {
			$data['action'] = $this->url->link('catalog/filials/insert', '&user_token=' . $this->session->data['user_token'], true);
		}
		
		$data['cancel'] = $this->url->link('catalog/filials', '&user_token=' . $this->session->data['user_token'], true);
		
		$data['heading_title'] = 'Филиалы';
		
		$data['text_image'] = $this->language->get('text_image');
		$data['text_title'] = $this->language->get('text_title');
		$data['text_description'] = $this->language->get('text_description');
		$data['text_short_description'] = $this->language->get('text_short_description');
		$data['text_status'] = $this->language->get('text_status');
		$data['text_keyword'] = $this->language->get('text_keyword');
		$data['text_enabled'] = $this->language->get('text_enabled');
		$data['text_disabled'] = $this->language->get('text_disabled');
		$data['text_browse'] = $this->language->get('text_browse');
		$data['text_clear'] = $this->language->get('text_clear');
		$data['text_image_manager'] = $this->language->get('text_image_manager');
		
		$data['button_save'] = $this->language->get('button_save');
		$data['button_cancel'] = $this->language->get('button_cancel');
		
		$data['user_token'] = $this->session->data['user_token'];
		
		$this->load->model('localisation/language');
		
		$data['languages'] = $this->model_localisation_language->getLanguages();
		
		if (isset($this->error['warning'])) {
			$data['error'] = $this->error['warning'];
		} else {
			$data['error'] = '';
		}

		if (isset($this->error['title'])) {
			$data['error_title'] = $this->error['title'];
		} else {
			$data['error_title'] = array();
		}

		if (isset($this->error['description'])) {
			$data['error_description'] = $this->error['description'];
		} else {
			$data['error_description'] = array();
		}
		
		if (isset($this->request->get['filials_id'])) {
			$filials = $this->model_catalog_filials->getFilials($this->request->get['filials_id']);
		} else {
			$filials = array();
		}
		
		if (isset($this->request->post['filials_description'])) {
			$data['filials_description'] = $this->request->post['filials_description'];
		} elseif (!empty($filials)) {
			$data['filials_description'] = $this->model_catalog_filials->getFilialsDescription($this->request->get['filials_id']);
		} else {
			$data['filials_description'] = '';
		}
		
		if (isset($this->request->post['status'])) {
			$data['status'] = $this->request->post['status'];
		} elseif (!empty($filials)) {
			$data['status'] = $filials['status'];
		} else {
			$data['status'] = '';
		}

		$data['zones'] = $this->model_catalog_filials->getFilialsZone();

		if (isset($this->request->post['zone_id'])) {
			$data['zone_id'] = $this->request->post['zone_id'];
		} elseif (!empty($filials)) {
			$data['zone_id'] = $filials['zone_id'];
		} else {
			$data['zone_id'] = 83;
		}

		if (isset($this->request->post['sort_order'])) {
			$data['sort_order'] = $this->request->post['sort_order'];
		} elseif (!empty($filials)) {
			$data['sort_order'] = $filials['sort_order'];
		} else {
			$data['sort_order'] = '';
		}
		
		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('catalog/filials_form', $data));
	}
	
	public function delete() {
		$this->language->load('catalog/filials');
		
		$this->load->model('catalog/filials');

		$this->document->setTitle('Филиалы');
		
		if (isset($this->request->post['selected']) && $this->validateDelete()) {
			foreach ($this->request->post['selected'] as $filials_id) {
				$this->model_catalog_filials->deleteFilials($filials_id);
			}

			$this->session->data['success'] = $this->language->get('text_success');
		}
		
		$this->response->redirect($this->url->link('catalog/filials', 'user_token=' . $this->session->data['user_token'], true));
	}

	protected function validateForm() {
		if (!$this->user->hasPermission('modify', 'catalog/news')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		foreach ($this->request->post['filials_description'] as $language_id => $value) {
			if ((utf8_strlen($value['title']) < 1) || (utf8_strlen($value['title']) > 255)) {
				$this->error['title'][$language_id] = $this->language->get('error_title');
			}

			if (utf8_strlen($value['description']) < 3) {
				$this->error['description'][$language_id] = $this->language->get('error_description');
			}
		}

		if ($this->error && !isset($this->error['warning'])) {
			$this->error['warning'] = $this->language->get('error_warning');
		}

		return !$this->error;
	}
	
	protected function validateDelete() {
		if (!$this->error) {
			return true; 
		} else {
			return false;
		}
	}
	
	protected function validate() {
		if (!$this->error) {
			return true;
		} else {
			return false;
		}	
	}
}
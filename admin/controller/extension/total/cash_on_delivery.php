<?php
class ControllerExtensionTotalCashOnDelivery extends Controller {
	private $error = array();

	public function index() {
		$this->load->language('extension/total/cash_on_delivery');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('setting/setting');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			$this->model_setting_setting->editSetting('total_cash_on_delivery', $this->request->post);

			$this->session->data['success'] = $this->language->get('text_success');

			$this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=total', true));
		}

		$data['heading_title'] = $this->language->get('heading_title');

		$data['text_edit'] = $this->language->get('text_edit');
		$data['text_enabled'] = $this->language->get('text_enabled');
		$data['text_disabled'] = $this->language->get('text_disabled');
		$data['text_none'] = $this->language->get('text_none');
		$data['text_percentage'] = $this->language->get('text_percentage');
		$data['text_fixed'] = $this->language->get('text_fixed');

		$data['entry_fee_type'] = $this->language->get('entry_fee_type');
		$data['entry_fee'] = $this->language->get('entry_fee');
		$data['entry_tax_class'] = $this->language->get('entry_tax_class');
		$data['entry_status'] = $this->language->get('entry_status');
		$data['entry_sort_order'] = $this->language->get('entry_sort_order');

		$data['help_fee'] = $this->language->get('help_fee');

		$data['button_save'] = $this->language->get('button_save');
		$data['button_cancel'] = $this->language->get('button_cancel');

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
			'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=total', true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('extension/total/cash_on_delivery', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['action'] = $this->url->link('extension/total/cash_on_delivery', 'user_token=' . $this->session->data['user_token'], true);

		$data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=total', true);
	
		if (isset($this->request->post['total_cash_on_delivery_type'])) {
			$data['total_cash_on_delivery_type'] = $this->request->post['total_cash_on_delivery_type'];
		} else {
			$data['total_cash_on_delivery_type'] = $this->config->get('total_cash_on_delivery_type');
		}
	
		if (isset($this->request->post['total_cash_on_delivery_fee'])) {
			$data['total_cash_on_delivery_fee'] = $this->request->post['total_cash_on_delivery_fee'];
		} else {
			$data['total_cash_on_delivery_fee'] = $this->config->get('total_cash_on_delivery_fee');
		}

		if (isset($this->request->post['total_cash_on_delivery_tax_class_id'])) {
			$data['total_cash_on_delivery_tax_class_id'] = $this->request->post['total_cash_on_delivery_tax_class_id'];
		} else {
			$data['total_cash_on_delivery_tax_class_id'] = $this->config->get('total_cash_on_delivery_tax_class_id');
		}

		$this->load->model('localisation/tax_class');
		$data['tax_classes'] = $this->model_localisation_tax_class->getTaxClasses();

		if (isset($this->request->post['total_cash_on_delivery_status'])) {
			$data['total_cash_on_delivery_status'] = $this->request->post['total_cash_on_delivery_status'];
		} else {
			$data['total_cash_on_delivery_status'] = $this->config->get('total_cash_on_delivery_status');
		}

		if (isset($this->request->post['total_cash_on_delivery_sort_order'])) {
			$data['total_cash_on_delivery_sort_order'] = $this->request->post['total_cash_on_delivery_sort_order'];
		} else {
			$data['total_cash_on_delivery_sort_order'] = $this->config->get('total_cash_on_delivery_sort_order');
		}

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('extension/total/cash_on_delivery', $data));
	}

	protected function validate() {
		if (!$this->user->hasPermission('modify', 'extension/total/cash_on_delivery')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}
		
		if($this->request->post['total_cash_on_delivery_type'] == 'P' && $this->request->post['total_cash_on_delivery_fee'] > 100) {
			$this->error['warning'] = $this->language->get('error_fee_amount');
		}
		
		return !$this->error;
	}
}

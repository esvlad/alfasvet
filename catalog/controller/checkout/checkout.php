<?php
class ControllerCheckoutCheckout extends Controller {
	public function index() {
		// Validate cart has products and has stock.
		if ((!$this->cart->hasProducts() && empty($this->session->data['vouchers'])) || (!$this->cart->hasStock() && !$this->config->get('config_stock_checkout'))) {
			$this->response->redirect($this->url->link('checkout/cart'));
		}

		// Validate minimum quantity requirements.
		$products = $this->cart->getProducts();

		foreach ($products as $product) {
			$product_total = 0;

			foreach ($products as $product_2) {
				if ($product_2['product_id'] == $product['product_id']) {
					$product_total += $product_2['quantity'];
				}
			}

			if ($product['minimum'] > $product_total) {
				$this->response->redirect($this->url->link('checkout/cart'));
			}
		}

		$this->load->language('checkout/checkout');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->document->addScript('catalog/view/javascript/jquery/datetimepicker/moment/moment.min.js');
		$this->document->addScript('catalog/view/javascript/jquery/datetimepicker/moment/moment-with-locales.min.js');
		$this->document->addScript('catalog/view/javascript/jquery/datetimepicker/bootstrap-datetimepicker.min.js');
		$this->document->addStyle('catalog/view/javascript/jquery/datetimepicker/bootstrap-datetimepicker.min.css');

		// Required by klarna
		if ($this->config->get('payment_klarna_account') || $this->config->get('payment_klarna_invoice')) {
			$this->document->addScript('http://cdn.klarna.com/public/kitt/toc/v1.0/js/klarna.terms.min.js');
		}

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/home')
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_cart'),
			'href' => $this->url->link('checkout/cart')
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('checkout/checkout', '', true)
		);

		$data['text_checkout_option'] = sprintf($this->language->get('text_checkout_option'), 1);
		$data['text_checkout_account'] = sprintf($this->language->get('text_checkout_account'), 2);
		$data['text_checkout_payment_address'] = sprintf($this->language->get('text_checkout_payment_address'), 2);
		$data['text_checkout_shipping_address'] = sprintf($this->language->get('text_checkout_shipping_address'), 3);
		$data['text_checkout_shipping_method'] = sprintf($this->language->get('text_checkout_shipping_method'), 4);
		
		if ($this->cart->hasShipping()) {
			$data['text_checkout_payment_method'] = sprintf($this->language->get('text_checkout_payment_method'), 5);
			$data['text_checkout_confirm'] = sprintf($this->language->get('text_checkout_confirm'), 6);
		} else {
			$data['text_checkout_payment_method'] = sprintf($this->language->get('text_checkout_payment_method'), 3);
			$data['text_checkout_confirm'] = sprintf($this->language->get('text_checkout_confirm'), 4);	
		}

		if (isset($this->session->data['error'])) {
			$data['error_warning'] = $this->session->data['error'];
			unset($this->session->data['error']);
		} else {
			$data['error_warning'] = '';
		}

		$data['logged'] = $this->customer->isLogged();

		if (isset($this->session->data['account'])) {
			$data['account'] = $this->session->data['account'];
		} else {
			$data['account'] = '';
		}

		$data['shipping_required'] = $this->cart->hasShipping();

		$data['column_left'] = $this->load->controller('common/column_left');
		$data['column_right'] = $this->load->controller('common/column_right');
		$data['content_top'] = $this->load->controller('common/content_top');
		$data['content_bottom'] = $this->load->controller('common/content_bottom');
		$data['footer'] = $this->load->controller('common/footer');
		$data['header'] = $this->load->controller('common/header');

		$this->response->setOutput($this->load->view('checkout/checkout', $data));
	}

	public function country() {
		$json = array();

		$this->load->model('localisation/country');

		$country_info = $this->model_localisation_country->getCountry($this->request->get['country_id']);

		if ($country_info) {
			$this->load->model('localisation/zone');

			$json = array(
				'country_id'        => $country_info['country_id'],
				'name'              => $country_info['name'],
				'iso_code_2'        => $country_info['iso_code_2'],
				'iso_code_3'        => $country_info['iso_code_3'],
				'address_format'    => $country_info['address_format'],
				'postcode_required' => $country_info['postcode_required'],
				'zone'              => $this->model_localisation_zone->getZonesByCountryId($this->request->get['country_id']),
				'status'            => $country_info['status']
			);
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function customfield() {
		$json = array();

		$this->load->model('account/custom_field');

		// Customer Group
		if (isset($this->request->get['customer_group_id']) && is_array($this->config->get('config_customer_group_display')) && in_array($this->request->get['customer_group_id'], $this->config->get('config_customer_group_display'))) {
			$customer_group_id = $this->request->get['customer_group_id'];
		} else {
			$customer_group_id = $this->config->get('config_customer_group_id');
		}

		$custom_fields = $this->model_account_custom_field->getCustomFields($customer_group_id);

		foreach ($custom_fields as $custom_field) {
			$json[] = array(
				'custom_field_id' => $custom_field['custom_field_id'],
				'required'        => $custom_field['required']
			);
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function order(){
		$json = array();

		if($this->cart->hasShipping()){
			$json['data'] = $this->request->post;
			
			$this->load->model('checkout/order');
			$order_data = array();

			$totals = array();
			$taxes = $this->cart->getTaxes();
			$total = 0;

			// Because __call can not keep var references so we put them into an array.
			$total_data = array(
				'totals' => &$totals,
				'taxes'  => &$taxes,
				'total'  => &$total
			);

			$this->load->model('setting/extension');

			$sort_order = array();

			$results = $this->model_setting_extension->getExtensions('total');

			foreach ($results as $key => $value) {
				$sort_order[$key] = $this->config->get($value['code'] . '_sort_order');
			}

			array_multisort($sort_order, SORT_ASC, $results);

			foreach ($results as $result) {
				if ($this->config->get($result['code'] . '_status')) {
					$this->load->model('extension/total/' . $result['code']);

					// We have to put the totals in an array so that they pass by reference.
					$this->{'model_extension_total_' . $result['code']}->getTotal($total_data);
				}
			}

			$sort_order = array();

			foreach ($totals as $key => $value) {
				$sort_order[$key] = $value['sort_order'];
			}

			array_multisort($sort_order, SORT_ASC, $totals);

			$order_data['totals'] = $totals;

			$this->load->language('checkout/checkout');

			$order_data['invoice_prefix'] = $this->config->get('config_invoice_prefix');
			$order_data['store_id'] = $this->config->get('config_store_id');
			$order_data['store_name'] = $this->config->get('config_name');

			if ($order_data['store_id']) {
				$order_data['store_url'] = $this->config->get('config_url');
			} else {
				if ($this->request->server['HTTPS']) {
					$order_data['store_url'] = HTTPS_SERVER;
				} else {
					$order_data['store_url'] = HTTP_SERVER;
				}
			}

			$order_data['firstname'] = $this->request->post['name'];
			$order_data['telephone'] = $this->request->post['phone'];
			$order_data['email'] = $this->request->post['email'];

			#CUSTOM_FIELD
			$custom_field = [];
			if(!empty($this->request->post['file_code'])){
				#array_push($custom_field, 1 => $this->request->post['date']);
				$custom_field += [1 => $this->request->post['file_code']];
				$order_data['rekviz'] = $this->url->link('tool/upload/rekviz', 'code=' . $this->request->post['file_code']);
			}
			if(!empty($this->request->post['inn'])){
				#array_push($custom_field, 1 => $this->request->post['date']);
				$custom_field += [2 => $this->request->post['inn']];
				$order_data['inn'] = $this->request->post['inn'];
			}
			if(!empty($this->request->post['kv'])){
				#array_push($custom_field, 1 => $this->request->post['date']);
				$custom_field += [3 => $this->request->post['kv']];
			}
			if(!empty($this->request->post['user_type'])){
				#array_push($custom_field, 1 => $this->request->post['date']);
				$user_type = 'Физ.лицо';
				if($this->request->post['user_type'] == 2){
					$user_type = 'Компания';
				}

				$custom_field += [4 => $user_type];
				$order_data['user_type'] = $user_type;
			}

			$order_data['custom_field'] = array();
			$order_data['shipping_custom_field'] = $custom_field;

			//Метод оплаты
			if($this->request->post['payment_method'] == 'cod'){
				$order_data['payment_method'] = 'Наличными при получении';
			} elseif($this->request->post['payment_method'] == 'cheque'){
				$order_data['payment_method'] = 'Банковской картой на сайте';
			} else{
				$order_data['payment_method'] = 'Банковский перевод';
			}

			$order_data['payment_code'] = $this->request->post['payment_method'];

			//Метод доставки
			if($this->request->post['shipping_method'] == 'pickup'){
				$order_data['shipping_method'] = 'Транспортной компанией';
				$order_data['shipping_code'] = 'pickup';
			} else {
				$order_data['shipping_method'] = 'Самовывоз';
				$order_data['shipping_code'] = 'free';
			}

			if(!empty($this->request->post['address_1'])){
				$address = $this->request->post['postcode'];
				$address .= ', ' . $this->request->post['zone'];
				$address .= ', ' . $this->request->post['city'];
				$address .= ', ' . $this->request->post['address_1'];
				$address .= ', ' . $this->request->post['kv'];
				
				$order_data['shipping_city'] = $this->request->post['city'];
				$order_data['shipping_address_1'] = $this->request->post['address_1'];
				$order_data['shipping_postcode'] = $this->request->post['postcode'];
				$order_data['shipping_zone'] = $this->request->post['zone'];

				$order_data['shipping_address_format'] = $address;
				$this->session->data['kv'] = $this->request->post['kv'];
			} else {
				$order_data['shipping_address_format'] = '';
				$order_data['shipping_city'] = '';
				$order_data['shipping_address_1'] = '';
				$order_data['shipping_postcode'] = '';
				$order_data['shipping_zone'] = '';
				$this->session->data['kv'] = '';
			}

			if(isset($this->request->post['inn'])){
				$this->session->data['inn'] = $this->request->post['inn'];
			}

			//SESSION
			$this->session->data['name'] = $this->request->post['name'];
			$this->session->data['phone'] = $this->request->post['phone'];
			$this->session->data['email'] = $this->request->post['email'];
			$this->session->data['payment_method'] = $this->request->post['payment_method'];
			$this->session->data['shipping_method'] = $this->request->post['shipping_method'];
			$this->session->data['postcode'] = $order_data['shipping_postcode'];
			$this->session->data['zone'] = $order_data['shipping_zone'];
			$this->session->data['city'] = $order_data['shipping_city'];
			$this->session->data['kv'] = $this->request->post['kv'];
			$this->session->data['address_1'] = $order_data['shipping_address_1'];
			

			//PRODUCTS
			$order_data['products'] = array();
			foreach ($this->cart->getProducts() as $product) {
				$option_data = array();

				foreach ($product['option'] as $option) {
					$option_data[] = array(
						'product_option_id'       => $option['product_option_id'],
						'product_option_value_id' => $option['product_option_value_id'],
						'option_id'               => $option['option_id'],
						'option_value_id'         => $option['option_value_id'],
						'name'                    => $option['name'],
						'value'                   => $option['value'],
						'type'                    => $option['type']
					);
				}

				$order_data['products'][] = array(
					'product_id' => $product['product_id'],
					'name'       => $product['name'],
					'model'      => $product['model'],
					'option'     => $option_data,
					'download'   => $product['download'],
					'quantity'   => $product['quantity'],
					'subtract'   => $product['subtract'],
					'price'      => $product['price'],
					'total'      => $product['total'],
					'tax'        => $this->tax->getTax($product['price'], $product['tax_class_id']),
					'reward'     => $product['reward']
				);
			}

			$order_data['comment'] = '';
			$order_data['total'] = $total_data['total'];
			$order_data['ip'] = $this->request->server['REMOTE_ADDR'];

			//$total_data['total']

			if (isset($this->request->server['HTTP_USER_AGENT'])) {
				$order_data['user_agent'] = $this->request->server['HTTP_USER_AGENT'];
			} else {
				$order_data['user_agent'] = '';
			}

			if (isset($this->request->server['HTTP_ACCEPT_LANGUAGE'])) {
				$order_data['accept_language'] = $this->request->server['HTTP_ACCEPT_LANGUAGE'];
			} else {
				$order_data['accept_language'] = '';
			}

			if ($this->customer->isLogged()) {
				$this->load->model('account/customer');
				$customer_info = $this->model_account_customer->getCustomer($this->customer->getId());

				$order_data['customer_id'] = $this->customer->getId();
				$order_data['customer_group_id'] = $customer_info['customer_group_id'];
				$order_data['custom_field'] = json_decode($customer_info['custom_field'], true);
			} else {
				$order_data['customer_id'] = 0;
				$order_data['customer_group_id'] = 0;
				$order_data['custom_field'] = [];
			}

			$ttotal = $this->request->post['total'];
			$reward_pay = $this->request->post['reward_pay'];

			$order_data['totals']['sub_total'] = [
				'code' => 'sub_total',
				'title' => 'Подварительная стоимость',
				'value' => $ttotal,
				'sort_order' => 1
			];
			$order_data['totals']['reward'] = [
				'code' => 'reward',
				'title' => 'Списание бонусов',
				'value' => $reward_pay,
				'sort_order' => 2
			];
			$order_data['totals']['total'] = [
				'code' => 'total',
				'title' => 'Итого',
				'value' => ($ttotal - $reward_pay),
				'sort_order' => 3
			];

			$order_data['total'] = $this->request->post['total'];

			$order_id = $this->model_checkout_order->newOrder($order_data);
			//
			//$json['redirect'] = $this->url->link('checkout/success');
		}

		if(!empty($order_id)){
			$this->response->redirect($this->url->link('checkout/success', '&order_id=' . $order_id, true));
		} else {
			$this->response->addHeader('Content-Type: application/json');
			$this->response->setOutput(json_encode($json));
		}
	}
}
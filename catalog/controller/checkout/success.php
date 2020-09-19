<?php
class ControllerCheckoutSuccess extends Controller {
	public function index() {
		$data = [];
		$this->load->language('checkout/success');

		if (isset($this->session->data['order_id'])) {
			$this->cart->clear();

			unset($this->session->data['shipping_method']);
			unset($this->session->data['shipping_methods']);
			unset($this->session->data['payment_method']);
			unset($this->session->data['payment_methods']);
			unset($this->session->data['guest']);
			unset($this->session->data['comment']);
			unset($this->session->data['coupon']);
			unset($this->session->data['voucher']);
			unset($this->session->data['vouchers']);
			unset($this->session->data['totals']);

			unset($this->session->data['name']);
			unset($this->session->data['phone']);
			unset($this->session->data['email']);
			unset($this->session->data['payment_method']);
			unset($this->session->data['shipping_method']);
			unset($this->session->data['postcode']);
			unset($this->session->data['zone']);
			unset($this->session->data['city']);
			unset($this->session->data['address_1']);
			unset($this->session->data['kv']);
		}

		unset($this->session->data['reward']);
		unset($this->session->data['reward_pay']);

		$this->document->setTitle($this->language->get('heading_title'));

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => 'Главная',
			'href' => $this->url->link('common/home')
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_success'),
			'href' => $this->url->link('checkout/success')
		);

		if(isset($this->request->get['order_id'])){
			$data['order_id'] = $this->request->get['order_id'];

			$this->load->model('checkout/order');
			$order = $data['order'] = $this->model_checkout_order->getOrder($data['order_id']);
			$products = $this->model_checkout_order->getOrderProducts($data['order_id']);
			$data['totals'] = $this->model_checkout_order->getOrderTotals($data['order_id']);

			$products = $this->model_checkout_order->getOrderProducts($data['order_id']);
			$data['products'] = [];
			$all_totals = 0;

			foreach($products as $product){
				$product_total = 0;

				foreach ($products as $product_2) {
					if ($product_2['product_id'] == $product['product_id']) {
						$product_total += $product_2['quantity'];
					}
				}

				$option_data = array();
				$options = $this->model_checkout_order->getOrderOptions($data['order_id'], $product['order_product_id']);

				foreach ($options as $option) {
					$option_data[] = array(
						'name'  => $option['name'],
						'value' => (utf8_strlen($option['value']) > 20 ? utf8_substr($option['value'], 0, 20) . '..' : $option['value'])
					);
				}

				// Display prices
				if ($this->customer->isLogged() || !$this->config->get('config_customer_price')) {
					$unit_price = $this->tax->calculate($product['price'], 9, $this->config->get('config_tax'));
					
					$price = $this->currency->format($unit_price, $this->session->data['currency']);
					$total = $this->currency->format($unit_price * $product['quantity'], $this->session->data['currency']);

					$all_totals = $all_totals + ($unit_price * $product['quantity']);
				} else {
					$price = false;
					$total = false;
				}

				$data['products'][] = array(
					'id'   => $product['product_id'],
					'name'      => $product['name'],
					'option'    => $option_data,
					'quantity'  => $product['quantity'],
					'reward'    => ($product['reward'] ? $product['reward'] : ''),
					'price'     => $price,
					'total'     => $total
				);
			}

			$data['totals'] = $total = $this->currency->format($all_totals, $this->session->data['currency']);
			

			$data['name'] = isset($order['firstname']) ? $order['firstname'] : '';
			$data['phone'] = isset($order['telephone']) ? $order['telephone'] : '';
			$data['email'] = isset($order['email']) ? $order['email'] : '';
			$data['payment_method'] = isset($order['payment_method']) ? $order['payment_method'] : '';
			$data['payment_code'] = isset($order['payment_code']) ? $order['payment_code'] : '';
			$data['shipping_method'] = isset($order['shipping_method']) ? $order['shipping_method'] : '';
			$data['shipping_code'] = isset($order['shipping_code']) ? $order['shipping_code'] : '';
			$data['shipping_postcode'] = isset($order['shipping_postcode']) ? $order['shipping_postcode'] : '';
			$data['shipping_zone'] = isset($order['shipping_zone']) ? $order['shipping_zone'] : '';
			$data['shipping_city'] = isset($order['shipping_city']) ? $order['shipping_city'] : '';
			$data['shipping_address_1'] = isset($order['shipping_address_1']) ? $order['shipping_address_1'] : '';


			if(!empty($order['shipping_custom_field'])){
				foreach($order['shipping_custom_field'] as $key => $custom_field){
					if($key == 4){
						$data['user_type'] = $custom_field;
					}

					if($key == 2){
						$data['inn'] = $custom_field;
					}

					if($key == 3){
						$data['kvartira'] = $custom_field;
					}

					if($key == 1){
						$data['file_code'] = $custom_field;
						
						$this->load->model('tool/upload');
						$upload = $this->model_tool_upload->getUploadByCode($custom_field);
						$data['file_name'] = $upload['origin_name'];
					}
				}
			}			

			/*echo '<pre>';
			print_r($data['products']);
			echo '</pre>';*/
		}

		if(empty($data['file_code'])) $data['file_code'] = '';

		if (!$this->customer->isLogged()){
			$data['customer'] = 0;
		} else {
			$data['customer'] = 1;
		}

		$data['order'] = $this->url->link('account/order', '', true);

		$data['continue'] = $this->url->link('common/home');

		$data['politics'] = $this->url->link('information/information', 'information_id=3');
		$data['footer'] = $this->load->controller('common/footer');
		$data['header'] = $this->load->controller('common/header');

		$mb_detected = new MobileDetect;
		if($mb_detected->isMobile() && !$mb_detected->isTablet()){
			$data['mobile'] = 1;
		}

		$this->response->setOutput($this->load->view('common/success', $data));
	}

	public function registration(){
		$json = array();
		$this->load->model('account/customer');
		
		if($this->request->server['REQUEST_METHOD'] == 'POST'){
			$data = array();
			$field = array();

			$field['customer_group_id'] = 1;
			$field['email'] = $this->request->post['email'];
			$field['password'] = $this->request->post['password'];

			$field['firstname'] = '';
			$field['lastname'] = '';
			$field['telephone'] = '';

			$emailvalid = $this->model_account_customer->getCustomerByEmail($field['email']);

			if(empty($emailvalid)){
				$customer_id = $this->model_account_customer->addCustomer($field);

				$order_id = $this->request->post['order_id'];
				$this->load->model('checkout/order');
				$this->model_checkout_order->editOrderCustomerId($order_id, $customer_id);

				$json['customer_id'] = $customer_id;
				$json['success'] = true;
			} else {
				$json['errors'] = 'Такой e-mail уже есть!';
			}
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function order_edit(){
		$json = [];

		if($this->request->server['REQUEST_METHOD'] == 'POST'){
			$data = [];

			$order_id = $this->request->post['order_id'];

			$data['firstname'] = $this->request->post['name'];
			$data['telephone'] = $this->request->post['phone'];
			$data['email'] = $this->request->post['email'];

			$shipping_custom_field = [];
			if(isset($this->request->post['file_code'])){
				$shipping_custom_field[1] = $this->request->post['file_code'];
				$data['rekviz'] = $this->url->link('tool/upload/rekviz', 'code=' . $this->request->post['file_code']);
			}
			if(isset($this->request->post['inn'])){
				$shipping_custom_field[2] = $this->request->post['inn'];
				$data['inn'] = $this->request->post['inn'];
			}
			if(isset($this->request->post['kv'])){
				$shipping_custom_field[3] = $this->request->post['kv'];
			}
			if($this->request->post['user_type'] == 1){
				$shipping_custom_field[4] = 'Физ.лицо';
			} else {
				$shipping_custom_field[4] = 'Компания';
			}
			$data['user_type'] = $shipping_custom_field[4];

			$data['shipping_custom_field'] = $shipping_custom_field;
			$data['shipping_address_format'] = '';

			if(isset($this->request->post['postcode'])){
				$data['shipping_postcode'] = $this->request->post['postcode'];
				$data['shipping_address_format'] .= $data['shipping_postcode'];
			} else {
				$data['shipping_postcode'] = '';
			}

			if(isset($this->request->post['zone'])){
				$data['shipping_zone'] = $this->request->post['zone'];
				$data['shipping_address_format'] .= ', ' . $data['shipping_zone'];
			} else {
				$data['shipping_zone'] = '';
			}

			if(isset($this->request->post['city'])){
				$data['shipping_city'] = $this->request->post['city'];
				$data['shipping_address_format'] .= ', ' . $data['shipping_city'];
			} else {
				$data['shipping_city'] = '';
			}

			if(isset($this->request->post['address_1'])){
				$data['shipping_address_1'] = $this->request->post['address_1'];
				$data['shipping_address_format'] .= ', ' . $data['shipping_address_1'];
			} else {
				$data['shipping_address_1'] = '';
			}

			if(isset($this->request->post['kv'])){
				$data['shipping_address_format'] .= ', ' . $this->request->post['kv'];
			}

			$this->load->model('checkout/order');
			$order = $this->model_checkout_order->editNewOrder($order_id, $data);

			$json['success'] = $order;
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
}
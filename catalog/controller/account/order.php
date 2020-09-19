1<?php
class ControllerAccountOrder extends Controller {
	public function index() {
		$data = [];
		
		if (!$this->customer->isLogged()) {
			$this->session->data['redirect'] = $this->url->link('account/order', '', true);

			$this->response->redirect($this->url->link('account/login', '', true));
		}

		$this->load->language('account/order');

		$this->document->setTitle($this->language->get('heading_title'));
		$this->document->addScript('catalog/view/javascript/jquery/datetimepicker/moment/moment.min.js');
		$this->document->addScript('catalog/view/javascript/jquery/datetimepicker/moment/moment-with-locales.min.js');
		$this->document->addScript('catalog/view/javascript/jquery/datetimepicker/bootstrap-datetimepicker.min.js');
		$this->document->addStyle('catalog/view/javascript/jquery/datetimepicker/bootstrap-datetimepicker.min.css');

		$mb_detected = new MobileDetect;
		if($mb_detected->isMobile() && !$mb_detected->isTablet()){
			$data['mobile'] = 1;
		}
		
		$url = '';

		if (isset($this->request->get['page'])) {
			$url .= '&page=' . $this->request->get['page'];
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
			'href' => $this->url->link('account/order', $url, true)
		);

		if (isset($this->request->get['orders_filter'])) {
			$furl = $this->getFilterGroup($this->request->get['orders_filter']);
			$filter_orders = $this->request->get['orders_filter'];
		} else {
			$furl = '';
			$filter_orders = '';
		}

		if (isset($this->request->get['page'])) {
			$page = $this->request->get['page'];
		} else {
			$page = 1;
		}

		$limit = 10;

		$data['orders'] = array();

		$this->load->model('account/order');

		$order_total = $this->model_account_order->getTotalOrders($filter_orders);

		$results = $this->model_account_order->getOrders(($page - 1) * $limit, $limit, $filter_orders);

		foreach ($results as $result) {
			$product_total = $this->model_account_order->getTotalOrderProductsByOrderId($result['order_id']);
			$voucher_total = $this->model_account_order->getTotalOrderVouchersByOrderId($result['order_id']);

			$data['orders'][] = array(
				'order_id'   => $result['order_id'],
				'name'       => $result['firstname'],
				'quantity'   => $result['quantity'],
				'status_id'  => $result['status_id'],
				'status'     => $result['status'],
				'date_added' => date('d.m.y', strtotime($result['date_added'])),
				'products'   => ($product_total + $voucher_total),
				'total'      => $this->currency->format($result['total'], $result['currency_code'], $result['currency_value'])
			);
		}

		$pagination = new Pagination();
		$pagination->total = $order_total;
		$pagination->page = $page;
		$pagination->limit = $limit;
		$pagination->url = $this->url->link('account/order', 'page={page}', true) . $furl;

		$data['pagination'] = $pagination->render();

		/*$data['results'] = sprintf($this->language->get('text_pagination'), ($order_total) ? (($page - 1) * 10) + 1 : 0, ((($page - 1) * 10) > ($order_total - 10)) ? $order_total : ((($page - 1) * 10) + 10), $order_total, ceil($order_total / 10));*/

		$order_total_now = ((($page - 1) * $limit) > ($order_total - $limit)) ? $order_total : ((($page - 1) * $limit) + $limit);
		$data['results'] = '<p class="ptotals">Показано <span class="total_now">' . $order_total_now . '</span> из <span class="total">' . $order_total . '</span> товаров</p>';

		if($order_total_now < $order_total){
			$data['next_page'] = ($page + 1);
		}

		$data['continue'] = $this->url->link('account/account', '', true);

		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');
		$data['header'] = $this->load->controller('common/header');
		$data['page'] = 'history';
		$data['date_now'] = date('d.m.Y');

		$this->response->setOutput($this->load->view('account/order_list', $data));
	}

	public function info() {
		$this->load->language('account/order');

		if (isset($this->request->get['order_id'])) {
			$order_id = $this->request->get['order_id'];
		} else {
			$order_id = 0;
		}

		if (!$this->customer->isLogged()) {
			$this->session->data['redirect'] = $this->url->link('account/order/info', 'order_id=' . $order_id, true);

			$this->response->redirect($this->url->link('account/login', '', true));
		}

		$this->load->model('account/order');

		$order_info = $this->model_account_order->getOrder($order_id);

		if ($order_info) {
			$this->document->setTitle($this->language->get('text_order'));

			$url = '';

			if (isset($this->request->get['page'])) {
				$url .= '&page=' . $this->request->get['page'];
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
				'href' => $this->url->link('account/order', $url, true)
			);

			$data['breadcrumbs'][] = array(
				'text' => $this->language->get('text_order'),
				'href' => $this->url->link('account/order/info', 'order_id=' . $this->request->get['order_id'] . $url, true)
			);

			if (isset($this->session->data['error'])) {
				$data['error_warning'] = $this->session->data['error'];

				unset($this->session->data['error']);
			} else {
				$data['error_warning'] = '';
			}

			if (isset($this->session->data['success'])) {
				$data['success'] = $this->session->data['success'];

				unset($this->session->data['success']);
			} else {
				$data['success'] = '';
			}

			if ($order_info['invoice_no']) {
				$data['invoice_no'] = $order_info['invoice_prefix'] . $order_info['invoice_no'];
			} else {
				$data['invoice_no'] = '';
			}

			$data['order_id'] = $this->request->get['order_id'];
			$data['date_added'] = date($this->language->get('date_format_short'), strtotime($order_info['date_added']));

			if ($order_info['payment_address_format']) {
				$format = $order_info['payment_address_format'];
			} else {
				$format = '{firstname} {lastname}' . "\n" . '{company}' . "\n" . '{address_1}' . "\n" . '{address_2}' . "\n" . '{city} {postcode}' . "\n" . '{zone}' . "\n" . '{country}';
			}

			$find = array(
				'{firstname}',
				'{lastname}',
				'{company}',
				'{address_1}',
				'{address_2}',
				'{city}',
				'{postcode}',
				'{zone}',
				'{zone_code}',
				'{country}'
			);

			$replace = array(
				'firstname' => $order_info['payment_firstname'],
				'lastname'  => $order_info['payment_lastname'],
				'company'   => $order_info['payment_company'],
				'address_1' => $order_info['payment_address_1'],
				'address_2' => $order_info['payment_address_2'],
				'city'      => $order_info['payment_city'],
				'postcode'  => $order_info['payment_postcode'],
				'zone'      => $order_info['payment_zone'],
				'zone_code' => $order_info['payment_zone_code'],
				'country'   => $order_info['payment_country']
			);

			$data['payment_address'] = str_replace(array("\r\n", "\r", "\n"), '<br />', preg_replace(array("/\s\s+/", "/\r\r+/", "/\n\n+/"), '<br />', trim(str_replace($find, $replace, $format))));

			$data['payment_method'] = $order_info['payment_method'];

			if ($order_info['shipping_address_format']) {
				$format = $order_info['shipping_address_format'];
			} else {
				$format = '{firstname} {lastname}' . "\n" . '{company}' . "\n" . '{address_1}' . "\n" . '{address_2}' . "\n" . '{city} {postcode}' . "\n" . '{zone}' . "\n" . '{country}';
			}

			$find = array(
				'{firstname}',
				'{lastname}',
				'{company}',
				'{address_1}',
				'{address_2}',
				'{city}',
				'{postcode}',
				'{zone}',
				'{zone_code}',
				'{country}'
			);

			$replace = array(
				'firstname' => $order_info['shipping_firstname'],
				'lastname'  => $order_info['shipping_lastname'],
				'company'   => $order_info['shipping_company'],
				'address_1' => $order_info['shipping_address_1'],
				'address_2' => $order_info['shipping_address_2'],
				'city'      => $order_info['shipping_city'],
				'postcode'  => $order_info['shipping_postcode'],
				'zone'      => $order_info['shipping_zone'],
				'zone_code' => $order_info['shipping_zone_code'],
				'country'   => $order_info['shipping_country']
			);

			$data['shipping_address'] = str_replace(array("\r\n", "\r", "\n"), '<br />', preg_replace(array("/\s\s+/", "/\r\r+/", "/\n\n+/"), '<br />', trim(str_replace($find, $replace, $format))));

			$data['shipping_method'] = $order_info['shipping_method'];

			$this->load->model('catalog/product');
			$this->load->model('tool/upload');

			// Products
			$data['products'] = array();

			$products = $this->model_account_order->getOrderProducts($this->request->get['order_id']);

			foreach ($products as $product) {
				$option_data = array();

				$options = $this->model_account_order->getOrderOptions($this->request->get['order_id'], $product['order_product_id']);

				foreach ($options as $option) {
					if ($option['type'] != 'file') {
						$value = $option['value'];
					} else {
						$upload_info = $this->model_tool_upload->getUploadByCode($option['value']);

						if ($upload_info) {
							$value = $upload_info['name'];
						} else {
							$value = '';
						}
					}

					$option_data[] = array(
						'name'  => $option['name'],
						'value' => (utf8_strlen($value) > 20 ? utf8_substr($value, 0, 20) . '..' : $value)
					);
				}

				$product_info = $this->model_catalog_product->getProduct($product['product_id']);

				if ($product_info) {
					$reorder = $this->url->link('account/order/reorder', 'order_id=' . $order_id . '&order_product_id=' . $product['order_product_id'], true);
				} else {
					$reorder = '';
				}

				$data['products'][] = array(
					'name'     => $product['name'],
					'model'    => $product['model'],
					'option'   => $option_data,
					'quantity' => $product['quantity'],
					'price'    => $this->currency->format($product['price'] + ($this->config->get('config_tax') ? $product['tax'] : 0), $order_info['currency_code'], $order_info['currency_value']),
					'total'    => $this->currency->format($product['total'] + ($this->config->get('config_tax') ? ($product['tax'] * $product['quantity']) : 0), $order_info['currency_code'], $order_info['currency_value']),
					'reorder'  => $reorder,
					'return'   => $this->url->link('account/return/add', 'order_id=' . $order_info['order_id'] . '&product_id=' . $product['product_id'], true)
				);
			}

			// Voucher
			$data['vouchers'] = array();

			$vouchers = $this->model_account_order->getOrderVouchers($this->request->get['order_id']);

			foreach ($vouchers as $voucher) {
				$data['vouchers'][] = array(
					'description' => $voucher['description'],
					'amount'      => $this->currency->format($voucher['amount'], $order_info['currency_code'], $order_info['currency_value'])
				);
			}

			// Totals
			$data['totals'] = array();

			$totals = $this->model_account_order->getOrderTotals($this->request->get['order_id']);

			foreach ($totals as $total) {
				$data['totals'][] = array(
					'title' => $total['title'],
					'text'  => $this->currency->format($total['value'], $order_info['currency_code'], $order_info['currency_value']),
				);
			}

			$data['comment'] = nl2br($order_info['comment']);

			// History
			$data['histories'] = array();

			$results = $this->model_account_order->getOrderHistories($this->request->get['order_id']);

			foreach ($results as $result) {
				$data['histories'][] = array(
					'date_added' => date($this->language->get('date_format_short'), strtotime($result['date_added'])),
					'status'     => $result['status'],
					'comment'    => $result['notify'] ? nl2br($result['comment']) : ''
				);
			}

			$data['continue'] = $this->url->link('account/order', '', true);

			$data['column_left'] = $this->load->controller('common/column_left');
			$data['column_right'] = $this->load->controller('common/column_right');
			$data['content_top'] = $this->load->controller('common/content_top');
			$data['content_bottom'] = $this->load->controller('common/content_bottom');
			$data['footer'] = $this->load->controller('common/footer');
			$data['header'] = $this->load->controller('common/header');

			$this->response->setOutput($this->load->view('account/order_info', $data));
		} else {
			return new Action('error/not_found');
		}
	}

	public function reorder() {
		$this->load->language('account/order');

		if (isset($this->request->get['order_id'])) {
			$order_id = $this->request->get['order_id'];
		} else {
			$order_id = 0;
		}

		$this->load->model('account/order');

		$order_info = $this->model_account_order->getOrder($order_id);

		if ($order_info) {
			if (isset($this->request->get['order_product_id'])) {
				$order_product_id = $this->request->get['order_product_id'];
			} else {
				$order_product_id = 0;
			}

			$order_product_info = $this->model_account_order->getOrderProduct($order_id, $order_product_id);

			if ($order_product_info) {
				$this->load->model('catalog/product');

				$product_info = $this->model_catalog_product->getProduct($order_product_info['product_id']);

				if ($product_info) {
					$option_data = array();

					$order_options = $this->model_account_order->getOrderOptions($order_product_info['order_id'], $order_product_id);

					foreach ($order_options as $order_option) {
						if ($order_option['type'] == 'select' || $order_option['type'] == 'radio' || $order_option['type'] == 'image') {
							$option_data[$order_option['product_option_id']] = $order_option['product_option_value_id'];
						} elseif ($order_option['type'] == 'checkbox') {
							$option_data[$order_option['product_option_id']][] = $order_option['product_option_value_id'];
						} elseif ($order_option['type'] == 'text' || $order_option['type'] == 'textarea' || $order_option['type'] == 'date' || $order_option['type'] == 'datetime' || $order_option['type'] == 'time') {
							$option_data[$order_option['product_option_id']] = $order_option['value'];
						} elseif ($order_option['type'] == 'file') {
							$option_data[$order_option['product_option_id']] = $this->encryption->encrypt($this->config->get('config_encryption'), $order_option['value']);
						}
					}

					$this->cart->add($order_product_info['product_id'], $order_product_info['quantity'], $option_data);

					$this->session->data['success'] = sprintf($this->language->get('text_success'), $this->url->link('product/product', 'product_id=' . $product_info['product_id']), $product_info['name'], $this->url->link('checkout/cart'));

					unset($this->session->data['shipping_method']);
					unset($this->session->data['shipping_methods']);
					unset($this->session->data['payment_method']);
					unset($this->session->data['payment_methods']);
				} else {
					$this->session->data['error'] = sprintf($this->language->get('error_reorder'), $order_product_info['name']);
				}
			}
		}

		$this->response->redirect($this->url->link('account/order/info', 'order_id=' . $order_id));
	}

	public function filter(){
		$data = [];

		$mb_detected = new MobileDetect;
		if($mb_detected->isMobile() && !$mb_detected->isTablet()){
			$data['mobile'] = 1;
		}

		if (isset($this->request->get['orders_filter'])) {
			$furl = $this->getFilterGroup($this->request->get['orders_filter']);
			$filter_orders = $this->request->get['orders_filter'];
		} else {
			$furl = '';
			$filter_orders = '';
		}

		if (isset($this->request->get['page'])) {
			$page = $this->request->get['page'];
		} else {
			$page = 1;
		}

		$limit = 10;

		$this->load->model('account/order');

		$order_total = $this->model_account_order->getTotalOrders($filter_orders);

		$results = $this->model_account_order->getOrders(($page - 1) * $limit, $limit, $filter_orders);

		foreach ($results as $result) {
			$product_total = $this->model_account_order->getTotalOrderProductsByOrderId($result['order_id']);
			$voucher_total = $this->model_account_order->getTotalOrderVouchersByOrderId($result['order_id']);

			$data['orders'][] = array(
				'order_id'   => $result['order_id'],
				'name'       => $result['firstname'],
				'quantity'   => $result['quantity'],
				'status_id'  => $result['status_id'],
				'status'     => $result['status'],
				'date_added' => date('d.m.y', strtotime($result['date_added'])),
				'products'   => ($product_total + $voucher_total),
				'total'      => $this->currency->format($result['total'], $result['currency_code'], $result['currency_value'])
			);
		}

		$pagination = new Pagination();
		$pagination->total = $order_total;
		$pagination->page = $page;
		$pagination->limit = $limit;
		$pagination->url = $this->url->link('account/order',  'page={page}', true) . $furl;

		$order_total_now = ((($page - 1) * $limit) > ($order_total - $limit)) ? $order_total : ((($page - 1) * $limit) + $limit);
		$data['results'] = '<p class="ptotals">Показано <span class="total_now">' . $order_total_now . '</span> из <span class="total">' . $order_total . '</span> товаров</p>';

		if($order_total_now < $order_total){
			$data['pagination'] = $pagination->render();
			$data['next_page'] = ($page + 1);
		}

		$this->response->setOutput($this->load->view('account/orders_ajax', $data));
	}

	public function report(){
		$json = [];

		if (isset($this->request->get['orders_filter'])) {
			$filter_orders = $json['filters'] = $this->request->get['orders_filter'];
		} else {
			$filter_orders = '';
		}

		$this->load->model('account/order');
		$this->load->model('catalog/product');

		$order_total = $this->model_account_order->getTotalOrders($filter_orders);
		$limit = (int)$order_total;

		$results = $this->model_account_order->getOrders(0, $limit, $filter_orders);

		if($order_total > 0){
			$xls = new PHPExcel();
			$xls->getProperties()->setTitle("Выгрузка заказов c сайта \"Альфа свет\"");
			$xls->getProperties()->setCreated(date('d.m.Y'));

			$xls->setActiveSheetIndex(0);
			$sheet = $xls->getActiveSheet();
			$sheet->getDefaultStyle()->getFont()->setName('Roboto');
			$sheet->getColumnDimension("A")->setWidth(20);
			$sheet->getColumnDimension("B")->setWidth(25);
			$sheet->getColumnDimension("C")->setWidth(15);
			$sheet->getColumnDimension("D")->setWidth(15);
			$sheet->getColumnDimension("E")->setWidth(15);

			$period = '';
			if(!empty($filter_orders['date_added'])){
				if(!empty($filter_orders['date_added']['to'])){
					$period .= ' от ' . $filter_orders['date_added']['to'];
				}

				if(!empty($filter_orders['date_added']['from'])){
					$period .= ' до ' . $filter_orders['date_added']['from'];
				}
			}

			$sheet->setCellValue("A1", "Выгрузка заказов за период" . $period);
			$sheet->getStyle("A1")->getFont()->setBold(true);
			$sheet->getStyle("A1")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
			$sheet->mergeCells("A1:E1");

			$c = array();
			$c = ['A', 'B', 'C', 'D', 'E'];

			$i = 3;
			foreach ($results as $result) {
				$product_total = $this->model_account_order->getTotalOrderProductsByOrderId($result['order_id']);
				$voucher_total = $this->model_account_order->getTotalOrderVouchersByOrderId($result['order_id']);

				$order_products = array();
				$products = $this->model_account_order->getOrderProducts($result['order_id']);

				$sheet->setCellValue("A".$i, "Дата заказа");
				$sheet->setCellValue("B".$i, "Заказ");
				$sheet->setCellValue("C".$i, "Позиций");
				$sheet->setCellValue("D".$i, "Товаров");
				$sheet->setCellValue("E".$i, "Сумма");

				for($j = 0; $j < count($c); $j++){
					$sheet->getStyle($c[$j].$i)->getFont()->setBold(true);
				}
				$i++;

				$sheet->setCellValue("A".$i, date('d.m.y', strtotime($result['date_added'])));
				$sheet->setCellValue("B".$i, (int)$result['order_id']);
				$sheet->setCellValue("C".$i, ($product_total + $voucher_total));
				$sheet->setCellValue("D".$i, (int)$result['quantity']);
				$sheet->setCellValue("E".$i, (int)$result['total'] . " р");

				for($j = 0; $j < count($c); $j++){
					$sheet->getStyle($c[$j].$i)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
				}
				$i++;

				$sheet->setCellValue("A".$i, "Товары");
				$sheet->setCellValue("B".$i, "Опции");
				$sheet->setCellValue("C".$i, "Цена");
				$sheet->setCellValue("D".$i, "Количество");
				$sheet->setCellValue("E".$i, "Сумма");

				for($j = 0; $j < count($c); $j++){
					$sheet->getStyle($c[$j].$i)->getFont()->setBold(true);
				}
				$i++;

				foreach ($products as $product){
					$option_data = array();
					$options = $this->model_account_order->getOrderOptions($result['order_id'], $product['order_product_id']);

					$sheet->setCellValue("A".$i, $product['name']);
					$sheet->getStyle("A".$i)->getAlignment()->setWrapText(true);

					$option_text = array();;
					foreach ($options as $option) {
						if ($option['type'] != 'file') {
							$value = $option['value'];
						} else {
							$upload_info = $this->model_tool_upload->getUploadByCode($option['value']);

							if ($upload_info) {
								$value = $upload_info['name'];
							} else {
								$value = '';
							}
						}

						$option_data[] = array(
							'name'  => $option['name'],
							'value' => (utf8_strlen($value) > 20 ? utf8_substr($value, 0, 20) . '..' : $value)
						);

						$option_text[] = $option['name'] .': '.$value;
					}

					if(count($option_text) > 0){
						$sheet->setCellValue("B".$i, implode(' ', $option_text));
						$sheet->getStyle("B".$i)->getAlignment()->setWrapText(true);
					}

					$sheet->setCellValue("C".$i, (int)$product['price'] .' р');
					$sheet->setCellValue("D".$i, (int)$product['quantity']);
					$sheet->setCellValue("E".$i, (int)$product['total'] .' р');

					$product_info = $this->model_catalog_product->getProduct($product['product_id']);

					$order_products[] = array(
						'name'     => $product['name'],
						'model'    => $product['model'],
						'option'   => $option_data,
						'quantity' => (int)$product['quantity'],
						'price'    => (float)$this->currency->format($product['price'] + ($this->config->get('config_tax') ? $product['tax'] : 0), $result['currency_code'], $result['currency_value']),
						'total'    => (float)$this->currency->format($product['total'] + ($this->config->get('config_tax') ? ($product['tax'] * $product['quantity']) : 0), $result['currency_code'], $result['currency_value'])
					);

					for($j = 0; $j < count($c); $j++){
						$sheet->getStyle($c[$j].$i)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
						$sheet->getStyle($c[$j].$i)->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
					}
					$i++;
				}

				$sheet->setCellValue("D".$i, "Итого");
				$sheet->setCellValue("E".$i, (int)$result['total'] .' р');
				$sheet->getStyle("D".$i)->getFont()->setBold(true);
				$sheet->getStyle("E".$i)->getFont()->setBold(true);
				$i++;

				$sheet->setCellValue("A".$i, "Способ оплаты");
				$sheet->getStyle("A".$i)->getFont()->setBold(true);
				$sheet->setCellValue("B".$i, $result['payment_method']);
				$i++;

				$sheet->setCellValue("A".$i, "Способ доставки");
				$sheet->getStyle("A".$i)->getFont()->setBold(true);
				$sheet->setCellValue("B".$i, $result['shipping_method']);
				$i++;

				$json['orders'][] = array(
					'order_id'   => (int)$result['order_id'],
					'name'       => $result['firstname'],
					'quantity'   => (int)$result['quantity'],
					'status_id'  => (int)$result['status_id'],
					'status'     => $result['status'],
					'payment_method' => $result['payment_method'],
					'shipping_method' => $result['shipping_method'],
					'date_added' => date('d.m.y', strtotime($result['date_added'])),
					'products_count'   => ($product_total + $voucher_total),
					'products'	 => $order_products,
					'total'      => (int)$this->currency->format($result['total'], $result['currency_code'], $result['currency_value'])
				);

				$i++;
			}			

			$objWriter = new PHPExcel_Writer_Excel2007($xls);
			$objWriter->save(DIR_R . 'uploads/orders/Выгрузка заказов c сайта Альфа свет.xlsx');

			$json['file'] = HTTPS_SERVER . 'uploads/orders/Выгрузка заказов c сайта Альфа свет.xlsx';
		}

		$this->load->model('account/customer');
		$customer_info = $this->model_account_customer->getCustomer($this->customer->getId());
		$json['customer'] = [
			'customer_id' => (int)$customer_info['customer_id'],
			'firstname' => $customer_info['firstname'],
			'email' => $customer_info['email'],
			'telephone' => $customer_info['telephone'],
			'date_added' => $customer_info['date_added'],
		];

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function repeat(){
		$json = [];

		if (isset($this->request->get['order_id'])) {
			$order_id = $json['order_id'] = $this->request->get['order_id'];
		} else {
			$order_id = $json['order_id'] = 0;
		}

		$this->load->model('account/order');

		$order_info = $this->model_account_order->getOrder($order_id);

		if ($order_info) {
			$order_products = $this->model_account_order->getOrderProducts($order_id);
			$json['products'] = [];

			foreach($order_products as $value){
				$order_product_info = $this->model_account_order->getOrderProduct($order_id, (int)$value['product_id']);

				if ($order_product_info) {
					$this->load->model('catalog/product');

					$product_info = $this->model_catalog_product->getProduct($order_product_info['product_id']);

					if ($product_info) {
						$option_data = array();

						$order_options = $this->model_account_order->getOrderOptions($order_product_info['order_id'], $order_product_info['order_product_id']);

						foreach ($order_options as $order_option) {
							if ($order_option['type'] == 'select' || $order_option['type'] == 'radio' || $order_option['type'] == 'image') {
								$option_data[$order_option['product_option_id']] = $order_option['product_option_value_id'];
							} elseif ($order_option['type'] == 'checkbox') {
								$option_data[$order_option['product_option_id']][] = $order_option['product_option_value_id'];
							} elseif ($order_option['type'] == 'text' || $order_option['type'] == 'textarea' || $order_option['type'] == 'date' || $order_option['type'] == 'datetime' || $order_option['type'] == 'time') {
								$option_data[$order_option['product_option_id']] = $order_option['value'];
							} elseif ($order_option['type'] == 'file') {
								$option_data[$order_option['product_option_id']] = $this->encryption->encrypt($this->config->get('config_encryption'), $order_option['value']);
							}
						}

						if (isset($this->request->post['recurring_id'])) {
							$recurring_id = $this->request->post['recurring_id'];
						} else {
							$recurring_id = 0;
						}

						$this->cart->add($order_product_info['product_id'], $order_product_info['quantity'], $option_data, $recurring_id);
						$json['products'][] = [$order_product_info['product_id'], $order_product_info['quantity'], $option_data];

						$json['success'] = sprintf($this->language->get('text_success'), $this->url->link('product/product', 'product_id=' . $product_info['product_id']), $product_info['name'], $this->url->link('checkout/cart'));

						unset($this->session->data['shipping_method']);
						unset($this->session->data['shipping_methods']);
						unset($this->session->data['payment_method']);
						unset($this->session->data['payment_methods']);

						$json['count_cart'] = $this->cart->countProducts();
					} else {
						$json['error'] = sprintf($this->language->get('error_reorder'), $order_product_info['name']);
					}
				}
			}
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function detail(){
		$data = [];

		if (isset($this->request->get['order_id'])) {
			$order_id = $data['order_id'] = $this->request->get['order_id'];
		} else {
			$order_id = $data['order_id'] = 0;
		}

		$this->load->model('account/order');
		$this->load->model('catalog/product');

		$order_info = $data['order'] = $this->model_account_order->getOrder($order_id);
		// Products
		$data['products'] = array();
		$products = $this->model_account_order->getOrderProducts($order_id);

		foreach ($products as $product){
			$option_data = array();
			$options = $this->model_account_order->getOrderOptions($order_id, $product['order_product_id']);

			foreach ($options as $option) {
				if ($option['type'] != 'file') {
					$value = $option['value'];
				} else {
					$upload_info = $this->model_tool_upload->getUploadByCode($option['value']);

					if ($upload_info) {
						$value = $upload_info['name'];
					} else {
						$value = '';
					}
				}

				$option_data[] = array(
					'name'  => $option['name'],
					'value' => (utf8_strlen($value) > 20 ? utf8_substr($value, 0, 20) . '..' : $value)
				);
			}

			$product_info = $this->model_catalog_product->getProduct($product['product_id']);

			$data['products'][] = array(
				'name'     => $product['name'],
				'model'    => $product['model'],
				'option'   => $option_data,
				'quantity' => $product['quantity'],
				'price'    => $this->currency->format($product['price'] + ($this->config->get('config_tax') ? $product['tax'] : 0), $order_info['currency_code'], $order_info['currency_value']),
				'total'    => $this->currency->format($product['total'] + ($this->config->get('config_tax') ? ($product['tax'] * $product['quantity']) : 0), $order_info['currency_code'], $order_info['currency_value'])
			);
		}

		$data['totals'] = $this->currency->format($order_info['total'], $order_info['currency_code'], $order_info['currency_value']);

		$data['name'] = isset($order_info['firstname']) ? $order_info['firstname'] : '';
		$data['phone'] = isset($order_info['telephone']) ? $order_info['telephone'] : '';
		$data['email'] = isset($order_info['email']) ? $order_info['email'] : '';
		$data['payment_method'] = isset($order_info['payment_method']) ? $order_info['payment_method'] : '';
		$data['shipping_method'] = isset($order_info['shipping_method']) ? $order_info['shipping_method'] : '';
		$data['postcode'] = isset($order_info['shipping_postcode']) ? $order_info['shipping_postcode'] : '';
		$data['zone'] = isset($order_info['shipping_zone']) ? $order_info['shipping_zone'] : '';
		$data['city'] = isset($order_info['shipping_city']) ? $order_info['shipping_city'] : '';
		$data['address_1'] = isset($order_info['shipping_address_1']) ? $order_info['shipping_address_1'] : '';

		$data['inn'] = '';
		$data['kv'] = '';
		$data['rekvizit'] = '';
		
		if(isset($order_info['shipping_custom_field'])){
			foreach($order_info['shipping_custom_field'] as $key => $value){
				if($key == 1) $data['inn'] = $value;
				if($key == 2) $data['rekvizit'] = true;
				if($key == 3) $data['kv'] = $value;
			}
		}

		$this->response->setOutput($this->load->view('account/order_detail', $data));
	}

	public function set_act(){
		$json = [];

		if (isset($this->request->get['order_id'])) {
			$order_id = $this->request->get['order_id'];
			$this->load->model('account/order');

			$json['success'] = $this->model_account_order->setActToMail($order_id);
		} else {
			$json['error'] = false;
		}
		
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	private function getFilterGroup($filters_groups, $url = ''){
		if(is_array($filters_groups)){
			foreach($filters_groups as $group_name => $group_value){
				if(!is_array($group_value)){
					$url .= '&orders_filter['.$group_name.']=' . $group_value;
				} else {
					if(is_array($group_value)){
						foreach($group_value as $id => $value){
							if(!is_array($value)){
								$url .= '&orders_filter['.$group_name.']['.$id.']=' . $value;
							} else {
								if(is_array($value)){
									foreach($value as $k => $v){
										$url .= '&orders_filter['.$group_name.']['.$id.']['.$k.']=' . $v;
									}
								}
							}
						}
					}
				}
			}
		}

		return $url;
	}


}
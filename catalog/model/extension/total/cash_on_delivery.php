<?php
class ModelExtensionTotalCashOnDelivery extends Model {
	public function getTotal($total) {
		if ($this->config->get('total_cash_on_delivery_status') && isset($this->session->data['payment_method']) && $this->session->data['payment_method']['code'] == 'cod') {
			
			$this->load->language('extension/total/cash_on_delivery');
			
			$fee_amount = 0;
			
			$sub_total = $this->cart->getSubTotal();
			
			if($this->config->get('total_cash_on_delivery_type') == 'P') {
				$fee_amount = round((($sub_total * $this->config->get('total_cash_on_delivery_fee')) / 100), 2);
			} else {
				$fee_amount = $this->config->get('total_cash_on_delivery_fee');
			}
			
			$tax_rates = $this->tax->getRates($fee_amount, $this->config->get('total_cash_on_delivery_tax_class_id'));

			foreach ($tax_rates as $tax_rate) {
				if (!isset($taxes[$tax_rate['tax_rate_id']])) {
					$taxes[$tax_rate['tax_rate_id']] = $tax_rate['amount'];
				} else {
					$taxes[$tax_rate['tax_rate_id']] += $tax_rate['amount'];
				}
			}
			
			
			$total['totals'][] = array(
				'code'       => 'cash_on_delivery',
				'title'      => $this->language->get('text_total_cash_on_delivery'),
				'value'      => $fee_amount,
				'sort_order' => $this->config->get('total_cash_on_delivery_sort_order')
			);
			
			$total['total'] += $fee_amount;
		}
	}
}

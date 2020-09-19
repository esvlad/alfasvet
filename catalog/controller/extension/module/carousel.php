<?php
class ControllerExtensionModuleCarousel extends Controller {
	public function index($setting) {
		static $module = 0;

		$this->load->model('design/banner');
		$this->load->model('tool/image');
		
		$this->document->addStyle('catalog/view/javascript/jquery/swiper/css/swiper.min.css');
		$this->document->addStyle('catalog/view/javascript/jquery/swiper/css/opencart.css');
		$this->document->addScript('catalog/view/javascript/jquery/swiper/js/swiper.jquery.js');

		$data['banners'] = array();

		$results = $this->model_design_banner->getBanner($setting['banner_id']);

		foreach ($results as $result) {
			if (is_file(DIR_IMAGE . $result['image'])) {
				$data['banners'][] = array(
					'title' => html_entity_decode($result['title'], ENT_QUOTES, 'UTF-8'),
					'link'  => $result['link'],
					'image' => $this->model_tool_image->resize($result['image'], $setting['width'], $setting['height'])
				);
			}
		}

		$data['module'] = $module++;

		return $this->load->view('extension/module/carousel', $data);
	}

	public function view(){
		$this->load->model('design/banner');
		$this->load->model('tool/image');

		if(isset($this->request->get['banner_id'])){
			$banner_id = $data['banner_id'] = (int)$this->request->get['banner_id'];
		} else {
			$banner_id = $data['banner_id'] = 10;
		}

		if(isset($this->request->get['slides'])){
			$data['slides'] = (int)$this->request->get['slides'];
		} else {
			$data['slides'] = 6;
		}

		if(isset($this->request->get['space'])){
			$data['space'] = (int)$this->request->get['space'];
		} else {
			$data['space'] = 20;
		}

		$setting = $this->model_design_banner->getSetting($banner_id);

		if(isset($this->request->get['width'])){
			$width = (int)$this->request->get['width'];
		} else {
			$width = $setting['width'];
		}

		if(isset($this->request->get['height'])){
			$height = (int)$this->request->get['height'];
		} else {
			$height = $setting['height'];
		}

		if(isset($this->request->get['full'])){
			$full = true;
		} else {
			$full = false;
		}

		$data['banners'] = array();
		$results = $this->model_design_banner->getBanner($banner_id);

		foreach ($results as $result) {
			if (is_file(DIR_IMAGE . $result['image'])) {
				$data['banners'][] = array(
					'title' => $result['title'],
					'link'  => $result['link'],
					'full_image' => (!$full) ? false : '../image/' . $result['image'],
					'image' => ($width == 1 || $height == 1) ? '../image/' . $result['image'] : $this->model_tool_image->resizeCrop($result['image'], $width, $height)
				);
			}
		}

		$this->response->setOutput($this->load->view('extension/module/carousel', $data));
	}
}
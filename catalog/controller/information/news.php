<?php
class ControllerInformationNews extends Controller {
	public function index() {
		$this->load->model('catalog/news');
	 
		$this->document->setTitle('Новости'); 
	 
		$data['breadcrumbs'] = array();
		
		$data['breadcrumbs'][] = array(
			'text' 		=> 'Главная',
			'href' 		=> $this->url->link('common/home')
		);
		$data['breadcrumbs'][] = array(
			'text' 		=> 'Новости',
			'href' 		=> $this->url->link('information/news')
		);
		  
		$url = '';
		
		if (isset($this->request->get['page'])) {
			$url .= '&page=' . $this->request->get['page'];
		}	

		if (isset($this->request->get['page'])) {
			$page = $this->request->get['page'];
		} else { 
			$page = 1;
		}
		
		$filter_data = array(
			'page' 	=> $page,
			'limit' => 10,
			'start' => 10 * ($page - 1),
		);
		
		$total = $this->model_catalog_news->getTotalNews();
		
		$pagination = new Pagination();
		$pagination->total = $total;
		$pagination->page = $page;
		$pagination->limit = 10;
		$pagination->url = $this->url->link('information/news', 'page={page}');
		
		$data['pagination'] = $pagination->render();
	 
		$data['results'] = sprintf($this->language->get('text_pagination'), ($total) ? (($page - 1) * 10) + 1 : 0, ((($page - 1) * 10) > ($total - 10)) ? $total : ((($page - 1) * 10) + 10), $total, ceil($total / 10));

		$data['heading_title'] = 'Новости';
	 
		$newses = $this->model_catalog_news->getAllNews($filter_data);
	 
		$data['newses'] = array();
		
		$this->load->model('tool/image');
	 
		foreach ($newses as $news) {
			$data['newses'][] = array (
				'title' 		=> $news['title'],
				'image'			=> $this->model_tool_image->resizeCrop($news['image'], 465, 223),
				'description' 	=> strip_tags(html_entity_decode($news['short_description'])),
				'view' 			=> $this->url->link('information/news/news', 'news_id=' . $news['news_id']),
				'date_added' 	=> date($this->language->get('date_format_short'), strtotime($news['date_added']))
			);
		}
	 
		$data['footer'] = $this->load->controller('common/footer');
		$data['header'] = $this->load->controller('common/header');

		$this->response->setOutput($this->load->view('information/news_list', $data));
	}

	public function home() {		
		$this->load->model('catalog/news');

		$page = 1;
		
		$filter_data = array(
			'page' 	=> $page,
			'limit' => 10,
			'start' => 10 * ($page - 1),
		);
		
	 
		$newses = $this->model_catalog_news->getMainNews($filter_data);
	 
		$data['newses'] = array();
		
		$this->load->model('tool/image');
	 
		foreach ($newses as $news) {
			$data['newses'][] = array (
				'title' 		=> $news['title'],
				'image'			=> $this->model_tool_image->resizeCrop($news['image'], 465, 223),
				'description' 	=> strip_tags(html_entity_decode($news['short_description'])),
				'view' 			=> $this->url->link('information/news/news', 'news_id=' . $news['news_id']),
				'date_added' 	=> date($this->language->get('date_format_short'), strtotime($news['date_added']))
			);
		}

		$this->response->setOutput($this->load->view('common/news', $data));
	}
 
	public function news() {
		$this->load->model('catalog/news');
 
		if (isset($this->request->get['news_id']) && !empty($this->request->get['news_id'])) {
			$news_id = $this->request->get['news_id'];
		} else {
			$news_id = 0;
		}
 
		$news = $this->model_catalog_news->getNews($news_id);
 
		$data['breadcrumbs'] = array();
	  
		$data['breadcrumbs'][] = array(
			'text' 			=> 'Главная',
			'href' 			=> $this->url->link('common/home')
		);
	  
		$data['breadcrumbs'][] = array(
			'text' => 'Новости',
			'href' => $this->url->link('information/news')
		);
 
		if ($news) {
			$data['breadcrumbs'][] = array(
				'text' 		=> $news['title'],
				'href' 		=> $this->url->link('information/news/news', 'news_id=' . $news_id)
			);
 
			$this->document->setTitle($news['title']);
			
			$this->load->model('tool/image');
			
			$data['image'] = $this->model_tool_image->resizeCrop($news['image'], 700, 440);
 
			$data['heading_title'] = $news['title'];
			$data['description'] = html_entity_decode($news['description']);
			$data['date_added'] = date('d.m.Y', strtotime($news['date_added']));
			$data['newslist'] = $this->url->link('information/news');
	 
			$data['footer'] = $this->load->controller('common/footer');
			$data['header'] = $this->load->controller('common/header');

			$this->response->setOutput($this->load->view('information/news', $data));
		} else {
			$data['breadcrumbs'][] = array(
				'text' 		=> $this->language->get('text_error'),
				'href' 		=> $this->url->link('information/news', 'news_id=' . $news_id)
			);
	 
			$this->document->setTitle($this->language->get('text_error'));
	 
			$data['heading_title'] = $this->language->get('text_error');
			$data['text_error'] = $this->language->get('text_error');
			$data['button_continue'] = $this->language->get('button_continue');
			$data['continue'] = $this->url->link('common/home');
	 
			$data['footer'] = $this->load->controller('common/footer');
			$data['header'] = $this->load->controller('common/header');

			$this->response->setOutput($this->load->view('error/not_found', $data));
		}
	}
}
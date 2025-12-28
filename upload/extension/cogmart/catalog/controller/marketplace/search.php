<?php
namespace Opencart\Catalog\Controller\Extension\Cogmart\Marketplace;

/**
 * Class Search
 *
 * @package Opencart\Catalog\Controller\Extension\Cogmart\Marketplace
 */
class Search extends \Opencart\System\Engine\Controller {
	/**
	 * Search products across all marketplace shops
	 *
	 * @return void
	 */
	public function index(): void {
		$this->load->language('extension/cogmart/marketplace/search');

		$this->load->model('extension/cogmart/marketplace/shop');

		$this->document->setTitle($this->language->get('heading_title'));

		$data['breadcrumbs'] = [];

		$data['breadcrumbs'][] = [
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/home', 'language=' . $this->config->get('config_language'))
		];

		$data['breadcrumbs'][] = [
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('extension/cogmart/marketplace/search', 'language=' . $this->config->get('config_language'))
		];

		// Get search parameters
		$search_query = $this->request->get['q'] ?? '';
		$filter_country = $this->request->get['country'] ?? '';
		$filter_min_price = $this->request->get['min_price'] ?? '';
		$filter_max_price = $this->request->get['max_price'] ?? '';
		$filter_available = $this->request->get['available'] ?? '';
		$sort = $this->request->get['sort'] ?? 'TITLE';

		// Get all active shops
		$filter_data = [
			'filter_country' => $filter_country,
			'sort'           => 'name',
			'order'          => 'ASC'
		];

		$shops = $this->model_extension_cogmart_marketplace_shop->getShops($filter_data);

		// Pass shop data to the view for JavaScript to query
		$data['shops'] = [];
		foreach ($shops as $shop) {
			// Only include shops with storefront access tokens
			if (!empty($shop['storefront_access_token'])) {
				$data['shops'][] = [
					'marketplace_shop_id' => $shop['marketplace_shop_id'],
					'name'                => $shop['name'],
					'domain'              => $shop['domain'],
					'country'             => $shop['country'],
					'storefront_access_token' => $shop['storefront_access_token'],
					'href'                => $this->url->link('extension/cogmart/marketplace/shop.info', 'marketplace_shop_id=' . $shop['marketplace_shop_id'] . '&language=' . $this->config->get('config_language'))
				];
			}
		}

		// Get countries for filter
		$data['countries'] = $this->model_extension_cogmart_marketplace_shop->getCountries();

		// Search parameters
		$data['search_query'] = $search_query;
		$data['filter_country'] = $filter_country;
		$data['filter_min_price'] = $filter_min_price;
		$data['filter_max_price'] = $filter_max_price;
		$data['filter_available'] = $filter_available;
		$data['sort'] = $sort;

		// Language data
		$data['heading_title'] = $this->language->get('heading_title');
		$data['text_search'] = $this->language->get('text_search');
		$data['text_no_results'] = $this->language->get('text_no_results');
		$data['text_loading'] = $this->language->get('text_loading');
		$data['text_all_countries'] = $this->language->get('text_all_countries');
		$data['text_any_price'] = $this->language->get('text_any_price');
		$data['text_all_availability'] = $this->language->get('text_all_availability');
		$data['text_in_stock_only'] = $this->language->get('text_in_stock_only');
		$data['text_sort_title'] = $this->language->get('text_sort_title');
		$data['text_sort_price'] = $this->language->get('text_sort_price');
		$data['text_sort_newest'] = $this->language->get('text_sort_newest');
		$data['entry_search'] = $this->language->get('entry_search');
		$data['entry_country'] = $this->language->get('entry_country');
		$data['entry_min_price'] = $this->language->get('entry_min_price');
		$data['entry_max_price'] = $this->language->get('entry_max_price');
		$data['entry_availability'] = $this->language->get('entry_availability');
		$data['entry_sort'] = $this->language->get('entry_sort');
		$data['button_search'] = $this->language->get('button_search');
		$data['button_reset'] = $this->language->get('button_reset');

		$data['column_left'] = $this->load->controller('common/column_left');
		$data['column_right'] = $this->load->controller('common/column_right');
		$data['content_top'] = $this->load->controller('common/content_top');
		$data['content_bottom'] = $this->load->controller('common/content_bottom');
		$data['footer'] = $this->load->controller('common/footer');
		$data['header'] = $this->load->controller('common/header');

		$this->response->setOutput($this->load->view('extension/cogmart/marketplace/search', $data));
	}
}

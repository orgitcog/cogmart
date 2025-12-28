<?php
namespace Opencart\Catalog\Controller\Extension\Cogmart\Marketplace;

/**
 * Class Shop
 *
 * @package Opencart\Catalog\Controller\Extension\Cogmart\Marketplace
 */
class Shop extends \Opencart\System\Engine\Controller {
	/**
	 * Index - List all shops
	 *
	 * @return void
	 */
	public function index(): void {
		$this->load->language('extension/cogmart/marketplace/shop');

		$this->load->model('extension/cogmart/marketplace/shop');

		$this->document->setTitle($this->language->get('heading_title'));

		$data['breadcrumbs'] = [];

		$data['breadcrumbs'][] = [
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/home', 'language=' . $this->config->get('config_language'))
		];

		$data['breadcrumbs'][] = [
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('extension/cogmart/marketplace/shop', 'language=' . $this->config->get('config_language'))
		];

		// Filters
		if (isset($this->request->get['country'])) {
			$filter_country = $this->request->get['country'];
		} else {
			$filter_country = '';
		}

		if (isset($this->request->get['search'])) {
			$filter_name = $this->request->get['search'];
		} else {
			$filter_name = '';
		}

		if (isset($this->request->get['sort'])) {
			$sort = $this->request->get['sort'];
		} else {
			$sort = 'name';
		}

		if (isset($this->request->get['order'])) {
			$order = $this->request->get['order'];
		} else {
			$order = 'ASC';
		}

		if (isset($this->request->get['page'])) {
			$page = (int)$this->request->get['page'];
		} else {
			$page = 1;
		}

		$limit = 12;

		$filter_data = [
			'filter_country' => $filter_country,
			'filter_name'    => $filter_name,
			'sort'           => $sort,
			'order'          => $order,
			'start'          => ($page - 1) * $limit,
			'limit'          => $limit
		];

		$shop_total = $this->model_extension_cogmart_marketplace_shop->getTotalShops($filter_data);

		$results = $this->model_extension_cogmart_marketplace_shop->getShops($filter_data);

		$data['shops'] = [];

		foreach ($results as $result) {
			$data['shops'][] = [
				'marketplace_shop_id' => $result['marketplace_shop_id'],
				'name'                => $result['name'],
				'domain'              => $result['domain'],
				'country'             => $result['country'],
				'href'                => $this->url->link('extension/cogmart/marketplace/shop.info', 'marketplace_shop_id=' . $result['marketplace_shop_id'] . '&language=' . $this->config->get('config_language'))
			];
		}

		// Countries for filter
		$data['countries'] = $this->model_extension_cogmart_marketplace_shop->getCountries();

		// Pagination
		$pagination = new \Opencart\System\Library\Pagination();
		$pagination->total = $shop_total;
		$pagination->page = $page;
		$pagination->limit = $limit;
		$pagination->url = $this->url->link('extension/cogmart/marketplace/shop', 'language=' . $this->config->get('config_language') . '&page={page}');

		$data['pagination'] = $pagination->render();
		$data['results'] = sprintf($this->language->get('text_pagination'), ($shop_total) ? (($page - 1) * $limit) + 1 : 0, ((($page - 1) * $limit) > ($shop_total - $limit)) ? $shop_total : ((($page - 1) * $limit) + $limit), $shop_total, ceil($shop_total / $limit));

		$data['filter_country'] = $filter_country;
		$data['filter_name'] = $filter_name;
		$data['sort'] = $sort;
		$data['order'] = $order;

		$data['column_left'] = $this->load->controller('common/column_left');
		$data['column_right'] = $this->load->controller('common/column_right');
		$data['content_top'] = $this->load->controller('common/content_top');
		$data['content_bottom'] = $this->load->controller('common/content_bottom');
		$data['footer'] = $this->load->controller('common/footer');
		$data['header'] = $this->load->controller('common/header');

		$this->response->setOutput($this->load->view('extension/cogmart/marketplace/shop_list', $data));
	}

	/**
	 * Info - View single shop
	 *
	 * @return void
	 */
	public function info(): void {
		$this->load->language('extension/cogmart/marketplace/shop');

		$this->load->model('extension/cogmart/marketplace/shop');

		if (isset($this->request->get['marketplace_shop_id'])) {
			$marketplace_shop_id = (int)$this->request->get['marketplace_shop_id'];
		} else {
			$marketplace_shop_id = 0;
		}

		$shop_info = $this->model_extension_cogmart_marketplace_shop->getShop($marketplace_shop_id);

		if ($shop_info) {
			$this->document->setTitle($shop_info['name']);

			$data['breadcrumbs'] = [];

			$data['breadcrumbs'][] = [
				'text' => $this->language->get('text_home'),
				'href' => $this->url->link('common/home', 'language=' . $this->config->get('config_language'))
			];

			$data['breadcrumbs'][] = [
				'text' => $this->language->get('heading_title'),
				'href' => $this->url->link('extension/cogmart/marketplace/shop', 'language=' . $this->config->get('config_language'))
			];

			$data['breadcrumbs'][] = [
				'text' => $shop_info['name'],
				'href' => $this->url->link('extension/cogmart/marketplace/shop.info', 'marketplace_shop_id=' . $marketplace_shop_id . '&language=' . $this->config->get('config_language'))
			];

			$data['shop_id'] = $shop_info['marketplace_shop_id'];
			$data['name'] = $shop_info['name'];
			$data['domain'] = $shop_info['domain'];
			$data['country'] = $shop_info['country'];
			$data['storefront_access_token'] = $shop_info['storefront_access_token'];

			$data['column_left'] = $this->load->controller('common/column_left');
			$data['column_right'] = $this->load->controller('common/column_right');
			$data['content_top'] = $this->load->controller('common/content_top');
			$data['content_bottom'] = $this->load->controller('common/content_bottom');
			$data['footer'] = $this->load->controller('common/footer');
			$data['header'] = $this->load->controller('common/header');

			$this->response->setOutput($this->load->view('extension/cogmart/marketplace/shop_info', $data));
		} else {
			return new \Opencart\System\Engine\Action('error/not_found');
		}
	}

	/**
	 * API endpoint for GraphQL-like queries
	 *
	 * @return void
	 */
	public function api(): void {
		$json = [];

		$this->load->model('extension/cogmart/marketplace/shop');

		if (isset($this->request->post['query'])) {
			$query = $this->request->post['query'];

			// Simple query parser (would need a proper GraphQL parser in production)
			if (strpos($query, 'shops') !== false) {
				$filter_data = [];

				if (isset($this->request->post['variables'])) {
					$variables = $this->request->post['variables'];

					if (isset($variables['country'])) {
						$filter_data['filter_country'] = $variables['country'];
					}

					if (isset($variables['nameIsLike'])) {
						$filter_data['filter_name'] = $variables['nameIsLike'];
					}

					if (isset($variables['reverse']) && $variables['reverse']) {
						$filter_data['order'] = 'DESC';
					}
				}

				$results = $this->model_extension_cogmart_marketplace_shop->getShops($filter_data);

				$json['data']['shops'] = [];

				foreach ($results as $result) {
					$json['data']['shops'][] = [
						'id'                      => $result['marketplace_shop_id'],
						'domain'                  => $result['domain'],
						'name'                    => $result['name'],
						'country'                 => $result['country'],
						'storefrontAccessToken'   => $result['storefront_access_token']
					];
				}
			} elseif (strpos($query, 'shop(') !== false) {
				// Get single shop
				if (isset($this->request->post['variables']['id'])) {
					$shop_id = (int)$this->request->post['variables']['id'];
					$shop_info = $this->model_extension_cogmart_marketplace_shop->getShop($shop_id);

					if ($shop_info) {
						$json['data']['shop'] = [
							'id'                    => $shop_info['marketplace_shop_id'],
							'domain'                => $shop_info['domain'],
							'name'                  => $shop_info['name'],
							'country'               => $shop_info['country'],
							'storefrontAccessToken' => $shop_info['storefront_access_token']
						];
					} else {
						$json['data']['shop'] = null;
					}
				}
			} elseif (strpos($query, 'shopCountries') !== false) {
				$json['data']['shopCountries'] = $this->model_extension_cogmart_marketplace_shop->getCountries();
			}
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->addHeader('Access-Control-Allow-Origin: *');
		$this->response->addHeader('Access-Control-Allow-Methods: GET, POST, OPTIONS');
		$this->response->addHeader('Access-Control-Allow-Headers: Content-Type');
		$this->response->setOutput(json_encode($json));
	}
}

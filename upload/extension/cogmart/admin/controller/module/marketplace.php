<?php
namespace Opencart\Admin\Controller\Extension\Cogmart\Module;

/**
 * Class Marketplace
 *
 * @package Opencart\Admin\Controller\Extension\Cogmart\Module
 */
class Marketplace extends \Opencart\System\Engine\Controller {
	/**
	 * @return void
	 */
	public function index(): void {
		$this->load->language('extension/cogmart/module/marketplace');

		$this->document->setTitle($this->language->get('heading_title'));

		$data['breadcrumbs'] = [];

		$data['breadcrumbs'][] = [
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'])
		];

		$data['breadcrumbs'][] = [
			'text' => $this->language->get('text_extension'),
			'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module')
		];

		$data['breadcrumbs'][] = [
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('extension/cogmart/module/marketplace', 'user_token=' . $this->session->data['user_token'])
		];

		$data['save'] = $this->url->link('extension/cogmart/module/marketplace.save', 'user_token=' . $this->session->data['user_token']);
		$data['back'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module');
		$data['shop'] = $this->url->link('extension/cogmart/module/marketplace.shop', 'user_token=' . $this->session->data['user_token']);

		$data['module_marketplace_status'] = $this->config->get('module_marketplace_status');

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('extension/cogmart/module/marketplace', $data));
	}

	/**
	 * @return void
	 */
	public function save(): void {
		$this->load->language('extension/cogmart/module/marketplace');

		$json = [];

		if (!$this->user->hasPermission('modify', 'extension/cogmart/module/marketplace')) {
			$json['error'] = $this->language->get('error_permission');
		}

		if (!$json) {
			$this->load->model('setting/setting');

			$this->model_setting_setting->editSetting('module_marketplace', $this->request->post);

			$json['success'] = $this->language->get('text_success');
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	/**
	 * @return void
	 */
	public function install(): void {
		// Run install SQL
		$sql_file = DIR_EXTENSION . 'cogmart/install.sql';
		
		if (file_exists($sql_file)) {
			$sql = file_get_contents($sql_file);
			$statements = array_filter(array_map('trim', explode(';', $sql)));
			
			foreach ($statements as $statement) {
				if (!empty($statement)) {
					$this->db->query($statement);
				}
			}
		}
	}

	/**
	 * @return void
	 */
	public function uninstall(): void {
		// Note: We don't drop tables on uninstall to preserve data
		// If you want to drop tables, uncomment the following:
		// $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "marketplace_shop`");
		// $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "marketplace_cart`");
	}

	/**
	 * Shop management
	 *
	 * @return void
	 */
	public function shop(): void {
		$this->load->language('extension/cogmart/module/marketplace');

		$this->document->setTitle($this->language->get('heading_title'));

		$data['breadcrumbs'] = [];

		$data['breadcrumbs'][] = [
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'])
		];

		$data['breadcrumbs'][] = [
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('extension/cogmart/module/marketplace.shop', 'user_token=' . $this->session->data['user_token'])
		];

		$data['add'] = $this->url->link('extension/cogmart/module/marketplace.form', 'user_token=' . $this->session->data['user_token']);
		$data['delete'] = $this->url->link('extension/cogmart/module/marketplace.delete', 'user_token=' . $this->session->data['user_token']);
		$data['list'] = $this->url->link('extension/cogmart/module/marketplace.list', 'user_token=' . $this->session->data['user_token']);

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('extension/cogmart/module/marketplace_shop_list', $data));
	}

	/**
	 * Shop list
	 *
	 * @return void
	 */
	public function list(): void {
		$this->load->language('extension/cogmart/module/marketplace');

		$this->load->model('extension/cogmart/module/marketplace');

		if (isset($this->request->get['page'])) {
			$page = (int)$this->request->get['page'];
		} else {
			$page = 1;
		}

		$data['shops'] = [];

		$filter_data = [
			'start' => ($page - 1) * 10,
			'limit' => 10
		];

		$shop_total = $this->model_extension_cogmart_module_marketplace->getTotalShops();

		$results = $this->model_extension_cogmart_module_marketplace->getShops($filter_data);

		foreach ($results as $result) {
			$data['shops'][] = [
				'marketplace_shop_id' => $result['marketplace_shop_id'],
				'name'                => $result['name'],
				'domain'              => $result['domain'],
				'country'             => $result['country'],
				'status'              => $result['status'],
				'edit'                => $this->url->link('extension/cogmart/module/marketplace.form', 'user_token=' . $this->session->data['user_token'] . '&marketplace_shop_id=' . $result['marketplace_shop_id'])
			];
		}

		$data['pagination'] = $this->load->controller('common/pagination', [
			'total' => $shop_total,
			'page'  => $page,
			'limit' => 10,
			'url'   => $this->url->link('extension/cogmart/module/marketplace.list', 'user_token=' . $this->session->data['user_token'] . '&page={page}')
		]);

		$data['results'] = sprintf($this->language->get('text_pagination'), ($shop_total) ? (($page - 1) * 10) + 1 : 0, ((($page - 1) * 10) > ($shop_total - 10)) ? $shop_total : ((($page - 1) * 10) + 10), $shop_total, ceil($shop_total / 10));

		$this->response->setOutput($this->load->view('extension/cogmart/module/marketplace_shop_list', $data));
	}

	/**
	 * Shop form
	 *
	 * @return void
	 */
	public function form(): void {
		$this->load->language('extension/cogmart/module/marketplace');

		$this->document->setTitle($this->language->get('heading_title'));

		$data['text_form'] = !isset($this->request->get['marketplace_shop_id']) ? $this->language->get('text_add') : $this->language->get('text_edit');

		$data['breadcrumbs'] = [];

		$data['breadcrumbs'][] = [
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'])
		];

		$data['breadcrumbs'][] = [
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('extension/cogmart/module/marketplace.shop', 'user_token=' . $this->session->data['user_token'])
		];

		$data['save'] = $this->url->link('extension/cogmart/module/marketplace.saveShop', 'user_token=' . $this->session->data['user_token']);
		$data['back'] = $this->url->link('extension/cogmart/module/marketplace.shop', 'user_token=' . $this->session->data['user_token']);

		$this->load->model('extension/cogmart/module/marketplace');

		if (isset($this->request->get['marketplace_shop_id'])) {
			$shop_info = $this->model_extension_cogmart_module_marketplace->getShop($this->request->get['marketplace_shop_id']);
		}

		if (isset($this->request->get['marketplace_shop_id'])) {
			$data['marketplace_shop_id'] = (int)$this->request->get['marketplace_shop_id'];
		} else {
			$data['marketplace_shop_id'] = 0;
		}

		if (!empty($shop_info)) {
			$data['name'] = $shop_info['name'];
		} else {
			$data['name'] = '';
		}

		if (!empty($shop_info)) {
			$data['domain'] = $shop_info['domain'];
		} else {
			$data['domain'] = '';
		}

		if (!empty($shop_info)) {
			$data['country'] = $shop_info['country'];
		} else {
			$data['country'] = '';
		}

		if (!empty($shop_info)) {
			$data['storefront_access_token'] = $shop_info['storefront_access_token'];
		} else {
			$data['storefront_access_token'] = '';
		}

		if (!empty($shop_info)) {
			$data['status'] = $shop_info['status'];
		} else {
			$data['status'] = 1;
		}

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('extension/cogmart/module/marketplace_shop_form', $data));
	}

	/**
	 * Save shop
	 *
	 * @return void
	 */
	public function saveShop(): void {
		$this->load->language('extension/cogmart/module/marketplace');

		$json = [];

		if (!$this->user->hasPermission('modify', 'extension/cogmart/module/marketplace')) {
			$json['error']['warning'] = $this->language->get('error_permission');
		}

		if ((oc_strlen($this->request->post['name']) < 3) || (oc_strlen($this->request->post['name']) > 255)) {
			$json['error']['name'] = $this->language->get('error_name');
		}

		if ((oc_strlen($this->request->post['domain']) < 3) || (oc_strlen($this->request->post['domain']) > 255)) {
			$json['error']['domain'] = $this->language->get('error_domain');
		}

		if (!$json) {
			$this->load->model('extension/cogmart/module/marketplace');

			if (!$this->request->post['marketplace_shop_id']) {
				$json['marketplace_shop_id'] = $this->model_extension_cogmart_module_marketplace->addShop($this->request->post);
			} else {
				$this->model_extension_cogmart_module_marketplace->editShop($this->request->post['marketplace_shop_id'], $this->request->post);
			}

			$json['success'] = $this->language->get('text_success');
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	/**
	 * Delete shop
	 *
	 * @return void
	 */
	public function delete(): void {
		$this->load->language('extension/cogmart/module/marketplace');

		$json = [];

		if (isset($this->request->post['selected'])) {
			$selected = $this->request->post['selected'];
		} else {
			$selected = [];
		}

		if (!$this->user->hasPermission('modify', 'extension/cogmart/module/marketplace')) {
			$json['error'] = $this->language->get('error_permission');
		}

		if (!$json) {
			$this->load->model('extension/cogmart/module/marketplace');

			foreach ($selected as $marketplace_shop_id) {
				$this->model_extension_cogmart_module_marketplace->deleteShop($marketplace_shop_id);
			}

			$json['success'] = $this->language->get('text_success');
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
}

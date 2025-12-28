<?php
namespace Opencart\Catalog\Controller\Extension\Cogmart\Marketplace;

/**
 * Class Cart
 *
 * @package Opencart\Catalog\Controller\Extension\Cogmart\Marketplace
 */
class Cart extends \Opencart\System\Engine\Controller {
	/**
	 * Add to cart
	 *
	 * @return void
	 */
	public function add(): void {
		$this->load->language('extension/cogmart/marketplace/cart');

		$json = [];

		if (isset($this->request->post['marketplace_shop_id'])) {
			$marketplace_shop_id = (int)$this->request->post['marketplace_shop_id'];
		} else {
			$marketplace_shop_id = 0;
		}

		$this->load->model('extension/cogmart/marketplace/shop');

		$shop_info = $this->model_extension_cogmart_marketplace_shop->getShop($marketplace_shop_id);

		if ($shop_info) {
			$cart_data = [
				'marketplace_shop_id' => $marketplace_shop_id,
				'cart_id'             => $this->request->post['cart_id'] ?? '',
				'checkout_url'        => $this->request->post['checkout_url'] ?? ''
			];

			// Check if cart already exists for this shop
			$existing_cart = $this->model_extension_cogmart_marketplace_shop->getCartByShop($marketplace_shop_id);

			if ($existing_cart) {
				// Update existing cart
				$this->model_extension_cogmart_marketplace_shop->updateCart($existing_cart['marketplace_cart_id'], $cart_data);
				$json['cart_id'] = $existing_cart['marketplace_cart_id'];
			} else {
				// Add new cart
				$json['cart_id'] = $this->model_extension_cogmart_marketplace_shop->addToCart($cart_data);
			}

			$json['success'] = $this->language->get('text_success');

			// Get total cart count
			$carts = $this->model_extension_cogmart_marketplace_shop->getCarts();
			$json['total'] = count($carts);
		} else {
			$json['error'] = $this->language->get('error_shop');
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	/**
	 * View cart
	 *
	 * @return void
	 */
	public function index(): void {
		$this->load->language('extension/cogmart/marketplace/cart');

		$this->load->model('extension/cogmart/marketplace/shop');

		$this->document->setTitle($this->language->get('heading_title'));

		$data['breadcrumbs'] = [];

		$data['breadcrumbs'][] = [
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/home', 'language=' . $this->config->get('config_language'))
		];

		$data['breadcrumbs'][] = [
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('extension/cogmart/marketplace/cart', 'language=' . $this->config->get('config_language'))
		];

		$carts = $this->model_extension_cogmart_marketplace_shop->getCarts();

		$data['carts'] = [];

		foreach ($carts as $cart) {
			$shop_info = $this->model_extension_cogmart_marketplace_shop->getShop($cart['marketplace_shop_id']);
			
			$data['carts'][] = [
				'marketplace_cart_id' => $cart['marketplace_cart_id'],
				'marketplace_shop_id' => $cart['marketplace_shop_id'],
				'shop_name'           => $cart['name'],
				'shop_domain'         => $cart['domain'],
				'cart_id'             => $cart['cart_id'],
				'checkout_url'        => $cart['checkout_url'],
				'storefront_access_token' => $shop_info['storefront_access_token'] ?? '',
				'shop_href'           => $this->url->link('extension/cogmart/marketplace/shop.info', 'marketplace_shop_id=' . $cart['marketplace_shop_id'] . '&language=' . $this->config->get('config_language')),
				'remove'              => $this->url->link('extension/cogmart/marketplace/cart.remove', 'marketplace_cart_id=' . $cart['marketplace_cart_id'] . '&language=' . $this->config->get('config_language'))
			];
		}

		$data['continue'] = $this->url->link('extension/cogmart/marketplace/shop', 'language=' . $this->config->get('config_language'));
		
		$data['heading_title'] = $this->language->get('heading_title');
		$data['text_empty'] = $this->language->get('text_empty');
		$data['text_cart_info'] = $this->language->get('text_cart_info');
		$data['text_loading'] = $this->language->get('text_loading');
		$data['text_total'] = $this->language->get('text_total');
		$data['text_confirm_remove'] = $this->language->get('text_confirm_remove');
		$data['column_shop'] = $this->language->get('column_shop');
		$data['column_action'] = $this->language->get('column_action');
		$data['button_checkout'] = $this->language->get('button_checkout');
		$data['button_remove'] = $this->language->get('button_remove');
		$data['button_shopping'] = $this->language->get('button_shopping');
		$data['button_browse_shops'] = $this->language->get('button_browse_shops');
		$data['button_continue_shopping'] = $this->language->get('button_continue_shopping');

		$data['column_left'] = $this->load->controller('common/column_left');
		$data['column_right'] = $this->load->controller('common/column_right');
		$data['content_top'] = $this->load->controller('common/content_top');
		$data['content_bottom'] = $this->load->controller('common/content_bottom');
		$data['footer'] = $this->load->controller('common/footer');
		$data['header'] = $this->load->controller('common/header');

		$this->response->setOutput($this->load->view('extension/cogmart/marketplace/cart', $data));
	}

	/**
	 * Remove from cart
	 *
	 * @return void
	 */
	public function remove(): void {
		$this->load->language('extension/cogmart/marketplace/cart');

		$json = [];

		if (isset($this->request->get['marketplace_cart_id'])) {
			$marketplace_cart_id = (int)$this->request->get['marketplace_cart_id'];
		} else {
			$marketplace_cart_id = 0;
		}

		if ($marketplace_cart_id) {
			$this->load->model('extension/cogmart/marketplace/shop');

			$this->model_extension_cogmart_marketplace_shop->deleteCart($marketplace_cart_id);

			$json['success'] = $this->language->get('text_remove');

			// Redirect back to cart
			$this->response->redirect($this->url->link('extension/cogmart/marketplace/cart', 'language=' . $this->config->get('config_language')));
		} else {
			$json['error'] = $this->language->get('error_cart');
		}
	}

	/**
	 * Get cart count
	 *
	 * @return void
	 */
	public function count(): void {
		$this->load->model('extension/cogmart/marketplace/shop');

		$carts = $this->model_extension_cogmart_marketplace_shop->getCarts();

		$json = [
			'total' => count($carts)
		];

		$this->response->addHeader('Content-Type: application/json');
		$this->response->addHeader('Access-Control-Allow-Origin: *');
		$this->response->setOutput(json_encode($json));
	}

	/**
	 * Create or update cart via API
	 * Accepts Shopify cart ID and checkout URL
	 *
	 * @return void
	 */
	public function api(): void {
		$this->load->language('extension/cogmart/marketplace/cart');

		$json = [];

		// Handle CORS preflight
		if ($this->request->server['REQUEST_METHOD'] === 'OPTIONS') {
			$this->response->addHeader('Access-Control-Allow-Origin: *');
			$this->response->addHeader('Access-Control-Allow-Methods: POST, GET, OPTIONS');
			$this->response->addHeader('Access-Control-Allow-Headers: Content-Type');
			$this->response->setOutput('');
			return;
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->addHeader('Access-Control-Allow-Origin: *');

		if ($this->request->server['REQUEST_METHOD'] !== 'POST') {
			$json['error'] = 'Method not allowed';
			$this->response->setOutput(json_encode($json));
			return;
		}

		// Get POST data
		$post_data = json_decode(file_get_contents('php://input'), true);

		if (!$post_data) {
			$post_data = $this->request->post;
		}

		if (isset($post_data['marketplace_shop_id'])) {
			$marketplace_shop_id = (int)$post_data['marketplace_shop_id'];
		} else {
			$marketplace_shop_id = 0;
		}

		if (!$marketplace_shop_id) {
			$json['error'] = 'Shop ID is required';
			$this->response->setOutput(json_encode($json));
			return;
		}

		$this->load->model('extension/cogmart/marketplace/shop');

		$shop_info = $this->model_extension_cogmart_marketplace_shop->getShop($marketplace_shop_id);

		if ($shop_info) {
			$cart_data = [
				'marketplace_shop_id' => $marketplace_shop_id,
				'cart_id'             => $post_data['cart_id'] ?? '',
				'checkout_url'        => $post_data['checkout_url'] ?? ''
			];

			// Check if cart already exists for this shop
			$existing_cart = $this->model_extension_cogmart_marketplace_shop->getCartByShop($marketplace_shop_id);

			if ($existing_cart) {
				// Update existing cart
				$this->model_extension_cogmart_marketplace_shop->updateCart($existing_cart['marketplace_cart_id'], $cart_data);
				$json['marketplace_cart_id'] = $existing_cart['marketplace_cart_id'];
				$json['action'] = 'updated';
			} else {
				// Add new cart
				$json['marketplace_cart_id'] = $this->model_extension_cogmart_marketplace_shop->addToCart($cart_data);
				$json['action'] = 'created';
			}

			$json['success'] = true;
			$json['message'] = $this->language->get('text_success');

			// Get total cart count
			$carts = $this->model_extension_cogmart_marketplace_shop->getCarts();
			$json['total_carts'] = count($carts);
		} else {
			$json['success'] = false;
			$json['error'] = $this->language->get('error_shop');
		}

		$this->response->setOutput(json_encode($json));
	}
}

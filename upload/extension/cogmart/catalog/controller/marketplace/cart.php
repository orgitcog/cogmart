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
			$data['carts'][] = [
				'marketplace_cart_id' => $cart['marketplace_cart_id'],
				'shop_name'           => $cart['name'],
				'shop_domain'         => $cart['domain'],
				'cart_id'             => $cart['cart_id'],
				'checkout_url'        => $cart['checkout_url'],
				'shop_href'           => $this->url->link('extension/cogmart/marketplace/shop.info', 'marketplace_shop_id=' . $cart['marketplace_shop_id'] . '&language=' . $this->config->get('config_language')),
				'remove'              => $this->url->link('extension/cogmart/marketplace/cart.remove', 'marketplace_cart_id=' . $cart['marketplace_cart_id'] . '&language=' . $this->config->get('config_language'))
			];
		}

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
		$this->response->setOutput(json_encode($json));
	}
}

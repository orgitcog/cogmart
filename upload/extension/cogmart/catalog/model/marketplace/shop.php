<?php
namespace Opencart\Catalog\Model\Extension\Cogmart\Marketplace;

/**
 * Class Shop
 *
 * @package Opencart\Catalog\Model\Extension\Cogmart\Marketplace
 */
class Shop extends \Opencart\System\Engine\Model {
	/**
	 * Get shop
	 *
	 * @param int $marketplace_shop_id
	 *
	 * @return array<string, mixed>
	 */
	public function getShop(int $marketplace_shop_id): array {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "marketplace_shop` WHERE `marketplace_shop_id` = '" . (int)$marketplace_shop_id . "' AND `status` = '1'");

		return $query->row;
	}

	/**
	 * Get shop by domain
	 *
	 * @param string $domain
	 *
	 * @return array<string, mixed>
	 */
	public function getShopByDomain(string $domain): array {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "marketplace_shop` WHERE `domain` = '" . $this->db->escape($domain) . "' AND `status` = '1'");

		return $query->row;
	}

	/**
	 * Get shops
	 *
	 * @param array<string, mixed> $data
	 *
	 * @return array<int, array<string, mixed>>
	 */
	public function getShops(array $data = []): array {
		$sql = "SELECT * FROM `" . DB_PREFIX . "marketplace_shop` WHERE `status` = '1'";

		if (!empty($data['filter_name'])) {
			$sql .= " AND `name` LIKE '%" . $this->db->escape($data['filter_name']) . "%'";
		}

		if (!empty($data['filter_country'])) {
			$sql .= " AND `country` = '" . $this->db->escape($data['filter_country']) . "'";
		}

		if (!empty($data['filter_domain'])) {
			$sql .= " AND `domain` LIKE '%" . $this->db->escape($data['filter_domain']) . "%'";
		}

		$sort_data = [
			'name',
			'domain',
			'country',
			'date_added'
		];

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sql .= " ORDER BY `" . $data['sort'] . "`";
		} else {
			$sql .= " ORDER BY `name`";
		}

		if (isset($data['order']) && ($data['order'] == 'DESC')) {
			$sql .= " DESC";
		} else {
			$sql .= " ASC";
		}

		if (isset($data['start']) || isset($data['limit'])) {
			if ($data['start'] < 0) {
				$data['start'] = 0;
			}

			if ($data['limit'] < 1) {
				$data['limit'] = 20;
			}

			$sql .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
		}

		$query = $this->db->query($sql);

		return $query->rows;
	}

	/**
	 * Get total shops
	 *
	 * @param array<string, mixed> $data
	 *
	 * @return int
	 */
	public function getTotalShops(array $data = []): int {
		$sql = "SELECT COUNT(*) AS total FROM `" . DB_PREFIX . "marketplace_shop` WHERE `status` = '1'";

		if (!empty($data['filter_name'])) {
			$sql .= " AND `name` LIKE '%" . $this->db->escape($data['filter_name']) . "%'";
		}

		if (!empty($data['filter_country'])) {
			$sql .= " AND `country` = '" . $this->db->escape($data['filter_country']) . "'";
		}

		if (!empty($data['filter_domain'])) {
			$sql .= " AND `domain` LIKE '%" . $this->db->escape($data['filter_domain']) . "%'";
		}

		$query = $this->db->query($sql);

		return (int)$query->row['total'];
	}

	/**
	 * Get countries
	 *
	 * @return array<int, string>
	 */
	public function getCountries(): array {
		$query = $this->db->query("SELECT DISTINCT `country` FROM `" . DB_PREFIX . "marketplace_shop` WHERE `status` = '1' AND `country` IS NOT NULL AND `country` != '' ORDER BY `country` ASC");

		$countries = [];

		foreach ($query->rows as $row) {
			$countries[] = $row['country'];
		}

		return $countries;
	}

	/**
	 * Add to cart
	 *
	 * @param array<string, mixed> $data
	 *
	 * @return int
	 */
	public function addToCart(array $data): int {
		$customer_id = $this->customer->isLogged() ? $this->customer->getId() : null;
		$session_id = $this->session->getId();

		$this->db->query("INSERT INTO `" . DB_PREFIX . "marketplace_cart` SET 
			`customer_id` = " . ($customer_id ? "'" . (int)$customer_id . "'" : "NULL") . ", 
			`session_id` = '" . $this->db->escape($session_id) . "', 
			`marketplace_shop_id` = '" . (int)$data['marketplace_shop_id'] . "', 
			`cart_id` = '" . $this->db->escape($data['cart_id'] ?? '') . "', 
			`checkout_url` = '" . $this->db->escape($data['checkout_url'] ?? '') . "', 
			`date_added` = NOW(), 
			`date_modified` = NOW()");

		return $this->db->getLastId();
	}

	/**
	 * Get cart by shop
	 *
	 * @param int $marketplace_shop_id
	 *
	 * @return array<string, mixed>
	 */
	public function getCartByShop(int $marketplace_shop_id): array {
		$customer_id = $this->customer->isLogged() ? $this->customer->getId() : null;
		$session_id = $this->session->getId();

		$sql = "SELECT * FROM `" . DB_PREFIX . "marketplace_cart` WHERE `marketplace_shop_id` = '" . (int)$marketplace_shop_id . "'";

		if ($customer_id) {
			$sql .= " AND `customer_id` = '" . (int)$customer_id . "'";
		} else {
			$sql .= " AND `session_id` = '" . $this->db->escape($session_id) . "'";
		}

		$sql .= " ORDER BY `date_modified` DESC LIMIT 1";

		$query = $this->db->query($sql);

		return $query->row;
	}

	/**
	 * Get all carts for current session/customer
	 *
	 * @return array<int, array<string, mixed>>
	 */
	public function getCarts(): array {
		$customer_id = $this->customer->isLogged() ? $this->customer->getId() : null;
		$session_id = $this->session->getId();

		$sql = "SELECT mc.*, ms.name, ms.domain FROM `" . DB_PREFIX . "marketplace_cart` mc 
				LEFT JOIN `" . DB_PREFIX . "marketplace_shop` ms ON (mc.marketplace_shop_id = ms.marketplace_shop_id) 
				WHERE 1=1";

		if ($customer_id) {
			$sql .= " AND mc.customer_id = '" . (int)$customer_id . "'";
		} else {
			$sql .= " AND mc.session_id = '" . $this->db->escape($session_id) . "'";
		}

		$sql .= " ORDER BY mc.date_modified DESC";

		$query = $this->db->query($sql);

		return $query->rows;
	}

	/**
	 * Update cart
	 *
	 * @param int                  $marketplace_cart_id
	 * @param array<string, mixed> $data
	 *
	 * @return void
	 */
	public function updateCart(int $marketplace_cart_id, array $data): void {
		$this->db->query("UPDATE `" . DB_PREFIX . "marketplace_cart` SET 
			`cart_id` = '" . $this->db->escape($data['cart_id'] ?? '') . "', 
			`checkout_url` = '" . $this->db->escape($data['checkout_url'] ?? '') . "', 
			`date_modified` = NOW() 
		WHERE `marketplace_cart_id` = '" . (int)$marketplace_cart_id . "'");
	}

	/**
	 * Delete cart
	 *
	 * @param int $marketplace_cart_id
	 *
	 * @return void
	 */
	public function deleteCart(int $marketplace_cart_id): void {
		$this->db->query("DELETE FROM `" . DB_PREFIX . "marketplace_cart` WHERE `marketplace_cart_id` = '" . (int)$marketplace_cart_id . "'");
	}
}

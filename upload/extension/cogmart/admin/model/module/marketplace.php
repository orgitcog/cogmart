<?php
namespace Opencart\Admin\Model\Extension\Cogmart\Module;

/**
 * Class Marketplace
 *
 * @package Opencart\Admin\Model\Extension\Cogmart\Module
 */
class Marketplace extends \Opencart\System\Engine\Model {
	/**
	 * Add shop
	 *
	 * @param array<string, mixed> $data
	 *
	 * @return int
	 */
	public function addShop(array $data): int {
		$this->db->query("INSERT INTO `" . DB_PREFIX . "marketplace_shop` SET 
			`name` = '" . $this->db->escape($data['name']) . "', 
			`domain` = '" . $this->db->escape($data['domain']) . "', 
			`country` = '" . $this->db->escape($data['country'] ?? '') . "', 
			`storefront_access_token` = '" . $this->db->escape($data['storefront_access_token'] ?? '') . "', 
			`onboarding_info_completed` = '" . (int)($data['onboarding_info_completed'] ?? 0) . "', 
			`terms_accepted` = '" . (int)($data['terms_accepted'] ?? 0) . "', 
			`onboarding_completed` = '" . (int)($data['onboarding_completed'] ?? 0) . "', 
			`status` = '" . (int)($data['status'] ?? 1) . "', 
			`date_added` = NOW(), 
			`date_modified` = NOW()");

		return $this->db->getLastId();
	}

	/**
	 * Edit shop
	 *
	 * @param int                  $marketplace_shop_id
	 * @param array<string, mixed> $data
	 *
	 * @return void
	 */
	public function editShop(int $marketplace_shop_id, array $data): void {
		$this->db->query("UPDATE `" . DB_PREFIX . "marketplace_shop` SET 
			`name` = '" . $this->db->escape($data['name']) . "', 
			`domain` = '" . $this->db->escape($data['domain']) . "', 
			`country` = '" . $this->db->escape($data['country'] ?? '') . "', 
			`storefront_access_token` = '" . $this->db->escape($data['storefront_access_token'] ?? '') . "', 
			`onboarding_info_completed` = '" . (int)($data['onboarding_info_completed'] ?? 0) . "', 
			`terms_accepted` = '" . (int)($data['terms_accepted'] ?? 0) . "', 
			`onboarding_completed` = '" . (int)($data['onboarding_completed'] ?? 0) . "', 
			`status` = '" . (int)($data['status'] ?? 1) . "', 
			`date_modified` = NOW() 
		WHERE `marketplace_shop_id` = '" . (int)$marketplace_shop_id . "'");
	}

	/**
	 * Delete shop
	 *
	 * @param int $marketplace_shop_id
	 *
	 * @return void
	 */
	public function deleteShop(int $marketplace_shop_id): void {
		$this->db->query("DELETE FROM `" . DB_PREFIX . "marketplace_shop` WHERE `marketplace_shop_id` = '" . (int)$marketplace_shop_id . "'");
		$this->db->query("DELETE FROM `" . DB_PREFIX . "marketplace_cart` WHERE `marketplace_shop_id` = '" . (int)$marketplace_shop_id . "'");
	}

	/**
	 * Get shop
	 *
	 * @param int $marketplace_shop_id
	 *
	 * @return array<string, mixed>
	 */
	public function getShop(int $marketplace_shop_id): array {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "marketplace_shop` WHERE `marketplace_shop_id` = '" . (int)$marketplace_shop_id . "'");

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
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "marketplace_shop` WHERE `domain` = '" . $this->db->escape($domain) . "'");

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
		$sql = "SELECT * FROM `" . DB_PREFIX . "marketplace_shop` WHERE 1=1";

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
		$sql = "SELECT COUNT(*) AS total FROM `" . DB_PREFIX . "marketplace_shop` WHERE 1=1";

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
		$query = $this->db->query("SELECT DISTINCT `country` FROM `" . DB_PREFIX . "marketplace_shop` WHERE `country` IS NOT NULL AND `country` != '' ORDER BY `country` ASC");

		$countries = [];

		foreach ($query->rows as $row) {
			$countries[] = $row['country'];
		}

		return $countries;
	}
}

<?php
class ModelInstallerInstallerGroup extends Model {
	public function addInstallerGroup($data) {
		$this->db->query("INSERT INTO " . DB_PREFIX . "installer_group SET approval = '" . (int)$data['approval'] . "', sort_order = '" . (int)$data['sort_order'] . "'");

		$installer_group_id = $this->db->getLastId();

		foreach ($data['installer_group_description'] as $language_id => $value) {
			$this->db->query("INSERT INTO " . DB_PREFIX . "installer_group_description SET installer_group_id = '" . (int)$installer_group_id . "', language_id = '" . (int)$language_id . "', name = '" . $this->db->escape($value['name']) . "', description = '" . $this->db->escape($value['description']) . "'");
		}
		
		return $installer_group_id;
	}

	public function editInstallerGroup($installer_group_id, $data) {
		$this->db->query("UPDATE " . DB_PREFIX . "installer_group SET approval = '" . (int)$data['approval'] . "', sort_order = '" . (int)$data['sort_order'] . "' WHERE installer_group_id = '" . (int)$installer_group_id . "'");

		$this->db->query("DELETE FROM " . DB_PREFIX . "installer_group_description WHERE installer_group_id = '" . (int)$installer_group_id . "'");

		foreach ($data['installer_group_description'] as $language_id => $value) {
			$this->db->query("INSERT INTO " . DB_PREFIX . "installer_group_description SET installer_group_id = '" . (int)$installer_group_id . "', language_id = '" . (int)$language_id . "', name = '" . $this->db->escape($value['name']) . "', description = '" . $this->db->escape($value['description']) . "'");
		}
	}

	public function deleteInstallerGroup($installer_group_id) {
		$this->db->query("DELETE FROM " . DB_PREFIX . "installer_group WHERE installer_group_id = '" . (int)$installer_group_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "installer_group_description WHERE installer_group_id = '" . (int)$installer_group_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "product_discount WHERE installer_group_id = '" . (int)$installer_group_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "product_special WHERE installer_group_id = '" . (int)$installer_group_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "product_reward WHERE installer_group_id = '" . (int)$installer_group_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "tax_rate_to_installer_group WHERE installer_group_id = '" . (int)$installer_group_id . "'");
	}

	public function getInstallerGroup($installer_group_id) {
		$query = $this->db->query("SELECT DISTINCT * FROM " . DB_PREFIX . "installer_group cg LEFT JOIN " . DB_PREFIX . "installer_group_description cgd ON (cg.installer_group_id = cgd.installer_group_id) WHERE cg.installer_group_id = '" . (int)$installer_group_id . "' AND cgd.language_id = '" . (int)$this->config->get('config_language_id') . "'");

		return $query->row;
	}

	public function getInstallerGroups($data = array()) {
		$sql = "SELECT * FROM " . DB_PREFIX . "installer_group cg LEFT JOIN " . DB_PREFIX . "installer_group_description cgd ON (cg.installer_group_id = cgd.installer_group_id) WHERE cgd.language_id = '" . (int)$this->config->get('config_language_id') . "'";

		$sort_data = array(
			'cgd.name',
			'cg.sort_order'
		);

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sql .= " ORDER BY " . $data['sort'];
		} else {
			$sql .= " ORDER BY cgd.name";
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

	public function getInstallerGroupDescriptions($installer_group_id) {
		$installer_group_data = array();

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "installer_group_description WHERE installer_group_id = '" . (int)$installer_group_id . "'");

		foreach ($query->rows as $result) {
			$installer_group_data[$result['language_id']] = array(
				'name'        => $result['name'],
				'description' => $result['description']
			);
		}

		return $installer_group_data;
	}

	public function getTotalInstallerGroups() {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "installer_group");

		return $query->row['total'];
	}
}

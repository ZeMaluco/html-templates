<?php
class ModelInstallerInstaller extends Model {
	public function addInstaller($data) {
		$this->db->query("INSERT INTO " . DB_PREFIX . "installer SET installer_group_id = '" . (int)$data['installer_group_id'] . "', firstname = '" . $this->db->escape($data['firstname']) . "', lastname = '" . $this->db->escape($data['lastname']) . "', email = '" . $this->db->escape($data['email']) . "', telephone = '" . $this->db->escape($data['telephone']) . "', status = '" . (int)$data['status'] ."', date_added = NOW()");

		$installer_id = $this->db->getLastId();

		if (isset($data['address'])) {
			foreach ($data['address'] as $address) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "addressi SET installer_id = '" . (int)$installer_id . "', firstname = '" . $this->db->escape($address['firstname']) . "', lastname = '" . $this->db->escape($address['lastname']) . "', company = '" . $this->db->escape($address['company']) . "', address_1 = '" . $this->db->escape($address['address_1']) . "', address_2 = '" . $this->db->escape($address['address_2']) . "', city = '" . $this->db->escape($address['city']) . "', postcode = '" . $this->db->escape($address['postcode']) . "', country_id = '" . (int)$address['country_id'] . "', zone_id = '" . (int)$address['zone_id'] . "'");

				if (isset($address['default'])) {
					$address_id = $this->db->getLastId();

					$this->db->query("UPDATE " . DB_PREFIX . "installer SET addressi_id = '" . (int)$address_id . "' WHERE installer_id = '" . (int)$installer_id . "'");
				}
			}
		}
		
		
		return $installer_id;
	}

	public function editInstaller($installer_id, $data) {
		$this->db->query("UPDATE " . DB_PREFIX . "installer SET installer_group_id = '" . (int)$data['installer_group_id'] . "', firstname = '" . $this->db->escape($data['firstname']) . "', lastname = '" . $this->db->escape($data['lastname']) . "', email = '" . $this->db->escape($data['email']) . "', telephone = '" . $this->db->escape($data['telephone']) . "', status = '" . (int)$data['status'] . "' WHERE installer_id = '" . (int)$installer_id . "'");


		$this->db->query("DELETE FROM " . DB_PREFIX . "addressi WHERE installer_id = '" . (int)$installer_id . "'");

		if (isset($data['address'])) {
			foreach ($data['address'] as $address) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "addressi SET addressi_id = '" . (int)$address['address_id'] ."', installer_id = '" . (int)$installer_id . "', firstname = '" . $this->db->escape($address['firstname']) . "', lastname = '" . $this->db->escape($address['lastname']) . "', company = '" . $this->db->escape($address['company']) . "', address_1 = '" . $this->db->escape($address['address_1']) . "', address_2 = '" . $this->db->escape($address['address_2']) . "', city = '" . $this->db->escape($address['city']) . "', postcode = '" . $this->db->escape($address['postcode']) . "', country_id = '" . (int)$address['country_id'] . "', zone_id = '" . (int)$address['zone_id'] . "'");

				if (isset($address['default'])) {
					$address_id = $this->db->getLastId();

					$this->db->query("UPDATE " . DB_PREFIX . "installer SET addressi_id = '" . (int)$address_id . "' WHERE installer_id = '" . (int)$installer_id . "'");
				}
			}
		}
			
	}

	public function editToken($installer_id, $token) {
		$this->db->query("UPDATE " . DB_PREFIX . "installer SET token = '" . $this->db->escape($token) . "' WHERE installer_id = '" . (int)$installer_id . "'");
	}

	public function deleteInstaller($installer_id) {
		$this->db->query("DELETE FROM " . DB_PREFIX . "installer WHERE installer_id = '" . (int)$installer_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "installer_activity WHERE installer_id = '" . (int)$installer_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "installer_approval WHERE installer_id = '" . (int)$installer_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "addressi WHERE installer_id = '" . (int)$installer_id . "'");
	}

	public function getInstaller($installer_id) {
		$query = $this->db->query("SELECT DISTINCT * FROM " . DB_PREFIX . "installer WHERE installer_id = '" . (int)$installer_id . "'");

		return $query->row;
	}

	public function getInstallerByEmail($email) {
		$query = $this->db->query("SELECT DISTINCT * FROM " . DB_PREFIX . "installer WHERE LCASE(email) = '" . $this->db->escape(utf8_strtolower($email)) . "'");

		return $query->row;
	}
	public function getAllInstallers() {
		$sql = "SELECT * FROM " . DB_PREFIX . "installer";

		$query = $this->db->query($sql);
		return $query->rows;

	}
	public function getInstallers($data = array()) {
		$sql = "SELECT *, CONCAT(c.firstname, ' ', c.lastname) AS name, cgd.name AS installer_group FROM " . DB_PREFIX . "installer c LEFT JOIN " . DB_PREFIX . "installer_group_description cgd ON (c.installer_group_id = cgd.installer_group_id)";
			
		
		$sql .= " WHERE cgd.language_id = '" . (int)$this->config->get('config_language_id') . "'";
		
		$implode = array();

		if (!empty($data['filter_name'])) {
			$implode[] = "CONCAT(c.firstname, ' ', c.lastname) LIKE '%" . $this->db->escape($data['filter_name']) . "%'";
		}

		if (!empty($data['filter_email'])) {
			$implode[] = "c.email LIKE '" . $this->db->escape($data['filter_email']) . "%'";
		}

		if (!empty($data['filter_installer_group_id'])) {
			$implode[] = "c.installer_group_id = '" . (int)$data['filter_installer_group_id'] . "'";
		}

		if (isset($data['filter_status']) && $data['filter_status'] !== '') {
			$implode[] = "c.status = '" . (int)$data['filter_status'] . "'";
		}

		if (!empty($data['filter_date_added'])) {
			$implode[] = "DATE(c.date_added) = DATE('" . $this->db->escape($data['filter_date_added']) . "')";
		}

		if ($implode) {
			$sql .= " AND " . implode(" AND ", $implode);
		}

		$sort_data = array(
			'name',
			'c.email',
			'installer_group',
			'c.status',
			'c.date_added'
		);

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sql .= " ORDER BY " . $data['sort'];
		} else {
			$sql .= " ORDER BY name";
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

	public function getAddress($address_id) {
		$address_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "addressi WHERE addressi_id = '" . (int)$address_id . "'");

		if ($address_query->num_rows) {
			$country_query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "country` WHERE country_id = '" . (int)$address_query->row['country_id'] . "'");

			if ($country_query->num_rows) {
				$country = $country_query->row['name'];
				$iso_code_2 = $country_query->row['iso_code_2'];
				$iso_code_3 = $country_query->row['iso_code_3'];
				$address_format = $country_query->row['address_format'];
			} else {
				$country = '';
				$iso_code_2 = '';
				$iso_code_3 = '';
				$address_format = '';
			}

			$zone_query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "zone` WHERE zone_id = '" . (int)$address_query->row['zone_id'] . "'");

			if ($zone_query->num_rows) {
				$zone = $zone_query->row['name'];
				$zone_code = $zone_query->row['code'];
			} else {
				$zone = '';
				$zone_code = '';
			}

			return array(
				'addressi_id'     => $address_query->row['address_id'],
				'installer_id'    => $address_query->row['installer_id'],
				'firstname'      => $address_query->row['firstname'],
				'lastname'       => $address_query->row['lastname'],
				'company'        => $address_query->row['company'],
				'address_1'      => $address_query->row['address_1'],
				'address_2'      => $address_query->row['address_2'],
				'postcode'       => $address_query->row['postcode'],
				'city'           => $address_query->row['city'],
				'zone_id'        => $address_query->row['zone_id'],
				'zone'           => $zone,
				'zone_code'      => $zone_code,
				'country_id'     => $address_query->row['country_id'],
				'country'        => $country,
				'iso_code_2'     => $iso_code_2,
				'iso_code_3'     => $iso_code_3,
				'address_format' => $address_format,
			);
		}
	}

	public function getAddresses($installer_id) {
		$address_data = array();

		$query = $this->db->query("SELECT addressi_id FROM " . DB_PREFIX . "addressi WHERE installer_id = '" . (int)$installer_id . "'");

		foreach ($query->rows as $result) {
			$address_info = $this->getAddress($result['address_id']);

			if ($address_info) {
				$address_data[$result['address_id']] = $address_info;
			}
		}

		return $address_data;
	}


	public function getTotalInstallers($data = array()) {
		$sql = "SELECT COUNT(*) AS total FROM " . DB_PREFIX . "installer";

		$implode = array();

		if (!empty($data['filter_name'])) {
			$implode[] = "CONCAT(firstname, ' ', lastname) LIKE '%" . $this->db->escape($data['filter_name']) . "%'";
		}

		if (!empty($data['filter_email'])) {
			$implode[] = "email LIKE '" . $this->db->escape($data['filter_email']) . "%'";
		}


		if (!empty($data['filter_installer_group_id'])) {
			$implode[] = "installer_group_id = '" . (int)$data['filter_installer_group_id'] . "'";
		}

		if (isset($data['filter_status']) && $data['filter_status'] !== '') {
			$implode[] = "status = '" . (int)$data['filter_status'] . "'";
		}

		if (!empty($data['filter_date_added'])) {
			$implode[] = "DATE(date_added) = DATE('" . $this->db->escape($data['filter_date_added']) . "')";
		}

		if ($implode) {
			$sql .= " WHERE " . implode(" AND ", $implode);
		}

		$query = $this->db->query($sql);

		return $query->row['total'];
	}
        

	
	public function getTotalAddressesByInstallerId($installer_id) {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "addressi WHERE installer_id = '" . (int)$installer_id . "'");

		return $query->row['total'];
	}

	public function getTotalAddressesByCountryId($country_id) {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "addressi WHERE country_id = '" . (int)$country_id . "'");

		return $query->row['total'];
	}

	public function getTotalAddressesByZoneId($zone_id) {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "addressi WHERE zone_id = '" . (int)$zone_id . "'");

		return $query->row['total'];
	}

	public function getTotalInstallersByInstallerGroupId($installer_group_id) {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "installer WHERE installer_group_id = '" . (int)$installer_group_id . "'");

		return $query->row['total'];
	}











}

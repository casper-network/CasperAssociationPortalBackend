<?php
/**
 *
 * GET /public/get-profile
 *
 * @api
 * @param string $pseudonym
 *
 */
class PublicGetProfile extends Endpoints {
	function __construct(
		$pseudonym = ''
	) {
		global $db, $helper;

		require_method('GET');

		$pseudonym = parent::$params['pseudonym'] ?? '';

		if (preg_match(Regex::$pseudonym['pattern'], $pseudonym)) {
			// pseudonym type
			$profile = $db->do_select("
				SELECT
				guid,
				account_type,
				role,
				pseudonym,
				created_at AS registered_at,
				avatar_url,
				kyc_hash
				FROM users
				WHERE pseudonym = '$pseudonym'
			");
		} else {
			$profile = array();
		}

		$profile   = $profile[0] ?? array();
		$guid      = $profile['guid'] ?? '';
		$pseudonym = $profile['pseudonym'] ?? '';
		$kyc_hash  = $profile['kyc_hash'] ?? '';

		if (!$guid) {
			_exit(
				'error',
				'Invalid user profile',
				404,
				'Invalid user profile'
			);
		}

		// nodes
		$nodes = $db->do_select("
			SELECT public_key
			FROM user_nodes
			WHERE guid = '$guid'
			AND verified IS NOT NULL
		");

		$profile["nodes"] = $nodes;

		// kyc
		$kyc_info = $db->do_select("
			SELECT
			reference_id,
			status AS kyc_status,
			updated_at AS verified_at
			FROM shufti
			WHERE guid = '$guid'
		");

		$kyc_info     = $kyc_info[0] ?? array();
		$reference_id = $kyc_info['reference_id'] ?? '';
		$kyc_status   = $kyc_info['kyc_status'] ?? '';
		$verified_at  = $kyc_info['verified_at'] ?? '';

		$kyc_hash = md5(
			$pseudonym.
			$reference_id.
			$kyc_status
		);

		$profile["kyc_hash"]    = $kyc_hash;
		$profile["kyc_status"]  = $kyc_status;
		$profile["verified_at"] = $verified_at;

		// account info standard
		$public_key0 = $nodes[0]['public_key'] ?? '';
		$account_info_standard = $helper->get_account_info_standard($public_key0);

		// merge
		$return = array_merge(
			$profile,
			$account_info_standard
		);

		_exit(
			'success',
			$return
		);
	}
}
new PublicGetProfile();

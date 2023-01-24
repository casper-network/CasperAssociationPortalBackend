<?php
include_once('../../core.php');
/**
 *
 * POST /user/remove-node
 *
 * HEADER Authorization: Bearer
 *
 * @api
 * @param string $public_key
 *
 */
class UserRemoveNode extends Endpoints {
	function __construct(
		$public_key = ''
	) {
		global $db, $helper;

		require_method('POST');

		$auth       = authenticate_session(1);
		$user_guid  = $auth['guid'] ?? '';
		$public_key = parent::$params['public_key'] ?? '';

		$check = $db->do_select("
			SELECT *
			FROM user_nodes
			WHERE guid     = '$user_guid'
			AND public_key = '$public_key'
		");

		$count = count($check ?? array());

		if (!$check) {
			_exit(
				'error',
				'Unable to remove this node your account',
				400
			);
		}

		if ($count < 2) {
			_exit(
				'error',
				'Cannot remove the only node from your account',
				400
			);
		}

		$db->do_query("
			DELETE FROM user_nodes
			WHERE guid     = '$user_guid'
			AND public_key = '$public_key'
		");

		_exit(
			'success',
			'Removed node from your account'
		);
	}
}
new UserRemoveNode();
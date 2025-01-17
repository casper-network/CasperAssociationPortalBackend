<?php
/**
 *
 * POST /admin/deny-user
 *
 * HEADER Authorization: Bearer
 *
 * @api
 * @param string  $guid
 *
 */
class AdminDenyUser extends Endpoints {
	function __construct(
		$guid = ''
	) {
		global $db, $helper;

		require_method('POST');

		$auth       = authenticate_session(2);
		$admin_guid = $auth['guid'] ?? '';
		$user_guid  = (string)(parent::$params['guid'] ?? '');

		$db->do_query("
			UPDATE users
			SET letter = ''
			WHERE guid = '$user_guid'
			AND role LIKE '%user'
		");

		// email for user, as per global settings
		$enabled = (bool)$helper->fetch_setting('enabled_letter_denied');

		if ($enabled) {
			$subject = 'Your letter of motivation has been denied';
			$body = $helper->fetch_setting('email_letter_denied');

			$user_email = $db->do_select("
				SELECT email
				FROM users
				WHERE guid = '$user_guid'
			");
			$user_email = $user_email[0]['email'] ?? '';

			if($body && $user_email) {
				$helper->schedule_email(
					'user-alert',
					$user_email,
					$subject,
					$body
				);
			}
		}

		_exit(
			'success',
			'User letter of motivation denied'
		);
	}
}
new AdminDenyUser();

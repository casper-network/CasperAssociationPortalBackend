<?php
/**
 * GET /user/get-finished-ballots
 *
 * HEADER Authorization: Bearer
 *
 * @api
 */
class UserGetFinishedBallots extends Endpoints
{
    function __construct()
    {
        global $db, $helper, $pagelock;

        require_method('GET');

        $auth      = authenticate_session(1);
        $user_guid = $auth['guid'] ?? '';

        // 403 if page is locked from user access due to KYC or probation
        $pagelock->check($user_guid, 'votes');

        $ballots   = $db->do_select(
            "
			SELECT
			a.id,
			a.guid,
			a.title,
			a.description,
			a.start_time,
			a.end_time,
			a.status,
			a.created_at,
			a.updated_at
			FROM ballots AS a
			WHERE a.status = 'done'
			ORDER BY a.updated_at DESC
		"
        );

        $ballots = $ballots ?? array();

        foreach ($ballots as &$ballot) {
            $ballot_id = (int)($ballot['id'] ?? 0);
            $for_votes = $db->do_select(
                "
				SELECT count(guid) AS vCount
				FROM votes
				WHERE ballot_id = $ballot_id
				AND direction = 'for'
			"
            );
            $for_votes = (int)($for_votes[0]['vCount'] ?? 0);

            $against_votes = $db->do_select(
                "
				SELECT count(guid) AS vCount
				FROM votes
				WHERE ballot_id = $ballot_id
				AND direction = 'against'
			"
            );
            $against_votes = (int)($against_votes[0]['vCount'] ?? 0);

            if ($for_votes > $against_votes) {
                   $ballot['for_against'] = 'Passed '.$for_votes.'/'.$against_votes;
            }

            if ($for_votes < $against_votes) {
                $ballot['for_against'] = 'Failed '.$for_votes.'/'.$against_votes;
            }

            if ($for_votes == $against_votes) {
                $ballot['for_against'] = 'Tied '.$for_votes.'/'.$against_votes;
            }

			$ballot['total_votes'] = $for_votes + $against_votes;

			// my vote
			$myvote = $db->do_select("
				SELECT direction
				FROM  votes
				WHERE ballot_id = $ballot_id
				AND   guid      = '$user_guid'
			");
			$ballot['my_vote'] = $myvote[0]['direction'] ?? '';
		}

        _exit(
            'success',
            $ballots
        );
    }
}
new UserGetFinishedBallots();

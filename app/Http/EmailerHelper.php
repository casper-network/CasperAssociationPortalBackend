<?php

namespace App\Http;

use App\Mail\AdminAlert;
use App\Mail\UserAlert;
use App\Models\EmailerAdmin;
use App\Models\EmailerTriggerAdmin;
use App\Models\EmailerTriggerUser;
use Illuminate\Support\Facades\Mail;

class EmailerHelper {
    // Get Emailer Data
	public static function getEmailerData() {
		$data = [
			'admins' => [],
			'triggerAdmin' => [],
			'triggerUser' => [],
      		'triggerMember' => []
		];

		$admins = EmailerAdmin::where('id', '>', 0)->orderBy('email', 'asc')->get();
		$triggerAdmin = EmailerTriggerAdmin::where('id', '>', 0)->orderBy('id', 'asc')->get();
		$triggerUser = EmailerTriggerUser::where('id', '>', 0)->orderBy('id', 'asc')->get();

		if ($admins && count($admins)) {
			foreach ($admins as $admin) {
				$data['admins'][] = $admin->email;
			}
		}

		if ($triggerAdmin && count($triggerAdmin)) {
			foreach ($triggerAdmin as $item) {
				if ((int) $item->enabled)
					$data['triggerAdmin'][$item->title] = $item;
				else
					$data['triggerAdmin'][$item->title] = null;
			}
		}

		if ($triggerUser && count($triggerUser)) {
			foreach ($triggerUser as $item) {
				if ((int) $item->enabled)
					$data['triggerUser'][$item->title] = $item;
				else
					$data['triggerUser'][$item->title] = null;
			}
		}
		return $data;
    }
    
    // Send Admin Email
  	public static function triggerAdminEmail($title, $emailerData, $user = null) {
	    if (count($emailerData['admins'] ?? [])) {
			$item = $emailerData['triggerAdmin'][$title] ?? null;
			if ($item) {
				$content = $item['content'];
				$subject =$item['subject'];
				if ($user) {
				    $name =  $user->first_name . ' ' .  $user->last_name;
				    $content = str_replace('[name]', $name, $content);
				    $subject = str_replace('[name]', $name, $subject);
				    $content = str_replace('[email]', $user->email, $content);
				    $subject = str_replace('[email]', $user->email, $subject);
				}
				Mail::to($emailerData['admins'])->send(new AdminAlert($subject, $content));
			}
	    }
  	}

  	// Send User Email
  	public static function triggerUserEmail($to, $title, $emailerData, $user = null, $userAddress = null, $extraOptions = []) {
	    $item = $emailerData['triggerUser'][$title] ?? null;
	    if ($item) {
			$content = $item['content'];
			$subject = $item['subject'];
	      	if ($user) {
	      		if (isset($user->first_name) && isset($user->last_name)) {
	        		$name = $user->first_name . ' ' .  $user->last_name;
	        		$subject = str_replace('[name]', $name, $subject);
	        		$content = str_replace('[name]', $name, $content);
	        	}
	        	if (isset($user->email)) {
	        		$subject = str_replace('[email]', $user->email, $subject);
	        		$content = str_replace('[email]', $user->email, $content);
	        	}
	        	if (isset($extraOptions['perk_title'])) {
	        		$content = str_replace('[perk]', $extraOptions['perk_title'], $content);
	        	}
	        	if (isset($extraOptions['vote_title'])) {
	        		$content = str_replace('[vote]', $extraOptions['vote_title'], $content);
	        	}
	        	if ($userAddress && isset($userAddress->public_address_node)) {
	        		$content = str_replace('[node address]', $userAddress->public_address_node, $content);
	        	} else if (isset($user->public_address_node)) {
	        		$content = str_replace('[node address]', $user->public_address_node, $content);
	        	}
	      	}
	    	Mail::to($to)->send(new UserAlert($subject, $content));
	    }
  	}
}
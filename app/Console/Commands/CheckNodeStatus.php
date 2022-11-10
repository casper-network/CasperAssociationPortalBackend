<?php

namespace App\Console\Commands;

use App\Console\Helper;

use App\Models\MonitoringCriteria;
use App\Models\User;
use App\Models\UserAddress;
use App\Models\NodeInfo;
use App\Models\AllNodeData2;

use Carbon\Carbon;

use Illuminate\Support\Facades\DB;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CheckNodeStatus extends Command
{
    protected $signature = 'node-status:check';

    protected $description = 'Check node status';

    public function __construct() {
        parent::__construct();
    }

    public function handle() {
        $settings = Helper::getSettings();
        $current_era_id = (int) ($settings['current_era_id'] ?? 0);

        $now = Carbon::now('UTC');
        $users = User::with(['addresses', 'profile'])
                        ->where('role', 'member')
                        ->where('banned', 0)
                        ->get();

        $uptimeHours = 0;
        if (isset($settings['uptime_correction_unit']) && isset($settings['uptime_correction_value'])) {
            $unit = $settings['uptime_correction_unit'];
            $value = (float) $settings['uptime_correction_value'];

            if ($unit == 'Weeks') {
                $uptimeHours = $value * 7 * 24;
            } else if ($unit == 'Days') {
                $uptimeHours = $value * 24;
            } else {
                $uptimeHours = $value;
            }
        }

        foreach ($users as $user) {
            $addresses = $user->addresses ?? [];

            if (
                $addresses &&
                count($addresses) > 0 &&
                $user->node_verified_at &&
                $user->letter_verified_at &&
                $user->signature_request_id &&
                isset($user->profile) &&
                $user->profile &&
                $user->profile->status == 'approved'
            ) {
                // Verified Users

                $hasOnline = $hasOnProbation = $hasNotSuspended = false;
                
                foreach ($addresses as $address) {
                    $public_address_node = strtolower($address->public_address_node);

                    $temp = AllNodeData2::select(['id', 'uptime'])
                                        ->where('public_key', $public_address_node)
                                        ->where('era_id', $current_era_id)
                                        ->where('bid_inactive', 0)
                                        ->where('in_auction', 1)
                                        ->where('in_current_era', 1)
                                        ->first();
                    if ($temp) {
                        // All Node Data2 Record Exists!

                        $hasOnline = true;
                        $address->node_status = 'Online';
                        $address->extra_status = null;
                        $address->save();

                        // Historical Performance
                        if (
                            isset($settings['uptime_probation']) && 
                            (float) $settings['uptime_probation'] > 0
                        ) {
                            $uptime = Helper::calculateUptime($temp, $public_address_node, $settings);

                            if ($uptime < (float) $settings['uptime_probation']) {
                                $address->extra_status = 'On Probation';
                                if (!$address->probation_start || !$address->probation_end) {
                                    $address->probation_start = now();
                                    $address->probation_end = Carbon::now('UTC')->addHours($uptimeHours);
                                }
                                $address->save();

                                $now = Carbon::now('UTC');
                                if ($address->probation_end <= $now) {
                                    $address->extra_status = 'Suspended';
                                    $address->probation_start = null;
                                    $address->probation_end = null;
                                    $address->save();
                                } else {
                                    $hasOnProbation = true;
                                }
                            } else {
                                $address->probation_start = null;
                                $address->probation_end = null;
                                $address->save();
                            }
                        }

                        // Redmarks
                        if (
                            isset($settings['redmarks_revoke']) &&
                            (int) $settings['redmarks_revoke'] > 0
                        ) {
                            $bad_marks = Helper::calculateBadMarks($temp, $public_address_node, $settings);
                            
                            if ($bad_marks > (int) $settings['redmarks_revoke']) {
                                $address->extra_status = 'Suspended';
                                $address->probation_start = null;
                                $address->probation_end = null;
                                $address->save();
                            } else {
                                $hasNotSuspended = true;
                            }
                        }
                    } else {
                        // All Node Data2 Record Doesn't Exist!

                        $address->node_status = 'Offline';
                        $address->extra_status = null;
                        $address->probation_start = null;
                        $address->probation_end = null;
                        $address->save();
                    }
                }

                if ($hasOnline) {
                    $user->node_status = 'Online';
                    $user->save();
                } else {
                    $user->node_status = 'Offline';
                    $user->save();
                }

                if ($hasOnProbation) {
                    $user->profile->extra_status = 'On Probation';
                    $user->profile->save();
                } else {
                    $user->profile->extra_status = null;
                    $user->profile->save();
                }

                if ($hasOnline && !$hasNotSuspended) {
                    $user->profile->extra_status = 'Suspended';
                    $user->profile->save();
                }
            } else {
                // Non-Verified Users

                $user->node_status = null;
                $user->save();

                if ($user->profile) {
                    $user->profile->extra_status = null;
                    $user->profile->save();
                }

                if ($addresses && count($addresses) > 0) {
                    foreach ($addresses as $address) {
                        $address->node_status = null;
                        $address->extra_status = null;
                        $address->probation_start = null;
                        $address->probation_end = null;
                        $address->save();
                    }
                }
            }
        }
    }
}
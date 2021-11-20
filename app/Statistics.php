<?php

namespace App;

use App\Currency\Currency;
use App\Events\PublicUserNotification;
use App\Events\UserNotification;
use App\Notifications\CustomNotification;
use App\User;
use Illuminate\Support\Facades\Notification;
use Jenssegers\Mongodb\Eloquent\Model;

class Statistics extends Model
{
    protected $connection = 'mongodb';

    protected $collection = 'user_statistics';

    protected $fillable = [
        'user',
        'data',
        'vip_progress',
        'viplevel',
        'current_rakeback',
        'last_rakeback',
    ];

    protected $casts = [
        'data' => 'json',
    ];

    public static function insert($user, $currency, $wager, $multiplier, $profit)
    {
        $stats = self::where('user', $user)->first();
        if (! $stats) {
            $stats = self::create([
                'user' => $user,
                'data' => [],
                'viplevel' => 0,
                'vip_progress' => 0,
                'current_rakeback' => 0,
                'last_rakeback',
            ]);
        }

        $vipModifier = 1;
        $currentViplevel = ($stats->viplevel ?? 0);
        $wagerAmount = $wager * Currency::find($currency)->tokenPrice();

        //Add VIP progression
        $stats->update([
            'vip_progress' => round(($stats->vip_progress ?? 0), 2) + round(($wagerAmount * $vipModifier), 2),
        ]);
        $user = User::where('_id', $user)->first();
        $getcurrentViplevels = \App\VIPLevels::where('level', '=', ''.$currentViplevel.'')->first();

        $rakeBonus = floatval(($wagerAmount / 100) * $getcurrentViplevels->rake_percent);
        $stats->update([
            'current_rakeback' => ($stats->current_rakeback ?? 0) + $rakeBonus,
        ]);

        $getViplevels = \App\VIPLevels::where('level', '=', ''.($currentViplevel + 1).'')->first();
        $getFs = $getViplevels->fs_bonus;

        //Check if next VIP wager requirement is reached, then change VIP level
        if (($stats->vip_progress ?? 0) > $getViplevels->start) {
            if ($stats->viplevel === 0) {
                event(new PublicUserNotification('New VIP player', ' '.$user->name.' has just joined the Player VIP Club. Welcome him in the chat!'));
            }
            $stats->update([
                'vip_progress' => 0,
                'viplevel' => ($stats->viplevel ?? 0) + 1,
            ]);
            $eventUpdated = event(new \App\Events\UserNotification($user, 'VIP Club!', 'You have reached new VIP level.'));
            $notificationMessage = 'You have reached VIP Level'.$stats->viplevel.'. We have added '.$getFs.' free spins to your account. Make sure to check VIP page for more info.';
            Notification::send($user, new CustomNotification('New VIP Level Reached', $notificationMessage));

            //Add free spins if level up
            $user->update([
                'freespins' => ($user->freespins ?? 0) + ($getFs),
            ]);
        }

        $var_bets = 'bets_'.$currency;
        $var_wins = 'wins_'.$currency;
        $var_loss = 'loss_'.$currency;
        $var_wagered = 'wagered_'.$currency;
        $var_profit = 'profit_'.$currency;

        $data = $stats->data ?? null;
        if ($data == null) {
            $keys = ['usd_wager'];
            $data = array_fill_keys($keys, '0');
        }
        if (! array_key_exists($var_bets, $data)) {
            $keys = ['games_played', 'usd_wins', $var_bets, $var_wins, $var_loss, $var_wagered, $var_profit];
            $newData = array_fill_keys($keys, '0');
            $data = array_merge($data, $newData);
        }
        $data['usd_wager'] += $wager * Currency::find($currency)->tokenPrice();
        $data['usd_wins'] += $profit * Currency::find($currency)->tokenPrice() ?? 0;
        $data['games_played'] += 1;
        $data[$var_bets] += 1;
        $data[$var_wins] += $profit > 0 ? ($multiplier < 1 ? 0 : 1) : 0;
        $data[$var_loss] += $profit > 0 ? ($multiplier < 1 ? 1 : 0) : 1;
        $data[$var_wagered] += $wager;
        $data[$var_profit] += $profit > 0 ? ($multiplier < 1 ? -($wager) : ($profit)) : -($wager);

        $stats->update([
            'data' => $data,
        ]);
    }
}

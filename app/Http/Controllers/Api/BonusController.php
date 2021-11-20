<?php

namespace App\Http\Controllers\Api;

use App\Challenges;
use App\Currency\Currency;
use App\Promocode;
use App\Settings;
use App\Statistics;
use App\Transaction;
use App\TransactionStatistics;
use App\Utils\APIResponse;
use App\VIPLevels;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class BonusController
{
    public function challenges()
    {
        return APIResponse::success(Challenges::orderBy('completed')->latest()->get()->toArray());
    }

    public function activatePromo(Request $request)
    {
        $promocode = Promocode::where('code', $request->code)->first();
        if ($promocode == null) {
            return APIResponse::reject(1, 'Invalid promocode');
        }
        if ($promocode->expires->timestamp != Carbon::minValue()->timestamp && $promocode->expires->isPast()) {
            return APIResponse::reject(2, 'Expired (time)');
        }
        if ($promocode->usages != -1 && $promocode->times_used >= $promocode->usages) {
            return APIResponse::reject(3, 'Expired (usages)');
        }
        if (($promocode->vip ?? false) && auth('sanctum')->user()->vipLevel() == 0) {
            return APIResponse::reject(7, 'VIP only');
        }
        if (in_array(auth('sanctum')->user()->_id, $promocode->used)) {
            return APIResponse::reject(4, 'Already activated');
        }

        if ($promocode->currency !== 'freespins') {
            if (auth('sanctum')->user()->balance(Currency::find($promocode->currency))->get() > floatval(auth('sanctum')->user()->clientCurrency()->option('withdraw')) / 2) {
                return APIResponse::reject(8, 'Invalid balance');
            }
        }
        if (auth('sanctum')->user()->vipLevel() < 3 || ($promocode->vip ?? false) == false) {
            if (auth('sanctum')->user()->promocode_limit_reset == null || auth('sanctum')->user()->promocode_limit_reset->isPast()) {
                auth('sanctum')->user()->update([
                    'promocode_limit_reset' => Carbon::now()->addHours(auth('sanctum')->user()->vipLevel() >= 5 ? 11 : 12)->format('Y-m-d H:i:s'),
                    'promocode_limit' => 0,
                ]);
            }

            if (auth('sanctum')->user()->promocode_limit != null && auth('sanctum')->user()->promocode_limit >= (auth('sanctum')->user()->vipLevel() >= 2 ? 8 : 5)) {
                return APIResponse::reject(5, 'Promocode timeout');
            }
        }

        if (auth('sanctum')->user()->vipLevel() < 3 || ($promocode->vip ?? false) == false) {
            auth('sanctum')->user()->update([
                'promocode_limit' => auth('sanctum')->user()->promocode_limit == null ? 1 : auth('sanctum')->user()->promocode_limit + 1,
            ]);
        }

        $used = $promocode->used;
        array_push($used, auth('sanctum')->user()->_id);

        $promocode->update([
            'times_used' => $promocode->times_used + 1,
            'used' => $used,
        ]);

        $user = auth('sanctum')->user();

        //Check if promocode is not freespins type, to add balance - else add free spins to user
        if ($promocode->currency !== 'freespins') {
            $currency = Currency::find($promocode->currency);

            if (auth('sanctum')->user()->vipLevel() > 0) {
                $currentViplevel = auth('sanctum')->user()->vipLevel();
                $getcurrentViplevels = \App\VIPLevels::where('level', '=', ''.$currentViplevel.'')->first();
                $bonus = round(($promocode->sum / 100) * $getcurrentViplevels->promocode_bonus, 3);
                $amount = $currency->convertUSDToToken(($promocode->sum + $bonus));
            } else {
                $amount = $currency->convertUSDToToken($promocode->sum);
            }
            auth('sanctum')->user()->balance(Currency::find($promocode->currency))->add($amount, Transaction::builder()->message('Promocode')->get());
            TransactionStatistics::statsUpdate(auth('sanctum')->user()->_id, 'promocode', $promocode->sum);
            event(new \App\Events\UserNotification($user, 'Success!', 'Added '.$amount.' to your '.$currency->name.' balance.'));
        } else {
            auth('sanctum')->user()->update([
                'freespins' => number_format((float) $promocode->sum, 0, '.', ''),
            ]);
            TransactionStatistics::statsUpdate(auth('sanctum')->user()->_id, 'freespins_amount', $promocode->sum);
            event(new \App\Events\UserNotification($user, 'Success!', 'Your free spins have been added. Refresh and look for Free Spins in menu. Enjoy!'));
        }

        return APIResponse::success();
    }

    public function demo(Request $request)
    {
        //  if(auth('sanctum')->user()->balance(auth('sanctum')->user()->clientCurrency())->demo()->get() > auth('sanctum')->user()->clientCurrency()->minBet()) return APIResponse::reject(1, 'Demo balance is higher than zero');
        //  auth('sanctum')->user()->balance(auth('sanctum')->user()->clientCurrency())->demo()->add(auth('sanctum')->user()->clientCurrency()->option('demo'), Transaction::builder()->message('Demo')->get());
        return APIResponse::success();
    }

    public function partnerBonus(Request $request)
    {
        return APIResponse::reject(1, 'Not enough referrals');
        if (count(auth('sanctum')->user()->referral_wager_obtainer ?? []) < 10 || count(auth('sanctum')->user()->referral_wager_obtained ?? []) < ((auth('sanctum')->user()->referral_bonus_obtained ?? 0) + 1) * 10) {
            return APIResponse::reject(1, 'Not enough referrals');
        }

        $currency = Currency::find(Settings::get('bonus_currency'));

        $slices = [
            $currency->convertUSDToToken((float) Settings::get('partner_bonus_1')),
            $currency->convertUSDToToken((float) Settings::get('partner_bonus_2')),
            $currency->convertUSDToToken((float) Settings::get('partner_bonus_3')),
            $currency->convertUSDToToken((float) Settings::get('partner_bonus_4')),
            $currency->convertUSDToToken((float) Settings::get('partner_bonus_5')),
            $currency->convertUSDToToken((float) Settings::get('partner_bonus_6')),
            $currency->convertUSDToToken((float) Settings::get('partner_bonus_7')),
            $currency->convertUSDToToken((float) Settings::get('partner_bonus_8')),
            $currency->convertUSDToToken((float) Settings::get('partner_bonus_9')),
            $currency->convertUSDToToken((float) Settings::get('partner_bonus_10')),
            $currency->convertUSDToToken((float) Settings::get('partner_bonus_11')),
            $currency->convertUSDToToken((float) Settings::get('partner_bonus_12')),
            $currency->convertUSDToToken((float) Settings::get('partner_bonus_13')),
            $currency->convertUSDToToken((float) Settings::get('partner_bonus_14')),
        ];

        $slice = mt_rand(0, count($slices) - 1);
        auth('sanctum')->user()->balance(Currency::find(Settings::get('bonus_currency')))->add($slices[$slice], \App\Transaction::builder()->message('Referral bonus wheel')->get());
        auth('sanctum')->user()->update([
            'referral_bonus_obtained' => (auth('sanctum')->user()->referral_bonus_obtained ?? 0) + 1,
        ]);

        return APIResponse::success([
            'slice' => $slice,
        ]);
    }

    public function bonus(Request $request)
    {
        if (auth('sanctum')->user()->bonus_claim != null && ! auth('sanctum')->user()->bonus_claim->isPast()) {
            return APIResponse::reject(1, 'Timeout');
        }
        if (auth('sanctum')->user()->balance(Currency::find(Settings::get('bonus_currency')))->get() > auth('sanctum')->user()->clientCurrency()->minBet() * 2) {
            return APIResponse::reject(2, 'Balance is greater than zero');
        }

        $currency = Currency::find(Settings::get('bonus_currency'));

        $slices = [
            [40, $currency->convertUSDToToken((float) Settings::get('wheel_bonus_1', 1.00))],
            [30, $currency->convertUSDToToken((float) Settings::get('wheel_bonus_2', 1.10))],
            [21, $currency->convertUSDToToken((float) Settings::get('wheel_bonus_3', 0.5))],
            [15, $currency->convertUSDToToken((float) Settings::get('wheel_bonus_4', 0.5))],
            [15, $currency->convertUSDToToken((float) Settings::get('wheel_bonus_5', 0.6))],
            [7, $currency->convertUSDToToken((float) Settings::get('wheel_bonus_6', 0.8))],
            [0.80, $currency->convertUSDToToken((float) Settings::get('wheel_bonus_7', 0.95))],
            [0.50, $currency->convertUSDToToken((float) Settings::get('wheel_bonus_8', 1.5))],
            [0.35, $currency->convertUSDToToken((float) Settings::get('wheel_bonus_9', 1.4))],
            [0.20, $currency->convertUSDToToken((float) Settings::get('wheel_bonus_10', 1.2))],
            [0.05, $currency->convertUSDToToken((float) Settings::get('wheel_bonus_11', 1.1))],
        ];

        $slice = 0;

        foreach ($slices as $index => $bonusData) {
            if (mt_rand(1, 101) / 100 < $bonusData[0] / 100) {
                $slice = $index;
                break;
            }
        }
        $currency = Currency::find(Settings::get('bonus_currency'));
        $amount = $currency->convertTokenToUSD($slices[$slice][1]);
        TransactionStatistics::statsUpdate(auth('sanctum')->user()->_id, 'faucet', $amount);

        auth('sanctum')->user()->balance(Currency::find(Settings::get('bonus_currency')))->add($slices[$slice][1], Transaction::builder()->message('Faucet')->get());
        auth('sanctum')->user()->update(['bonus_claim' => Carbon::now()->addHours(24)]);

        return APIResponse::success([
            'slice' => $slice,
            'next' => Carbon::now()->addHours(24)->timestamp,
        ]);
    }

    public function slices(Request $request)
    {
        $currency = Currency::find(Settings::get('bonus_currency'));
        $wheel = [
            number_format((float) Settings::get('wheel_bonus_1'), 2, '.', ''),
            number_format((float) Settings::get('wheel_bonus_2'), 2, '.', ''),
            number_format((float) Settings::get('wheel_bonus_3'), 2, '.', ''),
            number_format((float) Settings::get('wheel_bonus_4'), 2, '.', ''),
            number_format((float) Settings::get('wheel_bonus_5'), 2, '.', ''),
            number_format((float) Settings::get('wheel_bonus_6'), 2, '.', ''),
            number_format((float) Settings::get('wheel_bonus_7'), 2, '.', ''),
            number_format((float) Settings::get('wheel_bonus_8'), 2, '.', ''),
            number_format((float) Settings::get('wheel_bonus_9'), 2, '.', ''),
            number_format((float) Settings::get('wheel_bonus_10'), 2, '.', ''),
            number_format((float) Settings::get('wheel_bonus_11'), 2, '.', ''),
        ];

        $partner = [
            number_format((float) Settings::get('partner_bonus_1'), 2, '.', ''),
            number_format((float) Settings::get('partner_bonus_2'), 2, '.', ''),
            number_format((float) Settings::get('partner_bonus_3'), 2, '.', ''),
            number_format((float) Settings::get('partner_bonus_4'), 2, '.', ''),
            number_format((float) Settings::get('partner_bonus_5'), 2, '.', ''),
            number_format((float) Settings::get('partner_bonus_6'), 2, '.', ''),
            number_format((float) Settings::get('partner_bonus_7'), 2, '.', ''),
            number_format((float) Settings::get('partner_bonus_8'), 2, '.', ''),
            number_format((float) Settings::get('partner_bonus_9'), 2, '.', ''),
            number_format((float) Settings::get('partner_bonus_10'), 2, '.', ''),
            number_format((float) Settings::get('partner_bonus_11'), 2, '.', ''),
            number_format((float) Settings::get('partner_bonus_12'), 2, '.', ''),
            number_format((float) Settings::get('partner_bonus_13'), 2, '.', ''),
            number_format((float) Settings::get('partner_bonus_14'), 2, '.', ''),
        ];

        return APIResponse::success([
            'currency' => $currency->id(),
            'wheel' => $wheel,
            'partner' => $partner,
        ]);
    }

    public function vipBonus(Request $request)
    {
        //Disabled

        return [];
        if (auth('sanctum')->user()->vipLevel() == 0) {
            return APIResponse::reject(1, 'Invalid VIP level');
        }
        if (auth('sanctum')->user()->weekly_bonus < 0.1) {
            return APIResponse::reject(2, 'Weekly bonus is too small');
        }
        if (auth('sanctum')->user()->weekly_bonus_obtained) {
            return APIResponse::reject(3, 'Already obtained in this week');
        }
        auth('sanctum')->user()->balance(Currency::find(Settings::get('bonus_currency')))->add(((auth('sanctum')->user()->weekly_bonus ?? 0) / 100) * auth('sanctum')->user()->vipBonus(), Transaction::builder()->message('Weekly VIP bonus')->get());
        auth('sanctum')->user()->update([
            'weekly_bonus_obtained' => true,
        ]);
        $currency = Currency::find(Settings::get('bonus_currency'));
        $amount = $currency->convertUSDToToken(((auth('sanctum')->user()->weekly_bonus ?? 0) / 100) * auth('sanctum')->user()->vipBonus());
        TransactionStatistics::statsUpdate(auth('sanctum')->user()->_id, 'weeklybonus', $amount);

        return APIResponse::success();
    }

    public function rakeback(Request $request)
    {
        $user = auth('sanctum')->user();
        $stats = Statistics::where('user', $user->_id)->first();
        if (auth('sanctum')->user()->vipLevel() == 0) {
            return APIResponse::reject(1, 'You need to be VIP level 1 atleast to use rakeback.');
        }
        if ($stats->current_rakeback < 0.25) {
            return APIResponse::reject(2, 'You need to have minimum of 0.25$ before claiming rakeback.');
        }
        //if($stats->last_rakeback != null && $stats->last_rakeback > Carbon::now()->format('Y-m-d H:i:s')) return APIResponse::reject(5, 'Timeout rakeback');

        $currency = Currency::find(Settings::get('bonus_currency'));
        $amount = number_format((float) $currency->convertUSDToToken($stats->current_rakeback / 100 ?? 0), 7, '.', '');

        $stats->update([
            'last_rakeback' => Carbon::now(),
            'current_rakeback' => 0,
        ]);

        auth('sanctum')->user()->balance($currency)->add($amount, Transaction::builder()->message('Rakeback Bonus')->get());

        TransactionStatistics::statsUpdate(auth('sanctum')->user()->_id, 'rakeback', $amount);

        return APIResponse::success();
    }

    public function telegram(Request $request)
    {
        if (auth('sanctum')->user()->telegram == null) {
            return APIResponse::reject(1, 'Link Telegram');
        }
        if (auth('sanctum')->user()->telegram_bonus) {
            return APIResponse::reject(2, 'Telegram bonus taked already');
        }
        auth('sanctum')->user()->update([
            'telegram_bonus' => true,
        ]);
        auth('sanctum')->user()->balance(Currency::find(Settings::get('bonus_currency')))->add(((float) Settings::get('telegram_bonus') / Currency::find(Settings::get('bonus_currency'))->tokenPrice()), Transaction::builder()->message('Telegram bonus')->get());

        return APIResponse::success();
    }

    public function depositBonus(Request $request)
    {
        if (auth('sanctum')->user()->first_deposit_bonus === 'used') {
            return APIResponse::reject(1, 'Bonus used');
        }
        if (auth('sanctum')->user()->first_deposit_bonus === 'activated') {
            return APIResponse::reject(1, 'Already activated');
        }
        auth('sanctum')->user()->update([
            'first_deposit_bonus' => 'activated',
        ]);

        return APIResponse::success();
    }

    public function depositBonusCancel(Request $request)
    {
        if (auth('sanctum')->user()->first_deposit_bonus === 'used') {
            return APIResponse::reject(1, 'Bonus used');
        }
        if (auth('sanctum')->user()->first_deposit_bonus === null) {
            return APIResponse::reject(1, 'Bonus not activated');
        }
        if (auth('sanctum')->user()->first_deposit_bonus === 'activated') {
            auth('sanctum')->user()->update([
                'first_deposit_bonus' => 'used',
            ]);
        }

        return APIResponse::success();
    }

    public function bonusStatus(Request $request)
    {
        $statistics = Statistics::where('user', auth('sanctum')->user()->_id)->first();
        $var = 'wagered_local_bonus';

        return APIResponse::success([
            'bonus_wagered' => number_format(($statistics->data[$var] ?? 0), 2, '.', ''),
            'bonus_goal' => number_format((auth('sanctum')->user()->bonus_goal ?? 0), 2, '.', ''),
            'bonus_balance' => $request->user()->balance(Currency::find('local_bonus'))->get(),
        ]);
    }

    public function exchangeBonus(Request $request)
    {
        $statistics = Statistics::where('user', auth('sanctum')->user()->_id)->first();
        $var = 'wagered_local_bonus';
        if (! auth('sanctum')->user()->validate2FA(false)) {
            return APIResponse::invalid2FASession();
        }
        auth('sanctum')->user()->reset2FAOneTimeToken();
        $currencyFrom = Currency::find($request->from);
        $currencyTo = Currency::find($request->to);
        if ($currencyFrom == $currencyTo) {
            APIResponse::reject(2, 'Abuse both currencies');
        }
        if (! $currencyFrom || ! $currencyTo) {
            return APIResponse::reject(2, 'Invalid currency non-stated');
        }
        if ($currencyFrom->id() !== 'local_bonus') {
            return APIResponse::reject(2, 'Invalid currency non-bonus');
        }
        if (($statistics->data[$var] ?? 0) <= auth('sanctum')->user()->bonus_goal ?? 0) {
            return APIResponse::reject(3, 'Conditions not met');
        }
        $amount = floatval($request->amount);
        if ($amount == 0) {
            return APIResponse::reject(1, 'Invalid amount');
        }
        if ($request->user()->balance($currencyFrom)->get() < $amount) {
            return APIResponse::reject(1, 'Invalid amount');
        }
        $request->user()->balance($currencyFrom)->subtract($amount, Transaction::builder()->message('Exchange Bonus Substracted')->get());
        $request->user()->balance($currencyTo)->add($currencyTo->convertUSDToToken($currencyFrom->convertTokenToUSD($amount)), Transaction::builder()->message('Exchange Bonus Added')->get());
        TransactionStatistics::statsUpdate(auth('sanctum')->user()->_id, 'depositbonus', $amount);

        return APIResponse::success();
    }
}

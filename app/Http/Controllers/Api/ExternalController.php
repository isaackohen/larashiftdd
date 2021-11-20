<?php

namespace App\Http\Controllers\Api;

use App\Challenges;
use App\Currency\Currency;
use App\Events\LiveFeedGame;
use App\Game;
use App\Gameslist;
use App\Leaderboard;
use App\Settings;
use App\Statistics;
use App\Transaction;
use App\User;
use App\Utils\APIResponse;
use Cache;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ExternalController
{
    public function methodBalance(Request $request)
    {
        $user = User::where('_id', $_GET['playerid'])->first();
        $currency = Currency::find($_GET['currency']);
        $getBalance = $user->balance($currency)->get();
        $getBalanceUSD = intval($currency->convertTokenToUSD($getBalance) * 100);

        $responsePayload = ['status' => 'ok', 'result' => ['balance' => $getBalanceUSD, 'freegames' => 0]];

        echo json_encode($responsePayload);
    }

    public function methodBet(Request $request)
    {
        //Log::alert($request->fullUrl());
        $user = User::where('_id', $_GET['playerid'])->first();
        $currency = Currency::find($_GET['currency']);
        $win = $_GET['win'];
        $bet = $_GET['bet'];
        $final = $_GET['final'];
        $gameid = $_GET['gameid'];
        $roundid = $request['roundid'];

        if ($bet > 0) {
            $betFloat = $_GET['bet'] / 100;
            $gameData = json_encode($request);
            $getBalance = $user->balance($currency)->get();
            $bet = number_format($currency->convertUSDToToken($betFloat), 8, '.', '');
            if (env('DEMO_MODE')) {
                $stat = Statistics::where('user', $user->_id)->first();
                if ($stat->data['usd_wager'] > env('DEMO_MODE_MAX_BET')) {
                    $user->balance($currency)->subtract(floatval($getBalance), Transaction::builder()->message('Demo balance expired')->get());
                    event(new \App\Events\TemporaryNotice($user, 'Demo expired', 'Max. bet reached on platform in demo mode - request to refresh your token'));

                    return;
                }
            }
            if ($bet > $getBalance) {
                return;
            }
            $user->balance($currency)->subtract(floatval($bet), Transaction::builder()->meta($roundid)->game($gameid)->get());
        }

        if ($win > 0) {
            $winFloat = $_GET['win'] / 100;
            $win = number_format($currency->convertUSDToToken($winFloat), 8, '.', '');
            $user->balance($currency)->add(floatval($win), Transaction::builder()->meta($roundid)->game($gameid)->get());
        }

        if ($final === '1') {
            $wagerFloat = $_GET['totalBet'] / 100 ?? 0;
            $wager = floatval(number_format($currency->convertUSDToToken($wagerFloat), 8, '.', '')) ?? 0;
            $winFloat = $_GET['totalWin'] / 100 ?? 0;
            $win = floatval(number_format($currency->convertUSDToToken($winFloat), 8, '.', '')) ?? 0;

            $status = 'lose';
            if ($win > $wager) {
                $status = 'win';
            }
            if ($wager > 0) {
                $multi = floatval(number_format(($win / $wager), 2, '.', ''));
            } else {
                $multi = 0;
            }
            $profit = ($win - $wager);

            $game = Game::create([
                'id' => DB::table('games')->count() + 1,
                'user' => $user->_id,
                'game' => $gameid,
                'wager' => $wager,
                'multiplier' => $multi,
                'status' => $status,
                'profit' => $win,
                'server_seed' => '-1',
                'client_seed' => '-1',
                'nonce' => '-1',
                'data' => [],
                'type' => 'external',
                'currency' => $currency->id(),
            ]);

            event(new LiveFeedGame($game, '1'));
            Statistics::insert(
                $game->user,
                $game->currency,
                $game->wager,
                $game->multiplier,
                $game->profit
            );

            $multiAllow = 0;
            if ($multi > floatval(1.20) || $multi < floatval(0.95)) {
                $multiAllow = '1';
            }

            if ($wagerFloat > 0.08 && $multiAllow === '1') {
                Challenges::check($gameid, $wagerFloat, $multi, $user->_id);
                Leaderboard::insert($game);

                //Weekly Bonus (disabled)
            //if ($user->vipLevel() > 0 && ($user->weekly_bonus ?? 0) < 100 && ((Settings::get('weekly_bonus_minbet') / Currency::find(Settings::get('bonus_currency'))->tokenPrice()) ?? 1) <= $game->wager) $user->update(['weekly_bonus' => ($user->weekly_bonus ?? 0) + 0.1]);
            }
        }

        $getBalance = $user->balance($currency)->get();
        $getBalanceUSD = intval($currency->convertTokenToUSD($getBalance) * 100);

        $responsePayload = ['status' => 'ok', 'result' => ['balance' => $getBalanceUSD, 'freegames' => 0]];

        echo json_encode($responsePayload);
    }

    public function methodGetGamesByProvider(Request $request)
    {
        $amountTake = '10';
        $gameId = $request->id;
        $thirdpartyGames = Gameslist::cachedList()->where('id', '=', $gameId)->first()->provider;
        $thirdpartyGames = Gameslist::cachedList()->where('provider', '=', $thirdpartyGames)->take($amountTake);

        $games = [];

        foreach ($thirdpartyGames as $game) {
            array_push($games, [
                'ext' => true,
                'name' => $game->name,
                'id' => $game->id,
                'icon' => $game->image,
                'cat' => [$game->category],
                'p' => $game->provider,
                'type' => 'external',
            ]);
        }

        return $games;
    }

    public function methodGetUrl(Request $request)
    {
        $freespins = false;
        if (auth('sanctum')->guest()) {
            $mode = 'demo';
            $currencyId = 'usd';
            $userId = 'guest';
        } else {
            $userId = auth('sanctum')->user()->id;
            $currencyId = auth('sanctum')->user()->clientCurrency()->id();
            if (auth('sanctum')->user()->freespins > 0) {
                $freespin_slots = \App\Settings::get('category_freespins');
                if (strpos($freespin_slots, $request->id) !== false) {
                    $freespins = true;
                    $user = User::where('_id', $userId)->first();
                }
            }
        }
        if ($request->mode === true && ! auth('sanctum')->guest()) {
            $mode = 'real';
        } else {
            $mode = 'demo';
        }
        $apikey = env('API_KEY');
        if ($freespins === true) {
            $url = 'https://api.dk.games/v2/createSession?apikey='.$apikey.'&userid='.$userId.'-'.$currencyId.'&game='.$request->id.'&mode=bonus&freespins='.$user->freespins.'&freespins_value=0.5';
            $user->update(['freespins' => 0]);
        } else {
            $url = 'https://api.dk.games/v2/createSession?apikey='.$apikey.'&userid='.$userId.'-'.$currencyId.'&game='.$request->id.'&mode='.$mode;
        }

        $result = file_get_contents($url);
        $decodeArray = json_decode($result, true);

        $gameslist = (Gameslist::where('id', $request->id)->first());

        return APIResponse::success([
            'url' => $decodeArray['url'],
            'mode' => ($mode === 'real' ? true : false),
            'id' => $gameslist['id'],
            'name' => $gameslist['name'],
            'image' => $gameslist['image'],
            'provider' => $gameslist['provider'],
        ]);

        return APIResponse::success([
            'status' => 'error',
        ]);
    }
}

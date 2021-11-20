<?php
namespace App\Http\Controllers\Auth;

use App\User;
use Illuminate\Http\Request;
use App\Utils\APIResponse;

class LogoutController
{
    public function logout()
    {
		auth()->logout();
		return APIResponse::success();
	}
	
}
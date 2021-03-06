<?php namespace App\Modules\Users\Controllers;

use App\Modules\Users\Models\User;
use App\Modules\Users\Models\UserLoginHistory;

use App, Page,Input, Redirect, View, Confide,Auth, Session,Validator,Lang;

class UserController extends \BaseController {

	/**
	 * User Model
	 * @var User
	 */
	protected $user;

	/**
	 * Inject the models.
	 * @param User $user
	 */
	private $useravatwidth;
	private $useravatheight;
	private $page;
	private $pagecontent;
	
	public function __construct(User $user) {
		parent::__construct();
		$this -> user = $user;
		
		$this->page = \App\Modules\Pages\Models\Page::first();
		$this->pagecontent = \BaseController::createSiderContent($this->page->id);
	}
	
	/**
	 * Displays the login form
	 *
	 */
	public function getLogin() {
		if(Auth::user()){
		$r = Session::get('loginRedirect');
			if (!empty($r)) {
				Session::forget('loginRedirect');
				return Redirect::to($r);
			}
			return Redirect::to('/');
		}
		$data['sidebar_right'] = $this->pagecontent['sidebar_right'];
		$data['sidebar_left'] = $this->pagecontent['sidebar_left'];
		$data['page'] = $this->page;

		return View::make('users::login', $data);
	}

	/**
	 * Attempt to do login
	 *
	 */
	public function postLogin() {
		$input = array('email' => Input::get('email'), // May be the username too
		'username' => Input::get('email'), // May be the username too
		'password' => Input::get('password'), 'remember' => Input::get('remember'), );

		// If you wish to only allow login from confirmed users, call logAttempt
		// with the second parameter as true.
		// logAttempt will check if the 'email' perhaps is the username.
		// Check that the user is confirmed.
		if (Confide::logAttempt($input, true)) {
			$login_user = Auth::user();
			
			$userloginlog = new UserLoginHistory;
			$userloginlog -> user_id = $login_user->id;
			$userloginlog -> save();
			
			$r = Session::get('loginRedirect');
			if (!empty($r)) {
				Session::forget('loginRedirect');
				return Redirect::to($r);
			}
			return Redirect::to('/');
		} else {
			// Check if there was too many login attempts
			if (Confide::isThrottled($input)) {
				$err_msg = 'Too many attempts';
			} elseif ($this -> user -> checkUserExists($input) && !$this -> user -> isConfirmed($input)) {
				$err_msg = 'Not confirmed';
			} else {
				$err_msg = 'Wrong credentials';
			}

			return Redirect::to('users/login') -> withInput(Input::except('password')) -> with('error', $err_msg);
		}
	}

	/**
	 * Users settings page
	 *
	 * @return View
	 */
	public function getCreate() {
		if(Auth::user()){
		$r = Session::get('loginRedirect');
			if (!empty($r)) {
				Session::forget('loginRedirect');
				return Redirect::to($r);
			}
			return Redirect::to('/');
		}
		// Show the page
		$data['sidebar_right'] = $this->pagecontent['sidebar_right'];
		$data['sidebar_left'] = $this->pagecontent['sidebar_left'];
		$data['page'] = $this->page;		
		return View::make('users::create', $data);
	}

	/**
	 * Stores new user
	 *
	 */
	public function postCreate() {
		$this -> user -> name = Input::get('name');
		$this -> user -> surname = Input::get('surname');
		$this -> user -> username = Input::get('username');
		$this -> user -> email = Input::get('email');

		$password = Input::get('password');
		$passwordConfirmation = Input::get('password_confirmation');
		if (!empty($password)) {
			if ($password === $passwordConfirmation) {
				$this -> user -> password = $password;
				// The password confirmation will be removed from model
				// before saving. This field will be used in Ardent's
				// auto validation.
				$this -> user -> password_confirmation = $passwordConfirmation;
			} else {
				// Redirect to the new user page
				return Redirect::to('users/create') -> withInput(Input::except('password', 'password_confirmation')) -> with('error', Lang::get('admin/users/messages.password_does_not_match'));
			}
		} else {
			unset($this -> user -> password);
			unset($this -> user -> password_confirmation);
		}

		// Save if valid. Password field will be hashed before save
		$this -> user -> save();

		if ($this -> user -> id) {
			// Redirect with success message, You may replace "Lang::get(..." for your custom message.
			return Redirect::to('users/login') -> with('notice', Lang::get('user/user.user_account_created'));
		} else {
			// Get validation errors (see Ardent package)
			$error = $this -> user -> errors() -> all();

			return Redirect::to('users/create') -> withInput(Input::except('password')) -> with('error', $error);
		}
	}
	
	/**
	 * Users settings page
	 *
	 * @return View
	 */
	public function getEdit() {
		
		// Show the page
		$data['sidebar_right'] = $this->pagecontent['sidebar_right'];
		$data['sidebar_left'] = $this->pagecontent['sidebar_left'];
		$data['page'] = $this->page;
		$data['user'] =Auth::user();
		
		return View::make('users::index', $data);
	}
	/**
	 * Edits a user
	 *
	 */
	public function postEdit($user_id) {
		// Validate the inputs
		
		$validator = Validator::make(Input::all(), array(
    			'name' => 'required|min:3',
   			 'surname' => 'required|min:3',
			));
		
		if ($validator -> passes()) {
			$user = User::find($user_id);
			
			$user -> name = Input::get('name');
			$user -> surname = Input::get('surname');

			$password = Input::get('password');
			$passwordConfirmation = Input::get('password_confirmation');
		if(Input::hasFile('avatar'))
			{
				$file = Input::file('avatar');
				$destinationPath = public_path() . '\avatar\\/';
				$filename = $file->getClientOriginalName();				
				$extension = $file -> getClientOriginalExtension();
				$name = sha1($filename . time()) . '.' . $extension;		
			
				Input::file('avatar')->move($destinationPath, $name);
				Thumbnail::generate_image_thumbnail($destinationPath. $name, $destinationPath .$name,$this->useravatwidth,$this->useravatheight);
				
				$user -> avatar = $name;
			}
			if (!empty($password) && !empty($passwordConfirmation)) {
				if ($password === $passwordConfirmation) {
					$user -> password = $password;
					// The password confirmation will be removed from model
					// before saving. This field will be used in Ardent's
					// auto validation.
					$user -> password_confirmation = $passwordConfirmation;
				} else {
					// Redirect to the new user page
					return Redirect::to('users/'.$user->id.'/edit') -> with('error', Lang::get('admin/users/messages.password_does_not_match'));
				}
			} else {
				unset($user -> password);
				unset($user -> password_confirmation);
			}
			// Save if valid. Password field will be hashed before save
			$user -> amend();
		}

		// Get validation errors (see Ardent package)
		$error = $user -> errors() -> all();

		if (empty($error)) {
			return Redirect::to('users/profile') -> with('success', Lang::get('user/user.user_account_updated'));
		} else {
			return Redirect::to('users/profile') -> withInput(Input::except('password', 'password_confirmation')) -> with('error', $error);
		}
	}

	

	/**
	 * Attempt to confirm account with code
	 *
	 * @param  string  $code
	 */
	public function getConfirm($code) {
		if (Confide::confirm($code)) {
			return Redirect::to('users/login') -> with('notice', Lang::get('confide::confide.alerts.confirmation'));
		} else {
			return Redirect::to('users/login') -> with('error', Lang::get('confide::confide.alerts.wrong_confirmation'));
		}
	}

	/**
	 * Displays the forgot password form
	 *
	 */
	public function getForgot() {
		$data['sidebar_right'] = $this->pagecontent['sidebar_right'];
		$data['sidebar_left'] = $this->pagecontent['sidebar_left'];
		$data['page'] = $this->page;
		
		return View::make('users::forgot', $data);
	}

	/**
	 * Attempt to reset password with given email
	 *
	 */
	public function postForgot() {
		if (Confide::forgotPassword(Input::get('email'))) {
			return Redirect::to('users/login') -> with('notice', Lang::get('confide::confide.alerts.password_forgot'));
		} else {
			return Redirect::to('users/forgot') -> withInput() -> with('error', Lang::get('confide::confide.alerts.wrong_password_forgot'));
		}
	}

	/**
	 * Shows the change password form with the given token
	 *
	 */
	public function getReset($token) {
		
		$data['sidebar_right'] = $this->pagecontent['sidebar_right'];
		$data['sidebar_left'] = $this->pagecontent['sidebar_left'];
		$data['page'] = $this->page;
		$data['token'] = $token;
		return View::make('users::reset', $data);
	}

	/**
	 * Attempt change password of the user
	 *
	 */
	public function postReset() {
		$input = array('token' => Input::get('token'), 'password' => Input::get('password'), 'password_confirmation' => Input::get('password_confirmation'), );

		// By passing an array with the token, password and confirmation
		if (Confide::resetPassword($input)) {
			return Redirect::to('users/login') -> with('notice', Lang::get('confide::confide.alerts.password_reset'));
		} else {
			return Redirect::to('users/reset/' . $input['token']) -> withInput() -> with('error', Lang::get('confide::confide.alerts.wrong_password_reset'));
		}
	}

	/**
	 * Log the user out of the application.
	 *
	 */
	public function getLogout() {
		Confide::logout();

		return Redirect::to('/');
	}

	/**
	 * Process a dumb redirect.
	 * @param $url1
	 * @param $url2
	 * @param $url3
	 * @return string
	 */
	public function processRedirect($url1, $url2, $url3) {
		$redirect = '';
		if (!empty($url1)) {
			$redirect = $url1;
			$redirect .= (empty($url2) ? '' : '/' . $url2);
			$redirect .= (empty($url3) ? '' : '/' . $url3);
		}
		return $redirect;
	}

}

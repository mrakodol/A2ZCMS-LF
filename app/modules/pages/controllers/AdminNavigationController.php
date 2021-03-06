<?php namespace App\Modules\Pages\Controllers;

use App, View, Session,Auth,URL,Input,Datatables,Redirect,Validator;
use App\Modules\Pages\Models\Page;
use App\Modules\Pages\Models\Navigation;
use App\Modules\Pages\Models\NavigationGroup;
use App\Modules\Pages\Models\PagePluginFunction;
use App\Modules\Pages\Models\PluginFunction;

class AdminNavigationController extends \AdminController {

	/**
	 * Navigation Repository
	 *
	 * @var Navigation
	 */
	protected $navigation;

	protected $navigationGroup;

	public function __construct(Navigation $navigation, NavigationGroup $navigationGroup,\AdminController $admin) {
		parent::__construct();
		if (!array_key_exists('manage_navigation',$admin->roles)){
			header('Location: '. $_SERVER['HTTP_REFERER']);
			exit ;
		}
		$this -> navigation = $navigation;
		$this -> navigationGroup = $navigationGroup;
	}

	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function getIndex() {
		$title = 'Navigation management';
		$navigations = $this -> navigation -> all();

		return View::make('pages::admin/navigation/index', compact('title', 'navigations'));
	}

	/**
	 * Show the form for creating a new resource.
	 *
	 * @return Response
	 */
	public function getCreate() {
		$title = 'Create a new navigation';

		$navigations = Navigation::all();
		$pageList = Page::lists('title', 'id');
		$navigationList = Navigation::lists('title', 'id');
		$navigationGroupList = NavigationGroup::lists('title', 'id');

		// Show the navigation group
		return View::make('pages::admin/navigation/create_edit', compact('title', 'navigations', 'pageList', 'navigationGroupList', 'navigationList'));
	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @return Response
	 */
	public function postCreate() {
		// Declare the rules for the form validation
		$rules = array('title' => 'required|min:3', 'link_type' => 'required', 'target' => 'required', 'url' => 'url');
		if ($link_type = Input::get('link_type')) {
			$link_field = ($link_type == 'page') ? 'page_id' : $link_type;
			$rules[$link_field] = 'required';
		}

		// Validate the inputs
		$validator = Validator::make(Input::all(), $rules);

		// Check if the form validates with success
		if ($validator -> passes()) {
			// Create a new navigation
			$this -> navigation -> title = Input::get('title');
			$this -> navigation -> link_type = Input::get('link_type');
			//set to null if is empty
			$this -> navigation -> parent = (Input::get('parent') != '') ? Input::get('parent') : NULL;
			$this -> navigation -> page_id = Input::get('page_id');
			$this -> navigation -> url = Input::get('url');
			$this -> navigation -> uri = Input::get('uri');
			$this -> navigation -> navigation_group_id = Input::get('navigation_group_id');
			$this -> navigation -> target = Input::get('target');
			$this -> navigation -> class = Input::get('class');

			$this -> navigation -> save();

			if ($this -> navigation -> id) {
				// Redirect to the new navigation
				return Redirect::to('admin/pages/navigation/' . $this -> navigation -> id . '/edit') -> with('success', 'Success');
			} else {
				// Get validation errors (see Ardent package)
				$error = $this -> navigation -> errors() -> all();

				return Redirect::to('admin/pages/navigation/create') -> with('error', $error);
			}
		}

		// Form validation failed
		return Redirect::to('admin/pages/navigation/create') -> withInput() -> withErrors($validator);
	}

	/**
	 * Show the form for editing the specified resource.
	 *
	 * @param $id
	 * @return Response
	 */
	public function getEdit($id) {

		$navigations = Navigation::all();
		$pageList = Page::lists('title', 'id');
		$navigationGroupList = NavigationGroup::lists('title', 'id');

		if ($id) {
			$navigation = Navigation::find($id);
			$navigationList = Navigation::where('id', '<>', $id) -> lists('title', 'id');

			// Title
			$title = 'Navigation group update';
			// mode
			$mode = 'edit';

			return View::make('pages::admin/navigation/create_edit', compact('navigation', 'title', 'mode', 'pageList', 'navigationGroupList', 'navigationList'));
		} else {
			return Redirect::to('admin/pages/navigation') -> with('error', 'Does not exist');
		}
	}

	/**
	 * Update the specified resource in storage.
	 *
	 * @param $navigation
	 * @return Response
	 */
	public function postEdit($id) {
		// Declare the rules for the form validation
		$rules = array('title' => 'required|min:3', 'link_type' => 'required', 'target' => 'required');
		if ($link_type = Input::get('link_type')) {
			$link_field = ($link_type == 'page') ? 'page_id' : $link_type;
			$url = ($link_type == 'url') ? '|url' : '';
			$rules[$link_field] = 'required' . $url;
		}

		// Validate the inputs
		$validator = Validator::make(Input::all(), $rules);

		$navigation = Navigation::find($id);

		$inputs = Input::all();
		//set to null if is empty
		$inputs['parent'] = ($inputs['parent'] != '') ? $inputs['parent'] : NULL;

		// Check if the form validates with success
		if ($validator -> passes()) {

			// Was the page updated?
			if ($navigation -> update($inputs)) {
				// Redirect to the navigation navigation
				return Redirect::to('admin/pages/navigation/' . $navigation -> id . '/edit') -> with('success', 'Success');
			} else {
				// Redirect to the navigation navigation
				return Redirect::to('admin/pages/navigation/' . $navigation -> id . '/edit') -> with('error', 'Error');
			}
		}

		// Form validation failed
		return Redirect::to('admin/pages/navigation/' . $navigation -> id . '/edit') -> withInput() -> withErrors($validator);
	}

	/**
	 * Remove the specified user from storage.
	 *
	 * @param $role
	 * @internal param $id
	 * @return Response
	 */
	public function getDelete($id) {
		$navigation = Navigation::find($id);
		// Was the role deleted?
		if ($navigation -> delete()) {
			// Redirect to the role management page
			return Redirect::to('admin/pages/navigation') -> with('success', 'Success');
		}

		// There was a problem deleting the role
		return Redirect::to('admin/pages/navigation') -> with('error', 'Error');
	}

	/**
	 * Show a list of all the pages formatted for Datatables.
	 *
	 * @return Datatables JSON
	 */
	public function getData() {
		$navs = Navigation::leftjoin('navigation_groups', 'navigation_groups.id', '=', 'navigation_links.navigation_group_id') 
						-> leftjoin('pages', 'navigation_links.page_id', '=', 'pages.id') 
						-> orderBy('navigation_links.position') 
						-> select(array('navigation_links.id', 'navigation_links.title', 'pages.title as page', 'navigation_links.link_type', 'navigation_groups.title as navigtion_group','navigation_links.created_at'));

		return Datatables::of($navs) -> add_column('actions', '<a href="{{{ URL::to(\'admin/pages/navigation/\' . $id . \'/edit\' ) }}}" class="iframe btn btn-default btn-sm"><i class="icon-edit "></i></a>
                               <a href="{{{ URL::to(\'admin/pages/navigation/\' . $id . \'/delete\' ) }}}" class="btn btn-sm btn-danger"><i class="icon-trash "></i></a>
                               <input type="hidden" name="row" value="{{$id}}" id="row">                               
            		') -> remove_column('id') -> make();
	}

	/**
	 * Reorder navigation
	 *
	 * @param order navigation
	 * @param navigation list
	 * @return boolean is sorting would be a correct
	 * /
	 */
	public function getReorder() {
		$list = Input::get('list');
		$items = explode(",", $list);
		$order = 1;
		foreach ($items as $value) {
			if ($value != '') {
				Navigation::where('id', '=', $value) -> update(array('position' => $order));

				$order++;
			}
		}
		return $list;
	}

}

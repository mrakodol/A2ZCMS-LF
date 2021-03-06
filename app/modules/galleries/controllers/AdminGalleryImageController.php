<?php namespace App\Modules\Galleries\Controllers;

use App, View, Session,Auth,URL,Input,Datatables,Redirect,Validator;
use App\Modules\Galleries\Models\Gallery;
use App\Modules\Galleries\Models\GalleryImage;
use App\Modules\Galleries\Models\GalleryImageComment;

class AdminGalleryImageController extends \AdminController {

	/**
	 * Comment Model
	 * @var Comment
	 */
	protected $gallery_image;

	/**
	 * Inject the models.
	 * @param Comment $comment
	 */
	public function __construct(GalleryImage $gallery_image,\AdminController $admin) {
		parent::__construct();
		if (!array_key_exists('manage_gallery_images',$admin->roles)){
			header('Location: '. $_SERVER['HTTP_REFERER']);
			exit ;
		}
		$this -> gallery_image = $gallery_image;
	}

	/**
	 * Show a list of all the gallery posts.
	 *
	 * @return View
	 */
	public function getIndex() {
		// Title
		$title = 'Gallery image management';

		// Gallery category
		$galleries = Gallery::all();

		$options = array();

		$options[0] = 'Choose';

		foreach ($galleries as $gallery) {
			$options[$gallery -> id] = $gallery -> title;
		}

		// Show the page
		return View::make('galleries::admin/galleryimages/index', compact('options', 'galleries', 'title'));
	}

	/**
	 * Remove the specified resource from storage.
	 *
	 * @param $blog
	 * @return Response
	 */
	public function postDelete($id) {
		//echo $pageId;exit;
		$gallery_image = GalleryImage::find($id);
		// Was the role deleted?
		if ($gallery_image -> delete()) {
			// Redirect to last page
			return Redirect::to('admin/galleries/galleryimages') -> with('success', 'Success');
		}
		// There was a problem deleting the comment post
		return Redirect::to('admin/galleries/galleryimages') -> with('error', 'Error');
	}

	/**
	 * Show the form for creating a new resource.
	 *
	 * @return Response
	 */
	public function getImageforgallery($galleryid) {
		$images = GalleryImage::join('galleries', 'galleries.id', '=', 'gallery_images.gallery_id') 
		-> select(array('gallery_images.id', 'gallery_images.content', 'galleries.folderid', 'gallery_images.voteup', 'gallery_images.votedown', 'gallery_images.hits as hits', 'gallery_images.created_at')) -> where('gallery_id', '=', $galleryid);

		return Datatables::of($images) -> make();
	}

}

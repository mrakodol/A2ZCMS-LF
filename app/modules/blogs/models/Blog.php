<?php namespace App\Modules\Blogs\Models;
use String;
use Illuminate\Database\Eloquent\SoftDeletingTrait;

class Blog extends \Eloquent {

	protected $dates = ['deleted_at'];

	/**
	 * Returns a formatted post content entry,
	 * this ensures that line breaks are returned.
	 *
	 * @return string
	 */
	public function content() {
		return nl2br($this -> content);
	}
	/**
	 * Get the blog's author.
	 *
	 * @return User
	 */
	public function author() {
		return $this -> belongsTo('User', 'user_id');
	}
	/**
	 * Get the blog's comments.
	 *
	 * @return array
	 */
	public function blogcomments() {
		return $this -> hasMany('BlogComment');
	}
	/**
	 * Get the blog's category.
	 *
	 * @return array
	 */
	public function category() {
		return $this -> hasMany('BlogBlogCategory');
	}
	/**
	 * Get the date the blog was created.
	 *
	 * @param \Carbon|null $date
	 * @return string
	 */
	public function date($date = null) {
		if (is_null($date)) {
			$date = $this -> created_at;
		}

		return String::date($date);
	}
	/**
	 * Get the URL to the blog.
	 *
	 * @return string
	 */
	public function url() {
		return Url::to('blog/'.$this -> slug);
	}
	/**
	 * Returns the date of the blog post creation,
	 * on a good and more readable format :)
	 *
	 * @return string
	 */
	public function created_at() {
		return $this -> date($this -> created_at);
	}
	/**
	 * Returns the date of the blog post last update,
	 * on a good and more readable format :)
	 *
	 * @return string
	 */
	public function updated_at() {
		return $this -> date($this -> updated_at);
	}

}

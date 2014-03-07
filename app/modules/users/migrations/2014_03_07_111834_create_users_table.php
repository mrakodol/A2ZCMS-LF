<?php
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsersTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up() {
		// Creates the users table
		Schema::create('users', function(Blueprint $table) {
			$table -> engine = 'InnoDB';
			$table -> increments('id');
			$table -> string('name');
			$table -> string('surname');
			$table -> string('email') -> unique();
			$table -> string('username') -> unique();
			$table -> string('password');
			$table -> string('confirmation_code');
			$table -> boolean('confirmed') -> default(false);
			$table -> boolean('active') -> default(false);
			$table -> timestamps();
			$table -> softDeletes();
		});

		// Creates password reminders table
		Schema::create('password_reminders', function(Blueprint $table) {
			$table -> engine = 'InnoDB';
			$table -> string('email');
			$table -> string('token');
			$table -> boolean('used') -> default(false);
			$table -> timestamp('created_at');
			$table -> softDeletes();
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down() {
		Schema::drop('password_reminders');
		Schema::drop('users');
	}

}

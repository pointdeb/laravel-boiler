<?php

use Illuminate\Database\Seeder;
use App\User;
use App\Rule;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // $this->call(UsersTableSeeder::class);
        $role = factory(Rule::class)->create(['label' => 'Webmaster', 'alias' => 'site_admin']);
        $role = factory(Rule::class)->create(['label' => 'Default', 'alias' => 'default']);

        $user = factory(User::class)->create(['email' => 'john.doe@gmail.com', 'name' => 'john', 'password' => bcrypt('secret')]);
        $rules = Rule::select('rule_id')->get()->map(function ($item) {
            return $item->rule_id;
        });
        $user->rules()->sync($rules);
        $user->save();

        $user = factory(User::class)->create(['email' => 'jane@gmail.com', 'name' => 'jane', 'password' => bcrypt('secret')]);
        $user->rules()->sync([$role->rule_id]);
        $user->save();
    }
}

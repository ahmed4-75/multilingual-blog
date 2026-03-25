<?php

namespace App\Console\Commands;

use App\Enums\LanguagesEnum;
use App\Models\Role;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Enum;

class CreateOwner extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'create:owner';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This Command Create Owner User';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        if(!Role::where('name','owner')->first()){
            Artisan::call('db:seed',['--class' => 'RoleAndPermissionSeeder']);
        }
        $name = $this->ask('What is the Owner Name');
        $email = $this->ask('What is the Owner Email');
        $password = $this->ask('What is the Owner Password');
        $lang = $this->ask('What is the Owner default Language');

        $validator = Validator::make([
            'name' => $name,
            'email' => $email, 
            'password' => $password, 
            'lang' => $lang    
        ],[
            'name' => 'required|string|max:50',
            'email' => 'required|string|email|unique:users,email',
            'password' => 'required|string|min:6',
            'lang' => ['required', new Enum(LanguagesEnum::class)]
        ]);

        if($validator->fails()){
            foreach($validator->errors()->all() as $error){
                $this->error($error);
            }
            return Command::FAILURE;
        }
        $user = User::create([
            'name' => $name,
            'email' => $email, 
            'password' => Hash::make($password), 
            'email_verified_at' => now(),
            'lang' => $lang,
            'favicon' => 'user_favicon.jpg'
        ]);
        $ownerRole = Role::where('name','owner')->first();
        $user->roles()->attach($ownerRole->id);
        $this->info($name.' Owner Created Successfully');
    }
}

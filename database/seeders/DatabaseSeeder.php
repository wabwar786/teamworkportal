<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\Employee;
use App\Models\Owner;
use App\Models\Task;
use App\Models\WorkLog;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ---- owners ----
        $super = Owner::create([
            'name' => 'Wabwar Admin', 'email' => 'admin@wabwar.com',
            'password' => Hash::make('password'), 'tier' => 'super',
            'scopes' => ['Everything'], 'active' => true,
        ]);

        $tariq = Owner::create([
            'name' => 'Tariq Mahmood', 'email' => 'tariq@wabwar.com',
            'password' => Hash::make('password'), 'tier' => 'head',
            'scopes' => ['Dev team', 'Support', 'Devices'], 'active' => true,
        ]);

        $nadia = Owner::create([
            'name' => 'Nadia Saleem', 'email' => 'nadia@wabwar.com',
            'password' => Hash::make('password'), 'tier' => 'head',
            'scopes' => ['Design team', 'Support'], 'active' => true,
        ]);

        // ---- employees ----
        $team = [
            ['Hassan Raza', 'Developer', $tariq->id, ['done', 'up', 'crit', 'saved'], false],
            ['Ayesha Khan', 'Developer', $tariq->id, ['done', 'up', 'crit', 'saved'], false],
            ['Sana Iqbal', 'Designer', $nadia->id, ['done', 'up', 'saved'], false],
            ['Usman Tariq', 'Marketing', $tariq->id, ['done', 'up', 'crit'], false],
            ['Kamran Ali', 'Developer', $tariq->id, ['done', 'up', 'crit', 'saved'], true], // archived
        ];

        foreach ($team as [$name, $role, $head, $blocks, $archived]) {
            $emp = Employee::create([
                'name' => $name,
                'slug' => \Illuminate\Support\Str::slug($name),
                'role' => $role, 'head_id' => $head, 'blocks' => $blocks,
                'joined_at' => now()->subMonths(3),
                'active' => ! $archived, 'archived' => $archived,
                'left_at' => $archived ? now()->subDays(20) : null,
            ]);

            WorkLog::create([
                'employee_id' => $emp->id,
                'log_date' => now()->toDateString(),
                'done' => "Sample completed task for {$name}\nAnother finished item",
                'upload' => 'Uploaded a build',
                'pending' => $archived ? '' : 'One pending item',
                'critical' => 'A critical fix',
                'saved' => "https://example.com/{$emp->slug}\nuser: demo  pass: demo123",
                'saved_at' => now(),
            ]);
        }

        // ---- tasks ----
        $hassan = Employee::where('slug', 'hassan-raza')->first();
        Task::create(['employee_id' => $hassan->id, 'assigned_by' => $tariq->id, 'body' => 'Renew the Al-Noor SSL certificate.', 'due' => 'Tomorrow 12 PM', 'status' => 'open']);
        Task::create(['employee_id' => $hassan->id, 'assigned_by' => $tariq->id, 'body' => 'Write the POS invoice backup script.', 'due' => 'Monday', 'status' => 'open']);

        // ---- support clients ----
        Client::create(['name' => 'Al-Noor Traders', 'key' => 'alnoor-pos-7f3a', 'channel' => 'web']);
        Client::create(['name' => 'City Mart Billing', 'key' => 'citymart-billing-4b2c', 'channel' => 'desktop']);
        Client::create(['name' => 'Noor Pharmacy', 'key' => 'noor-pos-9a1d', 'channel' => 'web']);
    }
}

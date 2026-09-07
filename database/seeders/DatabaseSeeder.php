<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\Employee;
use App\Models\Owner;
use App\Models\Task;
use App\Models\WorkLog;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * Idempotent seeder — safe to run on every deploy. It creates the super
 * owner and demo data if missing, and never duplicates or overwrites real
 * data you enter later. The super owner's password is (re)set so you always
 * have a known way in; change it after first login.
 */
class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ---- super owner (always ensured) ----
        $super = Owner::updateOrCreate(
            ['email' => 'admin@wabwar.com'],
            [
                'name' => 'Wabwar Admin',
                'password' => Hash::make('password'),
                'tier' => 'super',
                'scopes' => ['Everything'],
                'active' => true,
            ]
        );

        // ---- demo heads (only created if missing; not overwritten) ----
        $tariq = Owner::firstOrCreate(
            ['email' => 'tariq@wabwar.com'],
            ['name' => 'Tariq Mahmood', 'password' => Hash::make('password'),
             'tier' => 'head', 'scopes' => ['Dev team', 'Support', 'Devices'], 'active' => true]
        );
        $nadia = Owner::firstOrCreate(
            ['email' => 'nadia@wabwar.com'],
            ['name' => 'Nadia Saleem', 'password' => Hash::make('password'),
             'tier' => 'head', 'scopes' => ['Design team', 'Support'], 'active' => true]
        );

        // ---- demo employees ----
        $team = [
            ['Hassan Raza', 'Developer', $tariq->id, ['done', 'up', 'crit', 'saved'], false],
            ['Ayesha Khan', 'Developer', $tariq->id, ['done', 'up', 'crit', 'saved'], false],
            ['Sana Iqbal', 'Designer', $nadia->id, ['done', 'up', 'saved'], false],
            ['Usman Tariq', 'Marketing', $tariq->id, ['done', 'up', 'crit'], false],
            ['Kamran Ali', 'Developer', $tariq->id, ['done', 'up', 'crit', 'saved'], true],
        ];

        foreach ($team as [$name, $role, $head, $blocks, $archived]) {
            $emp = Employee::firstOrCreate(
                ['slug' => Str::slug($name)],
                [
                    'name' => $name, 'role' => $role, 'head_id' => $head, 'blocks' => $blocks,
                    'joined_at' => now()->subMonths(3),
                    'active' => ! $archived, 'archived' => $archived,
                    'left_at' => $archived ? now()->subDays(20) : null,
                ]
            );

            WorkLog::firstOrCreate(
                ['employee_id' => $emp->id, 'log_date' => now()->toDateString()],
                [
                    'done' => "Sample completed task for {$name}\nAnother finished item",
                    'upload' => 'Uploaded a build',
                    'pending' => $archived ? '' : 'One pending item',
                    'critical' => 'A critical fix',
                    'saved' => "https://example.com/{$emp->slug}\nuser: demo  pass: demo123",
                    'saved_at' => now(),
                ]
            );
        }

        // ---- demo tasks (only if none exist for Hassan) ----
        $hassan = Employee::where('slug', 'hassan-raza')->first();
        if ($hassan && $hassan->tasks()->count() === 0) {
            Task::create(['employee_id' => $hassan->id, 'assigned_by' => $tariq->id,
                'body' => 'Renew the Al-Noor SSL certificate.', 'due' => 'Tomorrow 12 PM', 'status' => 'open']);
            Task::create(['employee_id' => $hassan->id, 'assigned_by' => $tariq->id,
                'body' => 'Write the POS invoice backup script.', 'due' => 'Monday', 'status' => 'open']);
        }

        // ---- demo support clients ----
        Client::firstOrCreate(['key' => 'alnoor-pos-7f3a'], ['name' => 'Al-Noor Traders', 'channel' => 'web']);
        Client::firstOrCreate(['key' => 'citymart-billing-4b2c'], ['name' => 'City Mart Billing', 'channel' => 'desktop']);
        Client::firstOrCreate(['key' => 'noor-pos-9a1d'], ['name' => 'Noor Pharmacy', 'channel' => 'web']);
    }
}

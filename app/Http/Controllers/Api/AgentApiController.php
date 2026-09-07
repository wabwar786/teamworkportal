<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AppUsage;
use App\Models\BlockRule;
use App\Models\Device;
use App\Models\DeviceEvent;
use App\Models\RemoteCommand;
use App\Models\Screenshot;
use Illuminate\Http\Request;

/**
 * Endpoints used by the C# agent. All except enroll require the
 * AuthenticateAgent middleware, which resolves `agent_device`.
 */
class AgentApiController extends Controller
{
    /**
     * First run: the agent enrolls with an employee slug + machine info.
     * Returns a long-lived enroll_token it stores and reuses.
     */
    public function enroll(Request $request)
    {
        $data = $request->validate([
            'employee_slug' => 'required|exists:employees,slug',
            'pc_name' => 'required|string|max:120',
            'os' => 'nullable|string|max:80',
            'ip' => 'nullable|string|max:60',
            'agent_version' => 'nullable|string|max:20',
        ]);

        $employee = \App\Models\Employee::where('slug', $data['employee_slug'])->firstOrFail();

        $device = Device::firstOrCreate(
            ['employee_id' => $employee->id, 'pc_name' => $data['pc_name']],
            ['enroll_token' => bin2hex(random_bytes(24))]
        );

        $device->update([
            'os' => $data['os'] ?? $device->os,
            'ip' => $data['ip'] ?? $device->ip,
            'agent_version' => $data['agent_version'] ?? $device->agent_version,
            'last_seen' => now(),
            'online' => true,
        ]);

        return response()->json(['token' => $device->enroll_token, 'device_id' => $device->id]);
    }

    private function device(Request $request): Device
    {
        return $request->attributes->get('agent_device');
    }

    /** Lightweight heartbeat (middleware already updated last_seen). */
    public function heartbeat(Request $request)
    {
        return response()->json(['ok' => true, 'server_time' => now()->toIso8601String()]);
    }

    /** Report a batch of file/USB/app events. */
    public function events(Request $request)
    {
        $device = $this->device($request);
        $events = $request->validate([
            'events' => 'required|array',
            'events.*.type' => 'required|in:copy,move,delete,usb,app',
            'events.*.path' => 'nullable|string',
            'events.*.destination' => 'nullable|string',
            'events.*.size' => 'nullable|string',
            'events.*.at' => 'nullable|date',
        ])['events'];

        foreach ($events as $e) {
            DeviceEvent::create([
                'device_id' => $device->id,
                'type' => $e['type'],
                'path' => $e['path'] ?? null,
                'destination' => $e['destination'] ?? null,
                'size' => $e['size'] ?? null,
                'happened_at' => $e['at'] ?? now(),
            ]);
        }

        return response()->json(['stored' => count($events)]);
    }

    /** Report per-app active minutes for today. */
    public function usage(Request $request)
    {
        $device = $this->device($request);
        $rows = $request->validate([
            'apps' => 'required|array',
            'apps.*.name' => 'required|string',
            'apps.*.minutes' => 'required|integer|min:0',
        ])['apps'];

        foreach ($rows as $r) {
            AppUsage::updateOrCreate(
                ['device_id' => $device->id, 'usage_date' => now()->toDateString(), 'app_name' => $r['name']],
                ['minutes' => $r['minutes']]
            );
        }

        return response()->json(['ok' => true]);
    }

    /** Upload a screenshot (visible monitoring). */
    public function screenshot(Request $request)
    {
        $device = $this->device($request);
        $data = $request->validate([
            'image' => 'required|string',       // base64 data URL or uploaded path
            'active_app' => 'nullable|string',
            'at' => 'nullable|date',
        ]);

        // In production, decode + store to disk/S3 and save the path.
        $path = 'screenshots/'.$device->id.'_'.now()->timestamp.'.jpg';
        // Storage::disk('public')->put($path, base64_decode(...));  // wired in real build

        Screenshot::create([
            'device_id' => $device->id,
            'url' => $path,
            'active_app' => $data['active_app'] ?? null,
            'taken_at' => $data['at'] ?? now(),
        ]);

        return response()->json(['ok' => true]);
    }

    /** Agent pulls queued safe commands, marks them sent. */
    public function pullCommands(Request $request)
    {
        $device = $this->device($request);

        $commands = RemoteCommand::where('device_id', $device->id)
            ->where('status', 'queued')->orderBy('id')->get();

        RemoteCommand::whereIn('id', $commands->pluck('id'))->update(['status' => 'sent']);

        return response()->json([
            'commands' => $commands->map(fn ($c) => [
                'id' => $c->id, 'action' => $c->action, 'args' => $c->args,
            ]),
        ]);
    }

    /** Agent reports a command finished. */
    public function ackCommand(Request $request)
    {
        $data = $request->validate([
            'id' => 'required|exists:remote_commands,id',
            'status' => 'required|in:done,failed',
        ]);

        RemoteCommand::where('id', $data['id'])
            ->where('device_id', $this->device($request)->id)
            ->update(['status' => $data['status'], 'done_at' => now()]);

        return response()->json(['ok' => true]);
    }

    /** Current blocklist for this device (site + app). */
    public function blocklist(Request $request)
    {
        $device = $this->device($request);
        $rules = BlockRule::where(function ($q) use ($device) {
            $q->where('target', 'all')->orWhere('target', (string) $device->id);
        })->get();

        return response()->json([
            'sites' => $rules->where('scope', 'site')->pluck('value')->values(),
            'apps' => $rules->where('scope', 'app')->pluck('value')->values(),
        ]);
    }
}

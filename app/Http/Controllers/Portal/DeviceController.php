<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\BlockRule;
use App\Models\Device;
use App\Models\DeviceEvent;
use App\Models\RemoteCommand;
use Illuminate\Http\Request;

class DeviceController extends Controller
{
    public function index()
    {
        return view('portal.devices', [
            'devices' => Device::with('employee')->get(),
        ]);
    }

    public function activity()
    {
        $devices = Device::with(['employee', 'appUsages' => function ($q) {
            $q->where('usage_date', now()->toDateString())->orderByDesc('minutes');
        }])->get();

        return view('portal.device-activity', compact('devices'));
    }

    public function files()
    {
        return view('portal.device-files', [
            'events' => DeviceEvent::with('device.employee')->orderByDesc('happened_at')->limit(200)->get(),
        ]);
    }

    public function blocklist()
    {
        return view('portal.blocklist', [
            'sites' => BlockRule::where('scope', 'site')->get(),
            'apps' => BlockRule::where('scope', 'app')->get(),
            'devices' => Device::with('employee')->get(),
        ]);
    }

    public function addBlock(Request $request)
    {
        $data = $request->validate([
            'scope' => 'required|in:site,app',
            'value' => 'required|string|max:200',
            'target' => 'nullable|string',
        ]);
        BlockRule::create($data + ['target' => $data['target'] ?? 'all']);
        return back()->with('ok', 'Blocked.');
    }

    public function removeBlock(BlockRule $rule)
    {
        $rule->delete();
        return back();
    }

    /** Remote actions page. */
    public function remote(Request $request)
    {
        return view('portal.remote', [
            'devices' => Device::with('employee')->get(),
            'commands' => RemoteCommand::with('device.employee')->orderByDesc('id')->limit(30)->get(),
            'actions' => [
                'lock' => 'Lock the PC', 'message' => 'Show a notice',
                'restart' => '15s warning', 'shutdown' => 'Power off',
                'logoff' => 'Sign out user', 'folder_sync' => 'Pull a folder',
                'screenshot' => 'Capture now', 'blocklist_push' => 'Send new list',
                'app_close' => 'Close an app', 'agent_update' => 'Push new version',
            ],
        ]);
    }

    /**
     * Queue a SAFE, whitelisted remote command. There is no arbitrary
     * command path by design — only the actions enumerated here.
     */
    public function runCommand(Request $request)
    {
        $data = $request->validate([
            'device_id' => 'required|exists:devices,id',
            'action' => 'required|in:lock,message,restart,shutdown,logoff,folder_sync,screenshot,blocklist_push,app_close,agent_update',
            'text' => 'nullable|string|max:500',
            'path' => 'nullable|string|max:300',
        ]);

        RemoteCommand::create([
            'device_id' => $data['device_id'],
            'action' => $data['action'],
            'args' => array_filter([
                'text' => $data['text'] ?? null,
                'path' => $data['path'] ?? null,
            ]),
            'status' => 'queued',
            'issued_by' => $request->user()->id,
        ]);

        return back()->with('ok', ucfirst($data['action']).' queued.');
    }
}

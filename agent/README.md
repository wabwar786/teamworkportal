# Wabwar Vault — Device Agent (Windows)

This is the endpoint agent that runs on each monitored PC. It is **consent-based
workplace monitoring**, not hidden spyware — and it is built that way on purpose:

- On first run it shows a **consent screen** the user must accept.
- It runs with a **visible system-tray icon** the whole time.
- It can be **uninstalled** normally from Add/Remove Programs; monitoring stops.
- It only ever performs the **whitelisted safe actions** the server sends
  (lock / message / restart / shutdown / logoff / folder_sync / screenshot /
  blocklist_push / app_close / agent_update). There is **no arbitrary command
  execution** — the server has no field for it and the agent has no handler for it.

Keeping those four properties is what makes this legal to deploy on company
machines. Do not remove them.

## What it does

| Feature | How |
|---|---|
| File / USB events | `FileSystemWatcher` on user folders + removable-drive detection → POST `/api/agent/events` |
| Active-app tracking | Foreground-window poll each minute → aggregate → POST `/api/agent/usage` |
| Periodic screenshots | Timer (e.g. every 10 min) → JPEG → POST `/api/agent/screenshot` (a small tray flash shows it happened) |
| Site / app blocking | Pull `/api/agent/blocklist` → hosts-file entries for sites, process-kill watch for apps |
| Safe remote actions | Poll `/api/agent/commands` → execute the named action → POST `/api/agent/commands/ack` |
| Auto-start | Registered as a Windows Service (or Run key) so it survives reboot |

## Project layout (create with `dotnet new`)

```
WabwarAgent/
├── WabwarAgent.csproj        (net8.0-windows, WinExe)
├── Program.cs                host builder + tray
├── Config.cs                 base URL, employee slug, token storage
├── ConsentForm.cs            first-run consent UI
├── TrayIcon.cs               NotifyIcon + status menu
├── ApiClient.cs              enroll, heartbeat, events, usage, screenshot, commands
├── Monitors/
│   ├── FileMonitor.cs        FileSystemWatcher + USB
│   ├── AppMonitor.cs         foreground window minutes
│   ├── ScreenshotService.cs  periodic capture
│   └── Blocker.cs            hosts-file + process kill
└── Commands/
    └── CommandRunner.cs      switch over the 10 whitelisted actions only
```

## Enrollment flow

1. Installer asks for the **employee slug** (matches an employee in the portal)
   and the **server base URL**.
2. First launch → consent screen → `POST /api/agent/enroll` with
   `{ employee_slug, pc_name, os, ip, agent_version }`.
3. Server returns a long-lived `token`. Store it (DPAPI-encrypted) and send it as
   `Authorization: Bearer <token>` on every later call.

## Minimal ApiClient.cs (reference)

```csharp
using System.Net.Http.Json;

public class ApiClient
{
    private readonly HttpClient _http;
    private string _token = "";

    public ApiClient(string baseUrl) =>
        _http = new HttpClient { BaseAddress = new Uri(baseUrl) };

    public async Task<bool> Enroll(string slug, string pc, string os, string ip, string ver)
    {
        var res = await _http.PostAsJsonAsync("/api/agent/enroll", new {
            employee_slug = slug, pc_name = pc, os, ip, agent_version = ver
        });
        if (!res.IsSuccessStatusCode) return false;
        var body = await res.Content.ReadFromJsonAsync<EnrollResp>();
        _token = body!.token;
        Config.SaveToken(_token);   // DPAPI-encrypted on disk
        _http.DefaultRequestHeaders.Authorization =
            new("Bearer", _token);
        return true;
    }

    public Task Heartbeat() => _http.PostAsync("/api/agent/heartbeat", null);

    public Task ReportEvents(object events) =>
        _http.PostAsJsonAsync("/api/agent/events", new { events });

    public Task ReportUsage(object apps) =>
        _http.PostAsJsonAsync("/api/agent/usage", new { apps });

    public Task SendScreenshot(string base64, string activeApp) =>
        _http.PostAsJsonAsync("/api/agent/screenshot",
            new { image = base64, active_app = activeApp });

    public async Task<Command[]> PullCommands()
    {
        var r = await _http.GetFromJsonAsync<CommandsResp>("/api/agent/commands");
        return r?.commands ?? Array.Empty<Command>();
    }

    public Task Ack(long id, string status) =>
        _http.PostAsJsonAsync("/api/agent/commands/ack", new { id, status });

    public Task<BlockList?> GetBlocklist() =>
        _http.GetFromJsonAsync<BlockList>("/api/agent/blocklist");

    record EnrollResp(string token, long device_id);
    record CommandsResp(Command[] commands);
}

public record Command(long id, string action, Dictionary<string,string>? args);
public record BlockList(string[] sites, string[] apps);
```

## CommandRunner.cs — whitelist only (reference)

```csharp
public static async Task Run(Command c, ApiClient api)
{
    try {
        switch (c.action) {
            case "lock":     LockWorkStation(); break;
            case "logoff":   Process.Start("shutdown", "/l"); break;
            case "restart":  Process.Start("shutdown", "/r /t 15"); break;
            case "shutdown": Process.Start("shutdown", "/s /t 15"); break;
            case "message":  MessageBox.Show(c.args?["text"] ?? "", "Message from admin"); break;
            case "screenshot":   await ScreenshotService.CaptureNow(api); break;
            case "app_close":    CloseApp(c.args?["text"]); break;
            case "folder_sync":  await FolderSync.Pull(c.args?["path"]); break;
            case "blocklist_push": await Blocker.Refresh(api); break;
            case "agent_update":   await Updater.SelfUpdate(); break;
            // NO default that runs arbitrary input — unknown actions are ignored.
            default: await api.Ack(c.id, "failed"); return;
        }
        await api.Ack(c.id, "done");
    } catch { await api.Ack(c.id, "failed"); }
}

[DllImport("user32.dll")] static extern void LockWorkStation();
```

## Consent screen (must stay)

Show this on first run and record acceptance. Wording it plainly is what keeps
the deployment honest and lawful:

> **This computer is monitored by [Company].**
> While you are signed in, this software records file transfers and USB use,
> which applications are active, and takes periodic screenshots. A tray icon
> stays visible the whole time. Monitoring is for work devices during work use.
> Click **I understand** to continue.

## Build

```powershell
dotnet new winforms -n WabwarAgent
# add the files above, then:
dotnet publish -c Release -r win-x64 --self-contained true `
    /p:PublishSingleFile=true
```

Ship the single EXE with a small installer (Inno Setup / MSI) that collects the
slug + base URL and registers the service.

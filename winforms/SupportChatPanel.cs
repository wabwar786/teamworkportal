// ============================================================
//  Wabwar Support Chat — C# WinForms client control
//
//  Drop this UserControl into any existing WinForms app:
//
//      var chat = new SupportChatPanel {
//          ApiBase   = "https://chat.wabwar.com",
//          ClientKey = "citymart-billing-4b2c",
//          CustomerName = "Sara Malik",     // if you already know it
//          Company   = "City Mart"
//      };
//      chat.Dock = DockStyle.Right;
//      this.Controls.Add(chat);
//
//  It talks to the SAME server as the JavaScript web widget, so
//  both web and desktop customers land in one support inbox.
//
//  Target: .NET 8 Windows (WinForms). NuGet: none required beyond
//  the framework; uses HttpClient + System.Text.Json.
// ============================================================

using System;
using System.Collections.Generic;
using System.Drawing;
using System.Drawing.Imaging;
using System.IO;
using System.Net.Http;
using System.Text;
using System.Text.Json;
using System.Threading;
using System.Threading.Tasks;
using System.Windows.Forms;

namespace Wabwar.SupportChat
{
    public class ChatMessage
    {
        public long Id { get; set; }
        public string From { get; set; } = "";   // "me" (customer) or "agent"
        public string Text { get; set; } = "";
        public string? Image { get; set; }
        public string At { get; set; } = "";
    }

    public class SupportChatPanel : UserControl
    {
        // ---- public config ------------------------------------------------
        public string ApiBase { get; set; } = "https://chat.wabwar.com";
        public string ClientKey { get; set; } = "demo";
        public string CustomerName { get; set; } = "";
        public string Company { get; set; } = "";

        // ---- private state ------------------------------------------------
        private readonly HttpClient _http = new HttpClient();
        private string? _convId;
        private long _lastId;
        private string _visitorId;
        private CancellationTokenSource? _pollCts;

        // ---- UI -----------------------------------------------------------
        private readonly Panel _header = new Panel();
        private readonly FlowLayoutPanel _thread = new FlowLayoutPanel();
        private readonly TextBox _input = new TextBox();
        private readonly Button _send = new Button();
        private readonly Button _screenBtn = new Button();

        public SupportChatPanel()
        {
            _visitorId = LoadOrCreateVisitorId();
            Width = 340;
            BackColor = Color.FromArgb(252, 252, 250);
            BuildUi();
        }

        // ================= UI build =================
        private void BuildUi()
        {
            // header
            _header.Dock = DockStyle.Top;
            _header.Height = 54;
            _header.BackColor = Color.FromArgb(20, 97, 78);
            var title = new Label
            {
                Text = "Wabwar Support",
                ForeColor = Color.FromArgb(234, 243, 239),
                Font = new Font("Segoe UI", 11f, FontStyle.Bold),
                AutoSize = true,
                Location = new Point(14, 10)
            };
            var sub = new Label
            {
                Text = "● Aam tor par 5 minute mein jawab",
                ForeColor = Color.FromArgb(185, 214, 203),
                Font = new Font("Segoe UI", 8.5f),
                AutoSize = true,
                Location = new Point(14, 30)
            };
            _header.Controls.Add(title);
            _header.Controls.Add(sub);

            // thread (scrollable message list)
            _thread.Dock = DockStyle.Fill;
            _thread.AutoScroll = true;
            _thread.WrapContents = false;
            _thread.FlowDirection = FlowDirection.TopDown;
            _thread.BackColor = Color.FromArgb(231, 232, 227);
            _thread.Padding = new Padding(12);

            // footer with input
            var foot = new Panel { Dock = DockStyle.Bottom, Height = 92, BackColor = Color.FromArgb(252, 252, 250) };

            _screenBtn.Text = "▢  Apni screen ka screenshot bhejein";
            _screenBtn.FlatStyle = FlatStyle.Flat;
            _screenBtn.FlatAppearance.BorderColor = Color.FromArgb(196, 199, 190);
            _screenBtn.ForeColor = Color.FromArgb(93, 98, 92);
            _screenBtn.BackColor = Color.White;
            _screenBtn.Font = new Font("Segoe UI", 8.5f);
            _screenBtn.Height = 26;
            _screenBtn.Dock = DockStyle.Top;
            _screenBtn.Click += async (s, e) => await SendScreenshotAsync();

            _input.Multiline = true;
            _input.BorderStyle = BorderStyle.FixedSingle;
            _input.Font = new Font("Segoe UI", 10f);
            _input.Dock = DockStyle.Fill;
            _input.Height = 40;
            _input.KeyDown += async (s, e) =>
            {
                if (e.KeyCode == Keys.Enter && !e.Shift)
                {
                    e.SuppressKeyPress = true;
                    await SendAsync();
                }
            };

            _send.Text = "Bhejein";
            _send.FlatStyle = FlatStyle.Flat;
            _send.BackColor = Color.FromArgb(20, 97, 78);
            _send.ForeColor = Color.White;
            _send.FlatAppearance.BorderSize = 0;
            _send.Width = 84;
            _send.Dock = DockStyle.Right;
            _send.Click += async (s, e) => await SendAsync();

            var inputRow = new Panel { Dock = DockStyle.Fill, Padding = new Padding(10, 4, 10, 8) };
            inputRow.Controls.Add(_input);
            inputRow.Controls.Add(_send);

            foot.Controls.Add(inputRow);
            foot.Controls.Add(_screenBtn);

            Controls.Add(_thread);
            Controls.Add(foot);
            Controls.Add(_header);
        }

        // ================= lifecycle =================
        protected override async void OnHandleCreated(EventArgs e)
        {
            base.OnHandleCreated(e);
            await OpenConversationAsync();
            StartPolling();
        }

        protected override void OnHandleDestroyed(EventArgs e)
        {
            _pollCts?.Cancel();
            base.OnHandleDestroyed(e);
        }

        // ================= context =================
        private object BuildContext()
        {
            return new
            {
                key = ClientKey,
                visitor = _visitorId,
                name = string.IsNullOrWhiteSpace(CustomerName) ? Environment.UserName : CustomerName,
                company = Company,
                app = Application.ProductName + " " + Application.ProductVersion + " (desktop)",
                machine = Environment.MachineName,          // desktop CAN read this legitimately
                osUser = Environment.UserName,
                os = "Windows " + Environment.OSVersion.Version.Major,
                screen = Screen.PrimaryScreen != null
                    ? $"{Screen.PrimaryScreen.Bounds.Width}×{Screen.PrimaryScreen.Bounds.Height}"
                    : "",
                lang = System.Globalization.CultureInfo.CurrentUICulture.Name,
                at = DateTime.UtcNow.ToString("o")
            };
        }

        // ================= networking =================
        private async Task OpenConversationAsync()
        {
            try
            {
                var res = await PostJsonAsync("/api/chat/open", BuildContext());
                using var doc = JsonDocument.Parse(res);
                _convId = doc.RootElement.GetProperty("convId").GetString();
                AddSystemLine("Support se juud gaye. Apna masla likhein.");
            }
            catch
            {
                AddSystemLine("Server se connect nahi ho paya. Dobara koshish karein.");
            }
        }

        private async Task SendAsync()
        {
            var text = _input.Text.Trim();
            if (text.Length == 0 || _convId == null) return;
            _input.Clear();
            AddBubble(new ChatMessage { From = "me", Text = text, At = Now() });
            try
            {
                await PostJsonAsync("/api/chat/send", new
                {
                    convId = _convId,
                    visitor = _visitorId,
                    text,
                    at = DateTime.UtcNow.ToString("o")
                });
            }
            catch { AddSystemLine("Message bhejne mein masla — dobara koshish karein."); }
        }

        // screenshot — customer explicitly clicks the button, so this is consented
        private async Task SendScreenshotAsync()
        {
            if (_convId == null) return;
            var confirm = MessageBox.Show(
                "Aap ki poori screen ka ek screenshot support ko bheja jayega. Jari rakhein?",
                "Screenshot bhejein",
                MessageBoxButtons.YesNo, MessageBoxIcon.Question);
            if (confirm != DialogResult.Yes) return;

            string dataUrl = CaptureScreenAsDataUrl();
            AddBubble(new ChatMessage { From = "me", Image = dataUrl, At = Now() });
            try
            {
                await PostJsonAsync("/api/chat/screenshot", new
                {
                    convId = _convId,
                    visitor = _visitorId,
                    image = dataUrl
                });
                AddSystemLine("Screenshot bhej diya gaya.");
            }
            catch { AddSystemLine("Screenshot bhejne mein masla."); }
        }

        private string CaptureScreenAsDataUrl()
        {
            var b = Screen.PrimaryScreen!.Bounds;
            using var bmp = new Bitmap(b.Width, b.Height);
            using (var g = Graphics.FromImage(bmp))
                g.CopyFromScreen(b.Location, Point.Empty, b.Size);
            using var ms = new MemoryStream();
            bmp.Save(ms, ImageFormat.Jpeg);
            return "data:image/jpeg;base64," + Convert.ToBase64String(ms.ToArray());
        }

        // poll for agent replies (SignalR can replace this in the final build)
        private void StartPolling()
        {
            _pollCts = new CancellationTokenSource();
            var token = _pollCts.Token;
            Task.Run(async () =>
            {
                while (!token.IsCancellationRequested)
                {
                    try
                    {
                        if (_convId != null)
                        {
                            var url = $"{ApiBase}/api/chat/poll?convId={Uri.EscapeDataString(_convId)}&since={_lastId}";
                            var json = await _http.GetStringAsync(url, token);
                            using var doc = JsonDocument.Parse(json);
                            foreach (var m in doc.RootElement.GetProperty("msgs").EnumerateArray())
                            {
                                var msg = new ChatMessage
                                {
                                    Id = m.GetProperty("id").GetInt64(),
                                    From = m.GetProperty("from").GetString() ?? "agent",
                                    Text = m.TryGetProperty("text", out var t) ? t.GetString() ?? "" : "",
                                    Image = m.TryGetProperty("image", out var im) ? im.GetString() : null,
                                    At = m.TryGetProperty("at", out var a) ? a.GetString() ?? Now() : Now()
                                };
                                _lastId = Math.Max(_lastId, msg.Id);
                                if (msg.From != "me")
                                    BeginInvoke(new Action(() => AddBubble(msg)));
                            }
                        }
                    }
                    catch { /* ignore transient errors */ }
                    await Task.Delay(2500, token);
                }
            }, token);
        }

        private async Task<string> PostJsonAsync(string path, object payload)
        {
            var body = new StringContent(JsonSerializer.Serialize(payload), Encoding.UTF8, "application/json");
            var res = await _http.PostAsync(ApiBase + path, body);
            return await res.Content.ReadAsStringAsync();
        }

        // ================= rendering =================
        private void AddBubble(ChatMessage m)
        {
            bool mine = m.From == "me";
            var bubble = new Panel
            {
                AutoSize = true,
                MaximumSize = new Size(240, 0),
                Margin = new Padding(mine ? 60 : 4, 3, mine ? 4 : 60, 3),
                Padding = new Padding(9, 6, 9, 6),
                BackColor = mine ? Color.FromArgb(20, 97, 78) : Color.White
            };
            if (!string.IsNullOrEmpty(m.Image))
            {
                try
                {
                    var bytes = Convert.FromBase64String(m.Image.Substring(m.Image.IndexOf(',') + 1));
                    using var ms = new MemoryStream(bytes);
                    var pic = new PictureBox
                    {
                        Image = Image.FromStream(ms),
                        SizeMode = PictureBoxSizeMode.Zoom,
                        Size = new Size(220, 130),
                        Dock = DockStyle.Top
                    };
                    bubble.Controls.Add(pic);
                }
                catch { }
            }
            if (!string.IsNullOrEmpty(m.Text))
            {
                var lbl = new Label
                {
                    Text = m.Text,
                    AutoSize = true,
                    MaximumSize = new Size(220, 0),
                    ForeColor = mine ? Color.White : Color.FromArgb(25, 28, 25),
                    Font = new Font("Segoe UI", 9.5f)
                };
                bubble.Controls.Add(lbl);
            }
            _thread.Controls.Add(bubble);
            _thread.ScrollControlIntoView(bubble);
        }

        private void AddSystemLine(string text)
        {
            var lbl = new Label
            {
                Text = text,
                AutoSize = true,
                ForeColor = Color.FromArgb(142, 147, 140),
                Font = new Font("Consolas", 8f),
                Margin = new Padding(30, 6, 30, 6)
            };
            _thread.Controls.Add(lbl);
            _thread.ScrollControlIntoView(lbl);
        }

        private static string Now() => DateTime.Now.ToString("h:mm tt");

        private string LoadOrCreateVisitorId()
        {
            try
            {
                var dir = Path.Combine(
                    Environment.GetFolderPath(Environment.SpecialFolder.ApplicationData), "WabwarSupport");
                Directory.CreateDirectory(dir);
                var file = Path.Combine(dir, "visitor.txt");
                if (File.Exists(file)) return File.ReadAllText(file).Trim();
                var id = "d_" + Guid.NewGuid().ToString("N").Substring(0, 14);
                File.WriteAllText(file, id);
                return id;
            }
            catch { return "d_" + Guid.NewGuid().ToString("N").Substring(0, 14); }
        }
    }
}

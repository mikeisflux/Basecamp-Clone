<?php if (!defined('ABSPATH')) exit; PFOB_Template::header('Timesheet Settings'); ?>
<div style="max-width:900px;margin:40px auto;padding:20px;font-family:sans-serif">
<a href="<?php echo home_url('/projectfob/timesheet'); ?>" style="color:#0066cc">← Back to Timesheet</a>
<h1 style="font-size:32px;margin:16px 0">⚙️ Timesheet Settings</h1>
<div style="background:#fff;border:1px solid #ddd;border-radius:8px;padding:32px;margin-top:24px">
<h2>Time Tracking Preferences</h2>
<p><label><input type="checkbox" checked> Automatically detect idle time</label></p>
<p><label><input type="checkbox"> Require task descriptions</label></p>
<p><label><input type="checkbox" checked> Round time entries to nearest 15 minutes</label></p>
<button style="padding:10px 20px;background:#0066cc;color:white;border:none;border-radius:6px;cursor:pointer">Save Settings</button>
</div>
</div>
<?php PFOB_Template::footer(); ?>

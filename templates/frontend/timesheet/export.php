<?php if (!defined('ABSPATH')) exit; PFOB_Template::header('Export Time Data'); ?>
<div style="max-width:900px;margin:40px auto;padding:20px;font-family:sans-serif">
<a href="<?php echo home_url('/projectfob/timesheet'); ?>" style="color:#0066cc">← Back</a>
<h1 style="font-size:32px;margin:16px 0">📥 Export Time Data</h1>
<div style="background:#fff;border:1px solid #ddd;border-radius:8px;padding:32px;margin-top:24px">
<h2>Export Format</h2>
<p><label><input type="radio" name="format" value="csv" checked> CSV</label></p>
<p><label><input type="radio" name="format" value="excel"> Excel (.xlsx)</label></p>
<p><label><input type="radio" name="format" value="pdf"> PDF Report</label></p>
<h3 style="margin-top:24px">Date Range</h3>
<p>From: <input type="date" style="padding:8px;border:1px solid #ddd;border-radius:4px"></p>
<p>To: <input type="date" style="padding:8px;border:1px solid #ddd;border-radius:4px"></p>
<button style="padding:12px 24px;background:#10b981;color:white;border:none;border-radius:6px;cursor:pointer;margin-top:16px">Generate Export</button>
</div>
</div>
<?php PFOB_Template::footer(); ?>

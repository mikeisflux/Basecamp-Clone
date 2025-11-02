<?php
/**
 * Timesheet Reports
 */
if (!defined('ABSPATH')) exit;
$user_id = get_current_user_id();
PFOB_Template::header('Time Reports');
?>
<div style="max-width:1000px;margin:40px auto;padding:20px;font-family:sans-serif">
<a href="<?php echo home_url('/projectfob/adminland'); ?>" style="color:#0066cc">← Back</a>
<h1 style="font-size:32px;margin:16px 0 8px">📊 Time Reports</h1>
<p style="color:#666;margin:0 0 32px">Comprehensive time tracking reports and analytics.</p>
<div style="background:#fff;border:1px solid #ddd;border-radius:8px;padding:32px;margin-bottom:24px">
<h2>Team Summary (Last 30 Days)</h2>
<div id="team-stats">Loading...</div>
</div>
<div style="background:#fff;border:1px solid #ddd;border-radius:8px;padding:32px">
<h2>Detailed Reports</h2>
<p>Time reports by project, user, and date range coming soon.</p>
</div>
</div>
<?php PFOB_Template::footer(); ?>

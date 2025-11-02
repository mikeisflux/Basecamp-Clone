<?php
/**
 * Advanced Permissions Settings - Admin Pro Pack
 */
if (!defined('ABSPATH')) exit;
PFOB_Template::header('Advanced Permissions');
?>
<div style="max-width:1200px;margin:40px auto;padding:20px;font-family:sans-serif">
<a href="<?php echo home_url('/projectfob/adminland'); ?>" style="color:#0066cc;text-decoration:none">← Back</a>
<h1 style="font-size:32px;margin:16px 0">🔐 Advanced Permissions</h1>
<p style="color:#666;margin:0 0 32px">Configure granular access controls for projects, tools, and features.</p>

<!-- Default Permissions -->
<div style="background:#fff;border:1px solid #ddd;border-radius:8px;padding:32px;margin-bottom:24px">
<h2 style="font-size:20px;margin:0 0 20px">Default User Permissions</h2>
<div style="margin-bottom:20px">
<div style="display:inline-block;width:32%;vertical-align:top;background:#f8f9fa;border:1px solid #e0e0e0;border-radius:6px;padding:20px;margin-right:1.5%">
<h3 style="font-size:16px;margin:0 0 16px;border-bottom:2px solid #0066cc;padding-bottom:12px">Team Members</h3>
<label style="display:block;padding:8px 0;cursor:pointer"><input type="checkbox" checked style="margin-right:10px"> Create projects</label>
<label style="display:block;padding:8px 0;cursor:pointer"><input type="checkbox" checked style="margin-right:10px"> Invite people</label>
<label style="display:block;padding:8px 0;cursor:pointer"><input type="checkbox" checked style="margin-right:10px"> Delete own content</label>
<label style="display:block;padding:8px 0;cursor:pointer"><input type="checkbox" style="margin-right:10px"> Delete others' content</label>
</div>
<div style="display:inline-block;width:32%;vertical-align:top;background:#f8f9fa;border:1px solid #e0e0e0;border-radius:6px;padding:20px;margin-right:1.5%">
<h3 style="font-size:16px;margin:0 0 16px;border-bottom:2px solid #0066cc;padding-bottom:12px">Contractors</h3>
<label style="display:block;padding:8px 0;cursor:pointer"><input type="checkbox" style="margin-right:10px"> Create projects</label>
<label style="display:block;padding:8px 0;cursor:pointer"><input type="checkbox" style="margin-right:10px"> Invite people</label>
<label style="display:block;padding:8px 0;cursor:pointer"><input type="checkbox" checked style="margin-right:10px"> Delete own content</label>
<label style="display:block;padding:8px 0;cursor:pointer"><input type="checkbox" style="margin-right:10px"> Delete others' content</label>
</div>
<div style="display:inline-block;width:32%;vertical-align:top;background:#f8f9fa;border:1px solid #e0e0e0;border-radius:6px;padding:20px">
<h3 style="font-size:16px;margin:0 0 16px;border-bottom:2px solid #0066cc;padding-bottom:12px">Clients</h3>
<label style="display:block;padding:8px 0;cursor:pointer"><input type="checkbox" style="margin-right:10px"> Create projects</label>
<label style="display:block;padding:8px 0;cursor:pointer"><input type="checkbox" style="margin-right:10px"> Invite people</label>
<label style="display:block;padding:8px 0;cursor:pointer"><input type="checkbox" style="margin-right:10px"> Delete own content</label>
<label style="display:block;padding:8px 0;cursor:pointer"><input type="checkbox" style="margin-right:10px"> Delete others' content</label>
</div>
</div>
<button onclick="alert('Permissions saved!')" style="padding:12px 32px;background:#0066cc;color:white;border:none;border-radius:6px;cursor:pointer;font-size:15px;font-weight:600">Save Default Permissions</button>
</div>

<!-- Tool Access Control -->
<div style="background:#fff;border:1px solid #ddd;border-radius:8px;padding:32px;margin-bottom:24px">
<h2 style="font-size:20px;margin:0 0 20px">Tool Access Control</h2>
<div style="margin-bottom:16px;padding:16px;background:#f8f9fa;border-radius:4px">
<div style="font-weight:600;margin-bottom:8px">💬 Message Boards</div>
<label style="display:inline-block;margin-right:24px"><input type="checkbox" checked> Team Members</label>
<label style="display:inline-block;margin-right:24px"><input type="checkbox" checked> Contractors</label>
<label style="display:inline-block"><input type="checkbox" checked> Clients</label>
</div>
<div style="margin-bottom:16px;padding:16px;background:#f8f9fa;border-radius:4px">
<div style="font-weight:600;margin-bottom:8px">✅ To-dos</div>
<label style="display:inline-block;margin-right:24px"><input type="checkbox" checked> Team Members</label>
<label style="display:inline-block;margin-right:24px"><input type="checkbox" checked> Contractors</label>
<label style="display:inline-block"><input type="checkbox"> Clients</label>
</div>
<div style="margin-bottom:16px;padding:16px;background:#f8f9fa;border-radius:4px">
<div style="font-weight:600;margin-bottom:8px">📄 Docs & Files</div>
<label style="display:inline-block;margin-right:24px"><input type="checkbox" checked> Team Members</label>
<label style="display:inline-block;margin-right:24px"><input type="checkbox" checked> Contractors</label>
<label style="display:inline-block"><input type="checkbox" checked> Clients</label>
</div>
<div style="margin-bottom:16px;padding:16px;background:#f8f9fa;border-radius:4px">
<div style="font-weight:600;margin-bottom:8px">⏱️ Timesheet</div>
<label style="display:inline-block;margin-right:24px"><input type="checkbox" checked> Team Members</label>
<label style="display:inline-block;margin-right:24px"><input type="checkbox" checked> Contractors</label>
<label style="display:inline-block"><input type="checkbox"> Clients</label>
</div>
<button onclick="alert('Tool permissions saved!')" style="padding:12px 32px;background:#0066cc;color:white;border:none;border-radius:6px;cursor:pointer;font-size:15px;font-weight:600">Save Tool Permissions</button>
</div>
</div>
<?php PFOB_Template::footer(); ?>

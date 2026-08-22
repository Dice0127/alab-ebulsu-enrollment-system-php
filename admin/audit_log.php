<?php require_once 'auth.php'; require_once '../includes/conn.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Audit Log — Alab E-BulSU Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../includes/style.css">
</head>
<body>
<div class="bg-blobs">
    <div class="blob blob-1"></div><div class="blob blob-2"></div>
    <div class="blob blob-3"></div><div class="blob blob-4"></div>
</div>

<?php require_once 'nav.php'; ?>
<div class="main-content">

    <div class="page-header">
        <h2> Audit Log</h2>
    </div>

    <div class="card">
        <div class="filter-bar">
            <input type="text" id="filterUser" placeholder=" Filter by admin username...">
            <select id="filterAction">
                <option value="">All Actions</option>
                <option value="login_success">Login Success</option>
                <option value="login_failed">Login Failed</option>
                <option value="logout">Logout</option>
                <option value="student_created">Student Created</option>
                <option value="student_updated">Student Updated</option>
                <option value="student_deleted">Student Deleted</option>
                <option value="enrollment_status_changed">Enrollment Status Changed</option>
                <option value="college_created">College Created</option>
                <option value="college_updated">College Updated</option>
                <option value="college_deleted">College Deleted</option>
                <option value="program_created">Program Created</option>
                <option value="program_updated">Program Updated</option>
                <option value="program_deleted">Program Deleted</option>
                <option value="section_created">Section Created</option>
                <option value="section_updated">Section Updated</option>
                <option value="section_deleted">Section Deleted</option>
                <option value="course_created">Course Created</option>
                <option value="course_updated">Course Updated</option>
                <option value="course_deleted">Course Deleted</option>
                <option value="curriculum_created">Curriculum Created</option>
                <option value="curriculum_updated">Curriculum Updated</option>
                <option value="curriculum_deleted">Curriculum Deleted</option>
                <option value="curriculum_bulk_added">Curriculum Bulk Added</option>
            </select>
            <button class="btn btn-primary" id="btnSearch">Search</button>
            <button class="btn btn-secondary" id="btnReset">Reset</button>
        </div>

        <div class="table-wrap">
            <table class="table">
                <thead>
                    <tr>
                        <th>When</th><th>Admin</th><th>Action</th><th>Details</th><th>IP Address</th>
                    </tr>
                </thead>
                <tbody id="logTable">
                    <tr><td colspan="5" class="loading">Loading...</td></tr>
                </tbody>
            </table>
        </div>

        <div class="pagination" id="pager">
            <button id="pagerPrev" class="btn btn-secondary">&laquo; Prev</button>
            <span class="page-info" id="pagerInfo">Page 1</span>
            <button id="pagerNext" class="btn btn-secondary">Next &raquo;</button>
        </div>
    </div>

</div>

<div id="toast"></div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="../includes/esc.js"></script>
<script>
var currentPage = 1;

function badgeForAction(action) {
    if (!action) return 'badge-pending';
    if (action.indexOf('deleted') !== -1 || action === 'login_failed') return 'badge-rejected';
    if (action.indexOf('created') !== -1 || action === 'login_success') return 'badge-approved';
    if (action.indexOf('updated') !== -1 || action.indexOf('bulk_added') !== -1 || action.indexOf('status_changed') !== -1) return 'badge-info';
    return 'badge-pending'; // logout and anything unrecognized
}

function loadLog(page) {
    currentPage = page || 1;
    var params = {
        user: $('#filterUser').val(),
        action: $('#filterAction').val(),
        page: currentPage,
        per_page: 25
    };
    $('#logTable').html('<tr><td colspan="5" class="loading">Loading...</td></tr>');
    $('#pager').addClass('is-disabled');
    $.get('../ajax/get_audit_log.php', params, function (resp) {
        var data = resp.data || [];
        renderPager(resp);
        if (!data.length) {
            $('#logTable').html('<tr><td colspan="5" class="empty">No log entries found.</td></tr>');
            return;
        }
        var rows = '';
        $.each(data, function (i, l) {
            var badgeClass = badgeForAction(l.action);
            rows += '<tr>' +
                '<td>' + esc(l.created_at) + '</td>' +
                '<td>' + esc(l.admin_username) + '</td>' +
                '<td><span class="badge ' + badgeClass + '">' + esc(l.action) + '</span></td>' +
                '<td>' + (l.details ? esc(l.details) : '<span style="color:rgba(255,255,255,0.4);">—</span>') + '</td>' +
                '<td>' + esc(l.ip_address || '—') + '</td>' +
            '</tr>';
        });
        $('#logTable').html(rows);
    }, 'json').fail(function (xhr) {
        var msg = 'Could not load the audit log.';
        if (xhr.status === 401) {
            msg = 'Your session has expired. Please log in again.';
        } else if (xhr.status === 500) {
            msg = 'Server error — the audit_log table may be missing. Run update_schema_v2.sql.';
        }
        $('#logTable').html('<tr><td colspan="5" class="empty">' + esc(msg) + '</td></tr>');
        $('#pagerInfo').text('—');
    }).always(function () {
        $('#pager').removeClass('is-disabled');
    });
}

function renderPager(resp) {
    var totalPages = resp.total_pages || 1;
    $('#pagerInfo').text('Page ' + resp.page + ' of ' + totalPages + ' (' + resp.total + ' total)');
    $('#pagerPrev').prop('disabled', resp.page <= 1);
    $('#pagerNext').prop('disabled', resp.page >= totalPages);
}

$('#pagerPrev').on('click', function () { if (currentPage > 1) loadLog(currentPage - 1); });
$('#pagerNext').on('click', function () { loadLog(currentPage + 1); });

$('#btnSearch').on('click', function () { loadLog(1); });
$('#btnReset').on('click', function () { $('#filterUser, #filterAction').val(''); loadLog(1); });
$('#filterUser').on('keypress', function (e) { if (e.which === 13) loadLog(1); });

loadLog();
</script>
</body>
</html>

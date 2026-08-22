<?php require_once 'auth.php'; require_once '../includes/conn.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Enrollments — Alab E-BulSU Admin</title>
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
        <h2> Enrollment Management</h2>
    </div>

    <!-- Search & Filter Bar -->
    <div class="card">
        <div class="filter-bar">
            <input type="text" id="searchName" placeholder=" Search by name or student no...">
            <select id="filterProgram">
                <option value="">All Programs</option>
                <?php
                $progs = $conn->query("SELECT * FROM program ORDER BY program_code");
                while ($p = $progs->fetch_assoc()):
                ?>
                <option value="<?php echo $p['program_code']; ?>"><?php echo htmlspecialchars($p['program_name']); ?></option>
                <?php endwhile; ?>
            </select>
            <select id="filterYear">
                <option value="">All Year Levels</option>
                <option>1st Year</option><option>2nd Year</option>
                <option>3rd Year</option><option>4th Year</option>
            </select>
            <select id="filterStatus">
                <option value="">All Status</option>
                <option>Pending</option><option>Approved</option><option>Rejected</option>
            </select>
            <button class="btn btn-primary" id="btnSearch">Search</button>
            <button class="btn btn-secondary" id="btnReset">Reset</button>
        </div>

        <div class="table-wrap">
            <table class="table">
                <thead>
                    <tr>
                        <th>Student No.</th><th>Name</th><th>Program</th>
                        <th>Year</th><th>Semester</th><th>School Year</th>
                        <th>Status</th><th>Actions</th>
                    </tr>
                </thead>
                <tbody id="enrollTable">
                    <tr><td colspan="8" class="loading">Loading...</td></tr>
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

<!-- View Enrollment Modal -->
<div class="modal-overlay" id="viewModal">
    <div class="modal modal-lg">
        <button class="modal-close" onclick="closeModal('viewModal')">&times;</button>
        <h3 id="viewModalTitle">Enrollment Details</h3>
        <div id="viewModalBody"></div>
    </div>
</div>

<!-- Remarks Modal (for rejection) -->
<div class="modal-overlay" id="remarksModal">
    <div class="modal">
        <button class="modal-close" onclick="closeModal('remarksModal')">&times;</button>
        <h3>Add Remarks (Optional)</h3>
        <div class="form-group">
            <label>Remarks / Reason</label>
            <textarea id="remarksInput" rows="4" placeholder="Enter reason for rejection or additional notes..."></textarea>
        </div>
        <input type="hidden" id="remarksEnrollId">
        <input type="hidden" id="remarksAction">
        <div class="modal-actions">
            <button class="btn btn-secondary" onclick="closeModal('remarksModal')">Cancel</button>
            <button class="btn btn-danger" id="confirmActionBtn">Confirm</button>
        </div>
    </div>
</div>

<div id="toast"></div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="../includes/esc.js"></script>
<?php require_once __DIR__ . "/../includes/csrf_setup.php"; ?>
<script>
function openModal(id)  { $('#' + id).addClass('open'); }
function closeModal(id) { $('#' + id).removeClass('open'); }

function showToast(msg, type) {
    $('#toast').text(msg).removeClass('success error info').addClass(type).fadeIn(200);
    setTimeout(function () { $('#toast').fadeOut(400); }, 3000);
}

var currentPage = 1;

function loadEnrollments(page) {
    currentPage = page || 1;
    var params = {
        search:  $('#searchName').val(),
        program: $('#filterProgram').val(),
        year:    $('#filterYear').val(),
        status:  $('#filterStatus').val(),
        page:    currentPage,
        per_page: 20
    };
    $('#enrollTable').html('<tr><td colspan="8" class="loading">Loading...</td></tr>');
    $.get('../ajax/get_enrollments.php', params, function (resp) {
        var data = resp.data || [];
        renderPager(resp);
        if (data.length === 0) {
            $('#enrollTable').html('<tr><td colspan="8" class="empty">No enrollments found.</td></tr>');
            return;
        }
        var bc = { Pending: 'badge-pending', Approved: 'badge-approved', Rejected: 'badge-rejected' };
        var rows = '';
        $.each(data, function (i, e) {
            var fullName = esc(e.first_name) + (e.middle_name ? ' ' + esc(e.middle_name) : '') + ' ' + esc(e.last_name);
            var actions =
                '<button class="btn btn-primary" onclick="viewEnrollment(' + e.enrollment_id + ')">View</button>';
            if (e.status === 'Pending') {
                actions +=
                    '<button class="btn btn-success" onclick="doAction(' + e.enrollment_id + ',\'Approved\')">Approve</button>' +
                    '<button class="btn btn-danger"  onclick="openRemarks(' + e.enrollment_id + ',\'Rejected\')">Reject</button>';
            } else {
                actions += '<button class="btn btn-secondary" onclick="openRemarks(' + e.enrollment_id + ',\'reset\')">Set Pending</button>';
            }
            rows += '<tr>' +
                '<td>' + esc(e.student_number) + '</td>' +
                '<td>' + fullName + '</td>' +
                '<td>' + esc(e.program_name) + '</td>' +
                '<td>' + esc(e.year_level) + '</td>' +
                '<td>' + esc(e.semester) + '</td>' +
                '<td>' + esc(e.school_year) + '</td>' +
                '<td><span class="badge ' + (bc[e.status]||'') + '">' + esc(e.status) + '</span></td>' +
                '<td>' + actions + '</td>' +
            '</tr>';
        });
        $('#enrollTable').html(rows);
    }, 'json');
}

function renderPager(resp) {
    var totalPages = resp.total_pages || 1;
    $('#pagerInfo').text('Page ' + resp.page + ' of ' + totalPages + ' (' + resp.total + ' total)');
    $('#pagerPrev').prop('disabled', resp.page <= 1);
    $('#pagerNext').prop('disabled', resp.page >= totalPages);
}

$('#pagerPrev').on('click', function () { if (currentPage > 1) loadEnrollments(currentPage - 1); });
$('#pagerNext').on('click', function () { loadEnrollments(currentPage + 1); });

function viewEnrollment(id) {
    $.get('../ajax/get_enrollment_detail.php', { id: id }, function (e) {
        if (!e) return;
        var fullName = esc(e.first_name) + (e.middle_name ? ' ' + esc(e.middle_name) : '') + ' ' + esc(e.last_name);
        var bc = { Pending: 'badge-pending', Approved: 'badge-approved', Rejected: 'badge-rejected' };
        var html =
            '<div style="display:grid;grid-template-columns:1fr 1fr;gap:12px 24px;font-size:13px;">' +
                row('Student No.',   e.student_number) +
                row('Full Name',     fullName, true) +
                row('Gender',        e.gender) +
                row('Birthday',      e.birthday) +
                row('Email',         e.email) +
                row('Contact No.',   e.contact_number) +
                row('Address',       e.address) +
                row('Program',       e.program_name + ' (' + e.program_code + ')') +
                row('Year Level',    e.year_level) +
                row('Semester',      e.semester) +
                row('School Year',   e.school_year) +
                row('Submitted',     e.created_at) +
            '</div>' +
            '<div style="margin-top:16px;">' +
                '<strong>Status:</strong> <span class="badge ' + (bc[e.status]||'') + '">' + esc(e.status) + '</span>' +
                (e.remarks ? '<div style="margin-top:10px;padding:10px 14px;background:#f9fbfc;border-left:3px solid #2981B8;border-radius:4px;font-size:13px;"><strong>Remarks:</strong> ' + esc(e.remarks) + '</div>' : '') +
            '</div>' +
            '<div class="modal-actions">' +
                (e.status === 'Pending'
                    ? '<button class="btn btn-success" onclick="doAction(' + e.enrollment_id + ',\'Approved\');closeModal(\'viewModal\')">Approve</button>' +
                      '<button class="btn btn-danger"  onclick="closeModal(\'viewModal\');openRemarks(' + e.enrollment_id + ',\'Rejected\')">Reject</button>'
                    : '') +
                '<button class="btn btn-secondary" onclick="closeModal(\'viewModal\')">Close</button>' +
            '</div>';
        $('#viewModalTitle').text('Enrollment — ' + fullName);
        $('#viewModalBody').html(html);
        openModal('viewModal');
    }, 'json');
}

function row(label, val, alreadyEscaped) {
    var display = val ? (alreadyEscaped ? val : esc(val)) : '—';
    return '<div><strong style="font-size:11px;text-transform:uppercase;color:#555;">' + esc(label) + '</strong><br>' + display + '</div>';
}

// Approve/Reject via AJAX (no page reload)
function doAction(id, action, remarks) {
    remarks = remarks || '';
    $.post('../ajax/update_enrollment_status.php', { id: id, status: action, remarks: remarks }, function (res) {
        if (res.success) {
            showToast(res.message, 'success');
            loadEnrollments(currentPage);
        } else {
            showToast(res.message, 'error');
        }
    }, 'json');
}

function openRemarks(id, action) {
    $('#remarksEnrollId').val(id);
    $('#remarksAction').val(action);
    $('#remarksInput').val('');
    var label = action === 'Rejected' ? 'Confirm Rejection' : 'Set to Pending';
    $('#confirmActionBtn').text(label).removeClass('btn-danger btn-warning').addClass(action === 'Rejected' ? 'btn-danger' : 'btn-warning');
    openModal('remarksModal');
}

$('#confirmActionBtn').on('click', function () {
    var id      = $('#remarksEnrollId').val();
    var action  = $('#remarksAction').val();
    var remarks = $('#remarksInput').val();
    var status  = (action === 'reset') ? 'Pending' : action;
    closeModal('remarksModal');
    doAction(id, status, remarks);
});

// Search & filter (AJAX)
$('#btnSearch').on('click', function () { loadEnrollments(1); });
$('#btnReset').on('click', function () {
    $('#searchName, #filterProgram, #filterYear, #filterStatus').val('');
    loadEnrollments(1);
});
$('#searchName').on('keypress', function (e) { if (e.which === 13) loadEnrollments(1); });

// Close modal on overlay click
$(document).on('click', '.modal-overlay', function (e) {
    if ($(e.target).hasClass('modal-overlay')) closeModal($(this).attr('id'));
});

// Init
loadEnrollments();
</script>
</body>
</html>

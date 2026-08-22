<?php require_once 'auth.php'; require_once '../includes/conn.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Colleges — Alab E-BulSU Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../includes/style.css">
    <style>
        .filter-bar {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            align-items: center;
            margin-bottom: 16px;
        }
        .filter-bar .btn-reset {
            padding: 8px 14px;
            background: rgba(255,255,255,0.1);
            border: 1px solid rgba(255,255,255,0.2);
            border-radius: 8px;
            font-size: 13px;
            color: rgba(255,255,255,0.6);
            cursor: pointer;
            font-family: inherit;
            transition: background 0.2s, color 0.2s;
        }
        .filter-bar .btn-reset:hover {
            background: rgba(255,255,255,0.15);
            color: rgba(255,255,255,0.8);
        }
        .filter-results { font-size: 12px; color: rgba(255,255,255,0.5); margin-left: auto; white-space: nowrap; }
    </style>
</head>
<body>
<div class="bg-blobs">
    <div class="blob blob-1"></div><div class="blob blob-2"></div>
    <div class="blob blob-3"></div><div class="blob blob-4"></div>
</div>

<?php require_once 'nav.php'; ?>
<div class="main-content">
    <div class="page-header"><h2>Colleges</h2><button class="btn btn-success" onclick="openAdd()">+ Add College</button></div>
    <div class="card">

        <!-- Filter Bar -->
        <div class="filter-bar">
            <input type="text" id="filter-search" placeholder="Search code or name…" oninput="applyFilters()">
            <button class="btn-reset" onclick="resetFilters()">✕ Reset</button>
            <span class="filter-results" id="filter-results"></span>
        </div>

        <div class="table-wrap">
            <table class="table" id="collegeTable">
                <thead><tr><th>College Code</th><th>College Name</th><th>Actions</th></tr></thead>
                <tbody id="tbody-colleges"><tr><td colspan="3" class="loading">Loading...</td></tr></tbody>
            </table>
        </div>

        <div class="pagination" id="pager" style="display:none;">
            <button id="pagerPrev" class="btn btn-secondary">&laquo; Prev</button>
            <span class="page-info" id="pagerInfo">Page 1</span>
            <button id="pagerNext" class="btn btn-secondary">Next &raquo;</button>
        </div>
    </div>
</div>

<div class="modal-overlay" id="formModal">
    <div class="modal"><button class="modal-close" onclick="closeModal('formModal')">&times;</button>
        <h3 id="formTitle">Add College</h3><div id="formBody"></div></div>
</div>
<div class="modal-overlay" id="deleteModal">
    <div class="modal modal-sm"><button class="modal-close" onclick="closeModal('deleteModal')">&times;</button>
        <h3>Confirm Delete</h3><p style="color:#555;font-size:14px;margin:12px 0 24px;">Delete this college?</p>
        <div class="modal-actions-center">
            <button class="btn btn-secondary" onclick="closeModal('deleteModal')">Cancel</button>
            <button class="btn btn-danger" id="confirmDeleteBtn">Delete</button>
        </div>
    </div>
</div>
<div id="toast"></div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<?php require_once __DIR__ . "/../includes/csrf_setup.php"; ?>
<script>
var pendingKey = null, allColleges = [];
var currentPage = 1;
var PER_PAGE = 10;

function openModal(id) { $('#'+id).addClass('open'); }
function closeModal(id) { $('#'+id).removeClass('open'); }
function showToast(msg,t) { $('#toast').text(msg).removeClass('success error').addClass(t).fadeIn(200); setTimeout(function(){$('#toast').fadeOut(400);},3000); }

function load() {
    $.get('../ajax/get_colleges.php', function(data) {
        allColleges = data || [];
        applyFilters();
    }, 'json');
}

function applyFilters() {
    var search = $('#filter-search').val().trim().toLowerCase();
    var filtered = allColleges.filter(function(c) {
        return !search || c.college_code.toLowerCase().includes(search) || c.college_name.toLowerCase().includes(search);
    });
    currentPage = 1;
    renderPage(filtered);
    $('#filter-results').text(
        filtered.length === allColleges.length
            ? allColleges.length + ' college(s)'
            : filtered.length + ' of ' + allColleges.length + ' college(s)'
    );
}

function renderPage(filtered) {
    var totalPages = Math.max(1, Math.ceil(filtered.length / PER_PAGE));
    currentPage = Math.min(currentPage, totalPages);
    var start = (currentPage - 1) * PER_PAGE;
    renderTable(filtered.slice(start, start + PER_PAGE));

    if (filtered.length <= PER_PAGE) {
        $('#pager').hide();
    } else {
        $('#pager').show();
        $('#pagerInfo').text('Page ' + currentPage + ' of ' + totalPages + ' (' + filtered.length + ' total)');
        $('#pagerPrev').prop('disabled', currentPage <= 1);
        $('#pagerNext').prop('disabled', currentPage >= totalPages);
    }
    lastFiltered = filtered;
}
var lastFiltered = [];
$('#pagerPrev').on('click', function(){ if (currentPage > 1) { currentPage--; renderPage(lastFiltered); } });
$('#pagerNext').on('click', function(){ currentPage++; renderPage(lastFiltered); });

function resetFilters() {
    $('#filter-search').val('');
    applyFilters();
}

function renderTable(data) {
    if (!data.length) { $('#tbody-colleges').html('<tr><td colspan="3" class="empty">No colleges found.</td></tr>'); return; }
    var r = '';
    $.each(data, function(i, c) {
        r += '<tr><td>'+c.college_code+'</td><td>'+c.college_name+'</td><td>'+
             '<button class="btn btn-warning" onclick="openEdit(\''+c.college_code+'\',\''+c.college_name.replace(/'/g,"\\'")+'\')">Edit</button> '+
             '<button class="btn btn-danger" onclick="openDel(\''+c.college_code+'\')">Delete</button></td></tr>';
    });
    $('#tbody-colleges').html(r);
}

function buildForm(code, name) {
    return '<div class="form-group"><label>College Code *</label><input type="text" id="f_college_code" maxlength="10" value="'+(code||'')+'" placeholder="e.g. CICT"></div>' +
           '<div class="form-group"><label>College Name *</label><input type="text" id="f_college_name" value="'+(name||'')+'" placeholder="e.g. College of..."></div>' +
           (code ? '<input type="hidden" id="f_original_key" value="'+code+'">':'') +
           '<div class="modal-actions"><button class="btn btn-secondary" onclick="closeModal(\'formModal\')">Cancel</button><button class="btn btn-success" onclick="save('+(code?'true':'false')+')">Save</button></div>';
}

function openAdd() { $('#formTitle').text('Add College'); $('#formBody').html(buildForm(null,null)); openModal('formModal'); }
function openEdit(code, name) { $('#formTitle').text('Update College'); $('#formBody').html(buildForm(code,name)); openModal('formModal'); }
function openDel(code) { pendingKey = code; openModal('deleteModal'); }

function save(isEdit) {
    var d = { is_edit: isEdit?1:0, college_code: $('#f_college_code').val().trim(), college_name: $('#f_college_name').val().trim() };
    if (isEdit) d.original_key = $('#f_original_key').val();
    if (!d.college_code || !d.college_name) { showToast('Fill in all fields.','error'); return; }
    $.post('../ajax/save_college.php', d, function(res){
        if (res.success) { closeModal('formModal'); showToast(res.message,'success'); load(); }
        else { showToast(res.message,'error'); }
    }, 'json');
}

$('#confirmDeleteBtn').on('click', function(){
    $.post('../ajax/delete_college.php', {college_code: pendingKey}, function(res){
        closeModal('deleteModal'); showToast(res.message, res.success?'success':'error');
        if (res.success) load();
    }, 'json');
});

$(document).on('click', '.modal-overlay', function(e){ if($(e.target).hasClass('modal-overlay')) closeModal($(this).attr('id')); });
load();
</script>
</body></html>
<?php require_once 'auth.php'; require_once '../includes/conn.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Programs — Alab E-BulSU Admin</title>
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
    <div class="page-header"><h2>Programs</h2><button class="btn btn-success" onclick="openAdd()">+ Add Program</button></div>
    <div class="card">

        <!-- Filter Bar -->
        <div class="filter-bar">
            <input type="text" id="filter-search" placeholder="Search code or name…" oninput="applyFilters()">
            <select id="filter-college" onchange="applyFilters()">
                <option value="">All Colleges</option>
            </select>
            <button class="btn-reset" onclick="resetFilters()">✕ Reset</button>
            <span class="filter-results" id="filter-results"></span>
        </div>

        <div class="table-wrap">
            <table class="table">
                <thead><tr><th>Program Code</th><th>Program Name</th><th>College</th><th>Actions</th></tr></thead>
                <tbody id="tbody-programs"><tr><td colspan="4" class="loading">Loading...</td></tr></tbody>
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
        <h3 id="formTitle">Add Program</h3><div id="formBody"></div></div>
</div>
<div class="modal-overlay" id="deleteModal">
    <div class="modal modal-sm"><button class="modal-close" onclick="closeModal('deleteModal')">&times;</button>
        <h3>Confirm Delete</h3><p style="color:#555;font-size:14px;margin:12px 0 24px;">Delete this program?</p>
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
var pendingKey = null, colleges = [], allPrograms = [];
var currentPage = 1, lastFiltered = [];
var PER_PAGE = 10;

function openModal(id){$('#'+id).addClass('open');}
function closeModal(id){$('#'+id).removeClass('open');}
function showToast(msg,t){$('#toast').text(msg).removeClass('success error').addClass(t).fadeIn(200);setTimeout(function(){$('#toast').fadeOut(400);},3000);}

// Load colleges and populate filter dropdown
$.get('../ajax/get_colleges.php', function(d){
    colleges = d;
    var opts = '<option value="">All Colleges</option>';
    $.each(colleges, function(i, c){
        opts += '<option value="'+c.college_code+'">'+c.college_code+' — '+c.college_name+'</option>';
    });
    $('#filter-college').html(opts);
}, 'json');

function load(){
    $.get('../ajax/get_programs.php', function(data){
        allPrograms = data || [];
        applyFilters();
    }, 'json');
}

function applyFilters(){
    var search  = $('#filter-search').val().trim().toLowerCase();
    var college = $('#filter-college').val();

    var filtered = allPrograms.filter(function(p){
        var matchSearch  = !search  || p.program_code.toLowerCase().includes(search) || p.program_name.toLowerCase().includes(search);
        var matchCollege = !college || p.college_code === college;
        return matchSearch && matchCollege;
    });

    currentPage = 1;
    renderPage(filtered);
    $('#filter-results').text(
        filtered.length === allPrograms.length
            ? allPrograms.length + ' program(s)'
            : filtered.length + ' of ' + allPrograms.length + ' program(s)'
    );
}

function renderPage(filtered){
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
$('#pagerPrev').on('click', function(){ if (currentPage > 1) { currentPage--; renderPage(lastFiltered); } });
$('#pagerNext').on('click', function(){ currentPage++; renderPage(lastFiltered); });

function resetFilters(){
    $('#filter-search').val('');
    $('#filter-college').val('');
    applyFilters();
}

function renderTable(data){
    if (!data.length) { $('#tbody-programs').html('<tr><td colspan="4" class="empty">No programs found.</td></tr>'); return; }
    var r = '';
    $.each(data, function(i, p){
        r += '<tr><td>'+p.program_code+'</td><td>'+p.program_name+'</td><td>'+p.college_name+'</td><td>'+
             '<button class="btn btn-warning" onclick="openEdit(\''+p.program_code+'\')">Edit</button> '+
             '<button class="btn btn-danger" onclick="openDel(\''+p.program_code+'\')">Delete</button></td></tr>';
    });
    $('#tbody-programs').html(r);
}

function buildCollegeOpts(selected){
    var o = '<option value="">— Select College —</option>';
    $.each(colleges, function(i, c){ o += '<option value="'+c.college_code+'"'+(c.college_code===selected?' selected':'')+'>'+c.college_code+' — '+c.college_name+'</option>'; });
    return o;
}

function openAdd(){
    $('#formTitle').text('Add Program');
    $('#formBody').html(
        '<div class="form-group"><label>Program Code *</label><input type="text" id="f_program_code" maxlength="15" placeholder="e.g. BSIT"></div>'+
        '<div class="form-group"><label>Program Name *</label><input type="text" id="f_program_name" placeholder="e.g. BS Information Technology"></div>'+
        '<div class="form-group"><label>College *</label><select id="f_college_code">'+buildCollegeOpts('')+'</select></div>'+
        '<div class="modal-actions"><button class="btn btn-secondary" onclick="closeModal(\'formModal\')">Cancel</button><button class="btn btn-success" onclick="save(false)">Add Program</button></div>'
    );
    openModal('formModal');
}

function openEdit(code){
    var p = allPrograms.find(function(x){ return x.program_code === code; });
    if (!p) return;
    $('#formTitle').text('Update Program');
    $('#formBody').html(
        '<input type="hidden" id="f_original_key" value="'+p.program_code+'">'+
        '<div class="form-group"><label>Program Code *</label><input type="text" id="f_program_code" maxlength="15" value="'+p.program_code+'"></div>'+
        '<div class="form-group"><label>Program Name *</label><input type="text" id="f_program_name" value="'+p.program_name+'"></div>'+
        '<div class="form-group"><label>College *</label><select id="f_college_code">'+buildCollegeOpts(p.college_code)+'</select></div>'+
        '<div class="modal-actions"><button class="btn btn-secondary" onclick="closeModal(\'formModal\')">Cancel</button><button class="btn btn-success" onclick="save(true)">Update Program</button></div>'
    );
    openModal('formModal');
}

function openDel(code){ pendingKey = code; openModal('deleteModal'); }

function save(isEdit){
    var d = {is_edit:isEdit?1:0, program_code:$('#f_program_code').val().trim(), program_name:$('#f_program_name').val().trim(), college_code:$('#f_college_code').val()};
    if (isEdit) d.original_key = $('#f_original_key').val();
    if (!d.program_code || !d.program_name || !d.college_code){ showToast('Fill in all fields.','error'); return; }
    $.post('../ajax/save_program.php', d, function(res){
        if (res.success){ closeModal('formModal'); showToast(res.message,'success'); load(); }
        else { showToast(res.message,'error'); }
    }, 'json');
}

$('#confirmDeleteBtn').on('click', function(){
    $.post('../ajax/delete_program.php', {program_code:pendingKey}, function(res){
        closeModal('deleteModal'); showToast(res.message, res.success?'success':'error');
        if (res.success) load();
    }, 'json');
});

$(document).on('click', '.modal-overlay', function(e){ if($(e.target).hasClass('modal-overlay')) closeModal($(this).attr('id')); });

load();
</script>
</body></html>
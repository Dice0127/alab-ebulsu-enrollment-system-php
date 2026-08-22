<?php require_once 'auth.php'; require_once '../includes/conn.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Courses — Alab E-BulSU Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../includes/style.css">
    <style>
        .filter-results { font-size: 12px; color: rgba(255,255,255,0.5); margin-left: auto; white-space: nowrap; }

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

        .active-banner { display: none; align-items: center; gap: 8px; background: #eef2ff; border: 1px solid #c7d2fe; border-radius: 8px; padding: 10px 14px; margin-bottom: 16px; font-size: 13px; color: #3730a3; }
        .active-banner.visible { display: flex; }
        .active-banner strong { font-weight: 600; }

        .empty-state { text-align: center; padding: 48px 20px; color: #9ca3af; }
        .empty-state .empty-icon { font-size: 36px; margin-bottom: 10px; }
        .empty-state p { font-size: 14px; margin: 0; }

        .unassigned-header { background: rgba(255,255,255,0.08) !important; }
        .unassigned-header td { color: rgba(255,255,255,0.7) !important; font-weight: 700 !important; border-top: 1px solid rgba(255,255,255,0.12); }
    </style>
</head>
<body>
<div class="bg-blobs">
    <div class="blob blob-1"></div><div class="blob blob-2"></div>
    <div class="blob blob-3"></div><div class="blob blob-4"></div>
</div>

<?php require_once 'nav.php'; ?>
<div class="main-content">
    <div class="page-header">
        <h2>Courses</h2>
        <button class="btn btn-success" onclick="openAdd()">+ Add Course</button>
    </div>

    <div class="card">
        <div class="filter-bar">
            <select id="filter-college" onchange="onCollegeChange()">
                <option value="">— Select a College —</option>
            </select>
            <select id="filter-program" onchange="onProgramChange()" disabled>
                <option value="">— Select a Program —</option>
            </select>
            <input type="text" id="filter-search" placeholder="Search code or name…" oninput="applyFilters()" disabled>
            <select id="filter-units" onchange="applyFilters()" disabled>
                <option value="">All Units</option>
            </select>
            <button class="btn-reset" onclick="resetFilters()">✕ Reset</button>
            <span class="filter-results" id="filter-results"></span>
        </div>

        <div class="active-banner" id="active-banner">
            <span></span>
            <span>Showing courses under <strong id="banner-program-name"></strong></span>
        </div>

        <div class="table-wrap">
            <table class="table">
                <thead><tr><th>Code</th><th>Name</th><th>Units</th><th>Description</th><th>Status</th><th>Actions</th></tr></thead>
                <tbody id="tbody-courses">
                    <tr><td colspan="6">
                        <div class="empty-state">
                            <div class="empty-icon"></div>
                            <p>Select a program above to view its courses.</p>
                        </div>
                    </td></tr>
                </tbody>
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
        <h3 id="formTitle">Add Course</h3><div id="formBody"></div></div>
</div>
<div class="modal-overlay" id="deleteModal">
    <div class="modal modal-sm"><button class="modal-close" onclick="closeModal('deleteModal')">&times;</button>
        <h3>Confirm Delete</h3><p style="color:#555;font-size:14px;margin:12px 0 24px;">Delete this subject?</p>
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
var pendingDeleteId = null;
var allCourses = []; 
var allCurriculum = [];
var programs = [];
var colleges = [];
var allPrograms = [];

function openModal(id){$('#'+id).addClass('open');}
function closeModal(id){$('#'+id).removeClass('open');}
function showToast(msg,t){$('#toast').text(msg).removeClass('success error').addClass(t).fadeIn(200);setTimeout(function(){$('#toast').fadeOut(400);},3000);}

function loadAll(callback){
    var done = 0;
    function check(){ if (++done === 4 && callback) callback(); }
    $.get('../ajax/get_colleges.php', function(data){
        colleges = data || [];
        var opts = '<option value="">— Select a College —</option>';
        $.each(colleges, function(i,c){ opts += '<option value="'+c.college_code+'">'+c.college_name+'</option>'; });
        $('#filter-college').html(opts);
        check();
    }, 'json');
    $.get('../ajax/get_programs.php', function(data){
        allPrograms = data || [];
        check();
    }, 'json');
    $.get('../ajax/get_subjects.php', function(data){ allCourses = data || []; check(); }, 'json');
    $.get('../ajax/get_curriculum.php', function(data){ allCurriculum = data || []; check(); }, 'json');
}

function onCollegeChange(){
    var collegeCode = $('#filter-college').val();
    var programSelect = $('#filter-program');
    programSelect.html('<option value="">— Select a Program —</option>').prop('disabled', !collegeCode);
    
    if(!collegeCode){ 
        $('#filter-search').val('').prop('disabled', true);
        $('#filter-units').val('').prop('disabled', true);
        $('#active-banner').removeClass('visible');
        resetTable();
        return;
    }
    
    programs = allPrograms.filter(function(p){ return p.college_code === collegeCode; });
    $.each(programs, function(i,p){
        programSelect.append('<option value="'+p.program_code+'">'+p.program_code+' — '+p.program_name+'</option>');
    });
}

function onProgramChange(){
    var prog = $('#filter-program').val();
    $('#filter-search').val('').prop('disabled', !prog);
    $('#filter-units').val('').prop('disabled', !prog);
    if (!prog){ $('#active-banner').removeClass('visible'); resetTable(); return; }
    var progObj = programs.find(function(p){ return p.program_code === prog; });
    $('#banner-program-name').text(progObj ? progObj.program_name+' ('+prog+')' : prog);
    $('#active-banner').addClass('visible');
    populateUnitsFilter(prog);
    applyFilters();
}

function populateUnitsFilter(prog){
    var units = [...new Set(allCourses.map(function(c){ return c.units; }))].sort(function(a,b){ return a-b; });
    var opts = '<option value="">All Units</option>';
    $.each(units, function(i,u){ opts += '<option value="'+u+'">'+u+' unit'+(u==1?'':'s')+'</option>'; });
    $('#filter-units').html(opts);
}

function applyFilters(){
    var college = $('#filter-college').val();
    var prog   = $('#filter-program').val();
    var search = $('#filter-search').val().trim().toLowerCase();
    var units  = $('#filter-units').val();

    if (!prog){ resetTable(); return; }

    // 1. Get courses assigned to THIS program
    var assignedIds = allCurriculum
        .filter(function(c){ return c.program_code === prog; })
        .map(function(c){ return String(c.course_id); });

    var assignedCourses = allCourses
        .filter(function(c){ return assignedIds.indexOf(String(c.course_id)) !== -1; })
        .map(function(c){ return $.extend({}, c, { isAssigned: true }); });

    // 2. Identify courses NOT assigned to ANY curriculum (but still filter by college & program)
    var assignedIdsGlobal = allCurriculum.map(function(c){ return String(c.course_id); });
    var unassignedCourses = allCourses
        .filter(function(c){ 
            return assignedIdsGlobal.indexOf(String(c.course_id)) === -1 && 
                   c.college_code === college && 
                   c.program_code === prog; 
        })
        .map(function(c){ return $.extend({}, c, { isAssigned: false }); });

    // 3. Filter both lists
    var filterFn = function(r){
        var matchSearch = !search || r.course_code.toLowerCase().includes(search) || r.course_name.toLowerCase().includes(search);
        var matchUnits  = !units  || String(r.units) === units;
        return matchSearch && matchUnits;
    };

    var filteredAssigned   = assignedCourses.filter(filterFn);
    var filteredUnassigned = unassignedCourses.filter(filterFn);

    currentPage = 1;
    renderPage(filteredAssigned, filteredUnassigned);
    $('#filter-results').text(filteredAssigned.length + ' assigned, ' + filteredUnassigned.length + ' unassigned');
}

var PER_PAGE = 10;
var currentPage = 1;
var lastAssigned = [], lastUnassigned = [];

function renderPage(assigned, unassigned){
    assigned = assigned.slice().sort(function(a,b){ return a.course_code.localeCompare(b.course_code); });

    // Flatten into one list of entries (a section header counts as one row)
    // so pagination applies across both the assigned and unassigned groups.
    var combined = [];
    $.each(assigned, function(i, s){ combined.push({ type: 'row', data: s }); });
    if (unassigned.length > 0) {
        combined.push({ type: 'header' });
        $.each(unassigned, function(i, s){ combined.push({ type: 'row', data: s }); });
    }

    var totalPages = Math.max(1, Math.ceil(combined.length / PER_PAGE));
    currentPage = Math.min(currentPage, totalPages);
    var start = (currentPage - 1) * PER_PAGE;
    renderCombinedTable(combined.slice(start, start + PER_PAGE), combined.length);

    if (combined.length <= PER_PAGE) {
        $('#pager').hide();
    } else {
        $('#pager').show();
        $('#pagerInfo').text('Page ' + currentPage + ' of ' + totalPages + ' (' + combined.length + ' total)');
        $('#pagerPrev').prop('disabled', currentPage <= 1);
        $('#pagerNext').prop('disabled', currentPage >= totalPages);
    }
    lastAssigned = assigned;
    lastUnassigned = unassigned;
}
$('#pagerPrev').on('click', function(){ if (currentPage > 1) { currentPage--; renderPage(lastAssigned, lastUnassigned); } });
$('#pagerNext').on('click', function(){ currentPage++; renderPage(lastAssigned, lastUnassigned); });

function renderCombinedTable(entries, totalCount){
    var rows = '';

    if (!totalCount) {
        $('#tbody-courses').html('<tr><td colspan="6" class="empty">No courses found matching your criteria.</td></tr>');
        return;
    }

    $.each(entries, function(i, e){
        rows += (e.type === 'header')
            ? '<tr class="unassigned-header"><td colspan="6" style="font-size:12px;padding:8px 12px;letter-spacing:0.05em;">NOT ADDED TO CURRICULUM YET</td></tr>'
            : generateRowHtml(e.data);
    });

    $('#tbody-courses').html(rows);
}

function generateRowHtml(s) {
    var statusBadge = s.isAssigned
        ? '<span style="background:#d1fae5;color:#065f46;padding:2px 8px;border-radius:999px;font-size:11px;font-weight:600;">In Curriculum</span>'
        : '<span style="background:#fee2e2;color:#991b1b;padding:2px 8px;border-radius:999px;font-size:11px;font-weight:600;">Unassigned</span>';
    return '<tr>'+
        '<td>'+s.course_code+'</td>'+
        '<td>'+s.course_name+'</td>'+
        '<td>'+s.units+'</td>'+
        '<td>'+(s.description||'—')+'</td>'+
        '<td>'+statusBadge+'</td>'+
        '<td><button class="btn btn-warning" onclick="openEdit('+s.course_id+')">Edit</button> <button class="btn btn-danger" onclick="openDelete('+s.course_id+')">Delete</button></td>'+
    '</tr>';
}

function resetTable(){
    $('#tbody-courses').html('<tr><td colspan="6"><div class="empty-state"><div class="empty-icon"></div><p>Select a program above to view its courses.</p></div></td></tr>');
    $('#pager').hide();
}

function resetFilters(){
    $('#filter-college').val('');
    $('#filter-program').val('').prop('disabled', true);
    $('#filter-search').val('').prop('disabled', true);
    $('#filter-units').val('').prop('disabled', true);
    $('#active-banner').removeClass('visible');
    $('#filter-results').text('');
    resetTable();
}

function onFormCollegeChange(){
    var collegeCode = $('#f_college_code').val();
    var progSelect = $('#f_program_code');
    progSelect.html('<option value="">Select Program</option>');
    
    if(!collegeCode) return;
    
    var collegePrograms = allPrograms.filter(function(p){
        return p.college_code === collegeCode;
    });
    $.each(collegePrograms, function(i,p){
        progSelect.append('<option value="'+p.program_code+'">'+p.program_code+' — '+p.program_name+'</option>');
    });
}

/* ── Form Logic ── */
function buildForm(course){
    var isEdit = Boolean(course);
    var id = isEdit ? course.course_id : '';
    
    var collegeOpts = '<option value="">Select College</option>';
    $.each(colleges, function(i,c){
        var selected = isEdit && course.college_code === c.college_code ? 'selected' : '';
        collegeOpts += '<option value="'+c.college_code+'" '+selected+'>'+c.college_name+'</option>';
    });
    
    var progOpts = '<option value="">Select Program</option>';
    if(isEdit && course.college_code){
        var collegePrograms = allPrograms.filter(function(p){
            return p.college_code === course.college_code;
        });
        $.each(collegePrograms, function(i,p){
            var selected = isEdit && course.program_code === p.program_code ? 'selected' : '';
            progOpts += '<option value="'+p.program_code+'" '+selected+'>'+p.program_code+' — '+p.program_name+'</option>';
        });
    }
    
    return '<input type="hidden" id="f_course_id" value="'+id+'">'+
           '<div class="form-group"><label>College</label><select id="f_college_code" onchange="onFormCollegeChange()">'+collegeOpts+'</select></div>'+
           '<div class="form-group"><label>Program</label><select id="f_program_code">'+progOpts+'</select></div>'+
           '<div class="form-group"><label>Course Code *</label><input type="text" id="f_course_code" maxlength="20" value="'+(isEdit?course.course_code:'')+'"></div>'+
           '<div class="form-group"><label>Course Name *</label><input type="text" id="f_course_name" value="'+(isEdit?course.course_name:'')+'"></div>'+
           '<div class="form-group"><label>Units *</label><input type="number" id="f_units" min="1" value="'+(isEdit?course.units:3)+'"></div>'+
           '<div class="form-group"><label>Description</label><textarea id="f_description" rows="3">'+(isEdit?(course.description||''):'')+'</textarea></div>'+
           '<div class="modal-actions"><button class="btn btn-secondary" onclick="closeModal(\'formModal\')">Cancel</button><button class="btn btn-success" onclick="save('+isEdit+')">'+(isEdit?'Update Course':'Add Course')+'</button></div>';
}

function openAdd(){
    $('#formTitle').text('Add Course');
    $('#formBody').html(buildForm(null));
    openModal('formModal');
}

function openEdit(id){
    var course = allCourses.find(function(item){ return item.course_id == id; });
    if (!course) return showToast('Course not found.','error');
    $('#formTitle').text('Update Course');
    $('#formBody').html(buildForm(course));
    openModal('formModal');
}

function save(isEdit){
    var payload = {
        action: isEdit ? 'edit' : 'save',
        course_id: $('#f_course_id').val(),
        course_code: $('#f_course_code').val().trim(),
        course_name: $('#f_course_name').val().trim(),
        units: $('#f_units').val(),
        description: $('#f_description').val().trim(),
        college_code: $('#f_college_code').val(),
        program_code: $('#f_program_code').val()
    };
    if (!payload.course_code || !payload.course_name || !payload.units) return showToast('Please fill required fields.','error');
    $.post('../ajax/manage_subjects.php', payload, function(res){
        if (res.success){ closeModal('formModal'); showToast(res.message,'success'); loadAll(function(){ applyFilters(); }); } 
        else { showToast(res.message,'error'); }
    }, 'json');
}

function openDelete(id){ pendingDeleteId = id; openModal('deleteModal'); }

$('#confirmDeleteBtn').on('click', function(){
    $.post('../ajax/manage_subjects.php', {action:'delete', course_id: pendingDeleteId}, function(res){
        closeModal('deleteModal');
        showToast(res.message, res.success?'success':'error');
        if (res.success) loadAll(function(){ applyFilters(); });
    }, 'json');
});

$(document).on('click', '.modal-overlay', function(e){ if($(e.target).hasClass('modal-overlay')) closeModal($(this).attr('id')); });
$(document).ready(function(){ loadAll(); });
</script>
</body>
</html>
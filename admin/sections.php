<?php require_once 'auth.php'; require_once '../includes/conn.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sections — Alab E-BulSU Admin</title>
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
        .filter-bar input[type="text"],
        .filter-bar select {
            padding: 8px 12px;
            border: 1px solid rgba(255,255,255,0.2);
            border-radius: 8px;
            font-size: 13px;
            font-family: inherit;
            background: rgba(255,255,255,0.05);
            color: rgba(255,255,255,0.7);
            outline: none;
            transition: border-color 0.2s;
            min-width: 140px;
        }
        .filter-bar input[type="text"] {
            min-width: 200px;
        }
        .filter-bar input[type="text"]:focus,
        .filter-bar select:focus {
            border-color: rgba(255,255,255,0.4);
            box-shadow: 0 0 0 3px rgba(255,255,255,0.1);
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
        .filter-results {
            font-size: 12px;
            color: rgba(255,255,255,0.5);
            margin-left: auto;
            white-space: nowrap;
        }
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
        <h2>Sections</h2>
        <button class="btn btn-success" onclick="openAdd()">+ Add Section</button>
    </div>

    <div class="card">
        <!-- Filter Bar -->
        <div class="filter-bar">
            <input type="text" id="filter-search" placeholder="Search code or name…" oninput="applyFilters()">
            <select id="filter-college" onchange="onCollegeChange()">
                <option value="">All Colleges</option>
            </select>
            <select id="filter-program" onchange="applyFilters()">
                <option value="">All Programs</option>
            </select>
            <select id="filter-year" onchange="applyFilters()">
                <option value="">All Year Levels</option>
                <option value="1st Year">1st Year</option>
                <option value="2nd Year">2nd Year</option>
                <option value="3rd Year">3rd Year</option>
                <option value="4th Year">4th Year</option>
            </select>
            <select id="filter-status" onchange="applyFilters()">
                <option value="">All Statuses</option>
                <option value="Open">Open</option>
                <option value="Full">Full</option>
                <option value="Closed">Closed</option>
            </select>
            <button class="btn-reset" onclick="resetFilters()">✕ Reset</button>
            <span class="filter-results" id="filter-results"></span>
        </div>

        <div class="table-wrap">
            <table class="table">
                <thead><tr><th>Code</th><th>Name</th><th>Program</th><th>Year Level</th><th>Slots</th><th>Status</th><th>Actions</th></tr></thead>
                <tbody id="tbody-sections"><tr><td colspan="7" class="loading">Loading...</td></tr></tbody>
            </table>
        </div>

        <div class="pagination" id="pager" style="display:none;">
            <button id="pagerPrev" class="btn btn-secondary">&laquo; Prev</button>
            <span class="page-info" id="pagerInfo">Page 1</span>
            <button id="pagerNext" class="btn btn-secondary">Next &raquo;</button>
        </div>
    </div>
</div>

<!-- Form Modal -->
<div class="modal-overlay" id="formModal">
    <div class="modal"><button class="modal-close" onclick="closeModal('formModal')">&times;</button>
        <h3 id="formTitle">Add Section</h3><div id="formBody"></div></div>
</div>

<!-- Delete Modal -->
<div class="modal-overlay" id="deleteModal">
    <div class="modal modal-sm"><button class="modal-close" onclick="closeModal('deleteModal')">&times;</button>
        <h3>Confirm Delete</h3><p style="color:#555;font-size:14px;margin:12px 0 24px;">Delete this section?</p>
        <div class="modal-actions-center">
            <button class="btn btn-secondary" onclick="closeModal('deleteModal')">Cancel</button>
            <button class="btn btn-danger" id="confirmDeleteBtn">Delete</button>
        </div>
    </div>
</div>

<!-- Students Modal -->
<div class="modal-overlay" id="studentsModal">
    <div class="modal modal-lg"><button class="modal-close" onclick="closeModal('studentsModal')">&times;</button>
        <h3 id="studentsModalTitle">Enrolled Students</h3>
        <div style="max-height: 500px; overflow-y: auto;">
            <table class="table">
                <thead><tr><th>Student No.</th><th>Name</th><th>Program</th><th>Year</th><th>Semester</th><th>School Year</th><th>Status</th></tr></thead>
                <tbody id="tbody-section-students">
                    <tr><td colspan="7" class="loading">Loading...</td></tr>
                </tbody>
            </table>
        </div>
        <div class="modal-actions">
            <button class="btn btn-secondary" onclick="closeModal('studentsModal')">Close</button>
        </div>
    </div>
</div>

<div id="toast"></div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="../includes/esc.js"></script>
<?php require_once __DIR__ . "/../includes/csrf_setup.php"; ?>
<script>
var colleges = [];
var programs = [];
var allPrograms = [];
var allSections = [];   // full dataset cache
var pendingDeleteId = null;

function openModal(id){$('#'+id).addClass('open');}
function closeModal(id){$('#'+id).removeClass('open');}
function showToast(msg,t){$('#toast').text(msg).removeClass('success error').addClass(t).fadeIn(200);setTimeout(function(){$('#toast').fadeOut(400);},3000);}

/* ── View Section Students ── */
function viewSectionStudents(sectionId, sectionCode){
    $('#studentsModalTitle').text('Enrolled Students - ' + sectionCode);
    $('#tbody-section-students').html('<tr><td colspan="7" class="loading">Loading...</td></tr>');
    $.get('../ajax/get_section_students.php', { section_id: sectionId }, function(data){
        if (!data.length) {
            $('#tbody-section-students').html('<tr><td colspan="7" class="empty">No students enrolled in this section.</td></tr>');
            openModal('studentsModal');
            return;
        }
        var rows = '';
        $.each(data, function(i, e){
            var fullName = esc(e.first_name) + (e.middle_name ? ' ' + esc(e.middle_name) : '') + ' ' + esc(e.last_name);
            var statusBadgeClass = { Pending: 'badge-pending', Approved: 'badge-approved', Rejected: 'badge-rejected' };
            rows += '<tr>'+
                        '<td>'+esc(e.student_number)+'</td>'+
                        '<td>'+fullName+'</td>'+
                        '<td>'+esc(e.program_name)+'</td>'+
                        '<td>'+esc(e.year_level)+'</td>'+
                        '<td>'+esc(e.semester)+'</td>'+
                        '<td>'+esc(e.school_year)+'</td>'+
                        '<td><span class="badge '+(statusBadgeClass[e.status]||'')+'">'+esc(e.status)+'</span></td>'+
                    '</tr>';
        });
        $('#tbody-section-students').html(rows);
        openModal('studentsModal');
    }, 'json');
}

/* ── Colleges & Programs ── */
function loadCollegesAndPrograms(){
    $.get('../ajax/get_colleges.php', function(data){
        colleges = data || [];
        var opts = '<option value="">All Colleges</option>';
        $.each(colleges, function(i, c){
            opts += '<option value="'+c.college_code+'">'+c.college_name+'</option>';
        });
        $('#filter-college').html(opts);
    }, 'json');
    $.get('../ajax/get_programs.php', function(data){
        allPrograms = data || [];
    }, 'json');
}

function onCollegeChange(){
    var collegeCode = $('#filter-college').val();
    var programSelect = $('#filter-program');
    programSelect.html('<option value="">All Programs</option>');
    
    if(!collegeCode){ 
        programs = allPrograms;
        $.each(allPrograms, function(i, p){
            programSelect.append('<option value="'+p.program_code+'">'+p.program_code+' — '+p.program_name+'</option>');
        });
        applyFilters();
        return;
    }
    
    programs = allPrograms.filter(function(p){ return p.college_code === collegeCode; });
    $.each(programs, function(i, p){
        programSelect.append('<option value="'+p.program_code+'">'+p.program_code+' — '+p.program_name+'</option>');
    });
    applyFilters();
}

/* ── Sections ── */
function loadSections(){
    $.get('../ajax/get_sections.php', function(data){
        allSections = data || [];
        applyFilters();
    },'json');
}

/* ── Filter Logic ── */
function applyFilters(){
    var search  = $('#filter-search').val().trim().toLowerCase();
    var college = $('#filter-college').val();
    var program = $('#filter-program').val();
    var year    = $('#filter-year').val();
    var status  = $('#filter-status').val();

    var filtered = allSections.filter(function(s){
        var matchSearch  = !search  || s.section_code.toLowerCase().includes(search) || s.section_name.toLowerCase().includes(search);
        var matchCollege = !college || s.college_code === college;
        var matchProgram = !program || s.program_code === program;
        var matchYear    = !year    || s.year_level === year;
        var matchStatus  = !status  || s.computed_status === status;
        return matchSearch && matchCollege && matchProgram && matchYear && matchStatus;
    });

    currentPage = 1;
    renderPage(filtered);

    // Results count
    $('#filter-results').text(
        filtered.length === allSections.length
            ? allSections.length + ' section(s)'
            : filtered.length + ' of ' + allSections.length + ' section(s)'
    );
}

var PER_PAGE = 15;
var currentPage = 1;
var lastFiltered = [];

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
    $('#filter-program').val('');
    $('#filter-year').val('');
    $('#filter-status').val('');
    applyFilters();
}

function renderTable(data){
    if (!data.length){
        return $('#tbody-sections').html('<tr><td colspan="7" class="empty">No sections found.</td></tr>');
    }
    var rows = '';
    $.each(data, function(i, s){
        rows += '<tr>'+
                    '<td>'+s.section_code+'</td>'+
                    '<td>'+s.section_name+'</td>'+
                    '<td>'+s.program_code+'</td>'+
                    '<td>'+s.year_level+'</td>'+
                    '<td>'+s.current_enrolled+'/'+s.max_capacity+' ('+s.available_slots+' left)</td>'+
                    '<td>'+s.computed_status+'</td>'+
                    '<td>'+
                        '<button class="btn btn-primary" onclick="viewSectionStudents('+s.section_id+', \''+s.section_code+'\')">View Students</button> '+
                        '<button class="btn btn-warning" onclick="openEdit('+s.section_id+')">Edit</button> '+
                        '<button class="btn btn-danger" onclick="openDelete('+s.section_id+')">Delete</button>'+
                    '</td>'+
                '</tr>';
    });
    $('#tbody-sections').html(rows);
}

/* ── Form ── */
function buildForm(section){
    var isEdit = Boolean(section);
    var id = isEdit ? section.section_id : '';
    var collegeOpts = '<option value="">— Select College —</option>';
    $.each(colleges, function(i,c){ collegeOpts += '<option value="'+c.college_code+'"'+(isEdit && c.college_code===section.college_code ? ' selected' : '')+'>'+c.college_name+'</option>'; });
    var progOpts = '<option value="">— Select Program —</option>';
    if(isEdit && section.college_code){
        var collegePrograms = allPrograms.filter(function(p){ return p.college_code === section.college_code; });
        $.each(collegePrograms, function(i,p){ progOpts += '<option value="'+p.program_code+'"'+(isEdit && p.program_code===section.program_code ? ' selected' : '')+'>'+p.program_name+' ('+p.program_code+')</option>'; });
    } else {
        $.each(allPrograms, function(i,p){ progOpts += '<option value="'+p.program_code+'"'+(isEdit && p.program_code===section.program_code ? ' selected' : '')+'>'+p.program_name+' ('+p.program_code+')</option>'; });
    }
    var yearLevels = ['1st Year','2nd Year','3rd Year','4th Year'];
    var yearOpts = '<option value="">— Select Year Level —</option>';
    $.each(yearLevels, function(i,y){ yearOpts += '<option value="'+y+'"'+(isEdit && y===section.year_level ? ' selected' : '')+'>'+y+'</option>'; });
    var statusOpts = '<option value="Open"'+(isEdit && section.status==='Open' ? ' selected' : '')+'>Open</option>' +
                     '<option value="Full"'+(isEdit && section.status==='Full' ? ' selected' : '')+'>Full</option>' +
                     '<option value="Closed"'+(isEdit && section.status==='Closed' ? ' selected' : '')+'>Closed</option>';
    return '<input type="hidden" id="f_section_id" value="'+id+'">'+
           '<div class="form-row">'+
               '<div class="form-group"><label>College *</label><select id="f_college_code" onchange="onFormCollegeChange()">'+collegeOpts+'</select></div>'+
               '<div class="form-group"><label>Program *</label><select id="f_program_code">'+progOpts+'</select></div>'+
           '</div>'+
           '<div class="form-row">'+
               '<div class="form-group"><label>Section Code *</label><input type="text" id="f_section_code" maxlength="20" value="'+(isEdit?section.section_code:'')+'"></div>'+
               '<div class="form-group"><label>Section Name *</label><input type="text" id="f_section_name" value="'+(isEdit?section.section_name:'')+'"></div>'+
           '</div>'+
           '<div class="form-row">'+
               '<div class="form-group"><label>Year Level *</label><select id="f_year_level">'+yearOpts+'</select></div>'+
           '</div>'+
           '<div class="form-row">'+
               '<div class="form-group"><label>Max Capacity *</label><input type="number" id="f_max_capacity" min="1" value="'+(isEdit?section.max_capacity:30)+'"></div>'+
               '<div class="form-group"><label>Status *</label><select id="f_status">'+statusOpts+'</select></div>'+
           '</div>'+
           '<div class="modal-actions"><button class="btn btn-secondary" onclick="closeModal(\'formModal\')">Cancel</button><button class="btn btn-success" onclick="save('+isEdit+')">'+(isEdit?'Update Section':'Add Section')+'</button></div>';
}

function onFormCollegeChange(){
    var collegeCode = $('#f_college_code').val();
    var programSelect = $('#f_program_code');
    programSelect.html('<option value="">— Select Program —</option>');
    
    if(!collegeCode) return;
    
    var collegePrograms = allPrograms.filter(function(p){ return p.college_code === collegeCode; });
    $.each(collegePrograms, function(i, p){
        programSelect.append('<option value="'+p.program_code+'">'+p.program_name+' ('+p.program_code+')</option>');
    });
}

function openAdd(){
    $('#formTitle').text('Add Section');
    $('#formBody').html(buildForm(null));
    openModal('formModal');
}

function openEdit(id){
    var section = allSections.find(function(item){ return item.section_id == id; });
    if (!section) return showToast('Section not found.','error');
    $('#formTitle').text('Update Section');
    $('#formBody').html(buildForm(section));
    openModal('formModal');
}

function save(isEdit){
    var payload = {
        action: isEdit ? 'edit' : 'save',
        section_id: $('#f_section_id').val(),
        section_code: $('#f_section_code').val().trim(),
        section_name: $('#f_section_name').val().trim(),
        college_code: $('#f_college_code').val(),
        program_code: $('#f_program_code').val(),
        year_level: $('#f_year_level').val(),
        max_capacity: $('#f_max_capacity').val(),
        status: $('#f_status').val()
    };
    if (!payload.section_code || !payload.section_name || !payload.college_code || !payload.program_code || !payload.year_level || !payload.max_capacity) {
        return showToast('Please fill in all required fields.','error');
    }
    $.post('../ajax/manage_sections.php', payload, function(res){
        if (res.success) {
            closeModal('formModal');
            showToast(res.message,'success');
            loadSections();
        } else {
            showToast(res.message,'error');
        }
    },'json');
}

function openDelete(id){
    pendingDeleteId = id;
    openModal('deleteModal');
}

$('#confirmDeleteBtn').on('click', function(){
    $.post('../ajax/manage_sections.php', {action:'delete', section_id: pendingDeleteId}, function(res){
        closeModal('deleteModal');
        showToast(res.message, res.success ? 'success' : 'error');
        if (res.success) loadSections();
    }, 'json');
});

$(document).on('click', '.modal-overlay', function(e){ if ($(e.target).hasClass('modal-overlay')) closeModal($(this).attr('id')); });

loadCollegesAndPrograms();
setTimeout(function(){ loadSections(); }, 100);
</script>
</body>
</html>
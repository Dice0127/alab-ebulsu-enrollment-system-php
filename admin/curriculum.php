<?php require_once 'auth.php'; require_once '../includes/conn.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Curriculum — Alab E-BulSU Admin</title>
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
        .active-banner .breadcrumb-sep { color: #a5b4fc; margin: 0 2px; }

        .empty-state { text-align: center; padding: 48px 20px; color: #9ca3af; }
        .empty-state .empty-icon { font-size: 36px; margin-bottom: 10px; }
        .empty-state p { font-size: 14px; margin: 0; }

        /* ── Course picker ── */
        .course-picker { border: 1px solid #d1d5db; border-radius: 8px; overflow: hidden; margin-top: 4px; }
        .course-picker-search { width: 100%; padding: 8px 12px; border: none; border-bottom: 1px solid #e5e7eb; font-size: 13px; font-family: inherit; outline: none; box-sizing: border-box; }
        .course-picker-search:focus { background: #f5f3ff; }
        .course-picker-list { max-height: 180px; overflow-y: auto; padding: 4px 0; background: #fff; }
        .course-picker-item { display: flex; align-items: center; gap: 8px; padding: 6px 12px; font-size: 13px; cursor: pointer; transition: background 0.15s; }
        .course-picker-item:hover { background: #f5f3ff; }
        .course-picker-item input[type="checkbox"] { accent-color: #6366f1; width: 14px; height: 14px; flex-shrink: 0; }
        .course-picker-item label { cursor: pointer; color: #374151; line-height: 1.3; flex-grow: 1; }
        .course-picker-item.selected { background: #eef2ff; }
        .course-picker-empty { padding: 12px; font-size: 13px; color: #9ca3af; text-align: center; }

        .selected-tags { display: flex; flex-wrap: wrap; gap: 6px; margin-top: 8px; min-height: 24px; }
        .selected-tag { display: inline-flex; align-items: center; gap: 5px; background: #eef2ff; border: 1px solid #c7d2fe; color: #3730a3; border-radius: 20px; padding: 3px 10px; font-size: 12px; font-weight: 500; }
        .selected-tag .tag-remove { cursor: pointer; color: #6366f1; font-size: 14px; line-height: 1; margin-left: 2px; }
        .selected-tag .tag-remove:hover { color: #dc2626; }
        .selected-count { font-size: 12px; color: #6b7280; margin-top: 6px; }

        /* Organization Styles */
        .group-header { background: rgba(255,255,255,0.08) !important; }
        .group-header td { font-weight: 600; font-size: 12px; color: rgba(255,255,255,0.7); padding: 8px 12px !important; letter-spacing: 0.05em; text-transform: uppercase; border-bottom: 1px solid rgba(255,255,255,0.12); }
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
        <h2>Program Curriculum</h2>
        <button class="btn btn-success" onclick="openAdd()">+ Add Curriculum Entry</button>
    </div>

    <div class="card">
        <div class="filter-bar">
            <select id="filter-college" onchange="onCollegeChange()">
                <option value="">— Select College —</option>
            </select>
            <select id="filter-program" onchange="onProgramChange()" disabled>
                <option value="">— Select Program —</option>
            </select>
            <select id="filter-year" onchange="onYearChange()" disabled>
                <option value="">— Select Year Level —</option>
                <option value="1st Year">1st Year</option>
                <option value="2nd Year">2nd Year</option>
                <option value="3rd Year">3rd Year</option>
                <option value="4th Year">4th Year</option>
            </select>
            <select id="filter-semester" onchange="applyFilters()" disabled>
                <option value="">All Semesters</option>
                <option value="1">1st Semester</option>
                <option value="2">2nd Semester</option>
                <option value="99">Summer Class</option>
            </select>
            <button class="btn-reset" onclick="resetFilters()">✕ Reset</button>
            <span class="filter-results" id="filter-results"></span>
        </div>

        <div class="active-banner" id="active-banner">
            <span></span>
            <span id="banner-text"></span>
        </div>

        <div class="table-wrap">
            <table class="table">
                <thead><tr><th>Program</th><th>Year Level</th><th>Semester</th><th>Course</th><th>Actions</th></tr></thead>
                <tbody id="tbody-curriculum">
                    <tr><td colspan="5">
                        <div class="empty-state">
                            <div class="empty-icon"></div>
                            <p>Select a program above to view its curriculum.</p>
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
        <h3 id="formTitle">Add Curriculum Entry</h3><div id="formBody"></div></div>
</div>

<div class="modal-overlay" id="deleteModal">
    <div class="modal modal-sm"><button class="modal-close" onclick="closeModal('deleteModal')">&times;</button>
        <h3>Confirm Delete</h3><p style="color:#555;font-size:14px;margin:12px 0 24px;">Delete this curriculum entry?</p>
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
var programs = [], subjects = [], allCurriculum = [], pendingDeleteId = null;
var selectedCourseIds = [];
var colleges = [];
var allPrograms = [];

function openModal(id){$('#'+id).addClass('open');}
function closeModal(id){$('#'+id).removeClass('open');}
function showToast(msg,t){$('#toast').text(msg).removeClass('success error').addClass(t).fadeIn(200);setTimeout(function(){$('#toast').fadeOut(400);},3000);}
function semLabel(v){ return v==1?'1st Semester':v==2?'2nd Semester':v==99?'Summer Class':''; }

function loadOptions(){
    $.get('../ajax/get_colleges.php', function(data){
        colleges = data;
        var opts = '<option value="">— Select College —</option>';
        $.each(colleges, function(i,c){ opts += '<option value="'+c.college_code+'">'+c.college_name+'</option>'; });
        $('#filter-college').html(opts);
    }, 'json');
    $.get('../ajax/get_programs.php', function(data){
        allPrograms = data;
    }, 'json');
    $.get('../ajax/get_subjects.php', function(data){ subjects = data; }, 'json');
}

function onCollegeChange(){
    var collegeCode = $('#filter-college').val();
    var programSelect = $('#filter-program');
    programSelect.html('<option value="">— Select Program —</option>').prop('disabled', !collegeCode);
    
    if(!collegeCode){ 
        $('#filter-year').val('').prop('disabled', true);
        $('#filter-semester').val('').prop('disabled', true);
        resetFilters();
        return;
    }
    
    programs = allPrograms.filter(function(p){ return p.college_code === collegeCode; });
    $.each(programs, function(i,p){
        programSelect.append('<option value="'+p.program_code+'">'+p.program_name+' ('+p.program_code+')</option>');
    });
}

function onProgramChange(){
    var prog = $('#filter-program').val();
    $('#filter-year').val('').prop('disabled', !prog);
    $('#filter-semester').val('').prop('disabled', true);
    updateBanner();
    if (!prog){ resetTable(); $('#filter-results').text(''); return; }
    applyFilters();
}

function onYearChange(){
    var year = $('#filter-year').val();
    $('#filter-semester').prop('disabled', !year).val('');
    updateBanner();
    applyFilters();
}

function applyFilters(){
    var prog = $('#filter-program').val();
    var year = $('#filter-year').val();
    var sem  = $('#filter-semester').val();
    if (!prog){ resetTable(); return; }
    var filtered = allCurriculum.filter(function(item){
        return item.program_code === prog && (!year || item.year_level === year) && (!sem || String(item.semester) === sem);
    });
    currentPage = 1;
    renderPage(filtered);
    updateBanner();
    var base = allCurriculum.filter(function(item){ return item.program_code === prog; }).length;
    $('#filter-results').text(filtered.length === base ? base+' entries' : filtered.length+' of '+base+' entries');
}

var PER_PAGE = 12;
var currentPage = 1;
var lastFiltered = [];

function renderPage(data){
    data = data.slice().sort(function(a, b){
        var y = a.year_level.localeCompare(b.year_level);
        if (y !== 0) return y;
        return a.semester - b.semester;
    });

    // Flatten into entries so a group header counts as one row for paging purposes.
    var entries = [];
    var lastGroup = '';
    $.each(data, function(i, item){
        var currentGroup = item.year_level + ' — ' + semLabel(item.semester);
        if (currentGroup !== lastGroup){
            entries.push({ type: 'header', label: currentGroup });
            lastGroup = currentGroup;
        }
        entries.push({ type: 'row', data: item });
    });

    var totalPages = Math.max(1, Math.ceil(entries.length / PER_PAGE));
    currentPage = Math.min(currentPage, totalPages);
    var start = (currentPage - 1) * PER_PAGE;
    renderTable(entries.slice(start, start + PER_PAGE), data.length);

    if (entries.length <= PER_PAGE) {
        $('#pager').hide();
    } else {
        $('#pager').show();
        $('#pagerInfo').text('Page ' + currentPage + ' of ' + totalPages + ' (' + data.length + ' total)');
        $('#pagerPrev').prop('disabled', currentPage <= 1);
        $('#pagerNext').prop('disabled', currentPage >= totalPages);
    }
    lastFiltered = data;
}
$('#pagerPrev').on('click', function(){ if (currentPage > 1) { currentPage--; renderPage(lastFiltered); } });
$('#pagerNext').on('click', function(){ currentPage++; renderPage(lastFiltered); });

function updateBanner(){
    var prog = $('#filter-program').val();
    var year = $('#filter-year').val();
    var sem  = $('#filter-semester').val();
    if (!prog){ $('#active-banner').removeClass('visible'); return; }
    var progObj = programs.find(function(p){ return p.program_code === prog; });
    var progLabel = progObj ? progObj.program_name+' ('+prog+')' : prog;
    var parts = ['<strong>'+progLabel+'</strong>'];
    if (year) parts.push('<strong>'+year+'</strong>');
    if (sem)  parts.push('<strong>'+semLabel(sem)+'</strong>');
    $('#banner-text').html(parts.join(' <span class="breadcrumb-sep">›</span> '));
    $('#active-banner').addClass('visible');
}

function renderTable(entries, totalCount){
    if (!totalCount){ $('#tbody-curriculum').html('<tr><td colspan="5" class="empty">No entries found.</td></tr>'); return; }

    var rows = '';
    $.each(entries, function(i, e){
        if (e.type === 'header') {
            rows += '<tr class="group-header"><td colspan="5">'+e.label+'</td></tr>';
            return;
        }
        var item = e.data;
        rows += '<tr>'+
                    '<td>'+item.program_name+' ('+item.program_code+')</td>'+
                    '<td>'+item.year_level+'</td>'+
                    '<td>'+semLabel(item.semester)+'</td>'+
                    '<td><strong>'+item.course_code+'</strong> — '+item.course_name+'</td>'+
                    '<td><button class="btn btn-warning" onclick="openEdit('+item.curriculum_id+')">Edit</button> <button class="btn btn-danger" onclick="openDelete('+item.curriculum_id+')">Delete</button></td>'+
                '</tr>';
    });
    $('#tbody-curriculum').html(rows);
}

function resetTable(){
    $('#tbody-curriculum').html('<tr><td colspan="5"><div class="empty-state"><div class="empty-icon"></div><p>Select a college and program above to view its curriculum.</p></div></td></tr>');
    $('#pager').hide();
}

function resetFilters(){
    $('#filter-college').val('');
    $('#filter-program').val('').prop('disabled', true);
    $('#filter-year').prop('disabled', true).val('');
    $('#filter-semester').prop('disabled', true).val('');
    $('#active-banner').removeClass('visible');
    $('#filter-results').text('');
    resetTable();
}

/* ── Course Picker Logic ── */
function renderCourseList(filterText){
    filterText = (filterText || '').toLowerCase();
    var existing = getExistingCourseIds();
    var programCode = $('#f_program_code').val();
    
    // Filter courses by program
    var courseList = programCode 
        ? subjects.filter(function(s){ return s.program_code === programCode; })
        : subjects;
    
    // Further filter by search text
    var list = courseList.filter(function(s){
        return !filterText || s.course_code.toLowerCase().includes(filterText) || s.course_name.toLowerCase().includes(filterText);
    });
    
    if (!list.length){ $('#course-picker-list').html('<div class="course-picker-empty">No courses match.</div>'); return; }
    var html = '';
    $.each(list, function(i, s){
        var checked = selectedCourseIds.indexOf(s.course_id) > -1;
        var alreadyIn = existing.indexOf(String(s.course_id)) > -1;
        var disabledStyle = alreadyIn ? ' style="opacity:0.45;pointer-events:none;"' : '';
        html += '<div class="course-picker-item'+(checked?' selected':'')+'"'+disabledStyle+' onclick="toggleCourse('+s.course_id+', this)">'+
                    '<input type="checkbox" '+(checked?'checked':'')+(alreadyIn?' disabled':'')+'>'+
                    '<label><strong>'+s.course_code+'</strong> — '+s.course_name+'</label>'+
                '</div>';
    });
    $('#course-picker-list').html(html);
}

function getExistingCourseIds(){
    var prog = $('#f_program_code').val(), year = $('#f_year_level').val(), sem = $('#f_semester').val();
    if (!prog || !year || !sem) return [];
    return allCurriculum.filter(function(item){ 
        return item.program_code===prog && item.year_level===year && String(item.semester)===String(sem); 
    }).map(function(item){ return String(item.course_id); });
}

function toggleCourse(id, el){
    var idx = selectedCourseIds.indexOf(id);
    if (idx > -1) selectedCourseIds.splice(idx, 1);
    else selectedCourseIds.push(id);
    $(el).toggleClass('selected').find('input').prop('checked', idx === -1);
    renderSelectedTags();
}

function renderSelectedTags(){
    var html = '';
    $.each(selectedCourseIds, function(i, id){
        var s = subjects.find(function(x){ return x.course_id == id; });
        if (s) html += '<span class="selected-tag">'+s.course_code+'<span class="tag-remove" onclick="removeTag('+s.course_id+')">×</span></span>';
    });
    $('#selected-tags').html(html || '<span style="font-size:12px;color:#9ca3af;">No courses selected.</span>');
}

function removeTag(id){
    selectedCourseIds = selectedCourseIds.filter(function(x){ return x != id; });
    renderCourseList($('#course-search').val());
    renderSelectedTags();
}

function onAddFormProgramChange(){
    renderCourseList($('#course-search').val());
}

function onAddFormCollegeChange(){
    var collegeCode = $('#f_college_code').val();
    var programSelect = $('#f_program_code');
    programSelect.html('<option value="">Select Program</option>');
    
    if(!collegeCode) {
        programSelect.prop('disabled', true);
        renderCourseList('');
        return;
    }
    
    programSelect.prop('disabled', false);
    var collegePrograms = allPrograms.filter(p => p.college_code === collegeCode);
    $.each(collegePrograms, function(i, p){
        programSelect.append('<option value="'+p.program_code+'">'+p.program_name+'</option>');
    });
}

/* ── Forms ── */
function openAdd(){
    selectedCourseIds = [];
    var collegeOpts = '';
    $.each(colleges, function(i, c){
        collegeOpts += '<option value="'+c.college_code+'">'+c.college_name+'</option>';
    });
    var yearOptions = ['1st Year','2nd Year','3rd Year','4th Year'].map(y => `<option value="${y}">${y}</option>`).join('');
    
    $('#formTitle').text('Add Curriculum Entry');
    $('#formBody').html(`
        <div class="form-row">
            <div class="form-group"><label>College *</label><select id="f_college_code" onchange="onAddFormCollegeChange()"><option value="">— Select College —</option>${collegeOpts}</select></div>
            <div class="form-group"><label>Program *</label><select id="f_program_code" onchange="onAddFormProgramChange()" disabled><option value="">Select Program</option></select></div>
        </div>
        <div class="form-row">
            <div class="form-group"><label>Year Level *</label><select id="f_year_level" onchange="onAddFormProgramChange()"><option value="">Select</option>${yearOptions}</select></div>
            <div class="form-group"><label>Semester *</label><select id="f_semester" onchange="renderCourseList($('#course-search').val())"><option value="">Select</option><option value="1">1st Sem</option><option value="2">2nd Sem</option><option value="99">Summer</option></select></div>
        </div>
        <div class="form-group"><label>Courses *</label>
            <div class="course-picker">
                <input type="text" class="course-picker-search" id="course-search" placeholder="Search..." oninput="renderCourseList(this.value)">
                <div class="course-picker-list" id="course-picker-list"></div>
            </div>
            <div class="selected-tags" id="selected-tags"></div>
        </div>
        <div class="modal-actions"><button class="btn btn-secondary" onclick="closeModal('formModal')">Cancel</button><button class="btn btn-success" onclick="saveMulti()">Add Entries</button></div>
    `);
    
    renderCourseList('');
    renderSelectedTags();
    openModal('formModal');
}

function saveMulti(){
    var payload = {
        action: 'save_multi',
        program_code: $('#f_program_code').val(),
        year_level: $('#f_year_level').val(),
        semester: $('#f_semester').val(),
        course_ids: JSON.stringify(selectedCourseIds)
    };
    if (!payload.program_code || !payload.year_level || !payload.semester || !selectedCourseIds.length) return showToast('Missing fields','error');
    $.post('../ajax/manage_curriculum.php', payload, function(res){
        if (res.success){ closeModal('formModal'); showToast(res.message,'success'); loadAllCurriculum(applyFilters); }
        else showToast(res.message, 'error');
    }, 'json');
}

function openEdit(id){
    var entry = allCurriculum.find(x => x.curriculum_id == id);
    var progOptions = allPrograms.map(p => `<option value="${p.program_code}" ${p.program_code==entry.program_code?'selected':''}>${p.program_name}</option>`).join('');
    var courseOptions = subjects.map(s => `<option value="${s.course_id}" ${s.course_id==entry.course_id?'selected':''}>${s.course_code}</option>`).join('');
    var yearOptions = ['1st Year','2nd Year','3rd Year','4th Year'].map(y => `<option value="${y}" ${y===entry.year_level?'selected':''}>${y}</option>`).join('');
    var semOptions = '<option value="">Select</option>' +
        '<option value="1" '+(entry.semester==1?'selected':'')+'>1st Sem</option>' +
        '<option value="2" '+(entry.semester==2?'selected':'')+'>2nd Sem</option>' +
        '<option value="99" '+(entry.semester==99?'selected':'')+'>Summer</option>';

    $('#formTitle').text('Update Entry');
    $('#formBody').html(`
        <input type="hidden" id="f_curriculum_id" value="${entry.curriculum_id}">
        <div class="form-row">
            <div class="form-group"><label>Program *</label><select id="f_program_code">${progOptions}</select></div>
            <div class="form-group"><label>Year Level *</label><select id="f_year_level">${yearOptions}</select></div>
        </div>
        <div class="form-group"><label>Semester *</label><select id="f_semester">${semOptions}</select></div>
        <div class="form-group"><label>Course *</label><select id="f_course_id">${courseOptions}</select></div>
        <div class="modal-actions"><button class="btn btn-secondary" onclick="closeModal('formModal')">Cancel</button><button class="btn btn-success" onclick="saveEdit()">Update</button></div>
    `);
    openModal('formModal');
}

function saveEdit(){
    var payload = {
        action: 'edit',
        curriculum_id: $('#f_curriculum_id').val(),
        program_code: $('#f_program_code').val(),
        year_level: $('#f_year_level').val(),
        semester: $('#f_semester').val(),
        course_id: $('#f_course_id').val()
    };
    if (!payload.program_code || !payload.year_level || !payload.semester || !payload.course_id) return showToast('Missing fields','error');
    $.post('../ajax/manage_curriculum.php', payload, function(res){
        if (res.success){ closeModal('formModal'); showToast(res.message,'success'); loadAllCurriculum(applyFilters); }
        else showToast(res.message, 'error');
    }, 'json');
}

function openDelete(id){ pendingDeleteId = id; openModal('deleteModal'); }

function loadAllCurriculum(callback){
    $.get('../ajax/get_curriculum.php', function(data){
        allCurriculum = data || [];
        if (callback) callback();
    }, 'json');
}

$('#confirmDeleteBtn').on('click', function(){
    $.post('../ajax/manage_curriculum.php', {action:'delete', curriculum_id: pendingDeleteId}, function(res){
        closeModal('deleteModal');
        showToast(res.message, res.success?'success':'error');
        if (res.success) loadAllCurriculum(applyFilters);
    }, 'json');
});

$(document).on('click', '.modal-overlay', function(e){ if($(e.target).hasClass('modal-overlay')) closeModal($(this).attr('id')); });
$(document).ready(function(){ loadOptions(); loadAllCurriculum(); });
</script>
</body>
</html>
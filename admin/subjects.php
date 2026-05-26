<?php
require 'auth.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/classes/Subject.php';
require_once __DIR__ . '/classes/SubjectSchedule.php';

$subjectModel = new Subject();
$scheduleModel = new SubjectSchedule();
$success_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        $action = $_POST['action'];

        if ($action === 'add') {
            $data = [
                'code' => strtoupper(trim($_POST['code'])),
                'name' => trim($_POST['name']),
                'teacher' => trim($_POST['teacher']),
                'units' => (int) $_POST['units'],
                'status' => 'Active',
            ];
            $subject_id = $subjectModel->add($data);

            if (isset($_POST['schedules']) && is_array($_POST['schedules'])) {
                foreach ($_POST['schedules'] as $sched) {
                    if (!empty($sched['time_slot'])) {
                        $sched['subject_id'] = $subject_id;
                        $scheduleModel->add($sched);
                    }
                }
            }
            $_SESSION['flash'] = '"' . $data['name'] . '" added successfully.';
        } elseif ($action === 'edit') {
            $edit_id = (int) $_POST['edit_id'];
            $data = [
                'code' => strtoupper(trim($_POST['code'])),
                'name' => trim($_POST['name']),
                'teacher' => trim($_POST['teacher']),
                'units' => (int) $_POST['units'],
            ];
            $subjectModel->update($edit_id, $data);

            // Replace schedules
            $scheduleModel->deleteBySubjectId($edit_id);
            if (isset($_POST['schedules']) && is_array($_POST['schedules'])) {
                foreach ($_POST['schedules'] as $sched) {
                    if (!empty($sched['time_slot'])) {
                        $sched['subject_id'] = $edit_id;
                        $scheduleModel->add($sched);
                    }
                }
            }
            $_SESSION['flash'] = '"' . $data['name'] . '" updated successfully.';
        } elseif ($action === 'delete') {
            $delete_id = (int) $_POST['delete_id'];
            $subjectModel->delete($delete_id);
            $_SESSION['flash'] = 'Subject deleted successfully.';
        }

        header('Location: subjects.php');
        exit;
    }
}

if (isset($_SESSION['flash'])) {
    $success_message = $_SESSION['flash'];
    unset($_SESSION['flash']);
}

// Pagination
$perPage = 5;
$page = max(1, (int)($_GET['page'] ?? 1));
$offset = ($page - 1) * $perPage;

$subjects = $subjectModel->getAll($perPage, $offset);
$total_subjects = $subjectModel->countAll();
$stats = $subjectModel->stats();
$total_units = (int) ($stats['total_units'] ?? array_sum(array_column($subjects, 'units')));

// Fetch schedules for displayed subjects
$allSchedules = [];
$subjectSchedulesJson = [];
foreach ($subjects as $subject) {
    $scheds = $scheduleModel->getBySubjectId($subject['id']);
    $allSchedules[$subject['id']] = $scheds;
    $subjectSchedulesJson[$subject['id']] = $scheds;
}

// ----------------------------------------------------------
// PAGE TITLES
// ----------------------------------------------------------
$active_page = 'subjects';
$page_title  = 'Subjects';
$page_icon   = '<i class="bi bi-journal-text"></i>';

include 'header.php';
?>


<main class="content">
    <?php if (!empty($success_message)): ?>
        <div class="alert-success"> <?= htmlspecialchars($success_message) ?></div>
    <?php endif; ?>

    <div class="stats-row">
        <div class="stat-card">
            <div class="stat-label">Total Subjects</div>
            <div class="stat-value blue"><?= $total_subjects ?></div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Total Units</div>
            <div class="stat-value green"><?= $total_units ?></div>
        </div>
    </div>

    <div style="margin-bottom: 24px;">
        <button type="button" class="btn-add" onclick="openAddModal()">
            <i class="bi bi-plus-square"></i> Add Subject
        </button>
    </div>

    <div class="table-card">
        <div class="table-card-header">
            <div class="table-card-title">Enrolled Subjects</div>
        </div>
        <table class="data-table" style="border-collapse: collapse;">
            <thead>
                <tr>
                    <th>Code</th>
                    <th>Subject Name</th>
                    <th>Units</th>
                    <th>Teacher</th>
                    <th>Time</th>
                    <th>Days</th>
                    <th>Type</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($total_subjects === 0): ?>
                <tr>
                    <td colspan="10" style="text-align:center; padding:24px; color:var(--text-muted);">
                        No subjects yet. Use the form above to add one.
                    </td>
                </tr>
                <?php endif; ?>

                <?php foreach ($subjects as $i => $subject): 
                    $schedules = $allSchedules[$subject['id']] ?? [];
                    $rowCount = max(1, count($schedules));
                ?>
                <tr class="subject-header-row">
                    <td class="code-cell" rowspan="<?= $rowCount ?>"><?= htmlspecialchars($subject['code']) ?></td>
                    <td rowspan="<?= $rowCount ?>"><?= htmlspecialchars($subject['name']) ?></td>
                    <td rowspan="<?= $rowCount ?>"><?= $subject['units'] ?>.0</td>
                    <td rowspan="<?= $rowCount ?>"><?= htmlspecialchars($subject['teacher']) ?></td>
                    
                    <?php if (empty($schedules)): ?>
                        <td colspan="4" style="text-align:center; color: var(--text-muted);">No schedules assigned.</td>
                    <?php else: ?>
                        <td style="white-space: nowrap; color: var(--accent);"><i class="bi bi-clock" style="margin-right: 6px;"></i><?= htmlspecialchars($schedules[0]['time_slot']) ?></td>
                        <td style="white-space: nowrap; font-weight: 500; color: var(--text);"><?= htmlspecialchars($schedules[0]['days']) ?></td>
                        <td style="white-space: nowrap; font-weight: 500; color: var(--text-muted);"><?= htmlspecialchars($schedules[0]['type']) ?></td>
                    <?php endif; ?>
                    
                    <td rowspan="<?= $rowCount ?>">
                        <div class="action-buttons">
                            <button type="button" class="btn-action btn-edit" onclick="openEditModal(<?= $subject['id'] ?>)">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <button type="button" class="btn-action btn-delete" onclick="deleteSubject(<?= $subject['id'] ?>, '<?= htmlspecialchars(addslashes($subject['name'])) ?>')">
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>
                    </td>
                </tr>
                
                <?php for ($k = 1; $k < count($schedules); $k++): 
                    $sched = $schedules[$k];
                ?>
                <tr class="schedule-row">
                    <td style="white-space: nowrap; color: var(--accent);"><i class="bi bi-clock" style="margin-right: 6px;"></i><?= htmlspecialchars($sched['time_slot']) ?></td>
                    <td style="white-space: nowrap; font-weight: 500; color: var(--text);"><?= htmlspecialchars($sched['days']) ?></td>
                    <td style="white-space: nowrap; font-weight: 500; color: var(--text-muted);"><?= htmlspecialchars($sched['type']) ?></td>
                </tr>
                <?php endfor; ?>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <?php if ($total_subjects > $perPage):
        $totalPages = (int) ceil($total_subjects / $perPage);
    ?>
    <div class="pagination">
        <?php if ($page > 1): ?>
            <a class="page-link" href="?page=<?= $page - 1 ?>">&laquo; Prev</a>
        <?php endif; ?>

        <?php for ($p = 1; $p <= $totalPages; $p++): ?>
            <?php if ($p === $page): ?>
                <span class="page-link current"><?= $p ?></span>
            <?php else: ?>
                <a class="page-link" href="?page=<?= $p ?>"><?= $p ?></a>
            <?php endif; ?>
        <?php endfor; ?>

        <?php if ($page < $totalPages): ?>
            <a class="page-link" href="?page=<?= $page + 1 ?>">Next &raquo;</a>
        <?php endif; ?>
    </div>
    <?php endif; ?>

    <form id="deleteForm" method="POST" action="" style="display: none;">
        <input type="hidden" name="action" value="delete">
        <input type="hidden" name="delete_id" value="">
    </form>

    <!-- Modal Form Template -->
    <template id="scheduleRowTemplate">
        <div class="schedule-input-row">
            <input type="text" name="schedules[{INDEX}][time_slot]" placeholder="Time (10:00-11:00)" required>
            <input type="text" name="schedules[{INDEX}][days]" placeholder="Days (TTh)" pattern=".*[a-zA-Z]+.*" title="Must contain at least one letter." required>
            <select name="schedules[{INDEX}][type]" required>
                <option value="Lecture">Lecture</option>
                <option value="Laboratory">Laboratory</option>
            </select>
            <button type="button" class="btn-remove-row" onclick="this.parentElement.remove()"><i class="bi bi-x-lg"></i></button>
        </div>
    </template>

    <!-- Subject Modal (Used for both Add & Edit) -->
    <div id="subjectModal" class="modal">
        <div class="modal-content" style="max-width: 800px;">
            <div class="modal-header">
                <h2 class="modal-title" id="modalTitle">Add Subject</h2>
                <button type="button" class="modal-close" onclick="closeModal()">&times;</button>
            </div>
            <div class="modal-body">
                <form method="POST" action="" id="subjectForm">
                    <input type="hidden" name="action" id="formAction" value="add">
                    <input type="hidden" name="edit_id" id="edit_id" value="">
                    
                    <h3 style="margin-bottom: 10px; font-size: 1rem; color: #00ff41;">Course Information</h3>
                    <div class="form-grid" style="grid-template-columns: 1fr 2fr 2fr 1fr;">
                        <div class="form-group">
                            <label>Code</label>
                            <input type="text" id="code" name="code" placeholder="e.g. SUBJ101" required maxlength="20">
                        </div>
                        <div class="form-group">
                            <label>Subject Name</label>
                            <input type="text" id="name" name="name" placeholder="e.g. Introduction to Programming" pattern=".*[a-zA-Z]+.*" title="Must contain at least one letter." required>
                        </div>
                        <div class="form-group">
                            <label>Teacher</label>
                            <input type="text" id="teacher" name="teacher" placeholder="e.g. Smith, Jane" pattern=".*[a-zA-Z]+.*" title="Must contain at least one letter." required>
                        </div>
                        <div class="form-group">
                            <label>Units</label>
                            <select id="units" name="units" required>
                                <option value="1">1.0</option>
                                <option value="2">2.0</option>
                                <option value="3" selected>3.0</option>
                                <option value="4">4.0</option>
                                <option value="5">5.0</option>
                            </select>
                        </div>
                    </div>
                    
                    <h3 style="margin: 20px 0 10px 0; font-size: 1rem; color: #00ff41;">Schedules</h3>
                    <div id="scheduleContainer">
                        <!-- Dynamic rows injected here -->
                    </div>
                    <button type="button" class="btn-add" onclick="addScheduleRow()" style="margin-top: 10px; font-size: 0.8rem; padding: 8px 16px; background: var(--surface2); color: var(--text); border: 1px dashed var(--border);">
                        <i class="bi bi-plus-circle"></i> Add Schedule Row
                    </button>

                    <div style="margin-top: 30px; text-align: right;">
                        <button type="submit" class="btn-submit" id="btnSubmitForm"><i class="bi bi-save"></i> Save Subject</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</main>

<script>
let scheduleIndex = 0;
const subjectsData = <?= json_encode($subjects) ?>;
const schedulesData = <?= json_encode($subjectSchedulesJson) ?>;

function openAddModal() {
    document.getElementById('modalTitle').innerText = 'Add New Subject';
    document.getElementById('formAction').value = 'add';
    document.getElementById('subjectForm').reset();
    document.getElementById('edit_id').value = '';
    
    document.getElementById('scheduleContainer').innerHTML = '';
    addScheduleRow(); // Add one empty row by default
    
    document.getElementById('subjectModal').classList.add('show');
}

function openEditModal(id) {
    document.getElementById('modalTitle').innerText = 'Edit Subject';
    document.getElementById('formAction').value = 'edit';
    document.getElementById('edit_id').value = id;
    
    // Fill subject info
    const subject = subjectsData.find(s => s.id == id);
    if (subject) {
        document.getElementById('code').value = subject.code;
        document.getElementById('name').value = subject.name;
        document.getElementById('teacher').value = subject.teacher;
        document.getElementById('units').value = subject.units;
    }
    
    // Fill schedules
    const container = document.getElementById('scheduleContainer');
    container.innerHTML = '';
    
    const scheds = schedulesData[id] || [];
    if (scheds.length === 0) {
        addScheduleRow();
    } else {
        scheds.forEach(s => addScheduleRow(s));
    }
    
    document.getElementById('subjectModal').classList.add('show');
}

function addScheduleRow(data = null) {
    const template = document.getElementById('scheduleRowTemplate').innerHTML;
    const html = template.replace(/{INDEX}/g, scheduleIndex++);
    
    const div = document.createElement('div');
    div.innerHTML = html;
    const row = div.firstElementChild;
    
    if (data) {
        row.querySelector('[name$="[time_slot]"]').value = data.time_slot;
        row.querySelector('[name$="[days]"]').value = data.days;
        row.querySelector('[name$="[type]"]').value = data.type;
    }
    
    document.getElementById('scheduleContainer').appendChild(row);
}

function closeModal() {
    document.getElementById('subjectModal').classList.remove('show');
}

function deleteSubject(id, name) {
    Swal.fire({
        title: 'Are you sure?',
        text: 'You are about to delete "' + name + '". This action cannot be undone!',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#e74c3c',
        cancelButtonColor: '#34495e',
        confirmButtonText: 'Yes, delete it!'
    }).then((result) => {
        if (result.isConfirmed) {
            document.getElementById('deleteForm').elements['delete_id'].value = id;
            document.getElementById('deleteForm').submit();
        }
    });
}

document.getElementById('subjectModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeModal();
    }
});

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape' && document.getElementById('subjectModal').classList.contains('show')) {
        closeModal();
    }
});
</script>

<?php include 'footer.php'; ?>

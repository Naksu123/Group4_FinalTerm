<?php
require 'auth.php';

// ----------------------------------------------------------
// STUDENT INFORMATION
// ----------------------------------------------------------
$student = [
    'id'       => $_SESSION['user']['id'],
    'name'     => $_SESSION['user']['name'],
    'birthdate'=> '2004-08-31',
    'age'      => 21,
    'gender'   => 'Male',
    'address'  => 'NGO Village, Barangay Handumanan, Bacolod City',
    'email'    => $_SESSION['user']['username'] . '@school.edu.ph',
    'phone'    => '09852264879',
    'guardian' => 'Lemonss Hamada',
    'guardian_rel'     => 'Father',
    'guardian_contact' => '09297729791',
    'status'   => 'Active',
];

// ----------------------------------------------------------
// PERSONAL INFORMATION
// ----------------------------------------------------------
$personal = [
    'Full Name' => $student['name'],
    'Age'       => $student['age'] . ' years old',
    'Gender'    => $student['gender'],
    'Address'   => $student['address'],
    'Email'     => $student['email'],
    'Phone'     => $student['phone'],
];

// ----------------------------------------------------------
// GUARDIAN INFORMATION
// ----------------------------------------------------------
$guardian = [
    'Guardian Name' => $student['guardian'],
    'Relationship'  => $student['guardian_rel'],
    'Contact Number' => $student['guardian_contact'],
];

// ----------------------------------------------------------
// PAGE TITLES
// ----------------------------------------------------------
$active_page = 'profile';
$page_title = 'Student Profile';
$page_icon = '<i class="bi bi-person-fill"></i>';

// Include header
include 'header.php'; 
?>
<main class="content">
    <div class="profile-header table-card" style="margin-bottom: 24px;">
        <div class="profile-banner">
            <img src="../src/assets/images/hiro-avatar.png" alt="Avatar">
        </div>
        <div class="profile-info-header">
            <div>
                <div class="profile-name"><?= htmlspecialchars($student['name']) ?></div>
                <div class="profile-id"><?= htmlspecialchars($student['id']) ?></div>
                <span class="badge badge-active"><?= htmlspecialchars($student['status']) ?></span>
            </div>
        </div>
    </div>
     <div class="table-card" style="margin-bottom: 24px;">
        <div class="table-card-header">
            <div class="table-card-title">Personal Information</div>
        </div>
        <?php foreach ($personal as $label => $value): ?>
        <div class="info-row">
            <div class="info-row-label"><?= htmlspecialchars($label) ?></div>
            <div class="info-row-value"><?= htmlspecialchars($value) ?></div>
        </div>
        <?php endforeach; ?>
    </div>
    <div class="table-card" style="margin-bottom: 0;">
        <div class="table-card-header">
            <div class="table-card-title">Guardian Information</div>
        </div>
        <?php foreach ($guardian as $label => $value): ?>
        <div class="info-row">
            <div class="info-row-label"><?= htmlspecialchars($label) ?></div>
            <div class="info-row-value"><?= htmlspecialchars($value) ?></div>
        </div>
        <?php endforeach; ?>
    </div>
</main>
<?php include 'footer.php'; ?>
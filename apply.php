<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $required = ['name', 'email', 'phone', 'cover-letter'];
    foreach ($required as $field) {
        if (empty($_POST[$field])) {
            die("❌ Please fill in all required fields. <a href='jobs.html'>Go back</a>");
        }
    }

    if ($_FILES['resume']['error'] !== UPLOAD_ERR_OK) {
        die("❌ Error uploading resume. <a href='jobs.html'>Try again</a>");
    }

    $fileType = strtolower(pathinfo($_FILES['resume']['name'], PATHINFO_EXTENSION));
    if ($fileType !== 'pdf') {
        die("❌ Only PDF resumes are accepted. <a href='jobs.html'>Try again</a>");
    }

    if (!file_exists('applications')) {
        mkdir('applications', 0777, true);
    }

    $applicationData = [
        'job_id' => $_POST['job-id'],
        'name' => htmlspecialchars($_POST['name']),
        'email' => htmlspecialchars($_POST['email']),
        'phone' => htmlspecialchars($_POST['phone']),
        'cover_letter' => htmlspecialchars($_POST['cover-letter']),
        'resume' => $_FILES['resume']['name'],
        'date' => date('Y-m-d H:i:s')
    ];

    $newFilename = uniqid() . '.' . $fileType;
    move_uploaded_file($_FILES['resume']['tmp_name'], 'applications/' . $newFilename);
    $applicationData['resume_path'] = $newFilename;

    file_put_contents('applications.txt', json_encode($applicationData) . PHP_EOL, FILE_APPEND);

    header("Location: application-success.html");
    exit;
} else {
    header("Location: jobs.html");
    exit;
}
?>
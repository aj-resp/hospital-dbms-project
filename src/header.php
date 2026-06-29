<?php
session_start();
require_once 'database.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ivor Paine Memorial Hospital</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container">
    <div class="header">
        <h1>🏥 IVOR PAINE MEMORIAL HOSPITAL</h1>
        <h3>Database Management System</h3>
        <p>Computer Science Department - National University of Computing & Emerging Sciences</p>
    </div>
    
    <div class="navbar">
        <a href="index.php">🏠 Home</a>
        
        <div class="dropdown">
            <span class="dropbtn">📋 Forms ▼</span>
            <div class="dropdown-content">
                <a href="patient_record.php">👤 Patient Record</a>
                <a href="ward_record.php">🏥 Ward Record</a>
                <a href="consultant_team_record.php">👨‍⚕️ Consultant Team Record</a>
            </div>
        </div>
        
        <div class="dropdown">
            <span class="dropbtn">📊 Reports ▼</span>
            <div class="dropdown-content">
                <a href="consultants_doctors.php">1. Consultants & Their Doctors</a>
                <a href="wards_sisters_careunits.php">2. Wards with Sisters & Care Units</a>
                <a href="patients_complaints_treatments.php">3. Patients Complaints & Treatments</a>
                <a href="junior_houseman_patients.php">4. Junior Houseman & Their Patients</a>
                <a href="consultants_unique_specialty.php">5. Consultants with Unique Specialty</a>
                <a href="complaints_treatments_doctor_exp.php">6. Complaints Treatments & Doctor Exp</a>
                <a href="patients_multiple_complaints.php">7. Patients with Multiple Complaints</a>
                <a href="patients_by_treatment.php">8. Patients Grouped by Treatment</a>
                <a href="doctor_performance.php">9. Doctor Performance History</a>
                <a href="patient_medical_details.php">10. Full Medical Details</a>
                <a href="treatments_by_complaint_dates.php">11. Treatments by Date Range</a>
                <a href="staff_positions_count.php">12. Staff Positions Count</a>
            </div>
        </div>
    </div>
    
    <div class="main-content">
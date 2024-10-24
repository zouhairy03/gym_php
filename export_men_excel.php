<?php
// export_men_excel.php
session_start();
require 'config.php'; // Include the database configuration

// Check if the admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: index.php");
    exit();
}

// Include PhpSpreadsheet library
require 'vendor/autoload.php'; // This is the path for autoloading the PhpSpreadsheet via Composer

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

// Create a new spreadsheet object
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// Set document properties
$spreadsheet->getProperties()
    ->setCreator("Gym Management System")
    ->setLastModifiedBy("Gym Management System")
    ->setTitle("men Members Export")
    ->setSubject("men Members Export")
    ->setDescription("Export of men members from the database.")
    ->setKeywords("php spreadsheet export")
    ->setCategory("Export File");

// Add some headers
$sheet->setCellValue('A1', 'First Name')
      ->setCellValue('B1', 'Last Name')
      ->setCellValue('C1', 'Phone Number')
      ->setCellValue('D1', 'CNE')
      ->setCellValue('E1', 'Activity Status')
      ->setCellValue('F1', 'Insurance Status')
      ->setCellValue('G1', 'Membership Status')
      ->setCellValue('H1', 'Created At');

// Fetch men members from the database
$stmt = $conn->prepare("SELECT first_name, last_name, phone_number, CNE, activity_status, insurance_status, membership_status, created_at FROM members WHERE gender = 'male'");
$stmt->execute();
$result = $stmt->get_result();

// Populate data into the spreadsheet
$rowNumber = 2; // Start from the second row
while ($row = $result->fetch_assoc()) {
    $sheet->setCellValue('A' . $rowNumber, $row['first_name'])
          ->setCellValue('B' . $rowNumber, $row['last_name'])
          ->setCellValue('C' . $rowNumber, $row['phone_number'])
          ->setCellValue('D' . $rowNumber, $row['CNE'])
          ->setCellValue('E' . $rowNumber, $row['activity_status'])
          ->setCellValue('F' . $rowNumber, $row['insurance_status'])
          ->setCellValue('G' . $rowNumber, $row['membership_status'])
          ->setCellValue('H' . $rowNumber, $row['created_at']);
    $rowNumber++;
}

// Rename sheet
$sheet->setTitle('men Members');

// Set active sheet index to the first sheet
$spreadsheet->setActiveSheetIndex(0);

// Redirect output to a client’s web browser (Excel2007)
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="men_members.xlsx"');
header('Cache-Control: max-age=0');

// Save Excel file to output
$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
?>

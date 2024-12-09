<?php
/*
Plugin Name: Form PDF Generator
Description: Generate PDFs from form submissions
Version: 0.1
Author: Marco Gergele
*/

// Prevent direct access
if (!defined('ABSPATH')) exit;

// Ensure Composer autoload is included
require_once plugin_dir_path(__FILE__) . 'vendor/autoload.php';

// Include additional functions
require_once plugin_dir_path(__FILE__) . 'includes/pdf-generator-functions.php';

// Hook into form submission (example with WPForms)
add_action('wpforms_process', 'my_generate_pdf_on_form_submission', 10, 3);

// Add admin menu
add_action('admin_menu', 'my_pdf_generator_admin_menu');

// Activation hook
register_activation_hook(__FILE__, 'my_pdf_generator_activate');
use setasign\Fpdi\Fpdi;

function my_pdf_generator_activate() {
    // Create upload directory for PDFs if not exists
    $upload_dir = wp_upload_dir();
    $pdf_dir = $upload_dir['basedir'] . '/generated-pdfs';
    if (!file_exists($pdf_dir)) {
        wp_mkdir_p($pdf_dir);
    }
}

// Add admin page
function my_pdf_generator_admin_menu() {
    add_menu_page(
        'PDF Generator Test',      // Page title
        'PDF Generator',           // Menu title
        'manage_options',          // Capability
        'pdf-generator-test',      // Menu slug
        'my_pdf_generator_test_page' // Callback function
    );
}

// Admin page content
function my_pdf_generator_test_page() {
    ?>
    <div class="wrap">
        <h1>PDF Generator Test</h1>
        <form method="post" action="">
            <?php
            // Add nonce for security
            wp_nonce_field('pdf_generator_test', 'pdf_generator_nonce');
            ?>
            <input type="submit" name="generate_test_pdf" class="button button-primary" value="Generate Test PDF">
        </form>
        <?php
        // Check if PDF was generated
        if (isset($_POST['generate_test_pdf']) && 
            check_admin_referer('pdf_generator_test', 'pdf_generator_nonce')) {
            $pdf_path = my_generate_test_pdf();
            if ($pdf_path) {
                echo '<p>Test PDF Generated: ' . esc_html($pdf_path) . '</p>';
            }
        }
        ?>
    </div>
    <?php
}

// Add this to your existing functions file or in the main plugin file
function my_generate_test_pdf() {

    // Prepare upload directory
    $upload_dir = wp_upload_dir();
    $pdf_dir = $upload_dir['basedir'] . '/generated-pdfs';
    
    // Ensure directory exists
    if (!file_exists($pdf_dir)) {
        wp_mkdir_p($pdf_dir);
    }

    try {
        // Create PDF
        $pdf = new Fpdi();
        $pdf->AddPage();
        
        // Load template (adjust path as needed)
        $templatePath = plugin_dir_path(__FILE__) . 'templates/your-template.pdf';
        
        // Check if template exists
        if (!file_exists($templatePath)) {
            // Create a blank template if none exists
            $tempPdf = new Fpdi();
            $tempPdf->AddPage();
            $tempPdf->SetFont('Arial', 'B', 16);
            $tempPdf->Cell(0, 10, 'Blank Template', 0, 1);
            $tempPdf->Output($templatePath, 'F');
        }

        $templateId = $pdf->setSourceFile($templatePath);
        $page = $pdf->importPage(1);
        $pdf->useTemplate($page);
        
        // Add test text
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->SetXY(50, 50);
        $pdf->Write(0, 'Test PDF Generated on ' . date('Y-m-d H:i:s'));
        
        // Generate unique filename
        $filename = 'test-pdf-' . uniqid() . '.pdf';
        $filepath = $pdf_dir . '/' . $filename;
        
        // Save PDF
        $pdf->Output($filepath, 'F');
        $pdf->Close();

        // Return relative path for display
        $upload_dir = wp_upload_dir();
        return str_replace($upload_dir['basedir'], $upload_dir['baseurl'], $filepath);
    } catch (Exception $e) {
        // Log and display error
        error_log('Test PDF Generation Error: ' . $e->getMessage());
        echo '<div class="error"><p>PDF Generation Failed: ' . esc_html($e->getMessage()) . '</p></div>';
        return false;
    }
}

function dummytst(){
  // Create PDF
  $pdf = new Fpdi();
  $pdf->AddPage();

  // Load template (adjust path as needed)
  $templatePath = plugin_dir_path(__FILE__) . 'templates/your-template.pdf';

  // Check if template exists
  if (!file_exists($templatePath)) {
    // Create a blank template if none exists
    $tempPdf = new Fpdi();
    $tempPdf->AddPage();
    $tempPdf->SetFont('Arial', 'B', 16);
    $tempPdf->Cell(0, 10, 'Blank Template', 0, 1);
    $tempPdf->Output('generated.pdf', 'F');
  }

  $templateId = $pdf->setSourceFile($templatePath);
  $page = $pdf->importPage(1);
  $pdf->useTemplate($page);

  // Add test text
  $pdf->SetFont('Arial', 'B', 12);
  $pdf->SetXY(50, 50);
  $pdf->Write(0, 'Test PDF Generated on ' . date('Y-m-d H:i:s'));

  // Generate unique filename
  $filename = 'test-pdf-1.pdf';
  $filepath = $filename;

  // Save PDF
  $pdf->Output($filepath, 'F');
  $pdf->Close();
}

dummytst();

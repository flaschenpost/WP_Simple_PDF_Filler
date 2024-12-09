<?php
use setasign\Fpdi\Fpdi;

function my_generate_pdf_on_form_submission($fields, $entry, $form_data) {
    // Prepare upload directory
    $upload_dir = wp_upload_dir();
    $pdf_dir = $upload_dir['basedir'] . '/generated-pdfs';
    
    // Create PDF
    $pdf = new Fpdi();
    $pdf->AddPage();
    
    // Load template (adjust path as needed)
    $templatePath = plugin_dir_path(__FILE__) . '../templates/your-template.pdf';
    $templateId = $pdf->setSourceFile($templatePath);
    $page = $pdf->importPage(1);
    $pdf->useTemplate($page);
    
    // Add dynamic text
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->SetXY(50, 50);
    $pdf->Write(0, $fields['name']['value']); // Example of adding form field
    
    // Generate unique filename
    $filename = 'pdf-' . uniqid() . '.pdf';
    $filepath = $pdf_dir . '/' . $filename;
    
    // Save PDF
    $pdf->Output($filepath, 'F');
    $pdf->Close();
    
    // Optional: Save PDF URL to entry meta or send email
    // You might want to add the PDF URL to entry meta or email
    do_action('my_pdf_generated', $filepath, $entry);
}

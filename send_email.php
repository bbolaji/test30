<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitize and validate input data
    $name = htmlspecialchars(strip_tags(trim($_POST["name"])));
    $email = filter_var($_POST["email"], FILTER_SANITIZE_EMAIL);
    $message = htmlspecialchars(strip_tags(trim($_POST["message"])));

    if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
        // Email details
        $to = "your-email@example.com";  // Replace with your email address
        $subject = "New Contact Form Submission from $name";
        $body = "Name: $name\nEmail: $email\n\nMessage:\n$message";
        $headers = "From: $email";

        // File upload handling
        $file_attached = false;
        if (isset($_FILES['file']) && $_FILES['file']['error'] == UPLOAD_ERR_OK) {
            $fileTmpPath = $_FILES['file']['tmp_name'];
            $fileName = $_FILES['file']['name'];
            $fileSize = $_FILES['file']['size'];
            $fileType = $_FILES['file']['type'];
            $fileNameCmps = explode(".", $fileName);
            $fileExtension = strtolower(end($fileNameCmps));

            $allowedfileExtensions = array('jpg', 'gif', 'png', 'txt', 'xls', 'xlsx', 'doc', 'docx', 'pdf');
            if (in_array($fileExtension, $allowedfileExtensions)) {
                $uploadFileDir = './uploaded_files/';
                if (!is_dir($uploadFileDir)) {
                    mkdir($uploadFileDir, 0755, true);
                }
                $dest_path = $uploadFileDir . $fileName;

                if(move_uploaded_file($fileTmpPath, $dest_path)) {
                    $file_attached = true;
                }
            }
        }

        // Email sending
        $boundary = md5("sanwebe");
        $headers .= "\r\nMIME-Version: 1.0\r\nContent-Type: multipart/mixed; boundary=\"".$boundary."\"";
        
        $body = "--$boundary\r\n";
        $body .= "Content-Type: text/plain; charset=ISO-8859-1\r\n";
        $body .= "Content-Transfer-Encoding: base64\r\n\r\n"; 
        $body .= chunk_split(base64_encode("Name: $name\nEmail: $email\n\nMessage:\n$message")); 

        if($file_attached) {
            $body .= "--$boundary\r\n";
            $body .= "Content-Type: $fileType; name=\"".$fileName."\"\r\n";
            $body .= "Content-Disposition: attachment; filename=\"".$fileName."\"\r\n";
            $body .= "Content-Transfer-Encoding: base64\r\n";
            $body .= "X-Attachment-Id: ".rand(1000, 99999)."\r\n\r\n";
            $body .= chunk_split(base64_encode(file_get_contents($dest_path))); 
        }

        $body .= "--$boundary--";

        if (mail($to, $subject, $body, $headers)) {
            echo "Email successfully sent!";
        } else {
            echo "Failed to send email.";
        }
    } else {
        echo "Invalid email address.";
    }
} else {
    echo "Invalid request method.";
}
?>

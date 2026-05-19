<?php
// process_review.php

// 1. Set headers to allow JSON requests
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// 2. Only process POST requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // 3. Get the JSON payload from the frontend
    $data = json_decode(file_get_contents("php://input"));

    // 4. Validate the incoming data
    if (!empty($data->rating) && !empty($data->products) && !empty($data->title) && !empty($data->content)) {
        
        $to = "chongshaokai1999@gmail.com";
        $subject = "New Product Review: " . htmlspecialchars($data->title);
        
        // Convert products array to a comma-separated string
        $productsList = implode(", ", $data->products);
        $dateSubmitted = date('Y-m-d H:i:s', strtotime($data->date));

        // 5. Construct the email body
        $message = "
        <html>
        <head>
        <title>New Product Review - DermDefine</title>
        </head>
        <body>
        <h2>New Review Submitted</h2>
        <p><strong>Date:</strong> {$dateSubmitted}</p>
        <p><strong>Rating:</strong> {$data->rating} / 5 Stars</p>
        <p><strong>Products Reviewed:</strong> {$productsList}</p>
        <hr>
        <p><strong>Title:</strong> " . htmlspecialchars($data->title) . "</p>
        <p><strong>Review Content:</strong><br>" . nl2br(htmlspecialchars($data->content)) . "</p>
        </body>
        </html>
        ";

        // 6. Set content-type headers for HTML email
        $headers = "MIME-Version: 1.0" . "\r\n";
        $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
        $headers .= "From: DermDefine Website <noreply@dermdefine.com>" . "\r\n";
        
        // 7. Send the email
        if(mail($to, $subject, $message, $headers)) {
            http_response_code(200);
            echo json_encode(["message" => "Review sent successfully."]);
        } else {
            http_response_code(500);
            echo json_encode(["message" => "Unable to send email. Server configuration issue."]);
        }
    } else {
        http_response_code(400);
        echo json_encode(["message" => "Incomplete data. Please fill all fields."]);
    }
} else {
    http_response_code(405);
    echo json_encode(["message" => "Method not allowed."]);
}
?>
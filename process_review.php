<?php
// process_review.php

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    $data = json_decode(file_get_contents("php://input"));

    // --- HONEYPOT BOT CHECK ---
    // If the invisible field is filled, it's a bot.
    if (!empty($data->bot_check)) {
        // Silently fake a success response so the bot leaves us alone
        http_response_code(200);
        echo json_encode(["message" => "Review sent successfully."]);
        exit; // Stop executing, do not send email
    }
    // --------------------------

    if (!empty($data->rating) && !empty($data->products) && !empty($data->title) && !empty($data->content)) {
        
        $to = "chongshaokai1999@gmail.com";
        $subject = "New Product Review: " . htmlspecialchars($data->title);
        
        $productsList = implode(", ", $data->products);
        $dateSubmitted = date('Y-m-d H:i:s', strtotime($data->date));

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

        $headers = "MIME-Version: 1.0" . "\r\n";
        $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
        $headers .= "From: DermDefine Website <noreply@dermdefine.com>" . "\r\n";
        
        // Use error suppressor (@) just in case mail fails and outputs plain text warnings
        if(@mail($to, $subject, $message, $headers)) {
            http_response_code(200);
            echo json_encode(["message" => "Review sent successfully."]);
        } else {
            http_response_code(500);
            echo json_encode(["message" => "Unable to send email. Check your server's mail configuration."]);
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
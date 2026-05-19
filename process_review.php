<?php
// process_review.php

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    $data = json_decode(file_get_contents("php://input"));

    if (!empty($data->bot_check)) {
        http_response_code(200);
        echo json_encode(["message" => "Review sent successfully."]);
        exit; 
    }

    if (!empty($data->rating) && !empty($data->products) && !empty($data->title) && !empty($data->content)) {
        
        $to = "marketing@dermdefine.com"; 
        $subject = "New Product Review: " . htmlspecialchars($data->title);
        
        $productsList = implode(", ", $data->products);
        
        // Convert UTC to GMT+8 (Malaysia Time)
        $date = new DateTime($data->date);
        $date->setTimezone(new DateTimeZone('Asia/Kuala_Lumpur'));
        $dateSubmitted = $date->format('d M Y, h:i A'); // e.g., 19 May 2026, 02:30 PM

        // Generate Star String (★ for filled, ☆ for empty)
        $ratingNum = (int)$data->rating;
        $stars = str_repeat('&#9733;', $ratingNum) . str_repeat('&#9734;', 5 - $ratingNum);

        // Styled HTML Email matching DermDefine Branding
        $message = "
        <html>
        <head>
        <title>New Product Review - DermDefine</title>
        </head>
        <body style='font-family: Helvetica, Arial, sans-serif; background-color: #F5F0EB; padding: 30px 10px; margin: 0;'>
            
            <div style='max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 15px rgba(0,0,0,0.05); border: 1px solid #e5dbcf;'>
                
                <div style='background-color: #8A4A4A; padding: 25px; text-align: center;'>
                    <h2 style='color: #F2D27F; margin: 0; text-transform: uppercase; letter-spacing: 2px; font-weight: 600;'>New Review Submitted</h2>
                </div>
                
                <div style='padding: 30px; color: #4A4A4A;'>
                    <table style='width: 100%; border-collapse: collapse; margin-bottom: 20px;'>
                        <tr>
                            <td style='padding: 8px 0; width: 140px;'><strong>Date:</strong></td>
                            <td style='padding: 8px 0;'>{$dateSubmitted}</td>
                        </tr>
                        <tr>
                            <td style='padding: 8px 0;'><strong>Rating:</strong></td>
                            <td style='padding: 8px 0;'><span style='color: #fbbf24; font-size: 20px; letter-spacing: 2px;'>{$stars}</span> <span style='font-size: 14px; color: #888;'>({$ratingNum}/5)</span></td>
                        </tr>
                        <tr>
                            <td style='padding: 8px 0;'><strong>Product(s):</strong></td>
                            <td style='padding: 8px 0;'>{$productsList}</td>
                        </tr>
                    </table>
                    
                    <hr style='border: none; border-top: 1px solid #f0e6d8; margin: 25px 0;'>
                    
                    <h3 style='color: #B86B6B; margin-top: 0; margin-bottom: 12px; font-size: 18px;'>" . htmlspecialchars($data->title) . "</h3>
                    <div style='background-color: #F5F0EB; padding: 20px; border-radius: 8px; line-height: 1.6; color: #5C3A3A;'>
                        " . nl2br(htmlspecialchars($data->content)) . "
                    </div>
                </div>
                
                <div style='background-color: #5C3A3A; color: #ffffff; padding: 15px; text-align: center; font-size: 12px; opacity: 0.9;'>
                    &copy; " . date('Y') . " DermDefine System. Auto-generated message.
                </div>

            </div>

        </body>
        </html>
        ";

        $headers = "MIME-Version: 1.0" . "\r\n";
        $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
        $headers .= "From: DermDefine Website <noreply@dermdefine.com>" . "\r\n";
        
        if(@mail($to, $subject, $message, $headers)) {
            http_response_code(200);
            echo json_encode(["message" => "Review sent successfully."]);
        } else {
            http_response_code(500);
            echo json_encode(["message" => "Unable to send email."]);
        }
    } else {
        http_response_code(400);
        echo json_encode(["message" => "Incomplete data."]);
    }
} else {
    http_response_code(405);
    echo json_encode(["message" => "Method not allowed."]);
}
?>
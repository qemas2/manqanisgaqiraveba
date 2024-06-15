<?php

// ფუნქცია ელ. ფოსტის მისამართის ვალიდაციაში
function validateEmail($email)
{
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

// ფუნქცია შეყვანილი მონაცემების სანიშნეების გასასუფთავებლად
function sanitizeInput($input)
{
    return htmlspecialchars(trim($input));
}

// ფუნქცია სათაურისა და წერილის ველების ვალიდაციაში
function validateSubjectMessage($subject, $message)
{
    if (empty($subject) || empty($message)) {
        return false;
    }
    return true;
}

// ფუნქცია მაქსიმალური სიგრძის ვალიდაციაში
function validateMaxLength($input, $maxLength)
{
    return strlen($input) <= $maxLength;
}

// თუ მოთხოვნილი მეთოდი POST-ია
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // შეამოწმებს თუ ყველა საჭირო POST პარამეტრი გამოქვეყნდა
    if (isset($_POST['email']) && isset($_POST['subject']) && isset($_POST['message'])) {

        // დააკლების ჰედერებს რესპონსის ფორმატისათვის
        header('Content-Type: application/json');

        // მიიღებს და სანიშნებისამებლად გააუქმებს POST მონაცემებს
        $to = sanitizeInput($_POST['email']);
        $subject = sanitizeInput($_POST['subject']);
        $message = sanitizeInput($_POST['message']);

        // ვალიდაციაში გადასამოწმებლად
        if (!validateEmail($to)) {
            $response = array(
                'message' => 'არასწორი ელ. ფოსტა',
                'status' => false
            );
            echo json_encode($response);
            exit();
        }

        // ვალიდაციაში გადასამოწმებლად
        if (!validateSubjectMessage($subject, $message)) {
            $response = array(
                'message' => 'თემა და შეტყობინება არ შეიცავს ცარიელ ველებს',
                'status' => false
            );
            echo json_encode($response);
            exit();
        }

        // ვალიდაციაში გადასამოწმებლად
        $maxLengthSubject = 100; // თემის მაქსიმალური სიგრძე
        $maxLengthMessage = 500; // შეტყობინების მაქსიმალური სიგრძე

        if (!validateMaxLength($subject, $maxLengthSubject) || !validateMaxLength($message, $maxLengthMessage)) {
            $response = array(
                'message' => 'თემა ან შეტყობინება გადააწყვეტინებს მაქსიმალურ სიგრძეს',
                'status' => false
            );
            echo json_encode($response);
            exit();
        }

        // იგზავნებს ელ. ფოსტას
        $result = mail($to, $subject, $message);

        // JSON პასუხის მომზადება
        $response = array(
            'message' => 'ელ. ფოსტა წარმატებით გაიგზავნა',
            'status' => true
        );

        // აბრუნებს JSON პასუხს
        echo json_encode($response);
    } else {
        // შეცდომის მოსმენა თუ ყველა საჭირო პარამეტრი არ გამოქვეყნდა
        http_response_code(400); // შეცდომა

        $response = array(
            'message' => 'პარამეტრები არ არის დამტკიცებული',
            'status' => false
        );

        // აბრუნებს JSON პასუხს
        echo json_encode($response);
    }
} else {
    // შეცდომის მოსმენა თუ მოთხოვნილი მეთოდი არ არის POST
    http_response_code(405); // არასწორი მეთოდი
    echo json_encode(array('error' => 'მხარდამჭერი მეთოდი მხარდამჭერობის შეზღუდვა'));
}

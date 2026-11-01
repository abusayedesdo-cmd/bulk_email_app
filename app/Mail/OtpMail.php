<?php
// app/Mail/OtpMail.php  (আগের ফাইল রিপ্লেস করুন)

namespace App\Mail;

use Illuminate\Mail\Mailable;

class OtpMail extends Mailable
{
    // ShouldQueue ইচ্ছা করেই নেই: সাথে সাথে যাওয়া দরকার, বাল্ক কিউয়ের পেছনে আটকে থাকলে চলবে না।
    public function __construct(
        public string $email,
        public string $username,
        public string $password,
    ) {
    }

    public function build()
    {
        // সব মান e() দিয়ে escape করা, যাতে কেউ প্যারামিটারে HTML/লিংক ঢোকাতে না পারে
        $email    = e($this->email);
        $username = e($this->username);
        $password = e($this->password);

        $html = <<<HTML
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<title>career.esdo.net.bd</title>
</head>
<body>
<table width="750" cellspacing="5" cellpadding="5">
<tr>
<td><font face="tahoma" size="2" color="#000000">
Dear Applicant,<br><br>Your User Access for ESDO eRecruitment System is given below:<br><br>
Web Link/URL: <a href="https://esdo.net.bd/career"><strong>esdo.net.bd/career/</strong></a><br>
Registered Email: {$email}<br>
Username: {$username}<br>
Password: {$password}<br><br>
Please remember your Email, Username and Password for further reference.<br>
You are kindly requested to complete your profile using above mentioned User Access.<br>
Please <a href="https://esdo.net.bd/career/resource.center/career@ESDO%20User%20Manual.pdf">Click Here</a> for User Manual.<br><br>
Thanks and Regards,<br>Human Resources Department<br>Eco-Social Development Organization (ESDO)<br><br><br>
This is an automated message, replies to this address are not monitored.</font></td>
</tr>
</table>
</body>
</html>
HTML;

        return $this->subject('ESDO eRecruitment System - User Access')->html($html);
    }
}
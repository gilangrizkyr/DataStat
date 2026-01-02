<?php

namespace App\Libraries;

class MailtrapEmail
{
    public function send(array $data): bool
    {
        try {
            // Create emails directory if it doesn't exist
            $emailsDir = WRITEPATH . 'emails';
            if (!is_dir($emailsDir)) {
                mkdir($emailsDir, 0755, true);
            }

            // Generate unique filename
            $timestamp = date('Y-m-d_H-i-s');
            $recipient = str_replace(['@', '.'], ['_', '_'], $data['to'][0]['email']);
            $filename = "email_{$recipient}_{$timestamp}.eml";
            $filepath = $emailsDir . DIRECTORY_SEPARATOR . $filename;

            // Format email content
            $emailContent = $this->formatEmailForFile($data);

            // Save email to file
            if (file_put_contents($filepath, $emailContent) !== false) {
                log_message('info', 'Email logged successfully to file: ' . $filename);
                return true;
            } else {
                log_message('error', 'Failed to save email to file: ' . $filename);
                return false;
            }
        } catch (\Exception $e) {
            log_message('error', 'Exception logging email to file: ' . $e->getMessage());
            return false;
        }
    }

    public function sendInvitationEmail(array $user, string $roleName, string $appName, ?string $message = null): bool
    {
        $emailData = [
            'from' => [
                'email' => 'noreply@datastat.com',
                'name' => 'DataStat Application'
            ],
            'to' => [
                [
                    'email' => $user['email']
                ]
            ],
            'subject' => 'Invitation to join ' . $appName . ' workspace',
            'html' => $this->getInvitationHtml($user, $roleName, $appName, $message),
            'category' => 'Invitation'
        ];

        return $this->send($emailData);
    }

    private function formatEmailForFile(array $data): string
    {
        $timestamp = date('Y-m-d H:i:s');
        $boundary = '----=_NextPart_' . md5(uniqid());

        $content = "Date: {$timestamp}\n";
        $content .= "From: \"{$data['from']['name']}\" <{$data['from']['email']}>\n";
        $content .= "To: {$data['to'][0]['email']}\n";
        $content .= "Subject: {$data['subject']}\n";
        $content .= "MIME-Version: 1.0\n";
        $content .= "Content-Type: multipart/alternative; boundary=\"{$boundary}\"\n\n";

        $content .= "This is a multi-part message in MIME format.\n\n";

        // Plain text part
        $content .= "--{$boundary}\n";
        $content .= "Content-Type: text/plain; charset=UTF-8\n";
        $content .= "Content-Transfer-Encoding: 8bit\n\n";
        $content .= strip_tags($data['html']) . "\n\n";

        // HTML part
        $content .= "--{$boundary}\n";
        $content .= "Content-Type: text/html; charset=UTF-8\n";
        $content .= "Content-Transfer-Encoding: quoted-printable\n\n";
        $content .= quoted_printable_encode($data['html']) . "\n\n";

        $content .= "--{$boundary}--\n";

        return $content;
    }

    private function getInvitationHtml(array $user, string $roleName, string $appName, ?string $message = null): string
    {
        $permissions = $roleName === 'owner' ?
            '<li>Full access to all data and features</li><li>Manage workspace users</li><li>Create and edit dashboards</li><li>Manage statistics and datasets</li>' :
            '<li>View all data and dashboards</li><li>Access statistics and reports</li>';

        return "
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background-color: #007bff; color: white; padding: 20px; text-align: center; }
                .content { padding: 20px; background-color: #f8f9fa; }
                .footer { background-color: #e9ecef; padding: 10px; text-align: center; font-size: 12px; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>Welcome to {$appName}</h1>
                </div>
                <div class='content'>
                    <h2>Hello {$user['nama_lengkap']},</h2>
                    <p>You have been invited to join the <strong>{$appName}</strong> workspace as a <strong>{$roleName}</strong>.</p>
                    <p>Your account has been set up and you now have access to the workspace. You can log in using your existing credentials.</p>
                    " . ($message ? "<p><strong>Personal message from the workspace owner:</strong><br>{$message}</p>" : "") . "
                    <p>As a {$roleName}, you will have the following permissions:</p>
                    <ul>{$permissions}</ul>
                    <p>If you have any questions, please contact the workspace administrator.</p>
                    <p>Welcome aboard!</p>
                </div>
                <div class='footer'>
                    <p>This email was sent by DataStat Application</p>
                </div>
            </div>
        </body>
        </html>";
    }
}
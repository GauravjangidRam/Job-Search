<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Application Received</title>
</head>
<body style="margin: 0; padding: 0; background-color: #f4f4f7; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background-color: #f4f4f7; padding: 40px 0;">
        <tr>
            <td align="center">
                <table role="presentation" width="600" cellspacing="0" cellpadding="0" style="background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                    <tr>
                        <td style="padding: 40px 48px;">
                            <h1 style="margin: 0 0 16px; font-size: 24px; font-weight: 600; color: #1a1a2e; text-align: center;">New Application Received</h1>
                            <p style="margin: 0 0 24px; font-size: 16px; color: #4a4a68; line-height: 1.5;">You have received a new application for one of your job listings.</p>
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="margin: 0 0 32px; background-color: #f0f4ff; border-radius: 8px;">
                                <tr>
                                    <td style="padding: 20px 24px;">
                                        <p style="margin: 0 0 12px; font-size: 14px; color: #6b7280;">Applicant</p>
                                        <p style="margin: 0 0 20px; font-size: 18px; font-weight: 600; color: #1a1a2e;">{{ $applicantName }}</p>
                                        <p style="margin: 0 0 12px; font-size: 14px; color: #6b7280;">Job Title</p>
                                        <p style="margin: 0; font-size: 18px; font-weight: 600; color: #1a1a2e;">{{ $jobTitle }}</p>
                                    </td>
                                </tr>
                            </table>
                            <div style="text-align: center;">
                                <a href="{{ $viewApplicationUrl }}" style="display: inline-block; padding: 14px 32px; background-color: #1a1a2e; color: #ffffff; font-size: 16px; font-weight: 600; text-decoration: none; border-radius: 8px;">View Application</a>
                            </div>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>

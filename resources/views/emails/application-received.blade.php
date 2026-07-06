<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Application Received</title>
</head>
<body style="margin: 0; padding: 0; background-color: #f3f4f6; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background-color: #f3f4f6; padding: 48px 0;">
        <tr>
            <td align="center">
                <table role="presentation" width="480" cellspacing="0" cellpadding="0" style="max-width: 480px; width: 100%; background-color: #ffffff; border: 1px solid #e5e7eb; border-radius: 12px;">
                    <tr>
                        <td style="padding: 40px 40px 32px;">

                            @include('emails.partials.header')

                            <h1 style="margin: 0 0 8px; font-size: 20px; font-weight: 600; color: #111827;">New application received</h1>
                            <p style="margin: 0 0 24px; font-size: 14px; color: #6b7280; line-height: 1.6;">
                                Someone just applied to one of your job listings.
                            </p>

                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="border: 1px solid #e5e7eb; border-radius: 8px;">
                                <tr>
                                    <td style="padding: 16px 20px; border-bottom: 1px solid #e5e7eb;">
                                        <p style="margin: 0 0 2px; font-size: 12px; color: #9ca3af;">Applicant</p>
                                        <p style="margin: 0; font-size: 14px; font-weight: 600; color: #111827;">{{ $applicantName }}</p>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding: 16px 20px;">
                                        <p style="margin: 0 0 2px; font-size: 12px; color: #9ca3af;">Position</p>
                                        <p style="margin: 0; font-size: 14px; font-weight: 600; color: #111827;">{{ $jobTitle }}</p>
                                    </td>
                                </tr>
                            </table>

                            <table role="presentation" cellspacing="0" cellpadding="0" style="margin: 28px 0 0;">
                                <tr>
                                    <td style="border-radius: 8px; background-color: #111827;">
                                        <a href="{{ $viewApplicationUrl }}" style="display: inline-block; padding: 11px 20px; font-size: 14px; font-weight: 600; color: #ffffff; text-decoration: none;">View application</a>
                                    </td>
                                </tr>
                            </table>

                            <div style="margin: 32px 0 0;">
                                @include('emails.partials.footer')
                            </div>

                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>

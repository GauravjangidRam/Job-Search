<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Company Verification Update</title>
</head>
<body style="margin: 0; padding: 0; background-color: #f3f4f6; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background-color: #f3f4f6; padding: 48px 0;">
        <tr>
            <td align="center">
                <table role="presentation" width="480" cellspacing="0" cellpadding="0" style="max-width: 480px; width: 100%; background-color: #ffffff; border: 1px solid #e5e7eb; border-radius: 12px;">
                    <tr>
                        <td style="padding: 40px 40px 32px;">

                            @include('emails.partials.header')

                            <table role="presentation" cellspacing="0" cellpadding="0" style="margin: 0 0 16px;">
                                <tr>
                                    <td style="background-color: #fef2f2; color: #dc2626; font-size: 12px; font-weight: 600; letter-spacing: 0.3px; text-transform: uppercase; padding: 4px 10px; border-radius: 999px;">Not approved</td>
                                </tr>
                            </table>

                            <h1 style="margin: 0 0 8px; font-size: 20px; font-weight: 600; color: #111827;">{{ $companyName }} wasn't approved</h1>
                            <p style="margin: 0 0 20px; font-size: 14px; color: #6b7280; line-height: 1.6;">
                                We reviewed your company profile and couldn't approve it this time.
                            </p>

                            @if($reason)
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="margin: 0 0 24px; border: 1px solid #e5e7eb; border-radius: 8px;">
                                <tr>
                                    <td style="padding: 14px 18px;">
                                        <p style="margin: 0 0 4px; font-size: 12px; color: #9ca3af;">Reason</p>
                                        <p style="margin: 0; font-size: 14px; color: #374151; line-height: 1.5;">{{ $reason }}</p>
                                    </td>
                                </tr>
                            </table>
                            @endif

                            <p style="margin: 0 0 24px; font-size: 14px; color: #6b7280; line-height: 1.6;">
                                Update your company profile to address this and we'll take another look.
                            </p>

                            <table role="presentation" cellspacing="0" cellpadding="0">
                                <tr>
                                    <td style="border-radius: 8px; background-color: #111827;">
                                        <a href="{{ $companyEditUrl }}" style="display: inline-block; padding: 11px 20px; font-size: 14px; font-weight: 600; color: #ffffff; text-decoration: none;">Update company profile</a>
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

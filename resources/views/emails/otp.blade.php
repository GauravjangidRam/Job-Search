<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Job Hub Verification Code</title>
</head>
<body style="margin: 0; padding: 0; background-color: #f4f4f7; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background-color: #f4f4f7; padding: 40px 0;">
        <tr>
            <td align="center">
                <table role="presentation" width="600" cellspacing="0" cellpadding="0" style="max-width: 600px; width: 100%;">
                    {{-- Header / Brand --}} 
                    <tr>
                        <td align="center" style="padding: 0 0 24px 0;">
                            <table role="presentation" cellspacing="0" cellpadding="0">
                                <tr>
                                    <td style="background-color: #ea580c; border-radius: 12px; padding: 12px 24px;">
                                        <span style="font-size: 22px; font-weight: 800; color: #ffffff; letter-spacing: -0.5px;">
                                            Job Hub
                                        </span>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    {{-- Main Card --}}
                    <tr>
                        <td style="background-color: #ffffff; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 16px rgba(0,0,0,0.08);">

                            {{-- Orange Top Bar --}}
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0">
                                <tr>
                                    <td style="background-color: #ea580c; height: 6px; font-size: 0; line-height: 0;">&nbsp;</td>
                                </tr>
                            </table> 

                            {{-- Body --}}
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0">
                                <tr>
                                    <td style="padding: 48px 48px 40px;">

                                        {{-- Icon --}}
                                        <table role="presentation" cellspacing="0" cellpadding="0" style="margin: 0 auto 24px;">
                                            <tr>
                                                <td style="background-color: #fff7ed; border-radius: 50%; width: 64px; height: 64px; text-align: center; vertical-align: middle;">
                                                    <span style="font-size: 30px; line-height: 64px;">🔐</span>
                                                </td>
                                            </tr>
                                        </table>
                                        {{-- Title --}}
                                        <h1 style="margin: 0 0 12px; font-size: 26px; font-weight: 700; color: #111827; text-align: center; letter-spacing: -0.5px;">
                                            Verify Your Email
                                        </h1>

                                        <p style="margin: 0 0 32px; font-size: 15px; color: #6b7280; line-height: 1.6; text-align: center;">
                                            Use the code below to verify your Job Hub account.<br>
                                            This code expires in <strong style="color: #ea580c;">10 minutes</strong>.
                                        </p>

                                        {{-- OTP Box --}}
                                        <table role="presentation" cellspacing="0" cellpadding="0" style="margin: 0 auto 32px;">
                                            <tr>
                                                <td style="background: linear-gradient(135deg, #fff7ed 0%, #ffedd5 100%); border: 2px solid #ea580c; border-radius: 12px; padding: 24px 48px; text-align: center;">
                                                    <p style="margin: 0 0 4px; font-size: 11px; font-weight: 600; color: #ea580c; letter-spacing: 2px; text-transform: uppercase;">Your OTP Code</p>
                                                    <span style="font-size: 42px; font-weight: 800; letter-spacing: 12px; color: #111827; font-family: 'Courier New', monospace;">{{ $otp }}</span>
                                                </td>
                                            </tr>
                                        </table>

                                        {{-- Security Warning --}}
                                        <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="margin: 0 0 32px;">
                                            <tr>
                                                <td style="background-color: #fef3c7; border-left: 4px solid #f59e0b; border-radius: 0 8px 8px 0; padding: 12px 16px;">
                                                    <p style="margin: 0; font-size: 13px; color: #92400e; line-height: 1.5;">
                                                        ⚠️ <strong>Never share this code</strong> with anyone. Job Hub will never ask for your OTP via phone or chat.
                                                    </p>
                                                </td>
                                            </tr>
                                        </table>

                                        {{-- Divider --}}
                                        <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="margin: 0 0 24px;">
                                            <tr>
                                                <td style="border-top: 1px solid #f3f4f6; font-size: 0; line-height: 0;">&nbsp;</td>
                                            </tr>
                                        </table>

                                        <p style="margin: 0; font-size: 13px; color: #9ca3af; text-align: center; line-height: 1.6;">
                                            If you didn't request this code, you can safely ignore this email.<br>
                                            Your account remains secure.
                                        </p>

                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    {{-- Footer --}}
                    <tr>
                        <td style="padding: 24px 0 0; text-align: center;">
                            <p style="margin: 0 0 8px; font-size: 13px; color: #9ca3af;">
                                © {{ date('Y') }} <strong style="color: #ea580c;">Job Hub</strong> — Find Your Dream Job
                            </p>
                            <p style="margin: 0; font-size: 12px; color: #d1d5db;">
                                This is an automated email, please do not reply.
                            </p>
                        </td>
                    </tr>

                </table>
            </td>
        </tr>
    </table>

</body>
</html>
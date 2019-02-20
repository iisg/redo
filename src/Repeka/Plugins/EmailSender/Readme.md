 Repeka SendMail Plugin

## Installation

1. Place the plugin sources in `src/Repeka/Plugins/EmailSender`

## Configuration

1. Add to 'var/config/config_local.yml' the following lines:
```
repeka_email_sender_plugin:
	smtp_address: 'SMTP server address'
        smtp_port: "465 or 25"
        smtp_username: 'SMTP server user login'
        smtp_password: 'SMTP server user password'
        smtp_encryption: "'ssl', 'tls' or 'null'"
        from_email: "E-mail sender's email address"
        from_name: "E-mail sender's name and surname"
```
1. Replace generic values with values specific to your SMTP server.

## Behavior

On every configured resource workflow place, the plugin will send e-mail with specified 'subject' and 'content'. The values of these parameters can be determined when adding a plug-in from the admin panel.

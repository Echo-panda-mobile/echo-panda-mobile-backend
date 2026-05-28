# Setting Up Brevo Email Testing

## Step 1: Get Brevo SMTP Credentials

1. Go to https://www.brevo.com (or your Brevo dashboard)
2. Navigate to **SMTP & API** section
3. Copy your **SMTP Credentials**:
   - **Host**: smtp-relay.brevo.com
   - **Port**: 587
   - **Username**: Your Brevo account email
   - **Password**: API key or account password

## Step 2: Update Laravel .env

Edit `.env` in the backend folder:

```bash
MAIL_MAILER=smtp
MAIL_HOST=smtp-relay.brevo.com
MAIL_PORT=587
MAIL_USERNAME=your-brevo-email@example.com
MAIL_PASSWORD=your-brevo-api-key
MAIL_FROM_ADDRESS="noreply@echopanda.local"
MAIL_FROM_NAME="Echo Panda"
```

## Step 3: Test the Flow

1. Restart Laravel container:
   ```bash
   docker-compose restart app
   ```

2. Create an artist in admin panel:
   - Name: "Test Artist"
   - Email: **test@example.com** (can be any test email)
   - Submit

3. Check Brevo Sandbox:
   - Go to your Brevo dashboard → **Email Sandboxes**
   - You should see the invite email appear in **My Sandbox**
   - Click to view and verify the Firebase password-reset link

## What Happens

```
Admin creates artist (test@example.com)
    ↓
ArtistController creates user
    ↓
FirebaseUserProvisioner.provision() creates Firebase user
    ↓
FirebaseUserProvisioner.sendInvite() sends email via Brevo
    ↓
Email appears in Brevo Sandbox (not actually sent to test@example.com)
    ↓
You can click the link to verify it works
```

## Email Content

The invite email contains:
- **From**: noreply@echopanda.local
- **To**: test@example.com
- **Subject**: Firebase password reset invitation
- **Body**: Link to set password in Firebase

## Notes

- **Sandbox emails**: Not actually sent to the recipient, captured in your Brevo sandbox
- **Real emails**: If you need to send to actual addresses, switch to production Brevo settings
- **Testing**: Use any test email address (no domain validation in sandbox)
- **Limit**: Brevo sandbox usually has a message limit (e.g., 10 emails) - check your dashboard

---

Once you've set up Brevo, try creating an artist again and check the sandbox for the invite email! 🚀

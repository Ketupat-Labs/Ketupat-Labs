# Render Deployment Guide for CompuPlay

This guide explains how to deploy the CompuPlay Laravel application to Render using the included `render.yaml` blueprint.

## Prerequisites

1. **GitHub Repository**: Your code must be pushed to GitHub
2. **Render Account**: Sign up at [render.com](https://render.com)
3. **Aiven MySQL Database**: Already configured (keep credentials handy)
4. **Gmail App Password**: For sending emails

## 🔐 Critical: Secrets Management

### ⚠️ NEVER commit these to GitHub:
- `.env` file (already in `.gitignore` ✅)
- `.env.docker` file (**ADD THIS TO `.gitignore`** if deploying)
- Database passwords
- API keys
- Gmail app passwords
- `APP_KEY`

### ✅ Safe to commit:
- `render.yaml` (does not contain secrets)
- `Dockerfile`
- `docker-compose.yml`
- All application code

## Step-by-Step Deployment

### 1. Prepare Your Repository

Before pushing to GitHub, ensure `.env.docker` is in `.gitignore`:

```bash
# Add this line to .gitignore if not already there
.env.docker
```

Then push your code:

```bash
git add .
git commit -m "Add Render deployment configuration"
git push origin main
```

### 2. Create Services on Render

You have **two options** for deployment:

#### Option A: Free Tier (No Payment Method Required) ⭐ RECOMMENDED FOR TESTING

This method creates services manually and works with Render's free tier.

**Step 1: Create Redis Service**

1. Go to [Render Dashboard](https://dashboard.render.com)
2. Click **"New +"** → **"Redis"**
3. Configure:
   - **Name**: `compuplay-redis`
   - **Region**: Singapore (or your preferred region)
   - **Plan**: **Free** (0 MB - 25 MB)
   - **Maxmemory Policy**: `allkeys-lru`
4. Click **"Create Redis"**
5. Wait for it to be "Available" (takes ~1 minute)

**Step 2: Create Web Service**

1. Click **"New +"** → **"Web Service"**
2. Connect your GitHub repository
3. Configure:
   - **Name**: `compuplay-app`
   - **Region**: Singapore (same as Redis)
   - **Branch**: `main`
   - **Runtime**: **Docker**
   - **Dockerfile Path**: `Dockerfile.render` ⚠️ IMPORTANT
   - **Plan**: **Free** (512 MB RAM, sleeps after 15 min inactivity)
   - **Docker Command**: Leave empty (uses Dockerfile's CMD)
4. Click **"Create Web Service"** (don't deploy yet!)

**Step 3: Link Redis to Web Service**

1. In your `compuplay-app` service, go to **"Environment"** tab
2. Add environment variable:
   - **Key**: `REDIS_HOST`
   - **Value**: Go to your `compuplay-redis` service → copy the **Internal Redis URL** (looks like `red-xxxxx`)
3. Add another:
   - **Key**: `REDIS_PORT`
   - **Value**: `6379`

Now proceed to **Step 3: Configure Environment Variables** below.

---

#### Option B: Blueprint (Requires Payment Method)

This method uses the `render.yaml` file for automatic setup but requires a payment method on file.

1. Go to [Render Dashboard](https://dashboard.render.com)
2. Click **"New +"** → **"Blueprint"**
3. Connect your GitHub repository
4. Render will detect `render.yaml` automatically
5. Click **"Apply"**

> **Note**: Even if you select free plans, Render requires a payment method for Blueprint deployments.

---

### 3. Configure Environment Variables

Render will create the services, but you need to set the **secret** environment variables manually.

Go to your **compuplay-app** service → **Environment** tab and set:

#### Required Secrets

| Variable | Value | Notes |
|----------|-------|-------|
| `APP_KEY` | `base64:+uDwNPDWfl89gHN+su08qtWdehoOJ68/PqYr8w0glb8=` | Copy from your `.env.docker` |
| `APP_URL` | `https://your-app-name.onrender.com` | Will be provided by Render |
| `DB_HOST` | `mysql-32396ee0-compuplay-736d.e.aivencloud.com` | Your Aiven host |
| `DB_USERNAME` | `avnadmin` | Your Aiven username |
| `DB_PASSWORD` | `AVNS_Durv8oyMWwCj77BNHtm` | Your Aiven password |
| `REVERB_APP_KEY` | `compuplay-key` | From `.env.docker` |
| `REVERB_APP_SECRET` | `compuplay-secret` | From `.env.docker` |
| `MAIL_USERNAME` | `ketupatlabs@gmail.com` | Your Gmail address |
| `MAIL_PASSWORD` | `omtpnfcvedacogfv` | Your Gmail App Password |

### 4. Update Aiven MySQL Firewall (if needed)

Render uses dynamic IPs, so you may need to:
1. Go to your Aiven console
2. Navigate to your MySQL service
3. Under **"Allowed IP Addresses"**, add Render's IP ranges or allow all (`0.0.0.0/0`)

> **Note**: For better security, check Render's documentation for their current IP ranges.

### 5. Verify Deployment

1. Wait for the build to complete (5-10 minutes)
2. Click on your service URL (e.g., `https://compuplay-app.onrender.com`)
3. You should see your application running!

## 🎯 Post-Deployment Checklist

- [ ] Application loads without errors
- [ ] Database connection works (check user registration)
- [ ] Email sending works (test OTP)
- [ ] Redis connection works (check sessions)
- [ ] Static assets load correctly
- [ ] WebSocket/Reverb works (if using real-time features)

## 🔧 Troubleshooting

### Build Fails

Check the build logs in Render dashboard. Common issues:
- Missing dependencies in `composer.json` or `package.json`
- Dockerfile errors

### 502 Bad Gateway

- Check if the service is running (Render dashboard)
- Verify environment variables are set correctly
- Check application logs for PHP errors

### Database Connection Failed

- Verify Aiven credentials in environment variables
- Check Aiven firewall allows Render's IPs
- Ensure database exists and migrations ran

### Emails Not Sending

- Verify `MAIL_PASSWORD` is the **App Password**, not your regular Gmail password
- Check Gmail security settings
- Look for errors in application logs

## 📊 Monitoring

- **Logs**: Available in Render dashboard under "Logs" tab
- **Metrics**: CPU, Memory usage visible in dashboard
- **Alerts**: Configure in Render settings

## 💰 Pricing

### Free Tier (Option A)
- **Web Service (Free)**: 512 MB RAM, sleeps after 15 min inactivity, 750 hours/month
- **Redis (Free)**: 25 MB storage
- **Total**: **$0/month** ✅

> **Note**: Free web services spin down after 15 minutes of inactivity and take ~30 seconds to wake up on the next request.

### Paid Tier (Option B)
- **Web Service (Starter)**: $7/month - Always on, 512 MB RAM
- **Redis (Starter)**: Free
- **Total**: ~$7/month

For production, consider upgrading to Standard plan for better performance.

## 🔄 Auto-Deploy

With `autoDeploy: true` in `render.yaml`, every push to your `main` branch will automatically trigger a new deployment.

To disable: Set `autoDeploy: false` in `render.yaml` or toggle in Render dashboard.

## 📚 Additional Resources

- [Render Documentation](https://render.com/docs)
- [Laravel Deployment Best Practices](https://laravel.com/docs/deployment)
- [Render Blueprint Specification](https://render.com/docs/blueprint-spec)

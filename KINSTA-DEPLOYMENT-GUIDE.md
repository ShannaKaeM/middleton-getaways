# Kinsta WordPress GitHub CI/CD Setup Guide

## Overview
This guide will help you set up automatic deployments from GitHub to your Kinsta WordPress site.

## Prerequisites
- ✅ Kinsta WordPress site (you have this)
- ✅ GitHub repository (middleton-getaways)
- ✅ SSH enabled on Kinsta (you just did this)

## Step-by-Step Setup

### 1. Prepare Your Local Repository
Since you already have your site in GitHub, skip the backup download step.

### 2. Get Kinsta Connection Details
From your Kinsta dashboard, you'll need:
- **Host IP**: Find in MyKinsta > Sites > Your Site > Info
- **Username**: Find in same location
- **SSH Port**: Find in same location
- **Password**: Your Kinsta password

### 3. Add GitHub Secrets
1. Go to: https://github.com/ShannaKaeM/middleton-getaways/settings/secrets/actions
2. Click "New repository secret"
3. Add these secrets:
   - `KINSTA_SERVER_IP` - Your Host IP
   - `KINSTA_USERNAME` - Your Username
   - `PASSWORD` - Your Kinsta password
   - `PORT` - Your SSH port

### 4. SSH into Kinsta Server
```bash
ssh username@ip-address -p port
```
Replace with your actual details from Kinsta dashboard.

### 5. Generate SSH Key on Kinsta Server
Once connected via SSH:
```bash
ssh-keygen -t rsa -b 4096 -C "admin@middinc.com"
```
- Press Enter for default location
- Leave passphrase blank (just press Enter twice)

### 6. Add Kinsta's SSH Key to GitHub
Still in Kinsta SSH:
```bash
cat ~/.ssh/id_rsa.pub
```
Copy the output, then:
1. Go to: https://github.com/settings/keys
2. Click "New SSH key"
3. Title: "Kinsta Server"
4. Paste the key

### 7. Configure Git on Kinsta Server
Still in SSH, navigate to your WordPress directory:
```bash
cd /www/yoursitename_123/public
git init
git remote add origin git@github.com:ShannaKaeM/middleton-getaways.git
```

### 8. Create GitHub Actions Workflow
Create this file in your local repository:

`.github/workflows/deploy.yml`
```yaml
name: Deploy to Kinsta

on:
  push:
    branches: [ main ]

jobs:
  deploy:
    runs-on: ubuntu-latest
    
    steps:
    - name: Deploy to Kinsta
      uses: appleboy/ssh-action@v0.1.5
      with:
        host: ${{ secrets.KINSTA_SERVER_IP }}
        username: ${{ secrets.KINSTA_USERNAME }}
        password: ${{ secrets.PASSWORD }}
        port: ${{ secrets.PORT }}
        script: |
          cd /www/yoursitename_123/public
          git fetch origin main
          git reset --hard origin/main
          composer install --no-dev --optimize-autoloader
          wp theme activate miGV --allow-root
```

**IMPORTANT**: Replace `/www/yoursitename_123/public` with your actual Kinsta path!

### 9. Important Notes for Your Setup

Since your WordPress is in `/app/public/`, you might need to:
1. Deploy the entire repository structure
2. OR set up a deployment script that copies files to the right location

### 10. Test Deployment
1. Commit and push the workflow file
2. Check GitHub Actions tab for deployment status
3. Verify changes on your Kinsta site

## Special Considerations for Your Project

Your repository structure:
```
/app/public/           <- WordPress files
/vendor/               <- Composer dependencies
/composer.json         <- Composer config
```

You may need to adjust the deployment script to handle this structure properly.

## Next Steps
1. Complete steps 4-7 (SSH setup)
2. Create the GitHub Actions workflow
3. Test with a small change first
4. Monitor deployments in GitHub Actions tab
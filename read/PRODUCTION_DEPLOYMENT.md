# 🚀 Production Deployment Guide

This guide covers deploying the ML server to a production environment.

---

## Prerequisites

- Linux server (Ubuntu 20.04+ recommended)
- Node.js 14+ and npm installed
- MySQL 8.0+ installed and running
- Nginx installed as reverse proxy
- Git installed
- Root or sudo access

---

## System Preparation

### 1. Create Application User

```bash
# Create user for running the application
sudo useradd -m -s /bin/bash mlserver

# Grant permissions
sudo usermod -aG sudo mlserver

# Switch to user
sudo su - mlserver
```

### 2. Setup Directory Structure

```bash
# Create application directory
sudo mkdir -p /var/www/legazpi-ml
sudo chown mlserver:mlserver /var/www/legazpi-ml

# Create logs directory
mkdir -p /var/www/legazpi-ml/logs

# Clone application
cd /var/www/legazpi-ml
git clone <your-repo> .
```

### 3. Install Node.js and npm

```bash
# Using NodeSource repository
curl -fsSL https://deb.nodesource.com/setup_18.x | sudo -E bash -
sudo apt-get install -y nodejs

# Verify installation
node --version
npm --version

# Install PM2 globally
sudo npm install -g pm2
```

---

## Application Setup

### 1. Install Dependencies

```bash
cd /var/www/legazpi-ml/backend/ml

# Install npm packages
npm ci --production  # Use 'ci' for exact versions

# Install TensorFlow CPU (smaller footprint)
npm install @tensorflow/tfjs-node-cpu

# Verify installation
ls -la node_modules/@tensorflow/
```

### 2. Configure Environment

```bash
# Create .env file with production settings
cat > .env << EOF
NODE_ENV=production
PORT=3000
DB_HOST=localhost
DB_USER=ml_app
DB_PASS=SecurePassword123!
DB_NAME=ibalong_ai
LOG_LEVEL=warn
EOF

# Restrict permissions
chmod 600 .env

# Verify
cat .env
```

### 3. Setup Database

```bash
# Login to MySQL
mysql -u root -p

# Run setup commands
CREATE USER 'ml_app'@'localhost' IDENTIFIED BY 'SecurePassword123!';
GRANT ALL PRIVILEGES ON ibalong_ai.* TO 'ml_app'@'localhost';
FLUSH PRIVILEGES;
EXIT;

# Initialize database schema
mysql -u ml_app -p ibalong_ai < schema.sql
```

### 4. Train Models

```bash
# From ml directory
npm run train

# Expected output: Models trained successfully
# This creates:
# - best-overcrowding-model/
# - best-waste-model/
# - normalization.json

# Verify models exist
ls -lah best-*-model/ normalization.json
```

---

## Systemd Service

### 1. Create Service File

```bash
# Create systemd service
sudo cat > /etc/systemd/system/ml-server.service << EOF
[Unit]
Description=Legazpi ML Server
After=network.target mysql.service

[Service]
Type=simple
User=mlserver
WorkingDirectory=/var/www/legazpi-ml/backend/ml
ExecStart=/usr/bin/node server.js
Restart=always
RestartSec=5
StandardOutput=append:/var/www/legazpi-ml/logs/server.log
StandardError=append:/var/www/legazpi-ml/logs/error.log
Environment="NODE_ENV=production"
Environment="PORT=3000"

[Install]
WantedBy=multi-user.target
EOF

# Reload systemd
sudo systemctl daemon-reload

# Enable service
sudo systemctl enable ml-server.service

# Start service
sudo systemctl start ml-server.service

# Check status
sudo systemctl status ml-server.service

# View logs
sudo tail -f /var/www/legazpi-ml/logs/server.log
```

### 2. Alternative: PM2 Process Manager

```bash
# From ml directory
pm2 start server.js --name "ml-server" --env production

# Save configuration
pm2 startup
pm2 save

# Verify
pm2 list
pm2 logs ml-server
```

---

## Nginx Reverse Proxy

### 1. Create Nginx Configuration

```bash
# Create nginx config
sudo cat > /etc/nginx/sites-available/legazpi-ml << 'EOF'
upstream ml_server {
    server localhost:3000;
    keepalive 64;
}

server {
    listen 80;
    server_name your-domain.com;

    # Redirect to HTTPS
    return 301 https://$server_name$request_uri;
}

server {
    listen 443 ssl http2;
    server_name your-domain.com;

    # SSL certificates (Let's Encrypt recommended)
    ssl_certificate /etc/letsencrypt/live/your-domain.com/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/your-domain.com/privkey.pem;

    # SSL configuration
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers HIGH:!aNULL:!MD5;
    ssl_prefer_server_ciphers on;

    # Security headers
    add_header Strict-Transport-Security "max-age=31536000; includeSubDomains" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header X-Frame-Options "DENY" always;

    # Logging
    access_log /var/log/nginx/ml-server-access.log;
    error_log /var/log/nginx/ml-server-error.log;

    # Gzip compression
    gzip on;
    gzip_types application/json;
    gzip_min_length 1000;

    # Reverse proxy settings
    location /api/ml/ {
        proxy_pass http://ml_server;
        proxy_http_version 1.1;
        proxy_set_header Upgrade $http_upgrade;
        proxy_set_header Connection 'upgrade';
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
        proxy_cache_bypass $http_upgrade;
        proxy_connect_timeout 60s;
        proxy_send_timeout 60s;
        proxy_read_timeout 60s;
    }

    # Health check
    location /ml/health {
        proxy_pass http://ml_server/health;
    }
}
EOF

# Enable site
sudo ln -s /etc/nginx/sites-available/legazpi-ml /etc/nginx/sites-enabled/

# Test configuration
sudo nginx -t

# Restart nginx
sudo systemctl restart nginx
```

### 2. Setup SSL with Let's Encrypt

```bash
# Install Certbot
sudo apt-get install -y certbot python3-certbot-nginx

# Get certificate
sudo certbot certonly --standalone -d your-domain.com

# Auto-renewal
sudo systemctl enable certbot.timer
sudo systemctl start certbot.timer
```

---

## Monitoring & Maintenance

### 1. Setup Monitoring

```bash
# Install Node Exporter for Prometheus
wget https://github.com/prometheus/node_exporter/releases/download/v1.6.1/node_exporter-1.6.1.linux-amd64.tar.gz
tar xzf node_exporter-1.6.1.linux-amd64.tar.gz
sudo cp node_exporter-1.6.1.linux-amd64/node_exporter /usr/local/bin/

# Create systemd service for node_exporter
sudo systemctl enable node_exporter
sudo systemctl start node_exporter
```

### 2. Log Management

```bash
# Create log rotation config
sudo cat > /etc/logrotate.d/ml-server << EOF
/var/www/legazpi-ml/logs/*.log {
    daily
    rotate 14
    compress
    delaycompress
    notifempty
    create 0640 mlserver mlserver
    sharedscripts
    postrotate
        systemctl reload ml-server > /dev/null 2>&1 || true
    endscript
}
EOF
```

### 3. Health Checks

```bash
# Create monitoring script
cat > /usr/local/bin/check-ml-server.sh << 'EOF'
#!/bin/bash

# Check if server is running
if curl -s http://localhost:3000/health > /dev/null; then
    echo "✅ ML Server is running"
    exit 0
else
    echo "❌ ML Server is down"
    systemctl restart ml-server
    exit 1
fi
EOF

chmod +x /usr/local/bin/check-ml-server.sh

# Add to crontab for periodic checks
# (crontab -l 2>/dev/null; echo "*/5 * * * * /usr/local/bin/check-ml-server.sh") | crontab -
```

---

## Backup & Recovery

### 1. Database Backup

```bash
# Create backup script
cat > /usr/local/bin/backup-ml-db.sh << 'EOF'
#!/bin/bash

BACKUP_DIR="/var/backups/ml-server"
mkdir -p $BACKUP_DIR

# Backup database
mysqldump -u ml_app -p$DB_PASSWORD ibalong_ai | gzip > $BACKUP_DIR/ibalong_ai-$(date +%Y%m%d).sql.gz

# Keep only 30 days of backups
find $BACKUP_DIR -name "*.gz" -mtime +30 -delete

echo "✅ Backup completed"
EOF

chmod +x /usr/local/bin/backup-ml-db.sh

# Schedule daily at 2 AM
# (crontab -l 2>/dev/null; echo "0 2 * * * /usr/local/bin/backup-ml-db.sh") | crontab -
```

### 2. Model Backup

```bash
# Backup trained models
tar -czf /var/backups/ml-server/models-$(date +%Y%m%d).tar.gz \
  /var/www/legazpi-ml/backend/ml/best-*-model/ \
  /var/www/legazpi-ml/backend/ml/normalization.json
```

---

## Performance Tuning

### 1. MySQL Optimization

```bash
# Add to /etc/mysql/mysql.conf.d/mysqld.cnf
[mysqld]
max_connections=200
innodb_buffer_pool_size=2G
innodb_log_file_size=512M
```

### 2. Node.js Optimization

```bash
# Use in ecosystem.config.js or systemd service
export NODE_OPTIONS="--max-old-space-size=2048"
```

### 3. Nginx Optimization

```bash
# In /etc/nginx/nginx.conf
worker_processes auto;
worker_connections 2048;
keepalive_timeout 65;
```

---

## Security Hardening

### 1. Firewall

```bash
# Enable UFW
sudo ufw enable

# Allow SSH
sudo ufw allow 22/tcp

# Allow HTTP/HTTPS
sudo ufw allow 80/tcp
sudo ufw allow 443/tcp

# Deny all other incoming
sudo ufw default deny incoming
sudo ufw default allow outgoing

# Check status
sudo ufw status
```

### 2. Fail2Ban

```bash
# Install and configure
sudo apt-get install -y fail2ban

# Create local config
sudo cp /etc/fail2ban/jail.conf /etc/fail2ban/jail.local

# Enable and start
sudo systemctl enable fail2ban
sudo systemctl start fail2ban
```

### 3. Database Security

```bash
# Remove root anonymous access
mysql -u root -p mysql
DELETE FROM user WHERE User='';
DELETE FROM user WHERE User='root' AND Host NOT IN ('localhost', '127.0.0.1', '::1');
FLUSH PRIVILEGES;
EXIT;
```

---

## Deployment Verification

### Deployment Checklist

- [ ] Node.js installed (v14+)
- [ ] npm dependencies installed
- [ ] .env configured with production settings
- [ ] Database created and schema applied
- [ ] Models trained and saved
- [ ] Systemd service created
- [ ] Nginx reverse proxy configured
- [ ] SSL certificates installed
- [ ] Firewall configured
- [ ] Health check passing
- [ ] Logs being created
- [ ] Backups scheduled
- [ ] Monitoring tools running

### Test Endpoints

```bash
# From another machine or server
curl https://your-domain.com/api/ml/health

curl -X POST https://your-domain.com/api/ml/predict \
  -H "Content-Type: application/json" \
  -d '{"attendance":25000,"venue_capacity":50000,"weekend":1,"is_free":1,"duration_hours":8,"food_stalls":45,"weather":0}'
```

---

## Troubleshooting

### Service Won't Start
```bash
# Check logs
sudo journalctl -u ml-server -n 50

# Check permissions
sudo ls -la /var/www/legazpi-ml/

# Verify Node.js
which node
```

### High Memory Usage
```bash
# Check process
ps aux | grep node

# Monitor in real-time
top -u mlserver

# Increase memory limit in .env or service
export NODE_OPTIONS="--max-old-space-size=4096"
```

### Database Connection Errors
```bash
# Test connection
mysql -u ml_app -p -h localhost ibalong_ai -e "SHOW TABLES;"

# Check permissions
mysql -u root -p mysql -e "SELECT user, host FROM user WHERE user='ml_app';"
```

---

## Maintenance Schedule

| Task | Frequency | Command |
|------|-----------|---------|
| Database backup | Daily | `backup-ml-db.sh` |
| Model backup | Weekly | `tar -czf models-*.tar.gz ...` |
| Log rotation | Daily | logrotate |
| SSL renewal | 60 days before | `certbot renew` |
| Model retraining | Monthly | `npm run train` |
| Security updates | As needed | `sudo apt update && apt upgrade` |

---

## Scaling for Production

### For Heavy Load:
1. Use GPU version: `npm install @tensorflow/tfjs-node-gpu`
2. Add caching layer (Redis)
3. Use load balancer (HAProxy) for multiple instances
4. Optimize database indexes
5. Consider CDN for static content

### For High Availability:
1. Setup database replication
2. Use managed database service (AWS RDS)
3. Deploy on Kubernetes
4. Use auto-scaling groups
5. Setup comprehensive monitoring & alerts

---

## Support & Documentation

- **Main README:** backend/ml/README.md
- **Architecture:** backend/ml/ARCHITECTURE.md
- **Logs:** /var/www/legazpi-ml/logs/
- **PM2 Logs:** `pm2 logs ml-server`
- **Nginx Logs:** /var/log/nginx/ml-server-*.log

---

**Deployment Ready!** 🚀

Your ML server is now deployed and monitoring-ready!

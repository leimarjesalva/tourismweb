#!/usr/bin/env node

/**
 * PM2 Ecosystem Configuration
 * 
 * Usage:
 *   pm2 start ecosystem.config.js
 *   pm2 logs ml-server
 *   pm2 restart ml-server
 */

module.exports = {
  apps: [
    {
      // Application name
      name: 'ml-server',
      
      // Application script
      script: './server.js',
      
      // Application working directory
      cwd: './backend/ml',
      
      // Number of instances
      instances: 1,
      
      // Process execution mode
      exec_mode: 'fork', // Use 'cluster' if running on multi-core
      
      // Environment variables
      env: {
        NODE_ENV: 'development',
        PORT: 3000,
        DB_HOST: 'localhost',
        DB_USER: 'root',
        DB_PASS: '',
        DB_NAME: 'ibalong_ai'
      },
      
      // Production environment variables
      env_production: {
        NODE_ENV: 'production',
        PORT: 3000,
        DB_HOST: 'localhost',
        DB_USER: 'ml_app',
        DB_PASS: 'secure_password_here',
        DB_NAME: 'ibalong_ai'
      },
      
      // Auto restart on file change (development only)
      watch: false,
      
      // Ignore watching these paths
      ignore_watch: ['node_modules', 'logs', 'best-*-model'],
      
      // Logs
      out: './logs/out.log',
      error: './logs/error.log',
      combine_logs: true,
      
      // Auto restart if crashed
      autorestart: true,
      max_restarts: 10,
      min_uptime: '10s',
      
      // Graceful shutdown
      kill_timeout: 5000,
      
      // Instance max memory
      max_memory_restart: '1G',
      
      // Merge logs from cluster mode
      merge_logs: true,
      
      // Arguments
      args: '',
      
      // Node arguments
      node_args: '--max-http-header-size=80000'
    }
  ],

  // Deploy configuration
  deploy: {
    production: {
      user: 'ubuntu',
      host: 'your-server.com',
      key: '~/.ssh/id_rsa',
      ref: 'origin/main',
      repo: 'https://github.com/yourusername/legazpi-explorer.git',
      path: '/var/www/legazpi-ml',
      'post-deploy': 'npm install && npm run train && pm2 reload ecosystem.config.js --env production'
    }
  }
};

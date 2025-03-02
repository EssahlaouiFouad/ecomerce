const express = require('express');
const router = express.Router();
const bcrypt = require('bcryptjs');
const pool = require('../config/db');

// Middleware to check if user is logged in
const isAuthenticated = (req, res, next) => {
  if (req.session.adminLoggedIn) {
    next();
  } else {
    res.redirect('/admin/login');
  }
};

// Login routes
router.get('/login', (req, res) => {
  res.render('admin/login');
});

router.post('/login', async (req, res) => {
  try {
    const { username, password } = req.body;
    const [users] = await pool.execute('SELECT * FROM admin_users WHERE username = ?', [username]);

    if (users.length && await bcrypt.compare(password, users[0].password)) {
      req.session.adminLoggedIn = true;
      req.session.adminId = users[0].id;
      req.session.adminRole = users[0].role;
      res.redirect('/admin/dashboard');
    } else {
      res.render('admin/login', { error: 'Invalid credentials' });
    }
  } catch (error) {
    console.error('Login error:', error);
    res.status(500).render('error', { error: 'Login failed' });
  }
});

// Dashboard route
router.get('/dashboard', isAuthenticated, async (req, res) => {
  try {
    const [orders] = await pool.execute('SELECT * FROM orders ORDER BY created_at DESC');
    res.render('admin/dashboard', { orders });
  } catch (error) {
    console.error('Dashboard error:', error);
    res.status(500).render('error', { error: 'Failed to load dashboard' });
  }
});

// Update order status
router.post('/update-order-status', isAuthenticated, async (req, res) => {
  try {
    const { orderId, status } = req.body;
    await pool.execute('UPDATE orders SET status = ? WHERE id = ?', [status, orderId]);
    res.redirect('/admin/dashboard');
  } catch (error) {
    console.error('Status update error:', error);
    res.status(500).render('error', { error: 'Failed to update order status' });
  }
});

// Settings routes
router.get('/settings', isAuthenticated, async (req, res) => {
  try {
    if (req.session.adminRole === 'admin') {
      const [agents] = await pool.execute('SELECT * FROM admin_users WHERE role = "agent"');
      res.render('admin/settings', { agents });
    } else {
      res.render('admin/settings');
    }
  } catch (error) {
    console.error('Settings error:', error);
    res.status(500).render('error', { error: 'Failed to load settings' });
  }
});

// Change password
router.post('/change-password', isAuthenticated, async (req, res) => {
  try {
    const { currentPassword, newPassword, confirmPassword } = req.body;
    if (newPassword !== confirmPassword) {
      return res.render('admin/settings', { error: 'New passwords do not match' });
    }

    const [user] = await pool.execute('SELECT * FROM admin_users WHERE id = ?', [req.session.adminId]);
    if (await bcrypt.compare(currentPassword, user[0].password)) {
      const hashedPassword = await bcrypt.hash(newPassword, 10);
      await pool.execute('UPDATE admin_users SET password = ? WHERE id = ?', [hashedPassword, req.session.adminId]);
      res.render('admin/settings', { success: 'Password updated successfully' });
    } else {
      res.render('admin/settings', { error: 'Current password is incorrect' });
    }
  } catch (error) {
    console.error('Password change error:', error);
    res.status(500).render('error', { error: 'Failed to change password' });
  }
});

// Create agent account
router.post('/create-agent', isAuthenticated, async (req, res) => {
  if (req.session.adminRole !== 'admin') {
    return res.status(403).render('error', { error: 'Unauthorized' });
  }

  try {
    const { username, password } = req.body;
    const hashedPassword = await bcrypt.hash(password, 10);
    await pool.execute(
      'INSERT INTO admin_users (username, password, role) VALUES (?, ?, "agent")',
      [username, hashedPassword]
    );
    res.redirect('/admin/settings');
  } catch (error) {
    console.error('Agent creation error:', error);
    res.status(500).render('error', { error: 'Failed to create agent account' });
  }
});

// Logout route
router.get('/logout', (req, res) => {
  req.session.destroy();
  res.redirect('/admin/login');
});

module.exports = router;
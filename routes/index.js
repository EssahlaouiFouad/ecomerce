const express = require('express');
const router = express.Router();
const pool = require('../config/db');

// Home page route
router.get('/', (req, res) => {
  res.render('index');
});

// Order form route
router.get('/order', (req, res) => {
  res.render('order');
});

// Submit order route
router.post('/submit_order', async (req, res) => {
  try {
    const { name, phone, address, product_type, quantity, notes } = req.body;
    
    const [result] = await pool.execute(
      'INSERT INTO orders (name, phone, address, product_type, quantity, notes, status) VALUES (?, ?, ?, ?, ?, ?, ?)',
      [name, phone, address, product_type, quantity, notes, 'pending']
    );

    res.redirect('/order_success');
  } catch (error) {
    console.error('Error submitting order:', error);
    res.status(500).render('error', { error: 'Failed to submit order' });
  }
});

// Order success route
router.get('/order_success', (req, res) => {
  res.render('order_success');
});

module.exports = router;
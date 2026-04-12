/**
 * Legazpi Explorer - Guest Feedback & Rating System
 * Allows guests to rate and provide feedback for:
 * - Shops, Products, Destinations, Hotels, and the System
 */

class FeedbackSystem {
  constructor() {
    this.modal = null;
    this.currentShopId = null; // Track current shop when opening from modal
    this.init();
  }

  init() {
    this.createFeedbackModal();
    // this.createFeedbackButton(); // Feedback button disabled - feedback now integrated into Shop/Itinerary sections
    this.attachEventListeners();
  }

  createFeedbackModal() {
    const modal = document.createElement('div');
    modal.id = 'feedbackModal';
    modal.style.cssText = `
      display: none;
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: rgba(0, 0, 0, 0.7);
      z-index: 9999;
      align-items: center;
      justify-content: center;
    `;

    modal.innerHTML = `
      <div style="
        background: white;
        border-radius: 16px;
        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
        max-width: 600px;
        width: 90%;
        max-height: 90vh;
        overflow-y: auto;
        animation: slideUp 0.3s ease;
      ">
        <style>
          @keyframes slideUp {
            from {
              opacity: 0;
              transform: translateY(30px);
            }
            to {
              opacity: 1;
              transform: translateY(0);
            }
          }
        </style>

        <!-- Header -->
        <div style="
          background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
          color: white;
          padding: 24px;
          border-radius: 16px 16px 0 0;
          display: flex;
          justify-content: space-between;
          align-items: center;
        ">
          <div>
            <h2 style="margin: 0; font-size: 24px; font-weight: 800;">💬 Share Your Feedback</h2>
            <small style="opacity: 0.9; display: block; margin-top: 4px;">Help us improve your experience with your honest review</small>
          </div>
          <button onclick="feedbackSystem.closeModal()" style="
            background: rgba(255, 255, 255, 0.2);
            border: none;
            color: white;
            font-size: 28px;
            cursor: pointer;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s;
          " onmouseover="this.style.background='rgba(255, 255, 255, 0.3)'" onmouseout="this.style.background='rgba(255, 255, 255, 0.2)'">✕</button>
        </div>

        <!-- Content -->
        <div style="padding: 28px;">
          <form id="feedbackForm" onsubmit="feedbackSystem.submitFeedback(event)">
            
            <!-- Rating Section -->
            <div style="margin-bottom: 24px;">
              <label style="display: block; font-weight: 700; color: #1a237e; margin-bottom: 10px; font-size: 15px;">✨ How would you rate your experience? *</label>
              <div style="display: flex; gap: 12px; justify-content: center; align-items: center;">
                <div id="starRating" style="display: flex; gap: 8px; font-size: 32px;">
                  <span class="star" data-value="1" style="cursor: pointer; transition: all 0.2s; opacity: 0.3;">★</span>
                  <span class="star" data-value="2" style="cursor: pointer; transition: all 0.2s; opacity: 0.3;">★</span>
                  <span class="star" data-value="3" style="cursor: pointer; transition: all 0.2s; opacity: 0.3;">★</span>
                  <span class="star" data-value="4" style="cursor: pointer; transition: all 0.2s; opacity: 0.3;">★</span>
                  <span class="star" data-value="5" style="cursor: pointer; transition: all 0.2s; opacity: 0.3;">★</span>
                </div>
                <span id="ratingText" style="font-weight: 700; color: #f59e0b; font-size: 18px; min-width: 60px;">Select a rating</span>
              </div>
              <input type="hidden" id="ratingValue" name="rating" value="0" required>
            </div>

            <!-- Feedback Type -->
            <div style="margin-bottom: 20px;">
              <label style="font-weight: 700; color: #1a237e; display: block; margin-bottom: 10px; font-size: 15px;">📝 Feedback Type *</label>
              <select id="feedbackTypeSelect" name="feedback_type" required style="
                width: 100%;
                padding: 14px 16px;
                border: 2px solid #ecf0f1;
                border-radius: 10px;
                font-size: 14px;
                font-family: inherit;
                cursor: pointer;
                transition: all 0.3s;
              " onchange="feedbackSystem.updateTargetOptions()">
                <option value="">-- Select what you'd like to review --</option>
                <option value="shop">🛍️ SHOP</option>
                <option value="product">🛒 PRODUCT (in a shop)</option>
              </select>
            </div>

            <!-- Shop Selection (For Products) -->
            <div id="shopSelectionContainer" style="margin-bottom: 20px; display: none;">
              <label style="font-weight: 700; color: #1a237e; display: block; margin-bottom: 10px; font-size: 15px;">🛍️ Select Shop *</label>
              <select id="shopSelect" name="shop_id" style="
                width: 100%;
                padding: 14px 16px;
                border: 2px solid #ecf0f1;
                border-radius: 10px;
                font-size: 14px;
                font-family: inherit;
                cursor: pointer;
                transition: all 0.3s;
              " onchange="feedbackSystem.loadProductsByShop()">
                <option value="">-- Select a shop --</option>
              </select>
            </div>

            <!-- Target Selection (Dynamic) -->
            <div id="targetSelectionContainer" style="margin-bottom: 20px; display: none;">
              <label style="font-weight: 700; color: #1a237e; display: block; margin-bottom: 10px; font-size: 15px;">🎯 Select Item *</label>
              <select id="targetSelect" name="target_id" style="
                width: 100%;
                padding: 14px 16px;
                border: 2px solid #ecf0f1;
                border-radius: 10px;
                font-size: 14px;
                font-family: inherit;
                cursor: pointer;
                transition: all 0.3s;
              ">
                <option value="">-- Select an option --</option>
              </select>
            </div>

            <!-- Guest Information -->
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 20px;">
              <div>
                <label style="font-weight: 700; color: #1a237e; display: block; margin-bottom: 10px; font-size: 14px;">👤 Your Name *</label>
                <input type="text" id="guestName" name="user_name" placeholder="Your name" required style="
                  width: 100%;
                  padding: 12px 14px;
                  border: 2px solid #ecf0f1;
                  border-radius: 8px;
                  font-size: 14px;
                  font-family: inherit;
                  transition: all 0.3s;
                " onfocus="this.style.borderColor='#667eea'; this.style.background='#f8f9ff'" onblur="this.style.borderColor='#ecf0f1'; this.style.background='white'">
              </div>
              <div>
                <label style="font-weight: 700; color: #1a237e; display: block; margin-bottom: 10px; font-size: 14px;">📧 Email *</label>
                <input type="email" id="guestEmail" name="user_email" placeholder="your.email@example.com" required style="
                  width: 100%;
                  padding: 12px 14px;
                  border: 2px solid #ecf0f1;
                  border-radius: 8px;
                  font-size: 14px;
                  font-family: inherit;
                  transition: all 0.3s;
                " onfocus="this.style.borderColor='#667eea'; this.style.background='#f8f9ff'" onblur="this.style.borderColor='#ecf0f1'; this.style.background='white'">
              </div>
            </div>

            <!-- Feedback Message -->
            <div style="margin-bottom: 20px;">
              <label style="font-weight: 700; color: #1a237e; display: block; margin-bottom: 10px; font-size: 14px;">💬 Your Feedback *</label>
              <textarea id="feedbackMessage" name="message" placeholder="Share your thoughts, suggestions, or experiences..." required rows="5" style="
                width: 100%;
                padding: 14px 16px;
                border: 2px solid #ecf0f1;
                border-radius: 8px;
                font-size: 14px;
                font-family: inherit;
                resize: vertical;
                transition: all 0.3s;
              " onfocus="this.style.borderColor='#667eea'; this.style.background='#f8f9ff'" onblur="this.style.borderColor='#ecf0f1'; this.style.background='white'"></textarea>
              <small style="color: #999; display: block; margin-top: 6px;">Be as detailed as possible to help us improve</small>
            </div>

            <!-- Anonymous Checkbox -->
            <div style="margin-bottom: 24px; display: flex; align-items: center; gap: 12px;">
              <input type="checkbox" id="anonymousCheckbox" name="anonymous" style="width: 18px; height: 18px; cursor: pointer;">
              <label for="anonymousCheckbox" style="cursor: pointer; color: #666; font-size: 14px; margin: 0;">
                🔒 Submit feedback anonymously
              </label>
            </div>

            <!-- Submit Button -->
            <div style="display: flex; gap: 12px;">
              <button type="submit" id="submitFeedbackBtn" style="
                flex: 1;
                padding: 14px 24px;
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                color: white;
                border: none;
                border-radius: 10px;
                font-weight: 700;
                font-size: 15px;
                cursor: pointer;
                transition: all 0.3s;
                box-shadow: 0 8px 20px rgba(102,126,234,0.3);
              " onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 12px 28px rgba(102,126,234,0.5)'" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 8px 20px rgba(102,126,234,0.3)'">
                📤 Submit Feedback
              </button>
              <button type="button" onclick="feedbackSystem.closeModal()" style="
                flex: 1;
                padding: 14px 24px;
                background: #f0f0f0;
                color: #333;
                border: 2px solid #ecf0f1;
                border-radius: 10px;
                font-weight: 700;
                font-size: 15px;
                cursor: pointer;
                transition: all 0.3s;
              " onmouseover="this.style.background='#e0e0e0'" onmouseout="this.style.background='#f0f0f0'">
                Cancel
              </button>
            </div>

            <!-- Info -->
            <div style="margin-top: 20px; background: #e3f2fd; padding: 12px 16px; border-radius: 8px; border-left: 4px solid #2196f3;">
              <small style="color: #1976d2; display: block; line-height: 1.6;">
                ℹ️ <strong>Your feedback matters to us!</strong> Every review helps us understand what we're doing well and where we can improve. Thank you for taking the time to share your thoughts.
              </small>
            </div>
          </form>
        </div>

        <!-- Success Message (Hidden) -->
        <div id="feedbackSuccessMessage" style="
          display: none;
          padding: 32px;
          text-align: center;
          background: linear-gradient(135deg, #27ae60 0%, #229954 100%);
          color: white;
          border-radius: 0 0 16px 16px;
        ">
          <div style="font-size: 48px; margin-bottom: 12px;">✅</div>
          <h3 style="margin: 0 0 8px 0; font-size: 20px;">Thank You!</h3>
          <p style="margin: 0; opacity: 0.95;">Your feedback has been submitted successfully. We appreciate your input!</p>
          <button onclick="feedbackSystem.closeModal()" style="
            margin-top: 16px;
            padding: 10px 24px;
            background: rgba(255, 255, 255, 0.2);
            border: 2px solid white;
            color: white;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s;
          " onmouseover="this.style.background='rgba(255, 255, 255, 0.3)'" onmouseout="this.style.background='rgba(255, 255, 255, 0.2)'">Close</button>
        </div>
      </div>
    `;

    document.body.appendChild(modal);
    this.modal = modal;

    // Attach star rating event listeners
    document.querySelectorAll('#starRating .star').forEach(star => {
      star.addEventListener('click', () => {
        const value = star.getAttribute('data-value');
        this.setRating(value);
      });
      star.addEventListener('mouseover', () => {
        const value = star.getAttribute('data-value');
        this.updateStarDisplay(value);
      });
    });

    document.getElementById('starRating').addEventListener('mouseout', () => {
      const currentRating = document.getElementById('ratingValue').value;
      this.updateStarDisplay(currentRating);
    });
  }

  createFeedbackButton() {
    const button = document.createElement('button');
    button.id = 'feedbackButton';
    button.style.cssText = `
      position: fixed;
      bottom: 30px;
      right: 30px;
      width: 60px;
      height: 60px;
      border-radius: 50%;
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      color: white;
      border: none;
      font-size: 28px;
      cursor: pointer;
      box-shadow: 0 8px 24px rgba(102, 126, 234, 0.4);
      transition: all 0.3s;
      z-index: 999;
      display: flex;
      align-items: center;
      justify-content: center;
    `;
    button.setAttribute('title', 'Share Feedback');
    button.innerHTML = '💬';
    button.onclick = () => this.openModal();

    button.onmouseover = function() {
      this.style.transform = 'scale(1.1)';
      this.style.boxShadow = '0 12px 32px rgba(102, 126, 234, 0.6)';
    };

    button.onmouseout = function() {
      this.style.transform = 'scale(1)';
      this.style.boxShadow = '0 8px 24px rgba(102, 126, 234, 0.4)';
    };

    document.body.appendChild(button);
  }

  attachEventListeners() {
    // Close modal when clicking outside
    this.modal.addEventListener('click', (e) => {
      if(e.target === this.modal) {
        this.closeModal();
      }
    });

    // Close on Escape key
    document.addEventListener('keydown', (e) => {
      if(e.key === 'Escape' && this.modal.style.display === 'flex') {
        this.closeModal();
      }
    });
  }

  openModal() {
    this.modal.style.display = 'flex';
    document.body.style.overflow = 'hidden';
    document.getElementById('feedbackForm').style.display = 'block';
    document.getElementById('feedbackSuccessMessage').style.display = 'none';
  }

  /**
   * Open modal and pre-select a target (shop, product, etc.)
   * @param {string} targetType - Type: 'shop' or 'product'
   * @param {number} targetId - ID of the shop/product
   * @param {string} targetName - Name of the shop/product
   * @param {number} parentShopId - (Optional) Parent shop ID if reviewing a product within a shop
   */
  openModalForTarget(targetType, targetId, targetName, parentShopId = null) {
    this.openModal();
    
    // Store current shop context for filtering products
    this.currentShopId = parentShopId || (targetType === 'shop' ? targetId : null);
    
    // Set the feedback type based on target
    const feedbackTypeSelect = document.getElementById('feedbackTypeSelect');
    feedbackTypeSelect.value = targetType;
    
    // For products, add visual indicator and pre-select shop
    if (targetType === 'product') {
      feedbackTypeSelect.style.opacity = '0.6';
      feedbackTypeSelect.style.cursor = 'not-allowed';
      feedbackTypeSelect.disabled = true;
      
      const typeLabel = feedbackTypeSelect.previousElementSibling;
      if (typeLabel) {
        typeLabel.innerHTML = `📝 Feedback Type * <small style="color: #667eea; font-weight: 600; font-size: 11px; margin-left: 8px;">(Pre-selected)</small>`;
      }
    }
    
    // Trigger the update to load/configure target options
    this.updateTargetOptions();
    
    // Set the target selection after options load
    setTimeout(() => {
      if (targetType === 'product' && this.currentShopId) {
        const shopSelect = document.getElementById('shopSelect');
        shopSelect.value = this.currentShopId;
        
        // Load products by shop
        this.loadProductsByShop();
        
        // Then pre-select the product
        setTimeout(() => {
          const targetSelect = document.getElementById('targetSelect');
          targetSelect.value = targetId;
          console.log(`Pre-selected product: ${targetName} (ID: ${targetId}) in shop ${this.currentShopId}`);
        }, 150);
      } else if (targetType === 'shop') {
        // For shop feedback, just show confirmation
        console.log(`Pre-selected shop: ${targetName} (ID: ${targetId})`);
      }
    }, 150);
  }

  closeModal() {
    this.modal.style.display = 'none';
    document.body.style.overflow = 'auto';
    this.resetForm();
  }

  setRating(value) {
    document.getElementById('ratingValue').value = value;
    const labels = ['', '😞 Poor', '😕 Fair', '😊 Good', '😄 Great', '🤩 Excellent'];
    document.getElementById('ratingText').textContent = labels[value];
    this.updateStarDisplay(value);
  }

  updateStarDisplay(rating) {
    document.querySelectorAll('#starRating .star').forEach((star, idx) => {
      if(idx < rating) {
        star.style.opacity = '1';
        star.style.color = '#fbbf24';
      } else {
        star.style.opacity = '0.3';
        star.style.color = 'inherit';
      }
    });
  }

  updateTargetOptions() {
    const feedbackType = document.getElementById('feedbackTypeSelect').value;
    const container = document.getElementById('targetSelectionContainer');
    const shopContainer = document.getElementById('shopSelectionContainer');
    const targetSelect = document.getElementById('targetSelect');

    // Hide all containers by default
    container.style.display = 'none';
    shopContainer.style.display = 'none';
    document.getElementById('targetSelect').removeAttribute('required');

    if(!feedbackType) {
      return;
    }

    // For shop feedback - show shop selector
    if(feedbackType === 'shop') {
      container.style.display = 'block';
      targetSelect.setAttribute('required', 'true');
      
      // Update the label to say "Select Shop"
      const targetLabel = container.querySelector('label');
      if (targetLabel) {
        targetLabel.textContent = '🛍️ Select Shop *';
      }
      
      this.loadAllShopsForRating();
      return;
    }

    // For product feedback - show shop selector first
    if(feedbackType === 'product') {
      shopContainer.style.display = 'block';
      document.getElementById('shopSelect').setAttribute('required', 'true');
      this.loadAllShops();
      return;
    }
  }

  async loadAllShops() {
    const shopSelect = document.getElementById('shopSelect');
    shopSelect.innerHTML = '<option value="">Loading shops...</option>';

    try {
      const response = await fetch('/capstone/backend/api.php?action=list_shops');
      if (!response.ok) throw new Error('Failed to load shops');
      
      const data = await response.json();
      const shops = data.shops || data.data || [];

      shopSelect.innerHTML = '<option value="">-- Select a shop --</option>';

      if(Array.isArray(shops) && shops.length > 0) {
        shops.forEach(shop => {
          const option = document.createElement('option');
          option.value = shop.id;
          option.textContent = shop.name || `Shop ${shop.id}`;
          shopSelect.appendChild(option);
        });
        
        // If we have a pre-selected shop, select it
        if (this.currentShopId) {
          shopSelect.value = this.currentShopId;
          this.loadProductsByShop();
        }
      } else {
        shopSelect.innerHTML = '<option disabled>No shops available</option>';
      }
    } catch(e) {
      console.error('Error loading shops:', e);
      shopSelect.innerHTML = '<option disabled>Error loading shops</option>';
    }
  }

  async loadAllShopsForRating() {
    const targetSelect = document.getElementById('targetSelect');
    targetSelect.innerHTML = '<option value="">Loading shops...</option>';

    try {
      const response = await fetch('/capstone/backend/api.php?action=list_shops');
      if (!response.ok) throw new Error('Failed to load shops');
      
      const data = await response.json();
      const shops = data.shops || data.data || [];

      targetSelect.innerHTML = '<option value="">-- Select a shop to rate --</option>';

      if(Array.isArray(shops) && shops.length > 0) {
        shops.forEach(shop => {
          const option = document.createElement('option');
          option.value = shop.id;
          option.textContent = shop.name || `Shop ${shop.id}`;
          targetSelect.appendChild(option);
        });
        
        // If we have a pre-selected shop, select it
        if (this.currentShopId) {
          targetSelect.value = this.currentShopId;
        }
      } else {
        targetSelect.innerHTML = '<option disabled>No shops available</option>';
      }
    } catch(e) {
      console.error('Error loading shops:', e);
      targetSelect.innerHTML = '<option disabled>Error loading shops</option>';
    }
  }


  loadProductsByShop() {
    const shopId = document.getElementById('shopSelect').value;
    if (!shopId) {
      document.getElementById('targetSelectionContainer').style.display = 'none';
      return;
    }

    this.currentShopId = shopId;
    const container = document.getElementById('targetSelectionContainer');
    const targetSelect = document.getElementById('targetSelect');
    
    container.style.display = 'block';
    document.getElementById('targetSelect').setAttribute('required', 'true');
    
    this.loadTargetOptions('product', targetSelect);
  }

  async loadTargetOptions(type, selectElement) {
    selectElement.innerHTML = '<option value="">Loading...</option>';

    try {
      // Determine the API endpoint based on type
      let endpoint;
      let dataKey;
      
      if(type === 'shop') {
        endpoint = '/capstone/backend/api.php?action=list_shops';
        dataKey = 'shops';
      } else if(type === 'product') {
        // If we have a shop context, filter products by that shop
        if (this.currentShopId) {
          endpoint = `/capstone/backend/api.php?action=list_products&shop_id=${this.currentShopId}`;
        } else {
          endpoint = '/capstone/backend/api.php?action=list_products';
        }
        dataKey = 'products';
      } else if(type === 'destination') {
        endpoint = '/capstone/backend/api.php?action=list_destinations';
        dataKey = 'destinations';
      } else if(type === 'hotel') {
        endpoint = '/capstone/backend/api.php?action=list_hotels';
        dataKey = 'hotels';
      } else {
        return;
      }

      const response = await fetch(endpoint);
      if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`);
      }
      const data = await response.json();

      selectElement.innerHTML = '<option value="">-- Select an item --</option>';

      // Try multiple possible data keys
      const items = data[dataKey] || data.data || data[type] || [];
      
      if(Array.isArray(items) && items.length > 0) {
        items.forEach(item => {
          const option = document.createElement('option');
          option.value = item.id;
          option.textContent = item.name || item.title || `Item ${item.id}`;
          selectElement.appendChild(option);
        });
      } else {
        console.warn(`No items found for ${type}. Data:`, data);
        selectElement.innerHTML += '<option disabled>No items available</option>';
      }
    } catch(e) {
      console.error('Error loading options for', type, ':', e);
      selectElement.innerHTML += '<option disabled>Error loading items</option>';
    }
  }

  async submitFeedback(event) {
    event.preventDefault();

    const rating = document.getElementById('ratingValue').value;
    if(rating === '0') {
      alert('Please select a rating before submitting');
      return;
    }

    const form = document.getElementById('feedbackForm');
    const feedbackType = document.getElementById('feedbackTypeSelect').value;
    
    // Get the target ID and name based on feedback type
    let targetId, targetName;
    
    if(feedbackType === 'shop') {
      // Shop type - get from targetSelect dropdown
      targetId = document.getElementById('targetSelect').value;
      if (!targetId) {
        alert('Please select a shop');
        return;
      }
      targetName = document.getElementById('targetSelect').options[document.getElementById('targetSelect').selectedIndex]?.text || 'Unknown Shop';
    } else if(feedbackType === 'product') {
      // Product type - get from targetSelect dropdown
      targetId = document.getElementById('targetSelect').value;
      if (!targetId) {
        alert('Please select a product');
        return;
      }
      targetName = document.getElementById('targetSelect').options[document.getElementById('targetSelect').selectedIndex]?.text || 'Unknown Product';
    } else {
      targetId = 'general';
      targetName = 'General';
    }

    const submitBtn = document.getElementById('submitFeedbackBtn');
    submitBtn.disabled = true;
    submitBtn.textContent = '⏳ Submitting...';

    try {
      const payload = {
        user_name: document.getElementById('guestName').value,
        user_email: document.getElementById('guestEmail').value,
        target_type: feedbackType,
        target_id: targetId,
        target_name: targetName,
        rating: parseInt(rating),
        message: document.getElementById('feedbackMessage').value,
        anonymous: document.getElementById('anonymousCheckbox').checked ? 1 : 0,
        feedback_type: 'review'
      };

      const response = await fetch('../backend/ratings_api.php?action=submit_rating', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
      });

      const result = await response.json();

      if(result.success) {
        console.log('✅ Feedback submitted successfully');
        
        // Show success message
        document.getElementById('feedbackForm').style.display = 'none';
        document.getElementById('feedbackSuccessMessage').style.display = 'block';
        
        // Refresh the page to show new feedback immediately
        console.log('🔄 Reloading page to show new feedback...');
        setTimeout(() => {
          location.reload();
        }, 2000);
      } else {
        alert('Error: ' + (result.error || 'Failed to submit feedback'));
        submitBtn.disabled = false;
        submitBtn.textContent = '📤 Submit Feedback';
      }
    } catch(e) {
      console.error('Error submitting feedback:', e);
      alert('Error submitting feedback: ' + e.message);
      submitBtn.disabled = false;
      submitBtn.textContent = '📤 Submit Feedback';
    }
  }

  resetForm() {
    document.getElementById('feedbackForm').reset();
    document.getElementById('ratingValue').value = '0';
    document.getElementById('ratingText').textContent = 'Select a rating';
    this.updateStarDisplay('0');
    document.getElementById('targetSelectionContainer').style.display = 'none';
    document.getElementById('shopSelectionContainer').style.display = 'none';
    document.getElementById('feedbackForm').style.display = 'block';
    document.getElementById('feedbackSuccessMessage').style.display = 'none';
    
    // Reset shop context
    this.currentShopId = null;
    
    // Restore feedback type selector to default state
    const feedbackTypeSelect = document.getElementById('feedbackTypeSelect');
    feedbackTypeSelect.value = '';
    feedbackTypeSelect.style.opacity = '1';
    feedbackTypeSelect.style.cursor = 'pointer';
    feedbackTypeSelect.disabled = false;
    
    // Restore label
    const typeLabel = feedbackTypeSelect.previousElementSibling;
    if (typeLabel) {
      typeLabel.innerHTML = '📝 Feedback Type *';
    }
  }
}

/**
 * Guest Ratings Display Functions
 * Handles the Guest Ratings & Reviews section on the frontend


/**
 * Shop Ratings Manager
 * Handles displaying ratings for shops and products in modals
 */
window.shopRatingsManager = {
  
  // Get rating statistics for a target (shop/product)
  getRatingStats: async function(targetType, targetId) {
    try {
      const url = `/capstone/backend/ratings_api.php?action=get_rating_stats&target_type=${targetType}&target_id=${targetId}`;
      console.log('📡 Fetching rating stats from:', url);
      const response = await fetch(url);
      if (!response.ok) throw new Error(`HTTP ${response.status}`);
      const data = await response.json();
      console.log('📦 Get rating stats response:', data);
      return data || null;
    } catch (error) {
      console.error('❌ Error fetching stats:', error);
      return null;
    }
  },

  // Get all ratings for a target
  getShopRatings: async function(targetId) {
    try {
      const url = `/capstone/backend/ratings_api.php?action=get_target_ratings&target_type=shop&target_id=${targetId}`;
      console.log('📡 Fetching shop ratings from:', url);
      const response = await fetch(url);
      if (!response.ok) throw new Error(`HTTP ${response.status}`);
      const data = await response.json();
      console.log('📦 Get shop ratings response:', data);
      return Array.isArray(data) ? data : (data.reviews || []);
    } catch (error) {
      console.error('❌ Error fetching ratings:', error);
      return [];
    }
  },

  // Create rating display HTML
  createRatingDisplay: function(stats) {
    if (!stats) {
      return '<div style="text-align:center; padding:20px; background:#f9fafb; border-radius:10px;"><p style="color:#666; margin:0; font-size:14px;">No ratings yet</p></div>';
    }

    const avgRating = parseFloat(stats.average_rating || 0).toFixed(1);
    const totalReviews = stats.total_reviews || 0;
    const stars = '⭐'.repeat(Math.round(avgRating)) + '☆'.repeat(5 - Math.round(avgRating));

    return `
      <div style="background: #f8f9ff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 16px; text-align: center;">
        <div style="font-size: 2.2rem; font-weight: 800; color: #667eea; margin-bottom: 4px;">${avgRating}</div>
        <div style="color: #fbbf24; font-size: 1rem; margin-bottom: 8px;">${stars}</div>
        <div style="color: #64748b; font-size: 0.85rem;">Based on ${totalReviews} ${totalReviews === 1 ? 'review' : 'reviews'}</div>
      </div>
    `;
  },

  // Create feedback card HTML
  createFeedbackCard: function(feedback) {
    const date = new Date(feedback.created_at).toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
    const name = feedback.anonymous === 1 ? 'Anonymous' : (feedback.user_name || 'Guest');
    const stars = '⭐'.repeat(feedback.rating) + '☆'.repeat(5 - feedback.rating);

    return `
      <div style="padding: 12px; background: white; border-left: 4px solid #667eea; border-radius: 6px; margin-bottom: 10px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">
          <strong style="color: #1f2937; font-size: 0.9rem;">${name}</strong>
          <span style="color: #999; font-size: 0.8rem;">${date}</span>
        </div>
        <div style="color: #fbbf24; font-size: 0.9rem; margin-bottom: 6px;">${stars}</div>
        <p style="color: #64748b; margin: 0; font-size: 0.85rem; line-height: 1.4;">${feedback.message || 'No message'}</p>
      </div>
    `;
  },

  // Open shop feedback form
  openShopFeedbackForm: function(shopId, shopName) {
    console.log('📝 Opening shop feedback form for shop:', shopId, shopName);
    if (window.feedbackSystem) {
      window.feedbackSystem.openModal();
      // Pre-populate form
      document.getElementById('feedbackTypeSelect').value = 'shop';
      console.log('🔄 Updating target options...');
      window.feedbackSystem.updateTargetOptions();
      
      // Wait longer for dropdown to load then select the shop
      setTimeout(() => {
        const targetSelect = document.getElementById('targetSelect');
        console.log('🏪 Available options:', Array.from(targetSelect.options).map(o => ({ value: o.value, text: o.text })));
        console.log('✅ Setting shop ID to:', shopId);
        targetSelect.value = shopId;
        
        // Verify it was set
        console.log('🔍 Verified shop ID:', targetSelect.value);
        if (targetSelect.value !== shopId) {
          console.warn('⚠️ Shop ID not found in dropdown! Looking for similar...');
          // Try to find by shop name
          for (let option of targetSelect.options) {
            if (option.text.includes(shopName) || option.value == shopId) {
              option.selected = true;
              console.log('✅ Found by name:', option.text);
              break;
            }
          }
        }
      }, 300);
    }
  },

  // Open product feedback form
  openProductFeedbackForm: function(productId, productName, shopId) {
    console.log('📝 Opening product feedback form for product:', productId, 'in shop:', shopId);
    if (window.feedbackSystem) {
      window.feedbackSystem.openModal();
      // Pre-populate form
      document.getElementById('feedbackTypeSelect').value = 'product';
      console.log('🔄 Updating target options...');
      window.feedbackSystem.updateTargetOptions();
      setTimeout(() => {
        if (shopId) {
          const shopSelect = document.getElementById('shopSelect');
          console.log('🏪 Available shops:', Array.from(shopSelect.options).map(o => ({ value: o.value, text: o.text })));
          console.log('✅ Setting shop ID to:', shopId);
          shopSelect.value = shopId;
          window.feedbackSystem.loadProductsByShop();
          setTimeout(() => {
            const productSelect = document.getElementById('targetSelect');
            console.log('🛍️ Setting product ID to:', productId);
            productSelect.value = productId;
          }, 300);
        }
      }, 300);
    }
  }
};

// Initialize when DOM is ready
if(document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', () => {
    window.feedbackSystem = new FeedbackSystem();
  });
} else {
  window.feedbackSystem = new FeedbackSystem();
}

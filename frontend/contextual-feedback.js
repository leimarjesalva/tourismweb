/**
 * Context-Aware Feedback & Ratings System
 * Provides feedback capabilities for:
 * - Shops & Products
 * - Destinations & Hotels
 * - Integrated into existing sections
 */

function maskText(text) {
  if (!text || text.length <= 2) return '***';
  const show = Math.min(2, Math.floor(text.length / 2));
  return text.substring(0, show) + '*'.repeat(text.length - show);
}

class ContextualFeedbackSystem {
  constructor() {
    this.currentContext = null;
    this.currentItemId = null;
    this.currentItemName = null;
  }

  // ============= INITIALIZE FOR EACH CONTEXT =============
  initShopFeedback(shopId, shopName) {
    this.currentContext = 'shop';
    this.currentItemId = shopId;
    this.currentItemName = shopName;
    this.loadAndDisplayFeedback();
  }

  initProductFeedback(productId, productName, shopId) {
    this.currentContext = 'product';
    this.currentItemId = productId;
    this.currentItemName = productName;
    this.shopId = shopId;
    this.loadAndDisplayFeedback();
  }

  initDestinationFeedback(destinationName, triggerElement, showForm = false) {
    this.currentContext = 'destination';
    this.currentItemId = destinationName;
    this.currentItemName = destinationName;
    this.showFeedbackModal(showForm);
  }

  initHotelFeedback(hotelName, triggerElement, showForm = false) {
    this.currentContext = 'hotel';
    this.currentItemId = hotelName;
    this.currentItemName = hotelName;
    this.showFeedbackModal(showForm);
  }

  // ============= SHOW FEEDBACK MODAL =============
  async showFeedbackModal(showForm = false) {
    if(!this.currentContext || !this.currentItemId) return;

    // Create modal if it doesn't exist
    let modal = document.getElementById('contextualFeedbackModal');
    if(!modal) {
      modal = document.createElement('div');
      modal.id = 'contextualFeedbackModal';
      modal.style.cssText = `
        position: fixed; top: 0; left: 0; width: 100%; height: 100%;
        background: rgba(0,0,0,0.7); z-index: 10000; display: flex;
        align-items: center; justify-content: center; padding: 20px;
      `;
      modal.innerHTML = `
        <div style="background: white; border-radius: 16px; max-width: 600px; width: 100%; max-height: 80vh; overflow-y: auto; position: relative;">
          <div style="padding: 24px; border-bottom: 1px solid #e5e7eb;">
            <div style="display: flex; justify-content: space-between; align-items: center;">
              <h3 id="feedbackModalTitle" style="margin: 0; color: #1f2937; font-size: 20px; font-weight: 700;">
                Loading...
              </h3>
              <button onclick="document.getElementById('contextualFeedbackModal').style.display='none'; document.body.style.overflow='auto';" style="background: none; border: none; font-size: 24px; cursor: pointer; color: #6b7280;">&times;</button>
            </div>
          </div>
          <div id="feedbackModalContent" style="padding: 24px;">
            <div style="text-align: center; padding: 40px;">
              <div style="font-size: 24px;">⏳</div>
              <p style="color: #6b7280; margin: 10px 0 0 0;">Loading feedback...</p>
            </div>
          </div>
        </div>
      `;
      document.body.appendChild(modal);

      // Close modal when clicking outside (attach once)
      modal.addEventListener('click', function(e) {
        if(e.target === modal) {
          modal.style.display = 'none';
          document.body.style.overflow = 'auto';
        }
      });
    }

    // Show modal and prevent body scroll
    modal.style.display = 'flex';
    document.body.style.overflow = 'hidden';

    // Update modal title
    const titleEl = document.getElementById('feedbackModalTitle');
    if(titleEl) {
      const icon = this.currentContext === 'destination' ? '🏔️' : '🏨';
      titleEl.innerHTML = `${icon} ${this.currentItemName}`;
    }

    // Close modal on Escape key
    const escapeHandler = function(e) {
      if(e.key === 'Escape') {
        modal.style.display = 'none';
        document.body.style.overflow = 'auto';
        document.removeEventListener('keydown', escapeHandler);
      }
    };
    document.addEventListener('keydown', escapeHandler);

    // Load feedback data
    try {
      const response = await fetch(
        `../backend/ratings_api.php?action=get_target_ratings&target_type=${this.currentContext}&target_id=${this.currentItemId}`
      );
      const data = await response.json();

      const content = document.getElementById('feedbackModalContent');
      if(content && data.success) {
        content.innerHTML = `
          <div id="${this.currentContext}FeedbackStats"></div>
          <div id="${this.currentContext}ReviewsList"></div>
          ${showForm ? this.createFeedbackFormHTML(this.currentContext, this.currentItemId, this.currentItemName) : ''}
        `;
        this.displayFeedbackStats(data.statistics);
        this.displayReviews(data.reviews);
      } else {
        content.innerHTML = `
          <div style="text-align: center; padding: 40px;">
            <div style="font-size: 24px;">❌</div>
            <p style="color: #ef4444; margin: 10px 0 0 0;">Failed to load feedback</p>
          </div>
        `;
      }
    } catch(e) {
      console.error('Error loading feedback:', e);
      const content = document.getElementById('feedbackModalContent');
      if(content) {
        content.innerHTML = `
          <div style="text-align: center; padding: 40px;">
            <div style="font-size: 24px;">❌</div>
            <p style="color: #ef4444; margin: 10px 0 0 0;">Error loading feedback</p>
          </div>
        `;
      }
    }
  }

  // ============= LOAD & DISPLAY FEEDBACK =============
  async loadAndDisplayFeedback() {
    if(!this.currentContext || !this.currentItemId) return;

    try {
      const response = await fetch(
        `../backend/ratings_api.php?action=get_target_ratings&target_type=${this.currentContext}&target_id=${this.currentItemId}`
      );
      const data = await response.json();

      if(data.success) {
        this.displayFeedbackStats(data.statistics);
        this.displayReviews(data.reviews);
      }
    } catch(e) {
      console.error('Error loading feedback:', e);
    }
  }

  displayFeedbackStats(stats) {
    const container = document.getElementById(`${this.currentContext}FeedbackStats`);
    if(!container) return;

    const avgRating = parseFloat(stats.average_rating) || 0;
    const totalReviews = stats.total_reviews || 0;

    container.innerHTML = `
      <div style="background: linear-gradient(135deg, rgba(255,122,24,0.1) 0%, rgba(102,126,234,0.1) 100%); 
                  padding: 16px 20px; border-radius: 12px; border: 1px solid rgba(102,126,234,0.2); margin-bottom: 20px;">
        <div style="display: flex; gap: 24px; align-items: center; flex-wrap: wrap;">
          <div style="display: flex; align-items: center; gap: 12px;">
            <div style="font-size: 32px; color: #f59e0b;">⭐</div>
            <div>
              <div style="font-size: 22px; font-weight: 900; color: #ff7a18;">${avgRating.toFixed(1)}/5</div>
              <div style="font-size: 12px; color: #666; margin-top: 2px;">Average Rating</div>
            </div>
          </div>
          
          <div style="border-left: 2px solid rgba(102,126,234,0.3); padding-left: 20px;">
            <div style="font-size: 20px; font-weight: 700; color: #1a237e;">${totalReviews}</div>
            <div style="font-size: 12px; color: #666;">Total Reviews</div>
          </div>
          
          <div style="border-left: 2px solid rgba(102,126,234,0.3); padding-left: 20px;">
            <div style="display: flex; gap: 8px; align-items: center;">
              <span style="font-weight: 700; color: #27ae60;">${stats.positive_reviews || 0}</span>
              <span style="color: #666; font-size: 12px;">Positive</span>
            </div>
            <div style="display: flex; gap: 8px; align-items: center; margin-top: 4px;">
              <span style="font-weight: 700; color: #e74c3c;">${stats.negative_reviews || 0}</span>
              <span style="color: #666; font-size: 12px;">Negative</span>
            </div>
          </div>
        </div>
      </div>
    `;
  }

  displayReviews(reviews) {
    const container = document.getElementById(`${this.currentContext}ReviewsList`);
    if(!container) return;

    if(!reviews || reviews.length === 0) {
      container.innerHTML = `
        <div style="text-align: center; padding: 30px 20px; color: #999;">
          <div style="font-size: 32px; margin-bottom: 10px;">💬</div>
          <p>No reviews yet. Be the first to share your experience!</p>
        </div>
      `;
      return;
    }

    container.innerHTML = reviews.map(review => {
      let displayName = review.user_name || 'Anonymous';
      if(review.anonymous || displayName === 'Anonymous') {
        displayName = maskText(displayName);
      }
      return `
      <div style="background: #f9f9f9; padding: 16px; border-radius: 10px; border-left: 4px solid #ff7a18; 
                  margin-bottom: 12px; box-shadow: 0 2px 6px rgba(0,0,0,0.04);">
        <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 10px;">
          <div>
            <div style="font-weight: 700; color: #1a237e; font-size: 14px;">
              ${displayName} ${review.anonymous ? '🔒' : ''}
            </div>
            <div style="font-size: 12px; color: #999; margin-top: 2px;">
              ${new Date(review.created_at).toLocaleDateString()}
            </div>
          </div>
          <div style="font-size: 18px; color: #f59e0b;">
            ${'⭐'.repeat(parseInt(review.rating) || 0)}${'☆'.repeat(5 - (parseInt(review.rating) || 0))}
          </div>
        </div>
        <p style="margin: 0; color: #555; font-size: 13px; line-height: 1.6;">${review.message}</p>
        ${review.image_url ? `<div style="margin-top: 10px;"><img src="${review.image_url}" alt="Review photo" style="max-width: 100%; max-height: 220px; border-radius: 8px; border: 1px solid #e5e7eb; object-fit: cover; cursor: pointer;" onclick="window.open(this.src, '_blank')" onerror="this.style.display='none'"></div>` : ''}
      </div>
    `}).join('');
  }

  // ============= SUBMIT FEEDBACK =============
  async submitFeedback(name, email, rating, message, anonymous = false, imageFile = null) {
    if(!this.currentContext || !this.currentItemId) {
      alert('Error: Context not set');
      return false;
    }

    if(rating < 1 || rating > 5) {
      alert('Please select a rating');
      return false;
    }

    if(!message.trim()) {
      alert('Please write a review message');
      return false;
    }

    try {
      // Upload image first if provided
      let imageUrl = null;
      if(imageFile) {
        const formData = new FormData();
        formData.append('file', imageFile);
        const uploadRes = await fetch('../backend/upload_feedback_image.php', {
          method: 'POST',
          body: formData
        });
        const uploadData = await uploadRes.json();
        if(uploadData.success) {
          imageUrl = uploadData.path;
        } else {
          alert('Image upload failed: ' + (uploadData.error || 'Unknown error'));
          return false;
        }
      }

      const payload = {
        user_name: name,
        user_email: email,
        target_type: this.currentContext,
        target_id: this.currentItemId,
        target_name: this.currentItemName,
        rating: parseInt(rating),
        message: message.trim(),
        anonymous: anonymous ? 1 : 0,
        feedback_type: 'review',
        image_url: imageUrl
      };

      const response = await fetch('../backend/ratings_api.php?action=submit_rating', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
      });

      const result = await response.json();

      if(result.success) {
        // Reload feedback
        await this.loadAndDisplayFeedback();
        return true;
      } else {
        alert('Error: ' + (result.error || 'Failed to submit'));
        return false;
      }
    } catch(e) {
      console.error('Error submitting feedback:', e);
      alert('Error submitting feedback');
      return false;
    }
  }

  // ============= CREATE FEEDBACK FORM HTML =============
  createFeedbackFormHTML(contextType, itemId, itemName) {
    // Create a safe ID slug from itemId (remove special chars, spaces)
    const safeId = String(itemId).replace(/[^a-zA-Z0-9]/g, '_');
    return `
      <div style="background: white; padding: 20px; border-radius: 12px; margin-top: 20px; border: 2px solid #ecf0f1;">
        <h4 style="margin: 0 0 16px 0; color: #1a237e; font-weight: 700; font-size: 15px;">
          ✍️ Share Your Review for "${itemName}"
        </h4>
        
        <form id="feedback-form-${contextType}-${safeId}" data-context="${contextType}" data-itemid="${itemId.replace(/"/g, '&quot;')}" data-itemname="${itemName.replace(/"/g, '&quot;')}" onsubmit="event.preventDefault(); submitContextFeedbackSafe(this);">
          
          <!-- Rating -->
          <div style="margin-bottom: 14px;">
            <label style="display: block; font-weight: 700; color: #333; font-size: 13px; margin-bottom: 8px;">Rating *</label>
            <div id="rating-stars-${contextType}-${safeId}" style="display: flex; gap: 8px; font-size: 28px; cursor: pointer;">
              ${Array(5).fill(0).map((_, i) => `
                <span class="rating-star" data-value="${i+1}" style="opacity: 0.3; transition: all 0.2s; cursor: pointer;">★</span>
              `).join('')}
            </div>
            <input type="hidden" id="rating-value-${contextType}-${safeId}" name="rating" value="0" required>
          </div>

          <!-- Name & Email -->
          <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 14px;">
            <div>
              <label style="display: block; font-weight: 700; color: #333; font-size: 13px; margin-bottom: 6px;">Your Name *</label>
              <input type="text" placeholder="Your name" required style="width: 100%; padding: 10px 12px; border: 1px solid #ddd; border-radius: 6px; font-size: 13px;">
            </div>
            <div>
              <label style="display: block; font-weight: 700; color: #333; font-size: 13px; margin-bottom: 6px;">Email *</label>
              <input type="email" placeholder="your@email.com" required style="width: 100%; padding: 10px 12px; border: 1px solid #ddd; border-radius: 6px; font-size: 13px;">
            </div>
          </div>

          <!-- Message -->
          <div style="margin-bottom: 14px;">
            <label style="display: block; font-weight: 700; color: #333; font-size: 13px; margin-bottom: 6px;">Review Message *</label>
            <textarea placeholder="Share your experience..." required rows="3" style="width: 100%; padding: 10px 12px; border: 1px solid #ddd; border-radius: 6px; font-size: 13px; resize: vertical;"></textarea>
          </div>

          <!-- Image Upload -->
          <div style="margin-bottom: 14px;">
            <label style="display: block; font-weight: 700; color: #333; font-size: 13px; margin-bottom: 6px;">📷 Upload Photo (optional)</label>
            <label style="display: inline-flex; align-items: center; gap: 8px; padding: 10px 16px; background: #f3f4f6; border: 2px dashed #d1d5db; border-radius: 8px; cursor: pointer; font-size: 13px; color: #6b7280; transition: all 0.2s;" onmouseover="this.style.borderColor='#6366f1'; this.style.background='#eef2ff'" onmouseout="this.style.borderColor='#d1d5db'; this.style.background='#f3f4f6'">
              📸 Choose Image
              <input type="file" accept="image/jpeg,image/png,image/webp,image/gif" style="display: none;" onchange="previewFeedbackImage(this)">
            </label>
            <div class="feedback-image-preview" style="margin-top: 8px; display: none;">
              <div style="position: relative; display: inline-block;">
                <img src="" style="max-width: 200px; max-height: 150px; border-radius: 8px; border: 1px solid #e5e7eb; object-fit: cover;">
                <button type="button" onclick="removeFeedbackImage(this)" style="position: absolute; top: -6px; right: -6px; width: 22px; height: 22px; border-radius: 50%; background: #ef4444; color: white; border: none; font-size: 14px; cursor: pointer; display: flex; align-items: center; justify-content: center; line-height: 1;">×</button>
              </div>
            </div>
          </div>

          <!-- Anonymous -->
          <div style="margin-bottom: 14px; display: flex; align-items: center; gap: 8px;">
            <input type="checkbox" id="anonymous-${contextType}-${safeId}" style="width: 16px; height: 16px; cursor: pointer;">
            <label for="anonymous-${contextType}-${safeId}" style="cursor: pointer; font-size: 13px; color: #666;">Submit anonymously</label>
          </div>

          <!-- Submit -->
          <button type="submit" style="padding: 10px 20px; background: #667eea; color: white; border: none; border-radius: 6px; font-weight: 700; cursor: pointer; font-size: 13px; width: 100%;">
            📤 Submit Review
          </button>
        </form>
      </div>
    `;
  }
}

// Global instance
window.contextualFeedback = new ContextualFeedbackSystem();

// ============= SUBMIT FEEDBACK HANDLER =============
async function submitContextFeedbackSafe(formEl) {
  const contextType = formEl.dataset.context;
  const itemId = formEl.dataset.itemid;
  const itemName = formEl.dataset.itemname;

  const inputs = formEl.querySelectorAll('input, textarea');
  const ratingInput = formEl.querySelector('input[name="rating"]');
  const nameInput = inputs[0]; // first text input after hidden rating
  const emailInput = inputs[1];
  const messageInput = formEl.querySelector('textarea');
  const anonymousCheck = formEl.querySelector('input[type="checkbox"]');

  // Find the actual text inputs (skip hidden)
  const textInputs = formEl.querySelectorAll('input[type="text"], input[type="email"]');
  const nameVal = textInputs[0] ? textInputs[0].value.trim() : '';
  const emailVal = textInputs[1] ? textInputs[1].value.trim() : '';

  const rating = parseInt(ratingInput ? ratingInput.value : 0);
  const message = messageInput ? messageInput.value.trim() : '';
  const anonymous = anonymousCheck ? anonymousCheck.checked : false;

  if(!rating || rating < 1 || rating > 5) {
    alert('Please select a rating');
    return;
  }

  if(!anonymous && (!nameVal || !emailVal)) {
    alert('Please fill in your name and email, or check "Submit anonymously"');
    return;
  }

  if(!message) {
    alert('Please write a review message');
    return;
  }

  const btn = formEl.querySelector('button[type="submit"]');
  btn.disabled = true;
  btn.textContent = '⏳ Submitting...';

  // Set context on the feedback system
  window.contextualFeedback.currentContext = contextType;
  window.contextualFeedback.currentItemId = itemId;
  window.contextualFeedback.currentItemName = itemName;

  // Get selected image file if any
  const fileInput = formEl.querySelector('input[type="file"]');
  const imageFile = fileInput && fileInput.files.length > 0 ? fileInput.files[0] : null;

  const success = await window.contextualFeedback.submitFeedback(
    anonymous ? 'Anonymous' : nameVal,
    anonymous ? '' : emailVal,
    rating,
    message,
    anonymous,
    imageFile
  );

  btn.disabled = false;
  btn.textContent = '📤 Submit Review';

  if(success) {
    formEl.reset();
    if(ratingInput) ratingInput.value = '0';
    formEl.querySelectorAll('.rating-star').forEach(s => {
      s.style.opacity = '0.3';
      s.style.color = 'inherit';
    });
    alert('✅ Thank you for your review!');
  }
}

// Legacy handler (kept for backward compatibility)
async function submitContextFeedback(contextType, itemId, itemName) {
  const form = document.getElementById(`feedback-form-${contextType}-${itemId}`);
  if(!form) return;

  const inputs = form.querySelectorAll('input, textarea');
  const nameInput = inputs[0];
  const emailInput = inputs[1];
  const messageInput = inputs[2];
  const anonymousCheck = inputs[3];
  const ratingInput = document.getElementById(`rating-value-${contextType}-${itemId}`);

  const rating = parseInt(ratingInput.value);
  const name = nameInput.value.trim();
  const email = emailInput.value.trim();
  const message = messageInput.value.trim();
  const anonymous = anonymousCheck.checked;

  if(!rating || rating < 1 || rating > 5) {
    alert('Please select a rating');
    return;
  }

  if(!anonymous && (!name || !email || !message)) {
    alert('Please fill in all required fields');
    return;
  }
  if(!message) {
    alert('Please write a review message');
    return;
  }

  const btn = form.querySelector('button[type="submit"]');
  btn.disabled = true;
  btn.textContent = '⏳ Submitting...';

  const success = await window.contextualFeedback.submitFeedback(
    anonymous ? 'Anonymous' : name,
    anonymous ? '' : email,
    rating,
    message,
    anonymous
  );

  btn.disabled = false;
  btn.textContent = '📤 Submit Review';

  if(success) {
    form.reset();
    ratingInput.value = '0';
    document.getElementById(`rating-stars-${contextType}-${itemId}`).querySelectorAll('.rating-star').forEach(s => {
      s.style.opacity = '0.3';
    });
    alert('✅ Thank you for your review!');
  }
}

// ============= STAR RATING INTERACTION =============
document.addEventListener('click', function(e) {
  if(e.target.classList.contains('rating-star')) {
    const value = e.target.getAttribute('data-value');
    const container = e.target.parentElement;
    const input = container.nextElementSibling;

    // Update visual
    container.querySelectorAll('.rating-star').forEach((star, idx) => {
      star.style.opacity = idx < value ? '1' : '0.3';
      if(idx < value) star.style.color = '#fbbf24';
      else star.style.color = 'inherit';
    });

    // Update hidden input
    input.value = value;
  }
});

// Hover effect for stars
document.addEventListener('mouseover', function(e) {
  if(e.target.classList.contains('rating-star')) {
    const value = e.target.getAttribute('data-value');
    const container = e.target.parentElement;
    container.querySelectorAll('.rating-star').forEach((star, idx) => {
      star.style.opacity = idx < value ? '1' : '0.3';
      star.style.color = idx < value ? '#fbbf24' : 'inherit';
    });
  }
});

document.addEventListener('mouseout', function(e) {
  if(e.target.classList.contains('rating-star')) {
    const container = e.target.parentElement;
    const currentValue = container.nextElementSibling?.value || 0;
    container.querySelectorAll('.rating-star').forEach((star, idx) => {
      star.style.opacity = idx < currentValue ? '1' : '0.3';
    });
  }
});

// ============= IMAGE PREVIEW & REMOVE =============
function previewFeedbackImage(input) {
  const previewDiv = input.closest('div').parentElement.querySelector('.feedback-image-preview');
  if(!previewDiv) return;
  if(input.files && input.files[0]) {
    // Validate size (3MB max)
    if(input.files[0].size > 3 * 1024 * 1024) {
      alert('Image too large. Maximum size is 3MB.');
      input.value = '';
      return;
    }
    const reader = new FileReader();
    reader.onload = function(e) {
      const img = previewDiv.querySelector('img');
      img.src = e.target.result;
      previewDiv.style.display = 'block';
    };
    reader.readAsDataURL(input.files[0]);
  }
}

function removeFeedbackImage(btn) {
  const previewDiv = btn.closest('.feedback-image-preview');
  if(previewDiv) {
    previewDiv.style.display = 'none';
    previewDiv.querySelector('img').src = '';
    const fileInput = previewDiv.closest('form').querySelector('input[type="file"]');
    if(fileInput) fileInput.value = '';
  }
}

// ============= STATISTICS DASHBOARD =============
async function loadFeedbackAnalytics() {
  try {
    const response = await fetch('../backend/ratings_api.php?action=get_rating_stats');
    const data = await response.json();

    if(data.success) {
      // Store for admin dashboard use
      window.feedbackAnalytics = data.statistics;
      return data.statistics;
    }
  } catch(e) {
    console.error('Error loading analytics:', e);
  }
  return [];
}

// Export for use in pages
window.contextualFeedbackSystem = window.contextualFeedback;

/**
 * Shop Feedback Sync & Diagnostics
 * Helps diagnose and fix feedback display issues
 */

class ShopFeedbackSync {
  constructor(apiBase = '/capstone/backend') {
    this.apiBase = apiBase;
  }

  /**
   * Diagnose shop feedback issues
   */
  async diagnose() {
    console.log('🔍 Starting diagnosis...');
    
    try {
      const response = await fetch(`${this.apiBase}/diagnose_shop_ids.php`);
      const data = await response.json();
      
      console.log('📊 === SHOP DIAGNOSIS REPORT ===');
      console.log('Total shops:', data.shops_in_database.length);
      console.log('Recent feedback entries:', data.recent_feedback.length);
      console.log('Mismatches found:', data.mismatches_found);
      
      if (data.mismatches_found > 0) {
        console.warn('⚠️ MISMATCHES DETECTED:');
        data.mismatches.forEach(mismatch => {
          console.warn(`  - Feedback ${mismatch.feedback_id}: references shop ID ${mismatch.stored_target_id} (NOT FOUND)`);
        });
      } else {
        console.log('✅ All feedback properly linked!');
      }
      
      console.log('\n📋 Available shops:');
      data.shops_in_database.forEach(shop => {
        console.log(`  - Shop ID: ${shop.id}, Name: "${shop.name}", Feedback Count: ${shop.feedback_count}`);
      });
      
      return data;
    } catch (error) {
      console.error('❌ Diagnosis error:', error);
      return null;
    }
  }

  /**
   * Fix all mismatches
   */
  async fixAllMismatches() {
    console.log('🔧 Fixing shop ID mismatches...');
    
    try {
      const response = await fetch(`${this.apiBase}/sync_feedback.php?action=sync_feedback`);
      const data = await response.json();
      
      if (data.success) {
        console.log('✅ ' + data.message);
        if (data.errors.length > 0) {
          console.warn('⚠️ Some errors occurred:');
          data.errors.forEach(err => console.warn(`  - ${err}`));
        }
      } else {
        console.error('❌ Fix failed:', data.error);
      }
      
      return data;
    } catch (error) {
      console.error('❌ Fix error:', error);
      return null;
    }
  }

  /**
   * Fix target names to match current shop names
   */
  async fixTargetNames() {
    console.log('🔧 Fixing shop names in feedback...');
    
    try {
      const response = await fetch(`${this.apiBase}/sync_feedback.php?action=fix_shop_references`);
      const data = await response.json();
      
      if (data.success) {
        console.log('✅ ' + data.message);
      } else {
        console.error('❌ Error:', data.error);
      }
      
      return data;
    } catch (error) {
      console.error('❌ Fix error:', error);
      return null;
    }
  }

  /**
   * Verify a specific shop's feedback
   */
  async verifyShop(shopId) {
    console.log('🔍 Verifying shop:', shopId);
    
    try {
      const response = await fetch(`${this.apiBase}/sync_feedback.php?action=verify_shop&shop_id=${shopId}`);
      const data = await response.json();
      
      if (data.success) {
        console.log('✅ Shop: ' + data.shop.name);
        console.log(`   Total Feedback: ${data.feedback_count}`);
        console.log(`   Average Rating: ${parseFloat(data.statistics.avg_rating || 0).toFixed(1)}`);
        console.log(`   Rating Range: ${data.statistics.min_rating} - ${data.statistics.max_rating}`);
        
        if (data.feedback_count > 0) {
          console.log('   Recent feedback:');
          data.feedback.slice(0, 3).forEach(fb => {
            console.log(`     - ${fb.user_name} (${fb.rating}⭐) - ${new Date(fb.created_at).toLocaleDateString()}`);
          });
        }
      } else {
        console.error('❌ Error:', data.error);
      }
      
      return data;
    } catch (error) {
      console.error('❌ Verification error:', error);
      return null;
    }
  }
}

// Initialize globally
const shopSync = new ShopFeedbackSync();

// Expose helper functions to console
window.debugShop = function(shopId) {
  return shopSync.verifyShop(shopId);
};

window.fixShopFeedback = async function() {
  console.log('🔧 Running comprehensive feedback fix...');
  const diagnosis = await shopSync.diagnose();
  if (diagnosis.mismatches_found > 0) {
    await shopSync.fixAllMismatches();
    await shopSync.fixTargetNames();
    setTimeout(() => location.reload(), 1000);
  } else {
    console.log('✅ No fixes needed!');
  }
};

window.diagnoseFeedback = function() {
  return shopSync.diagnose();
};

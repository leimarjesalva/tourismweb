/**
 * NLP Analytics Visualizer
 * Displays sentiment analysis, keyword extraction, and aspect mentions
 * Integrates with Chart.js for visualizations
 */

class NLPAnalytics {
  constructor(apiBase = '/capstone/backend') {
    this.apiBase = apiBase;
  }

  /**
   * Analyze feedback for a specific shop
   */
  async analyzeShop(shopId) {
    try {
      const response = await fetch(
        `${this.apiBase}/nlp_analytics_api.php?action=analyze_shop_feedback&shop_id=${shopId}`
      );
      if (!response.ok) throw new Error('Failed to fetch analysis');
      return await response.json();
    } catch (error) {
      console.error('Error analyzing shop:', error);
      return null;
    }
  }

  /**
   * Analyze all feedback
   */
  async analyzeAllFeedback(filterType = null, limit = 100) {
    try {
      let url = `${this.apiBase}/nlp_analytics_api.php?action=analyze_all_feedback&limit=${limit}`;
      if (filterType) url += `&filter_type=${filterType}`;
      
      const response = await fetch(url);
      if (!response.ok) throw new Error('Failed to fetch analysis');
      return await response.json();
    } catch (error) {
      console.error('Error analyzing feedback:', error);
      return null;
    }
  }

  /**
   * Get comprehensive analytics summary
   */
  async getAnalyticsSummary() {
    try {
      const response = await fetch(
        `${this.apiBase}/nlp_analytics_api.php?action=get_analytics_summary`
      );
      if (!response.ok) throw new Error('Failed to fetch summary');
      return await response.json();
    } catch (error) {
      console.error('Error fetching summary:', error);
      return null;
    }
  }

  /**
   * Create sentiment distribution visualization
   */
  createSentimentChart(containerId, data) {
    const container = document.getElementById(containerId);
    if (!container) return;

    const ctx = container.querySelector('canvas') || this.createCanvas(container);
    
    const sentimentDist = data.sentiment_distribution;
    const total = data.total_feedback;
    
    return new Chart(ctx, {
      type: 'doughnut',
      data: {
        labels: ['😊 Positive', '😐 Neutral', '😞 Negative'],
        datasets: [{
          data: [
            sentimentDist.positive,
            sentimentDist.neutral,
            sentimentDist.negative
          ],
          backgroundColor: ['#10b981', '#f59e0b', '#ef4444'],
          borderColor: ['rgba(16,185,129,0.1)', 'rgba(245,158,11,0.1)', 'rgba(239,68,68,0.1)'],
          borderWidth: 2
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
          legend: {
            position: 'bottom',
            labels: {
              padding: 15,
              font: { size: 12, weight: '600' },
              usePointStyle: true
            }
          },
          tooltip: {
            callbacks: {
              label: function(context) {
                const value = context.parsed;
                const percentage = ((value / total) * 100).toFixed(1);
                return `${context.label}: ${value} (${percentage}%)`;
              }
            }
          }
        }
      }
    });
  }

  /**
   * Create aspect mentions bar chart
   */
  createAspectChart(containerId, aspects) {
    const container = document.getElementById(containerId);
    if (!container) return;

    const ctx = container.querySelector('canvas') || this.createCanvas(container);
    const labels = Object.keys(aspects);
    const values = Object.values(aspects);

    return new Chart(ctx, {
      type: 'bar',
      data: {
        labels: labels.map(l => this.capitalizeFirst(l)),
        datasets: [{
          label: 'Mentions',
          data: values,
          backgroundColor: '#667eea',
          borderColor: 'rgba(102,126,234,0.2)',
          borderWidth: 1,
          borderRadius: 6
        }]
      },
      options: {
        indexAxis: 'y',
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
          legend: { display: false }
        },
        scales: {
          x: {
            beginAtZero: true,
            ticks: { stepSize: 1 }
          }
        }
      }
    });
  }

  /**
   * Create keyword cloud visualization
   */
  createKeywordCloud(containerId, keywords) {
    const container = document.getElementById(containerId);
    if (!container) return;

    container.innerHTML = '';
    
    if (!keywords || keywords.length === 0) {
      container.innerHTML = '<p style="text-align: center; color: #999;">No keywords found</p>';
      return;
    }

    const maxFreq = Math.max(...keywords.map(k => k.count || k.frequency));
    const minFreq = Math.min(...keywords.map(k => k.count || k.frequency));

    const cloudDiv = document.createElement('div');
    cloudDiv.style.cssText = `
      display: flex;
      flex-wrap: wrap;
      gap: 12px;
      justify-content: center;
      padding: 20px;
    `;

    keywords.forEach(kw => {
      const count = kw.count || kw.frequency;
      const size = 12 + ((count - minFreq) / (maxFreq - minFreq)) * 20;
      const opacity = 0.5 + ((count - minFreq) / (maxFreq - minFreq)) * 0.5;

      const tag = document.createElement('div');
      tag.style.cssText = `
        font-size: ${size}px;
        opacity: ${opacity};
        color: #667eea;
        font-weight: 600;
        padding: 8px 12px;
        background: rgba(102, 126, 234, 0.1);
        border-radius: 20px;
        border: 1px solid rgba(102, 126, 234, 0.2);
        transition: all 0.3s;
        cursor: pointer;
      `;
      
      tag.textContent = kw.keyword;
      tag.onmouseover = function() {
        this.style.background = 'rgba(102, 126, 234, 0.2)';
        this.style.transform = 'scale(1.1)';
      };
      tag.onmouseout = function() {
        this.style.background = 'rgba(102, 126, 234, 0.1)';
        this.style.transform = 'scale(1)';
      };

      cloudDiv.appendChild(tag);
    });

    container.appendChild(cloudDiv);
  }

  /**
   * Create sentiment trend over time
   */
  createSentimentTrendChart(containerId, detailedAnalysis) {
    const container = document.getElementById(containerId);
    if (!container || !detailedAnalysis || detailedAnalysis.length === 0) return;

    const ctx = container.querySelector('canvas') || this.createCanvas(container);
    
    // Group by date
    const dateGroups = {};
    detailedAnalysis.forEach(item => {
      const date = new Date(item.created_at).toLocaleDateString('en-US', {
        month: 'short',
        day: 'numeric'
      });
      
      if (!dateGroups[date]) {
        dateGroups[date] = { positive: 0, neutral: 0, negative: 0 };
      }
      dateGroups[date][item.sentiment]++;
    });

    const dates = Object.keys(dateGroups);
    const positiveData = dates.map(d => dateGroups[d].positive);
    const neutralData = dates.map(d => dateGroups[d].neutral);
    const negativeData = dates.map(d => dateGroups[d].negative);

    return new Chart(ctx, {
      type: 'line',
      data: {
        labels: dates,
        datasets: [
          {
            label: '😊 Positive',
            data: positiveData,
            borderColor: '#10b981',
            backgroundColor: 'rgba(16,185,129,0.1)',
            fill: true,
            tension: 0.4
          },
          {
            label: '😐 Neutral',
            data: neutralData,
            borderColor: '#f59e0b',
            backgroundColor: 'rgba(245,158,11,0.1)',
            fill: true,
            tension: 0.4
          },
          {
            label: '😞 Negative',
            data: negativeData,
            borderColor: '#ef4444',
            backgroundColor: 'rgba(239,68,68,0.1)',
            fill: true,
            tension: 0.4
          }
        ]
      },
      options: {
        responsive: true,
        interaction: { mode: 'index', intersect: false },
        plugins: {
          legend: {
            position: 'top',
            labels: { padding: 15, font: { weight: '600' } }
          }
        },
        scales: {
          y: { beginAtZero: true }
        }
      }
    });
  }

  /**
   * Display detailed feedback analysis
   */
  renderFeedbackAnalysis(containerId, detailedAnalysis) {
    const container = document.getElementById(containerId);
    if (!container) return;

    const html = detailedAnalysis.map((item, idx) => {
      const sentimentColor = {
        positive: '#10b981',
        neutral: '#f59e0b',
        negative: '#ef4444'
      }[item.sentiment] || '#666';

      const sentimentEmoji = {
        positive: '😊',
        neutral: '😐',
        negative: '😞'
      }[item.sentiment] || '😮';

      return `
        <div style="
          background: white;
          border: 1px solid #e2e8f0;
          border-left: 4px solid ${sentimentColor};
          border-radius: 8px;
          padding: 16px;
          margin-bottom: 12px;
          transition: all 0.3s;
        " onmouseover="this.style.boxShadow='0 4px 12px rgba(0,0,0,0.1)'" onmouseout="this.style.boxShadow='none'">
          <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 8px;">
            <div>
              <span style="font-weight: 700; color: #0f172a;">${item.user_name || 'Anonymous'}</span>
              <span style="color: #999; font-size: 0.85rem; margin-left: 8px;">${new Date(item.created_at).toLocaleDateString()}</span>
            </div>
            <div style="text-align: right;">
              <div style="font-size: 24px; margin-right: 8px;">${sentimentEmoji}</div>
              <div style="color: ${sentimentColor}; font-weight: 700; font-size: 0.9rem;">${this.capitalizeFirst(item.sentiment)}</div>
              <div style="color: #999; font-size: 0.8rem;">Score: ${item.sentiment_score}</div>
            </div>
          </div>
          
          <div style="background: rgba(248,249,255,0.5); padding: 12px; border-radius: 6px; margin-bottom: 8px; border-left: 3px solid ${sentimentColor};">
            <p style="margin: 0; color: #555; font-size: 0.9rem; line-height: 1.5;">"${item.message}"</p>
          </div>
          
          ${item.aspects && item.aspects.length > 0 ? `
            <div style="margin-bottom: 8px;">
              <span style="font-size: 0.75rem; color: #999; font-weight: 600;">ASPECTS:</span>
              <div style="display: flex; flex-wrap: wrap; gap: 6px; margin-top: 4px;">
                ${item.aspects.map(asp => `<span style="background: rgba(102,126,234,0.1); color: #667eea; padding: 2px 8px; border-radius: 4px; font-size: 0.8rem;">${asp}</span>`).join('')}
              </div>
            </div>
          ` : ''}
          
          ${item.keywords && item.keywords.length > 0 ? `
            <div>
              <span style="font-size: 0.75rem; color: #999; font-weight: 600;">KEY TERMS:</span>
              <div style="display: flex; flex-wrap: wrap; gap: 6px; margin-top: 4px;">
                ${item.keywords.slice(0, 3).map(kw => `<span style="background: rgba(107,114,128,0.1); color: #666; padding: 2px 8px; border-radius: 4px; font-size: 0.8rem;">${kw.keyword}</span>`).join('')}
              </div>
            </div>
          ` : ''}
        </div>
      `;
    }).join('');

    container.innerHTML = html;
  }

  /**
   * Helper: Create canvas element
   */
  createCanvas(container) {
    const canvas = document.createElement('canvas');
    container.appendChild(canvas);
    return canvas;
  }

  /**
   * Helper: Capitalize first letter
   */
  capitalizeFirst(str) {
    return str ? str.charAt(0).toUpperCase() + str.slice(1).toLowerCase() : '';
  }

  /**
   * Generate comprehensive dashboard
   */
  async generateDashboard(containerId, shopId = null) {
    const container = document.getElementById(containerId);
    if (!container) return;

    // Show loading
    container.innerHTML = '<div style="text-align: center; padding: 40px; color: #999;">⏳ Loading NLP Analysis...</div>';

    // Fetch data
    const data = shopId 
      ? await this.analyzeShop(shopId)
      : await this.analyzeAllFeedback();

    if (!data || !data.success) {
      container.innerHTML = '<div style="text-align: center; padding: 40px; color: #ef4444;">⚠️ Failed to load analysis</div>';
      return;
    }

    // Build dashboard
    const dashboardHTML = `
      <div style="
        background: linear-gradient(135deg, #f8f9ff 0%, #fafbfc 100%);
        border-radius: 12px;
        padding: 24px;
      ">
        ${shopId ? `<h3 style="margin-top: 0; color: #0f172a;">${data.shop_name} - NLP Analysis</h3>` : '<h3 style="margin-top: 0; color: #0f172a;">📊 Overall NLP Analytics</h3>'}
        
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 16px; margin-bottom: 24px;">
          <div style="background: white; padding: 16px; border-radius: 8px; border-left: 4px solid #10b981;">
            <div style="font-size: 0.85rem; color: #999; font-weight: 600; margin-bottom: 4px;">TOTAL FEEDBACK</div>
            <div style="font-size: 2rem; font-weight: 800; color: #0f172a;">${data.total_feedback || 0}</div>
          </div>
          
          <div style="background: white; padding: 16px; border-radius: 8px; border-left: 4px solid #667eea;">
            <div style="font-size: 0.85rem; color: #999; font-weight: 600; margin-bottom: 4px;">AVG SENTIMENT</div>
            <div style="font-size: 2rem; font-weight: 800; color: #667eea;">${(data.average_sentiment_score || 0).toFixed(2)}</div>
            <div style="font-size: 0.75rem; color: #999; margin-top: 4px;">-1 to +1 scale</div>
          </div>
          
          <div style="background: white; padding: 16px; border-radius: 8px; border-left: 4px solid #f59e0b;">
            <div style="font-size: 0.85rem; color: #999; font-weight: 600; margin-bottom: 4px;">SENTIMENT MIX</div>
            <div style="font-size: 0.9rem; line-height: 1.6;">
              <div>😊 ${data.sentiment_distribution.positive || 0}</div>
              <div>😐 ${data.sentiment_distribution.neutral || 0}</div>
              <div>😞 ${data.sentiment_distribution.negative || 0}</div>
            </div>
          </div>
        </div>
        
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 24px;">
          <div style="background: white; padding: 16px; border-radius: 8px;">
            <h4 style="margin: 0 0 16px 0; color: #0f172a; font-size: 0.95rem;">Sentiment Distribution</h4>
            <div id="nlpSentimentChart"></div>
          </div>
          
          ${data.aspect_mentions && Object.keys(data.aspect_mentions).length > 0 ? `
            <div style="background: white; padding: 16px; border-radius: 8px;">
              <h4 style="margin: 0 0 16px 0; color: #0f172a; font-size: 0.95rem;">Aspect Mentions</h4>
              <div id="nlpAspectChart"></div>
            </div>
          ` : ''}
        </div>
        
        ${data.top_keywords && data.top_keywords.length > 0 ? `
          <div style="background: white; padding: 16px; border-radius: 8px; margin-bottom: 24px;">
            <h4 style="margin: 0 0 16px 0; color: #0f172a; font-size: 0.95rem;">Top Keywords</h4>
            <div id="nlpKeywordCloud"></div>
          </div>
        ` : ''}
        
        ${data.detailed_analysis && data.detailed_analysis.length > 0 ? `
          <div style="background: white; padding: 16px; border-radius: 8px;">
            <h4 style="margin: 0 0 16px 0; color: #0f172a; font-size: 0.95rem;">📝 Detailed Feedback Analysis</h4>
            <div id="nlpDetailedAnalysis"></div>
          </div>
        ` : ''}
      </div>
    `;

    container.innerHTML = dashboardHTML;

    // Create visualizations
    this.createSentimentChart('nlpSentimentChart', data);
    
    if (data.aspect_mentions && Object.keys(data.aspect_mentions).length > 0) {
      this.createAspectChart('nlpAspectChart', data.aspect_mentions);
    }
    
    if (data.top_keywords) {
      this.createKeywordCloud('nlpKeywordCloud', data.top_keywords);
    }
    
    if (data.detailed_analysis && data.detailed_analysis.length > 0) {
      this.renderFeedbackAnalysis('nlpDetailedAnalysis', data.detailed_analysis);
    }
  }
}

// Initialize globally
const nlpAnalytics = new NLPAnalytics();

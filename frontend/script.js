/* ===== LEGAZPI EXPLORER - CLEAN VERSION ===== */
/* All duplicates and non-functioning code removed */

// SNACKBAR / TOAST UTILITY
(function(){
  function createContainer(){
    let c = document.querySelector('.snackbar-wrapper');
    if(!c){ c = document.createElement('div'); c.className = 'snackbar-wrapper'; document.body.appendChild(c); }
    return c;
  }
  window.showSnackbar = function(message, type='info', duration=3500){
    try{
      const container = createContainer();
      const s = document.createElement('div');
      s.className = 'snackbar ' + (type||'info');
      s.textContent = String(message);
      container.appendChild(s);
      // allow entrance animation
      requestAnimationFrame(()=> s.classList.add('show'));
      setTimeout(()=>{ s.classList.remove('show'); setTimeout(()=> s.remove(), 250); }, duration);
      return s;
    }catch(e){ console.error('snackbar error', e); }
  };
  // override native alert where script.js is loaded
  try{ if(typeof window !== 'undefined') window.alert = function(msg){ window.showSnackbar(msg, 'info', 3500); }; }catch(e){}
})();

// Anonymous analytics tracker (no login required)
(function(){
  function uuid(){ return ([1e7]+-1e3+-4e3+-8e3+-1e11).replace(/[018]/g,c=>(c^crypto.getRandomValues(new Uint8Array(1))[0]&15>>c/4).toString(16)); }
  const COOKIE_NAME = 'anon_sid';
  function getCookie(name){ return document.cookie.split('; ').find(r=>r?.startsWith(name+'='))?.split('=')[1]; }
  function setCookie(name,val,days=365){ document.cookie = `${name}=${val}; path=/; max-age=${60*60*24*days}`; }
  let sid = getCookie(COOKIE_NAME); if(!sid){ sid = uuid(); setCookie(COOKIE_NAME,sid,365); }

  function sendEvent(eventType, payload, useBeacon=false){
    const body = { session_id: sid, type: eventType, ts: Date.now(), url: location.pathname, referrer: document.referrer||'', ua: navigator.userAgent, payload };
    const url = '/backend/api.php?action=log_event';
    if(useBeacon && navigator.sendBeacon){ const blob = new Blob([JSON.stringify(body)], {type:'application/json'}); navigator.sendBeacon(url, blob); return; }
    fetch(url, {method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify(body)}).catch(()=>{});
  }

  // page view
  sendEvent('page_view', {title: document.title});

  // click tracking for elements with data-track
  document.addEventListener('click', e=>{
    const t = e.target.closest('[data-track]');
    if(!t) return;
    sendEvent('click', {selector: t.getAttribute('data-track') || t.outerHTML});
  });

  // search forms removed (unused feature)

  // heartbeat every 30s
  let last = Date.now();
  setInterval(()=>{ const now = Date.now(); sendEvent('heartbeat',{duration_s: Math.round((now-last)/1000)}); last = now; }, 30000);

  // send final unload event
  window.addEventListener('beforeunload', ()=> sendEvent('page_unload',{duration_s: Math.round((Date.now()-last)/1000)}, true));
})();

// ===== HOTEL MANAGEMENT =====

const hotelDatabase = [
    {
      id: 8, name: 'Alicia Hotel',
      description: 'Modern hotel with comfortable rooms and great location.',
      image: 'https://content.r9cdn.net/rimg/himg/b4/ee/d8/ostrovok-318743-7d24e3-971188.jpg?width=1200&height=630&crop=true',
      rating: 4.4, ratePerNight: 2100, category: '3-Star', phone: '(052) 480-8888',
      address: 'F. Aquende Drive, Legazpi City', landmark: 'Near Lignon Hill',
      features: ['WiFi', 'Restaurant', 'Parking', 'Air Conditioning']
    },
    {
      id: 9, name: 'Proxy by The Oriental Albay',
      description: 'Stylish hotel with modern amenities and central location.',
      image: 'https://ik.imagekit.io/tvlk/apr-asset/TzEv3ZUmG4-4Dz22hvmO9NUDzw1DGCIdWl4oPtKumOg=/lodging/33000000/32440000/32436200/32436107/6f30c21d_z.jpg?tr=q-80,c-at_max,w-740,h-500&_src=imagekit',
      rating: 4.5, ratePerNight: 2500, category: '4-Star', phone: '(052) 480-7777',
      address: 'Rizal Street', landmark: 'Downtown Legazpi',
      features: ['WiFi', 'Restaurant', 'Bar', 'Conference', 'Parking']
    },
    {
      id: 10, name: 'Hotel St. Ellis',
      description: 'Upscale hotel with excellent service and amenities.',
      image: 'https://pix10.agoda.net/hotelImages/237713/0/dd9bba179c0c378184dc722263dacb61.jpeg?s=414x232',
      rating: 4.7, ratePerNight: 3200, category: '4-Star', phone: '(052) 480-6666',
      address: 'Rizal Street', landmark: 'Near Embarcadero',
      features: ['WiFi', 'Restaurant', 'Pool', 'Spa', 'Parking']
    },
    {
      id: 11, name: 'Hotel Venezia',
      description: 'Elegant hotel with Italian-inspired design and comfort.',
      image: 'https://pix10.agoda.net/hotelImages/108416/0/264d01d0a7773aaa231ad0bd6d986541.jpeg?ce=0&s=414x232',
      rating: 4.6, ratePerNight: 2800, category: '4-Star', phone: '(052) 480-5555',
      address: 'Washington Drive', landmark: 'Near Airport',
      features: ['WiFi', 'Restaurant', 'Bar', 'Parking', 'Airport Shuttle']
    },
    {
      id: 12, name: 'Emerald Boutique Hotel',
      description: 'Boutique hotel with personalized service and cozy rooms.',
      image: 'https://cf.bstatic.com/xdata/images/hotel/max1024x768/74067984.jpg?k=ff9ffc1120fd859012aa9c4d1cf7c34466763d13512bde7cdf746148f111109d&o=',
      rating: 4.5, ratePerNight: 2300, category: 'Boutique', phone: '(052) 480-4444',
      address: 'Rizal Street', landmark: 'Near City Center',
      features: ['WiFi', 'Restaurant', 'Parking', 'Family Rooms']
    },
    {
      id: 13, name: 'La Edley Resort & Hotel',
      description: 'Resort hotel with relaxing atmosphere and pool.',
      image: 'https://scontent.fceb1-2.fna.fbcdn.net/v/t39.30808-6/480292972_659765783094080_4944662862255816960_n.jpg?_nc_cat=111&ccb=1-7&_nc_sid=7b2446&_nc_eui2=AeH518KJ-ABhaV2890UKFvWWSX47XrocBzJJfjteuhwHMkXAIILeanG4JPfxMbHA6TEIbFmVTR4FK3f92NvlskfQ&_nc_ohc=w_2QrTmJvmEQ7kNvwF97zu0&_nc_oc=Adm-NgZ_AcHipDYxF576387mCN2jhac0x20X3HpPdfL6IsU6Yt8-QTGO-tvynSYP_2U&_nc_zt=23&_nc_ht=scontent.fceb1-2.fna&_nc_gid=ppcxCKUzE1uzq0uH5nHeyA&_nc_ss=8&oh=00_Afyeg3U1OZp5f7rGycGpizRcx4JM0DlXjIofK5ULzdn-DQ&oe=69B9504C',
      rating: 4.3, ratePerNight: 1800, category: 'Resort', phone: '(052) 480-3333',
      address: 'San Fernando, Santo Domingo', landmark: 'Near Nuestra Senora de Salvacion',
      features: ['WiFi', 'Pool', 'Restaurant', 'Parking']
    },
    {
      id: 14, name: 'Daraga Guesthouses',
      description: 'Affordable guesthouses for travelers and families.',
      image: 'https://images.trvl-media.com/lodging/19000000/18800000/18799100/18799033/578e2d7f.jpg?impolicy=resizecrop&rw=575&rh=575&ra=fill',
      rating: 4.2, ratePerNight: 1200, category: 'Guesthouse', phone: '(052) 480-2222',
      address: 'Daraga town', landmark: 'Near Daraga Church',
      features: ['WiFi', 'Parking', 'Family Rooms']
    },
  {
    id: 1, name: 'The Marison Hotel', distance: 1.6,
    lat: 13.1130, lon: 123.7540,
    description: 'Highly recommended for its comfort and proximity to attractions.',
    rating: 4.8, ratePerNight: 2800, category: '3-Star', phone: '(052) 480-9999',
    address: 'Rizal Avenue, Downtown Legazpi', landmark: 'Across City Hall',
    image: 'https://dynamic-media-cdn.tripadvisor.com/media/photo-o/2f/ed/29/3b/caption.jpg?w=900&h=500&s=1',
    features: ['WiFi', 'Restaurant', 'Bar', 'Pool', 'Gym']
  },
  {
    id: 2, name: 'Lotus Blu Hotel', distance: 2.4,
    lat: 13.1340, lon: 123.7520,
    description: 'Known for clean rooms and good service with ocean views.',
    rating: 4.5, ratePerNight: 2200, category: '3-Star', phone: '(052) 481-1111',
    address: 'Embarcadero Road, Waterfront', landmark: 'Near Albay Gulf',
    image: 'https://cf.bstatic.com/xdata/images/hotel/max1024x768/715749771.jpg?k=b158ac2bfcf19b8d45917a1a96fe298a67004d86d71a51287d24f7e97306f803&o=',
    features: ['WiFi', 'Restaurant', 'Spa', 'Ocean View', 'Parking']
  },
  {
    id: 3, name: 'Hotel Sentro Legazpi', distance: 2.8,
    lat: 13.1150, lon: 123.7480,
    description: 'Centrally located with modern amenities and city views.',
    rating: 4.7, ratePerNight: 3200, category: '4-Star', phone: '(052) 481-2222',
    address: 'Quezon Avenue, City Center', landmark: 'Near Ibalong Monument',
    image: 'https://scontent.fceb1-4.fna.fbcdn.net/v/t39.30808-6/482066976_1109196634586174_4270641781487625568_n.jpg?_nc_cat=107&ccb=1-7&_nc_sid=7b2446&_nc_eui2=AeGP3_KibGIGKCWPAqoDVoKs1ele98MjXXLV6V73wyNdcmuLMgSlO4q10wIrDTd0SBa1e0BMpHlZq0OWkgWGIStz&_nc_ohc=ueQFigw2YcQQ7kNvwEEb_Cb&_nc_oc=Adkuf8sf2-n_NTDozcTGmluE4zIpTk27-WQmOcRSWt8wic4HLmp5tTlUWttlTQgH5F4&_nc_zt=23&_nc_ht=scontent.fceb1-4.fna&_nc_gid=mQP81rSgFAgIAt2a9dVqCQ&_nc_ss=8&oh=00_Afwk-H9IkFN3iL7IoZW_23Ks_rggShCRkDqQ5YWl-Lgegw&oe=69B985D2',
    features: ['WiFi', 'Restaurant', 'Bar', 'Conference', 'Parking', 'Gym']
  },
  {
    id: 4, name: 'F2M Tower', distance: 2.8,
    lat: 13.1200, lon: 123.7500,
    description: 'A solid choice with good, affordable rooms and excellent service.',
    rating: 4.4, ratePerNight: 1800, category: 'Budget-Friendly', phone: '(052) 481-3333',
    address: 'Caedo Street, Commercial Area', landmark: 'Next to Embarcadero Mall',
    image: 'https://images.trvl-media.com/lodging/13000000/12470000/12465000/12464957/b5f22c6c.jpg?impolicy=resizecrop&rw=575&rh=575&ra=fill',
    features: ['WiFi', 'Restaurant', 'Parking', 'Air Conditioning']
  },
];

// Load custom hotels from localStorage
function loadCustomHotels() {
  const saved = localStorage.getItem('customHotels');
  return saved ? JSON.parse(saved) : [];
}

// Save custom hotels to localStorage
function saveCustomHotels(hotels) {
  localStorage.setItem('customHotels', JSON.stringify(hotels));
}

// Get all hotels (default + custom)
function getAllHotels() {
  const customHotels = loadCustomHotels().map((h, idx) => ({
    ...h, id: 1000 + idx, isCustom: true,
    rating: h.rating || 4.5, category: h.category || 'Custom'
  }));
  return [...hotelDatabase, ...customHotels];
}

// Get hotel image URL by hotel name (fallback to placeholder)
function getHotelImage(name) {
  if (!name) return 'https://via.placeholder.com/240x160?text=Hotel';
  const allHotels = getAllHotels();
  const match = allHotels.find(h => h.name.toLowerCase().trim() === name.toLowerCase().trim());
  if (match && match.image) return match.image;
  // fallback to generic hotel photo
  return 'https://images.unsplash.com/photo-1566073771259-6a8506099945?w=400&h=300&fit=crop';
}

// ===== DESTINATION COORDINATES & TRANSPORT ROUTES =====

const destinationCoords = {
  'Mayon Volcano': { lat: 13.1550, lon: 123.7050 },
  'Lignon Hill': { lat: 13.1520, lon: 123.7390 },
  'Embarcadero de Legazpi': { lat: 13.1340, lon: 123.7520 },
  'Albay Park & Wildlife': { lat: 13.1500, lon: 123.7400 },
  'Highlands Park Legazpi City': { lat: 13.1610, lon: 123.7240 },
  'Nuestra Senora de Salvacion Legaspi': { lat: 13.1300, lon: 123.7200 },
  'SEVENTY-SIX Farm': { lat: 13.1200, lon: 123.7400 }
};

// Destination Database with Images and Details
const destinationDatabase = {
  'Mayon Volcano': {
    image: 'https://images.unsplash.com/photo-1506905925346-21bda4d32df4?w=400&h=300&fit=crop',
    description: 'The iconic perfect cone-shaped volcano of Legazpi. A UNESCO World Heritage Site and symbol of Albay Province.',
    category: 'Natural Landmark',
    activities: ['Hiking', 'Photography', 'Sightseeing']
  },
  'Lignon Hill': {
    image: 'https://images.unsplash.com/photo-1506905925346-21bda4d32df4?w=400&h=300&fit=crop',
    description: 'A scenic hill offering panoramic views of Mayon Volcano and Legazpi City. Perfect for sunset viewing.',
    category: 'Scenic Viewpoint',
    activities: ['Hiking', 'Sunset View', 'Photography']
  },
  'Embarcadero de Legazpi': {
    image: 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=400&h=300&fit=crop',
    description: 'A vibrant waterfront district with shops, restaurants, and recreational activities along Albay Gulf.',
    category: 'Waterfront Area',
    activities: ['Shopping', 'Dining', 'Leisure']
  },
  'Albay Park & Wildlife': {
    image: 'https://images.unsplash.com/photo-1511632765486-a01980e01a18?w=400&h=300&fit=crop',
    description: 'A pristine natural reserve showcasing local wildlife and park ecosystems. Perfect for nature lovers and eco-tourism activities.',
    category: 'Nature Park',
    activities: ['Wildlife Watching', 'Hiking', 'Nature Photography', 'Bird Watching']
  },
  'Highlands Park Legazpi City': {
    image: 'https://images.unsplash.com/photo-1469022563149-aa64dbd37dae?w=400&h=300&fit=crop',
    description: 'A scenic highland park offering breathtaking views of Mayon Volcano and the city. Great for picnics, walks, and landscape photography.',
    category: 'Park',
    activities: ['Scenic Views', 'Picnicking', 'Photography', 'Walking Trails']
  },
  'Nuestra Senora de Salvacion Legaspi': {
    image: 'https://images.unsplash.com/photo-1506905925346-21bda4d32df4?w=400&h=300&fit=crop',
    description: 'A beautiful historic church dedicated to Our Lady of Salvation. A spiritual and architectural landmark of Legazpi with stunning religious heritage.',
    category: 'Religious Site',
    activities: ['Religious Visit', 'Photography', 'Architecture Tour']
  },
  'SEVENTY-SIX Farm': {
    image: 'https://images.unsplash.com/photo-1500382017468-7049bae30402?w=400&h=300&fit=crop',
    description: 'A working farm offering agritourism experiences with fresh produce, farm activities, and authentic farm-to-table dining.',
    category: 'Agritourism',
    activities: ['Farm Tour', 'Fruit Picking', 'Farm Dining', 'Agricultural Learning']
  }
};


const noveltyShops = [
  {
    id: 1, name: 'Albay Gift & Souvenir Shop', rating: 4.8,
    address: 'Rizal Street, Downtown Legazpi', distance: 0.5, phone: '(052) 480-1234',
    description: 'Premium local crafts and Mayon souvenirs curated for tourists.',
    specialties: ['Abaca products', 'Mayon postcards', 'Local crafts', 'Souvenirs'],
    hours: '9 AM - 7 PM', landmark: 'Near Legazpi City Hall',
    directions: 'From LCC Legazpi: Take Rizal Street going downtown. Shop is on the left side before City Hall, next to a convenience store.'
  },
  {
    id: 2, name: 'Mayon View Souvenir Shop', rating: 4.9,
    address: 'Embarcadero Road, Waterfront', distance: 1.5, phone: '(052) 480-5678',
    description: 'Highest-rated shop with premium souvenirs and handmade art pieces.',
    specialties: ['Premium souvenirs', 'Local handicrafts', 'Art pieces'],
    hours: '10 AM - 8:30 PM', landmark: 'Inside Embarcadero Complex',
    directions: 'From LCC Legazpi: Head to Embarcadero Road via Rizal Avenue. Turn left at the traffic light near the waterfront. Shop is inside the Embarcadero Complex facing the bay.'
  },
  {
    id: 3, name: 'Legazpi Baybayin Souvenir Hub', rating: 4.7,
    address: 'Rawis Street, Tourist Area', distance: 2.1, phone: '(052) 480-3456',
    description: 'Cultural items and Mayon Volcano merchandise in prime tourist area.',
    specialties: ['Cultural items', 'Mayon Volcano merch', 'Local art', 'Premium gifts'],
    hours: '8 AM - 9 PM', landmark: 'Near Lignon Hill entrance',
    directions: 'From LCC Legazpi: Go north on Rawis Street. Continue until you reach the Lignon Hill area. Shop is on your right before the hill entrance, across from a local bakery.'
  },
  {
    id: 4, name: 'Bicolania Crafts & Souvenir', rating: 4.6,
    address: 'Caedo Street, Commercial Area', distance: 1.2, phone: '(052) 480-2345',
    description: 'Handmade crafts featuring authentic Bicol Express products and bamboo items.',
    specialties: ['Handmade crafts', 'Bicol Express products', 'Bamboo items'],
    hours: '10 AM - 8 PM', landmark: 'Next to Embarcadero Mall',
    directions: 'From LCC Legazpi: Take Caedo Street towards commercial district. Shop is located right next to Embarcadero Mall, easy to spot from the main road.'
  },
  {
    id: 5, name: 'Heritage Albay Shop', rating: 4.5,
    address: 'Peñaranda Street, City Center', distance: 0.8, phone: '(052) 480-4567',
    description: 'Heritage items and authentic abaca handbags, plus traditional Bicol delicacies.',
    specialties: ['Heritage items', 'Abaca handbags', 'Bicol delicacies'],
    hours: '9:30 AM - 6:30 PM', landmark: 'Across Plaza Independencia',
    directions: 'From LCC Legazpi: Head to Peñaranda Street in the city center. Shop is directly across from Plaza Independencia, very accessible location with good parking.'
  },
  {
    id: 6, name: 'Ibalong Cultural Shop', rating: 4.4,
    address: 'Quezon Avenue, Downtown', distance: 0.9, phone: '(052) 480-6789',
    description: 'Traditional crafts, literature, and local artwork celebrating Bicolano culture.',
    specialties: ['Traditional crafts', 'Literature', 'Artwork'],
    hours: '9 AM - 7 PM', landmark: 'Near Ibalong Monument',
    directions: 'From LCC Legazpi: Take Quezon Avenue heading downtown. Shop is located near the Ibalong Monument, opposite to a local restaurant. Easy walking distance from city center.'
  }
];

// Expose for modal consumption and render guest-side shop cards
if (typeof window !== 'undefined') {
  window.noveltyShops = noveltyShops;
}

function renderNoveltyShops() {
  const container = document.getElementById('shopsContainer');
  if (!container) return;
  container.innerHTML = '';

  noveltyShops.forEach(shop => {
    const imageUrl = shop.image || 'https://source.unsplash.com/featured/400x260/?souvenir,market';
    const card = document.createElement('div');
    card.className = 'shop-card';
    card.innerHTML = `
      <div class="shop-card-image" style="background-image:url('${imageUrl}');"></div>
      <div class="shop-card-content">
        <div class="shop-card-header">
          <h3>${shop.name}</h3>
        </div>
        <button class="view-shop-btn" onclick="openShopModalFunc(${shop.id})">View Shop</button>
      </div>
    `;
    container.appendChild(card);
  });
}

if (document.readyState === 'loading') {
  window.addEventListener('DOMContentLoaded', renderNoveltyShops);
} else {
  renderNoveltyShops();
}

// ===== ENHANCED ROUTES WITH DETAILED DIRECTIONS =====

const routeDirections = {
  'LCC Legazpi|Mayon Volcano': {
    distance: 8.5,
    time: 25,
    directions: 'From LCC Legazpi: Head north on Rizal Avenue → Turn right on Andres Bonifacio Street → Follow signs to Mayon Volcano Tourist Information Center → Entrance gate with parking facility',
    landmark: 'Pass by Daraga Church on your left, then continue to Volcanic Slope'
  },
  'LCC Legazpi|Lignon Hill': {
    distance: 3.5,
    time: 15,
    directions: 'From LCC Legazpi: Head north on Rawis Street → Continue straight → Turn right at traffic light near market → Follow uphill road → Parking area at summit',
    landmark: 'Look for "Lignon Hill" signage, located above the city center'
  },
  'LCC Legazpi|Daraga Church': {
    distance: 2.8,
    time: 12,
    directions: 'From LCC Legazpi: Go north on Rizal Avenue → Turn right on Andres Bonifacio Street → Take immediate left on Daraga Street → Church is on hillside with parking',
    landmark: 'Historic white church on elevated platform with scenic view'
  },
  'LCC Legazpi|Embarcadero de Legazpi': {
    distance: 4.2,
    time: 18,
    directions: 'From LCC Legazpi: Head south on Rizal Avenue → Turn right on Embarcadero Road → Follow waterfront boulevard → Arrive at natural park entrance',
    landmark: 'Waterfront area with mangrove trees and bay views'
  },
  'LCC Legazpi|Sawangan': {
    distance: 5.5,
    time: 20,
    directions: 'From LCC Legazpi: Take Caedo Street going south → Turn left on Legazpi-Daraga Road → Continue through residential area → Watch for Sawangan signage on the right → Turn right to enter village',
    landmark: 'Small village area, look for local community center or school'
  },
  'LCC Legazpi|Albay Park & Wildlife': {
    distance: 4.8,
    time: 18,
    directions: 'From LCC Legazpi: Head east on Rawis Street → Continue to park approach → Turn right at park entrance sign → Follow nature trail to main visitor center',
    landmark: 'Look for green park entrance sign, located in the eastern area of Legazpi City'
  },
  'LCC Legazpi|Highlands Park Legazpi City': {
    distance: 3.9,
    time: 15,
    directions: 'From LCC Legazpi: Head northeast on Rawis Street → Turn right at highland park junction → Follow uphill scenic road → Parking area and entrance gate',
    landmark: 'Scenic highland park with Mayon Volcano views, clearly marked entrance on elevated terrain'
  },
  'LCC Legazpi|Nuestra Senora de Salvacion Legaspi': {
    distance: 3.2,
    time: 12,
    directions: 'From LCC Legazpi: Head north on Rizal Avenue → Turn left on San Fernando Street → Continue for 1.5 km → Church will be visible on your right with its distinctive white bell tower',
    landmark: 'Historic white church with bell tower, marked by religious monuments and shrine entrance'
  },
  'LCC Legazpi|SEVENTY-SIX Farm': {
    distance: 5.1,
    time: 18,
    directions: 'From LCC Legazpi: Head south on Rizal Avenue → Turn right on Embarcadero Road extension → Continue southeast toward rural area → Follow farm signage → Arrive at main farm entrance and parking lot',
    landmark: 'Working agricultural farm with fruit orchards, clearly marked main gate with farm logo'
  }
};

// ===== WEATHER SYSTEM =====

function getWeatherForecast() {
  const today = new Date();
  const forecast = [];
  
  for (let i = 0; i < 5; i++) {
    const date = new Date(today);
    date.setDate(date.getDate() + i);
    
    const conditions = ['Partly Cloudy', 'Sunny with Clouds', 'Scattered Showers', 'Mostly Sunny', 'Cloudy'];
    const temps = [24, 26, 23, 27, 25];
    const humidity = [65, 55, 75, 50, 70];
    
    forecast.push({
      date: date.toLocaleDateString('en-US', { weekday: 'short', month: 'short', day: 'numeric' }),
      condition: conditions[i % conditions.length],
      tempLow: temps[i] - 2,
      tempHigh: temps[i] + 5,
      humidity: humidity[i],
      recommendation: i % 2 === 0 ? '✓ Good day for outdoor activities' : '⚠️ Bring umbrella, scattered showers expected',
      icon: i % 2 === 0 ? '☀️' : '🌧️'
    });
  }
  
  return forecast;
}

// Fetch real weather via OpenWeatherMap API for a given lat/lon (3-day summary)
// NOTE: Requires OpenWeatherMap API key - get one at https://openweathermap.org/api
async function fetchWeatherForLatLon(lat, lon, days=1){
  try{
    // Use OpenWeatherMap API with API key (you'll need to get a free API key from openweathermap.org)
    const apiKey = 'c3c1662ef224ae5ba69897a6642ddbe2'; // OpenWeatherMap API key provided by user
    const url = `https://api.openweathermap.org/data/2.5/weather?lat=${lat}&lon=${lon}&appid=${apiKey}&units=metric`;
    const r = await fetch(url);
    if (!r.ok) {
      console.warn('OpenWeatherMap API failed, falling back to static data');
      return getWeatherForecast().slice(0,1);
    }
    const j = await r.json();

    // Parse OpenWeatherMap response
    const currentTemp = j.main ? Math.round(j.main.temp) : null;
    const tempLow = j.main ? Math.round(j.main.temp_min) : currentTemp;
    const tempHigh = j.main ? Math.round(j.main.temp_max) : currentTemp;
    const humidity = j.main ? j.main.humidity : null;
    const windSpeed = j.wind ? j.wind.speed : null;

    // Determine condition from weather description
    let condition = 'Clear';
    let icon = '☀️';
    if (j.weather && j.weather.length > 0) {
      const desc = j.weather[0].description.toLowerCase();
      if (desc.includes('rain') || desc.includes('drizzle')) {
        condition = 'Rainy';
        icon = '🌧️';
      } else if (desc.includes('cloud')) {
        condition = 'Cloudy';
        icon = '☁️';
      } else if (desc.includes('clear')) {
        condition = 'Clear';
        icon = '☀️';
      } else {
        condition = 'Partly Cloudy';
        icon = '⛅';
      }
    }

    const recommendation = (condition === 'Rainy') ? 'Bring umbrella / raincoat' : 'Good for outdoor activities';

    const out = [{
      date: new Date().toLocaleDateString(),
      condition,
      tempLow,
      tempHigh,
      currentTemp,
      humidity,
      windspeed: windSpeed,
      recommendation,
      icon
    }];

    return out;
  }catch(e){
    console.warn('OpenWeatherMap fetch failed, using fallback:', e);
    return getWeatherForecast().slice(0,1);
  }
}

// ===== LEARNING-ENABLED SUGGESTION ENGINE =====

// Simple local model stored in localStorage to track choices per destination
function loadSuggestionModel(){
  try{ return JSON.parse(localStorage.getItem('suggestionModel')||'{}'); }catch(e){ return {}; }
}
function saveSuggestionModel(m){ localStorage.setItem('suggestionModel', JSON.stringify(m)); }

function recordSuggestionChoice(destination, hotelId, transportMode){
  const m = loadSuggestionModel();
  m[destination] = m[destination]||{hotels:{},transports:{}};
  m[destination].hotels[hotelId] = (m[destination].hotels[hotelId]||0) + 1;
  if (transportMode) m[destination].transports[transportMode] = (m[destination].transports[transportMode]||0) + 1;
  saveSuggestionModel(m);
}

// Build suggestions asynchronously using enhanced ML/personalization and analytics
async function buildSuggestions(startLocationName, destinations){
  try {
    // First, get smart recommendations from backend
    const recResponse = await fetch(apiUrl('api.php?action=recommendations&limit=20&destination=' + encodeURIComponent(destinations[0] || '')));
    const recData = await recResponse.json();

    // Extract recommended destinations and experiences from ML/analytics
    const recommendedDestinations = recData.recommendations ? recData.recommendations.filter(r => r.type === 'destination').map(r => r.title) : [];
    const recommendedExperiences = recData.recommendations ? recData.recommendations.filter(r => r.type === 'experience') : [];

    // hotels: enhanced scoring with ML insights
    const allHotels = getAllHotels();
    const hotelsOut = [];
    const model = loadSuggestionModel();

    // compute global min/max for price and rating to normalize
    const prices = allHotels.map(h=>h.ratePerNight||2000);
    const minP = Math.min(...prices); const maxP = Math.max(...prices);
    const ratings = allHotels.map(h=>h.rating||4.0);
    const minR = Math.min(...ratings); const maxR = Math.max(...ratings);

    destinations.forEach(dest => {
      const destCoords = destinationCoords[dest];
      const scored = allHotels.map(h => {
        // distance: if hotel has lat/lon and dest has coords, compute haversine
        let dist = h.distance || 3.0;
        if (h.lat && h.lon && destCoords && destCoords.lat && destCoords.lon){
          dist = haversine(h.lat, h.lon, destCoords.lat, destCoords.lon);
        }
        const priceNorm = (h.ratePerNight - minP) / Math.max(1, (maxP - minP));
        const ratingNorm = (h.rating - minR) / Math.max(0.1, (maxR - minR));
        const pop = (model[dest] && model[dest].hotels && model[dest].hotels[h.id]) ? model[dest].hotels[h.id] : 0;

        // Enhanced scoring with ML factors
        let mlBoost = 0;
        if (recommendedDestinations.includes(dest)) mlBoost += 0.5; // Boost for ML-recommended destinations
        if (h.features && h.features.toLowerCase().includes('wifi')) mlBoost += 0.2; // Popular features

        // score: higher is better - enhanced with ML insights
        const score = (ratingNorm * 2.5) - (dist * 0.3) - (priceNorm * 0.8) + (Math.log(1+pop) * 0.6) + mlBoost;
        return {...h, _score: score, _distanceToDest: Number(dist.toFixed(2)), recommended_for: [dest]};
      }).sort((a,b)=>b._score - a._score);

      // push top 3 for this destination
      scored.slice(0,3).forEach(s=> hotelsOut.push(s));
    });

    // Deduplicate hotels (keep highest score occurrence)
    const seen = {};
    const uniqueHotels = [];
    hotelsOut.forEach(h => {
      if (!seen[h.id]){ seen[h.id]=true; uniqueHotels.push(h); }
    });

    // transportation: enhanced with ML preferences
    const transports = [];
    const startCoords = (userLocation && userLocation.lat && userLocation.lon) ? userLocation : null;
    destinations.forEach(dest => {
      const destCoords = destinationCoords[dest];
      if (!destCoords) return;
      const fromLat = startCoords ? startCoords.lat : 13.1126; const fromLon = startCoords ? startCoords.lon : 123.7535;
      const distance = Number(haversine(fromLat, fromLon, destCoords.lat, destCoords.lon).toFixed(2));
      const time = Math.max(8, Math.round(distance * 6));
      const fares = calculateFare(distance);
      // preferred transport from model + ML insights
      const pref = model[dest] && model[dest].transports ? Object.entries(model[dest].transports).sort((a,b)=>b[1]-a[1])[0] : null;

      // ML-enhanced transport suggestion
      let bestOption = pref ? pref[0] : 'Tricycle';
      if (distance > 10) bestOption = 'Taxi'; // ML insight: longer distances prefer taxi
      if (distance < 2) bestOption = 'Walking'; // ML insight: short distances prefer walking

      transports.push({
        destination: dest,
        distance,
        time,
        directions: routeDirections[`LCC Legazpi|${dest}`]?.directions || ('Head to ' + dest),
        landmark: routeDirections[`LCC Legazpi|${dest}`]?.landmark || '',
        fares,
        bestOption,
        ml_insight: distance > 10 ? 'Long distance - taxi recommended' : distance < 2 ? 'Short walk - walking recommended' : 'Standard distance - tricycle optimal'
      });
    });

    // weather: using OpenWeatherMap API
    let weatherArray = [];
    if (destinations.length > 0){
      const first = destinations[0];
      const dc = destinationCoords[first] || (startCoords?{lat:startCoords.lat, lon:startCoords.lon}:null);
      if (dc) weatherArray = await fetchWeatherForLatLon(dc.lat, dc.lon, 1);
    }

    // Add ML-enhanced recommendations for experiences
    const enhancedExperiences = recommendedExperiences.map(exp => ({
      ...exp,
      ml_recommended: true,
      confidence: exp.confidence || 85
    }));

    return {
      hotels: uniqueHotels.slice(0,6),
      transportation: transports,
      weather: weatherArray,
      noveltyShops: noveltyShops.slice().sort((a,b)=>b.rating-a.rating),
      ml_recommendations: enhancedExperiences,
      analytics_insights: recData.analytics_used || {}
    };

  } catch (error) {
    console.warn('Enhanced suggestions failed, using basic suggestions:', error);
    // Fallback to basic suggestions if ML fails
    return await buildBasicSuggestions(startLocationName, destinations);
  }
}

// Fallback basic suggestions function
async function buildBasicSuggestions(startLocationName, destinations){
  // ... (keep the original logic as fallback)
  const allHotels = getAllHotels();
  const hotelsOut = [];
  const model = loadSuggestionModel();

  const prices = allHotels.map(h=>h.ratePerNight||2000);
  const minP = Math.min(...prices); const maxP = Math.max(...prices);
  const ratings = allHotels.map(h=>h.rating||4.0);
  const minR = Math.min(...ratings); const maxR = Math.max(...ratings);

  destinations.forEach(dest => {
    const destCoords = destinationCoords[dest];
    const scored = allHotels.map(h => {
      let dist = h.distance || 3.0;
      if (h.lat && h.lon && destCoords && destCoords.lat && destCoords.lon){
        dist = haversine(h.lat, h.lon, destCoords.lat, destCoords.lon);
      }
      const priceNorm = (h.ratePerNight - minP) / Math.max(1, (maxP - minP));
      const ratingNorm = (h.rating - minR) / Math.max(0.1, (maxR - minR));
      const pop = (model[dest] && model[dest].hotels && model[dest].hotels[h.id]) ? model[dest].hotels[h.id] : 0;
      const score = (ratingNorm * 2.5) - (dist * 0.3) - (priceNorm * 0.8) + (Math.log(1+pop) * 0.6);
      return {...h, _score: score, _distanceToDest: Number(dist.toFixed(2)), recommended_for: [dest]};
    }).sort((a,b)=>b._score - a._score);

    scored.slice(0,3).forEach(s=> hotelsOut.push(s));
  });

  const seen = {};
  const uniqueHotels = [];
  hotelsOut.forEach(h => {
    if (!seen[h.id]){ seen[h.id]=true; uniqueHotels.push(h); }
  });

  const transports = [];
  const startCoords = (userLocation && userLocation.lat && userLocation.lon) ? userLocation : null;
  destinations.forEach(dest => {
    const destCoords = destinationCoords[dest];
    if (!destCoords) return;
    const fromLat = startCoords ? startCoords.lat : 13.1126; const fromLon = startCoords ? startCoords.lon : 123.7535;
    const distance = Number(haversine(fromLat, fromLon, destCoords.lat, destCoords.lon).toFixed(2));
    const time = Math.max(8, Math.round(distance * 6));
    const fares = calculateFare(distance);
    const pref = model[dest] && model[dest].transports ? Object.entries(model[dest].transports).sort((a,b)=>b[1]-a[1])[0] : null;
    transports.push({destination: dest, distance, time, directions: routeDirections[`LCC Legazpi|${dest}`]?.directions || ('Head to ' + dest), landmark: routeDirections[`LCC Legazpi|${dest}`]?.landmark || '', fares, bestOption: pref ? pref[0] : 'Tricycle'});
  });

  let weatherArray = [];
  if (destinations.length > 0){
    const first = destinations[0];
    const dc = destinationCoords[first] || (startCoords?{lat:startCoords.lat, lon:startCoords.lon}:null);
    if (dc) weatherArray = await fetchWeatherForLatLon(dc.lat, dc.lon, 1);
  }

  return { hotels: uniqueHotels.slice(0,6), transportation: transports, weather: weatherArray, noveltyShops: noveltyShops.slice().sort((a,b)=>b.rating-a.rating) };
}

// Called from UI when user selects a suggested hotel/transport
function selectHotelSuggestion(hotelId, destination, transportMode){
  try{ recordSuggestionChoice(destination||'unknown', parseInt(hotelId), transportMode||null); showSnackbar('Choice saved — model updated', 'success'); }catch(e){ console.warn(e); }
}

const transportModes = [
  { mode: 'Walking', base: 0, perKm: 0, note: 'Free - best for short distances' },
  { mode: 'Tricycle', base: 20, perKm: 15, note: 'Most common - affordable & convenient' },
  { mode: 'Jeepney', base: 10, perKm: 12, note: 'Cheap shared transport' },
  { mode: 'Taxi', base: 40, perKm: 30, note: 'Metered - good for groups' },
  { mode: 'Ride-hailing', base: 60, perKm: 40, note: 'App-based services' }
];

// Haversine distance calculation (km)
function haversine(lat1, lon1, lat2, lon2) {
  function toRad(v) { return v * Math.PI / 180; }
  const R = 6371;
  const dLat = toRad(lat2 - lat1);
  const dLon = toRad(lon2 - lon1);
  const a = Math.sin(dLat / 2) * Math.sin(dLat / 2) + 
            Math.cos(toRad(lat1)) * Math.cos(toRad(lat2)) * 
            Math.sin(dLon / 2) * Math.sin(dLon / 2);
  const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
  return R * c;
}

// ===== UI FUNCTIONS =====

// Filter destinations by category
function filterDestinations(category) {
  const cards = document.querySelectorAll('.card[data-category]');
  const buttons = document.querySelectorAll('.filter-btn');
  buttons.forEach(btn => {
    btn.classList.remove('active');
    if (btn.dataset.filter === category) btn.classList.add('active');
  });
  cards.forEach(card => {
    card.classList.toggle('hidden', card.dataset.category !== category);
  });
}

// ===== MODAL FUNCTIONS (SINGLE SOURCE) =====

// Card Modal
function openCardModal(title, image, description, date_start, date_end) {
  const isVideo = image && image.endsWith('.mp4');
  
  if (isVideo) {
    // Open festival modal for videos
    const modal = document.getElementById('festivalModal');
    if (!modal) {
      // Fallback if no festival modal exists
      alert('Video: ' + title + '\n\n' + description);
      return;
    }
    const video = document.getElementById('festivalVideo');
    document.getElementById('festivalTitle').textContent = title;
    let dateStr = '';
    if (date_start && date_start !== '0000-00-00') {
      const startDate = new Date(date_start).toLocaleDateString('en-US', {month: 'short', day: 'numeric', year: 'numeric'});
      if (date_end && date_end !== '0000-00-00') {
        const endDate = new Date(date_end).toLocaleDateString('en-US', {month: 'short', day: 'numeric', year: 'numeric'});
        dateStr = startDate + ' - ' + endDate;
      } else {
        dateStr = startDate;
      }
    }
    document.getElementById('festivalDate').textContent = dateStr;
    document.getElementById('festivalDescription').textContent = description;
    video.src = image;
    try {
      video.muted = true;
      video.playsInline = true;
      video.autoplay = true;
      video.loop = true;
      video.play().catch(()=>{});
    } catch(e) {}
    modal.classList.add('active');
  } else {
    // Open card modal for images
    const modal = document.getElementById('cardModal');
    document.getElementById('modalTitle').textContent = title;
    document.getElementById('modalImage').src = image;
    document.getElementById('modalDescription').textContent = description;
    // ensure any previous inline style is cleared
    // show card modal normally
    modal.classList.add('active');
    modal.style.display = 'flex';
  }
}

function closeCardModal() {
  const modal = document.getElementById('cardModal');
  modal.classList.remove('active');
}

// Festival Modal
function openFestivalModal(title, videoSrc, description) {
  const modal = document.getElementById('festivalModal');
  const video = document.getElementById('festivalVideo');
  document.getElementById('festivalTitle').textContent = title;
  document.getElementById('festivalDescription').textContent = description;
  video.src = videoSrc;
  try {
    video.muted = true;
    video.playsInline = true;
    video.autoplay = true;
    video.loop = true;
    video.play().catch(()=>{});
  } catch(e) {}
  modal.classList.add('active');
}

function closeFestivalModal() {
  const modal = document.getElementById('festivalModal');
  const video = document.getElementById('festivalVideo');
  if (video) {
    try{ video.pause(); }catch(e){}
    try{ video.src = ''; }catch(e){}
  }
  modal.classList.remove('active');
}

// Event Modal (for upcoming events)
function openEventModal(title, videoSrc, description, date) {
  let modal = document.getElementById('eventModal');
  if (!modal) {
    modal = document.createElement('div');
    modal.id = 'eventModal';
    modal.className = 'festival-modal';
    modal.innerHTML = `
      <div class="festival-modal-content">
        <div style="width:60%; padding:0;">
          <video id="eventModalVideo" controls autoplay muted style="width:100%; height:100%; object-fit:cover;"></video>
        </div>
        <div style="width:40%; padding:20px; background:#fff;">
          <button style="float:right; font-size:24px; border:none; background:transparent; cursor:pointer;" onclick="closeEventModal()">&times;</button>
          <h3 id="eventModalTitle"></h3>
          <p id="eventModalDate" style="color:#666; font-size:0.95rem;"></p>
          <p id="eventModalDesc"></p>
        </div>
      </div>
    `;
    document.body.appendChild(modal);
  }
  document.getElementById('eventModalTitle').textContent = title;
  document.getElementById('eventModalDate').textContent = date;
  document.getElementById('eventModalDesc').textContent = description;
  const vid = document.getElementById('eventModalVideo');
  if (videoSrc) {
    vid.src = videoSrc;
    vid.play().catch(() => {});
    vid.style.display = 'block';
  } else {
    vid.style.display = 'none';
    vid.pause();
  }
  modal.classList.add('active');
}

function closeEventModal() {
  const modal = document.getElementById('eventModal');
  if (!modal) return;
  const vid = document.getElementById('eventModalVideo');
  if (vid) {
    vid.pause();
    vid.src = '';
  }
  modal.classList.remove('active');
}

// Single modal close handler
window.onclick = function(event) {
  const cardModal = document.getElementById('cardModal');
  const festivalModal = document.getElementById('festivalModal');
  if (cardModal && event.target === cardModal) closeCardModal();
  if (festivalModal && event.target === festivalModal) closeFestivalModal();
}

// ===== USER AUTH (Google Sign-In) =====
let currentUser = null; // {email,name}
const GOOGLE_CLIENT_ID = '592851137026-ojducpgk2od9rvtob47sn5k5fktqvi6h.apps.googleusercontent.com';

function updateUserUI(){
  const badge = document.getElementById('userBadge') || document.getElementById('userBadgeSmall');
  const nameEl = document.getElementById('userName') || document.getElementById('userNameSmall');
  const gsiBtn = document.getElementById('gsiButton') || document.getElementById('gsiButtonSmall');
  if (currentUser){
    if (nameEl) nameEl.textContent = currentUser.name || currentUser.email;
    if (badge) badge.style.display = 'inline-block';
    if (gsiBtn) gsiBtn.style.display = 'none';
  } else {
    if (badge) badge.style.display = 'none';
    if (gsiBtn) gsiBtn.style.display = 'block';
  }
}

async function handleCredentialResponse(response){
  try {
    const id_token = response.credential;
    const r = await fetch(apiUrl('verify_google.php'),{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({id_token})});
    const j = await r.json();
    if (j.success){
      currentUser = {email: j.email, name: j.name || j.email};
      updateUserUI();
    } else {
      showSnackbar('Google sign-in failed', 'error');
    }
  } catch (e){ console.error('Google verify error', e); showSnackbar('Google sign-in failed', 'error'); }
}

function initGoogleSignIn(){
  if (typeof google === 'undefined' || !google.accounts || !google.accounts.id) return;
  google.accounts.id.initialize({ client_id: GOOGLE_CLIENT_ID, callback: handleCredentialResponse });
  // render small button for feedback area
  const smallContainer = document.getElementById('gsiButton');
  if (smallContainer) google.accounts.id.renderButton(smallContainer, { theme: 'outline', size: 'medium' });
  const smallContainer2 = document.getElementById('gsiButtonSmall') || document.getElementById('gsiButton');
  if (smallContainer2 && smallContainer2 !== smallContainer) google.accounts.id.renderButton(smallContainer2, { theme: 'outline', size: 'small' });
  // optional: prompt one-tap disabled for now
}

document.getElementById('signOutBtn')?.addEventListener('click', ()=>{ currentUser = null; updateUserUI(); if (google && google.accounts && google.accounts.id) google.accounts.id.disableAutoSelect(); });
document.getElementById('signOutBtnSmall')?.addEventListener('click', ()=>{ currentUser = null; updateUserUI(); if (google && google.accounts && google.accounts.id) google.accounts.id.disableAutoSelect(); });

// ===== FEEDBACK SUBMISSION =====
// Email validation function
function isValidEmail(email) {
  const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
  return emailRegex.test(email);
}

async function submitFeedback() {
  const feedbackText = document.getElementById('feedbackText').value.trim();
  if (!feedbackText) { showSnackbar('Please write your feedback', 'error'); return; }
  const anon = !!document.getElementById('feedbackAnonymous')?.checked;
  let name = document.getElementById('feedbackName')?.value.trim();
  let email = document.getElementById('feedbackEmail')?.value.trim();
  
  if (anon) {
    name = 'Anonymous' + Math.floor(Math.random()*90000+10000);
    email = 'guest@legazpi.local';
  } else {
    if (!name) { showSnackbar('Please enter your name', 'error'); return; }
    if (!email) { showSnackbar('Please enter your email', 'error'); return; }
    if (!isValidEmail(email)) { showSnackbar('Please enter a valid email address', 'error'); return; }
  }
  
  try {
    const result = await FeedbackAPI.submit(name, email, feedbackText, 5, anon?1:0, null);
    if (result.success) {
      showSnackbar('⭐ Thank you for your feedback!', 'success');
      document.getElementById('feedbackText').value = '';
      document.getElementById('feedbackName').value = '';
      document.getElementById('feedbackEmail').value = '';
    } else {
      showSnackbar('❌ Error: ' + (result.message || 'Unknown error'), 'error');
    }
  } catch (error) {
    console.error('Feedback error:', error);
    showSnackbar('❌ Error submitting feedback. Please try again.', 'error');
  }
}

// ===== Backend API Client =====
const API = {
  async listEvents(){ const r = await fetch(apiUrl('api.php?action=list_events')); return r.json(); },
  async createEvent(data){ const r = await fetch(apiUrl('api.php?action=create_event'),{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify(data)}); return r.json(); },
  async createShop(data){ const r = await fetch(apiUrl('api.php?action=create_shop'),{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify(data)}); return r.json(); },
  async addFeedback(data){ const r = await fetch(apiUrl('api.php?action=add_feedback'),{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify(data)}); return r.json(); },
  async saveItinerary(data){ const r = await fetch(apiUrl('api.php?action=save_itinerary'),{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify(data)}); return r.json(); },
  async predict(params){ const qs = new URLSearchParams(params).toString(); const r = await fetch(apiUrl('api.php?action=predict&'+qs)); return r.json(); }
};

// Feedback wrapper to integrate with UI and optional Google token
const FeedbackAPI = {
  async submit(name,email,message,rating=5,anonymous=false,imagePath=null){
    const data = {name,email,message,rating,anonymous};
    if (imagePath) data.image_path = imagePath;
    return API.addFeedback(data);
  }
};

// Save itinerary to backend
// Save itinerary to backend (allows guest/anonymous saves)
document.getElementById('itineraryForm')?.addEventListener('submit', async (e)=>{
  e.preventDefault();
  const title = document.getElementById('tripTitle') ? document.getElementById('tripTitle').value : (document.querySelector('#itineraryForm input[name="title"]').value || 'My Trip');
  const daysEl = document.getElementById('tripDays') || document.querySelector('#itineraryForm input[name="days"]');
  const days = parseInt(daysEl ? daysEl.value || 1 : 1);
  const dests = Array.from(document.querySelectorAll('.dest-checkbox:checked')).map(x=>x.value);
  const anon = !!document.getElementById('itineraryAnonymous')?.checked || !!document.querySelector('#itineraryForm input[name="anonymous"]')?.checked;

  let name = '';
  let email = '';
  if (currentUser){ name = anon ? ('Anonymous'+Math.floor(Math.random()*90000+10000)) : currentUser.name; email = currentUser.email; }
  else { name = anon ? ('Guest'+Math.floor(Math.random()*90000+10000)) : 'Guest'; email = 'guest+'+Date.now()+'@local'; }

  const res = await API.saveItinerary({name, email, anonymous: anon?1:0, title, days, destinations: dests});
  if (res.success) { showSnackbar('Itinerary saved', 'success'); document.getElementById('itineraryForm').reset(); } else showSnackbar('Save failed', 'error');
});

// API extensions: itineraries and logging
API.listItineraries = async function(email){ const qs = email ? ('&email='+encodeURIComponent(email)) : ''; const r = await fetch(apiUrl('api.php?action=list_itineraries'+qs)); return r.json(); };
API.getItinerary = async function(id){ const r = await fetch(apiUrl('api.php?action=get_itinerary&id='+encodeURIComponent(id))); return r.json(); };
API.editItinerary = async function(data){ const r = await fetch(apiUrl('api.php?action=edit_itinerary'),{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify(data)}); return r.json(); };
API.deleteItinerary = async function(id,email=''){ const r = await fetch(apiUrl('api.php?action=delete_itinerary'),{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({id,email})}); return r.json(); };
API.logActivity = async function(data){ const r = await fetch(apiUrl('api.php?action=log_activity'),{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify(data)}); return r.json(); };

// Generate printable PDF for itinerary using html2pdf
async function generateItineraryPDF(it){
  // it: {title, days, destinations, user_name}
  const container = document.createElement('div');
  container.style.padding = '18px';
  container.style.fontFamily = 'Arial, Helvetica, sans-serif';
  const destList = Array.isArray(it.destinations) ? it.destinations : (typeof it.destinations === 'string' ? (it.destinations.startsWith('[')?JSON.parse(it.destinations):it.destinations.split(',').map(s=>s.trim())) : []);
  container.innerHTML = `<h1>${escapeHtml(it.title||'Itinerary')}</h1>
    <p><strong>By:</strong> ${escapeHtml(it.user_name||it.name||'Guest')}</p>
    <p><strong>Days:</strong> ${escapeHtml(String(it.days||1))}</p>
    <h3>Destinations</h3>
    <ol>${destList.map(d=>'<li>'+escapeHtml(String(d))+'</li>').join('')}</ol>`;
  document.body.appendChild(container);
  const opt = { margin:0.5, filename: (it.title||'itinerary')+'.pdf', html2canvas:{scale:2}, jsPDF:{unit:'in',format:'a4',orientation:'portrait'} };
  await html2pdf().from(container).set(opt).save();
  document.body.removeChild(container);
}

function escapeHtml(s){ return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;'); }

// Log page view on load
document.addEventListener('DOMContentLoaded', ()=>{
  API.logActivity({ email: currentUser ? currentUser.email : '', name: currentUser ? currentUser.name : 'Guest', anonymous: currentUser?0:1, page: window.location.pathname + window.location.hash, action: 'view' });
  // load saved itineraries for current user
  setTimeout(()=>{ if (typeof loadMyItineraries === 'function') loadMyItineraries(); }, 200);
});

// Load admin-posted events marquee - REMOVED

async function loadMyItineraries(){
  const container = document.getElementById('myItineraries'); if (!container) return;
  container.innerHTML = 'Loading...';
  const email = currentUser ? currentUser.email : '';
  const res = await API.listItineraries(email);
  const list = res.itineraries || [];
  container.innerHTML = '';
  if (list.length === 0) { container.textContent = 'No saved itineraries.'; return; }
  list.forEach(it=>{
    const div = document.createElement('div');
    div.style.borderBottom = '1px solid #eee'; div.style.padding = '6px 0';
    const dests = it.destinations && typeof it.destinations === 'string' ? it.destinations : (Array.isArray(it.destinations)?it.destinations.join(', '):'');
    div.innerHTML = `<strong>${it.title}</strong> <small>(${it.days} days)</small><div style="font-size:0.9rem;color:#555">${dests}</div><div style="margin-top:6px;"><button class="downloadIt" data-id="${it.id}">Download</button> <button class="delIt" data-id="${it.id}">Delete</button></div>`;
    container.appendChild(div);
  });
  container.querySelectorAll('.downloadIt').forEach(btn=>btn.addEventListener('click', async e=>{ const id=e.target.dataset.id; const r = await API.getItinerary(id); const it = r.itinerary; if (it) await generateItineraryPDF({title:it.title, days:it.days, destinations: it.destinations, user_name: it.user_name || it.user_name}); }));
  container.querySelectorAll('.delIt').forEach(btn=>btn.addEventListener('click', async e=>{ const id=e.target.dataset.id; if (!await showConfirm('Delete itinerary?')) return; const r = await API.deleteItinerary(id, currentUser?currentUser.email:''); if (r.success) loadMyItineraries(); else showSnackbar('Delete failed','error'); }));
}

// Simple example: predict attendance when viewing an event modal
async function getEventPrediction(location, capacity){
  const r = await API.predict({location:location, capacity: capacity||0});
  return r;
}


// ===== NAVIGATION SCROLLING =====

// Smooth scroll navigation
const navLinks = document.querySelectorAll(".nav-links a");
if (navLinks) {
  navLinks.forEach(link => {
    link.addEventListener("click", e => {
      e.preventDefault();
      const section = document.querySelector(link.getAttribute("href"));
      const navbar = document.querySelector(".navbar");
      if (section && navbar) {
        const navbarHeight = navbar.offsetHeight;
        window.scrollTo({
          top: section.offsetTop - navbarHeight + 20,
          behavior: "smooth"
        });
      }
    });
  });
}

// Navbar hide/show on scroll
let lastScrollTop = 0;
const navbar = document.querySelector('.navbar');

if (navbar) {
  window.addEventListener('scroll', function() {
    let currentScroll = window.pageYOffset || document.documentElement.scrollTop;
    if (currentScroll > lastScrollTop) {
      navbar.classList.add('hidden');
    } else {
      navbar.classList.remove('hidden');
    }
    lastScrollTop = currentScroll <= 0 ? 0 : currentScroll;
  }, false);
}

// ===== PARALLAX EFFECTS =====

// Mouse parallax for hero
document.addEventListener("mousemove", (e) => {
  const back = document.querySelector(".layer-back");
  const mid = document.querySelector(".layer-mid");
  const front = document.querySelector(".layer-front");
  
  if (back || mid || front) {
    const x = (window.innerWidth / 2 - e.clientX) / 80;
    const y = (window.innerHeight / 2 - e.clientY) / 80;
    if (back) back.style.transform = `translateX(${x}px) translateY(${y}px) scale(2)`;
    if (mid) mid.style.transform = `translateX(${x * 1.5}px) translateY(${y * 1.5}px) scale(1.3)`;
    if (front) front.style.transform = `translateX(${x * 3}px) translateY(${y * 3}px) scale(1.1)`;
  }
});

// Events parallax
function eventsParallaxHandler() {
  const wrapper = document.querySelector('.upcoming-events-wrapper');
  if (!wrapper) return;
  const back = wrapper.querySelector('.events-bg-back');
  const front = wrapper.querySelector('.events-bg-front');
  const rect = wrapper.getBoundingClientRect();
  const winH = window.innerHeight;
  const pct = Math.min(1, Math.max(0, (winH - rect.top) / (winH + rect.height)));
  if (back) back.style.transform = `translateY(${(pct * 30 - 15).toFixed(2)}px)`;
  if (front) front.style.transform = `translateY(${(pct * -20 + 10).toFixed(2)}px)`;
}

window.addEventListener('scroll', eventsParallaxHandler);
window.addEventListener('resize', eventsParallaxHandler);
document.addEventListener('DOMContentLoaded', eventsParallaxHandler);
// Google Sign-in removed - now using simple name/email inputs for feedback

// ===== FESTIVAL ANALYTICS CHARTS =====

let hourlyFestivalChart = null;
let phaseFestivalChart = null;
let festivalMap = null;
let festivalMarkers = [];

function initFestivalCharts() {
  if (typeof Chart === 'undefined') return;
  // ensure canvas elements exist before trying to get context
  const el1 = document.getElementById('hourlyFestivalChart');
  const el2 = document.getElementById('phaseFestivalChart');
  if (!el1 && !el2) {
    // nothing to initialise on this page
    return;
  }
  try {
    if (el1) {
      const ctx = el1.getContext('2d');
      hourlyFestivalChart = new Chart(ctx, {
        type: 'line',
        data: { labels: [], datasets: [{ label: 'Visitors', data: [], borderColor: '#00ffb3', backgroundColor: 'rgba(0,255,179,0.18)', tension:0.3, fill:true }] },
        options: { responsive:true, plugins:{legend:{display:false}}, scales:{ x:{ ticks:{ color:'#fff'}}, y:{ beginAtZero:true, ticks:{ color:'#fff'} } } }
      });
    }

    if (el2) {
      const ctx2 = el2.getContext('2d');
      phaseFestivalChart = new Chart(ctx2, {
        type: 'bar',
        data: { labels: ['Before Festival','During Festival','After Festival'], datasets: [{ data: [0,0,0], backgroundColor:['rgba(0,255,179,0.6)','rgba(255,80,80,0.7)','rgba(80,160,255,0.7)'] }] },
        options: { responsive:true, plugins:{legend:{display:false}}, scales:{ x:{ ticks:{ color:'#fff'}}, y:{ beginAtZero:true, ticks:{ color:'#fff'} } } }
      });
    }
  } catch (e) { console.warn('initFestivalCharts error', e); }
}

function initFestivalMap() {
  if (festivalMap) return;
  const mapEl = document.getElementById('festivalMap');
  if (!mapEl) return;
  festivalMap = L.map(mapEl).setView([13.1448, 123.7435], 13);
  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '&copy; OpenStreetMap contributors'
  }).addTo(festivalMap);
}

function clearFestivalMarkers(){
  (festivalMarkers||[]).forEach(m=>{ try{ m.setMap(null); }catch(e){} });
  festivalMarkers = [];
}

function renderFestivalHeat(points){
  if (!festivalMap || !points) return;
  clearFestivalMarkers();
  const maxAtt = points.reduce((m,p)=>Math.max(m,p.attendance||0), 0) || 1;
  points.forEach(p => {
    const norm = Math.sqrt((p.attendance||0) / maxAtt);
    const minR = 40, maxR = 600;
    const radius = Math.round(minR + (maxR - minR) * norm);
    const color = p.intensity > 0.6 ? '#ff5050' : (p.intensity > 0.3 ? '#ffa050' : '#00c878');
    const circle = L.circle([p.lat, p.lon], {
      color: color,
      fillColor: color,
      fillOpacity: 0.32,
      radius: radius
    }).addTo(festivalMap);
    festivalMarkers.push(circle);
    circle.bindPopup(`<strong>${p.location}</strong><br/>Visitors: ${p.attendance}<br/>Intensity: ${p.intensity}`);
  });
  if (points.length > 0) {
    try {
      const bounds = L.latLngBounds(points.map(p => [p.lat, p.lon]));
      festivalMap.fitBounds(bounds);
    } catch (e) { /* ignore */ }
  }
}

// Admin attendance moved to admin dashboard; functions removed from public script.

async function loadFestivalAnalytics(params={}){
  try{
    initFestivalCharts();
    initFestivalMap();
    const q = new URLSearchParams(params).toString();
    const res = await fetch(apiUrl('api.php?action=festival_analytics&'+q));
    const data = await res.json();
    if (!data.success) return;

    // update charts
    if (hourlyFestivalChart){
      hourlyFestivalChart.data.labels = data.hourly_labels || [];
      hourlyFestivalChart.data.datasets[0].data = data.hourly_values || [];
      hourlyFestivalChart.update();
    }

    if (phaseFestivalChart){
      phaseFestivalChart.data.datasets[0].data = [data.phase.before||0, data.phase.during||0, data.phase.after||0];
      phaseFestivalChart.update();
    }

    // update confidence
    const cEl = document.getElementById('confidenceValue');
    if (cEl) cEl.innerText = (data.ai_confidence || 0) + '%';

    // render heatmap points
    if (data.heatmap_points && data.heatmap_points.length>0){
      renderFestivalHeat(data.heatmap_points);
    }
  } catch (e){ console.error('loadFestivalAnalytics error', e); }
}

// Kick off analytics when DOM ready
document.addEventListener('DOMContentLoaded', function(){
  // only initialize if relevant elements are present
  if (document.getElementById('hourlyFestivalChart') || document.getElementById('phaseFestivalChart') || document.getElementById('festivalMap')) {
    try{ loadFestivalAnalytics(); }catch(e){console.warn('festival analytics init failed', e);} 
  }
});

// ===== EVENTS MANAGEMENT (API) =====

async function getEvents() {
  try {
    const result = await EventsAPI.getAll();
    return result.success ? result.data || [] : [];
  } catch (error) {
    console.error('Error fetching events:', error);
    return [];
  }
}

async function renderEventsOnSite() {
  const container = document.getElementById('eventsContainer');
  if (!container) return;

  const events = await getEvents();

  if (events.length === 0) {
    container.innerHTML = '<p class="empty-state">No upcoming events.</p>';
    return;
  }

  container.innerHTML = events.map(e => `
    <div class="card event-card" data-event-id="${e.id}">
      <div class="card-image-container" style="background:#000; height:160px; display:flex; align-items:center; justify-content:center; color:#fff;">
        ${e.video_path ? `<video src="${e.video_path}" muted loop style="width:100%; height:100%; object-fit:cover;"></video>` : '<span style="text-align:center;">🎬</span>'}
      </div>
      <div class="card-content">
        <h3>${e.title}</h3>
        <div class="event-meta">${new Date(e.event_date).toLocaleDateString()}</div>
        <p style="margin-top:8px;">${e.description || ''}</p>
        <div class="event-cta"><button class="feature-btn" onclick="openEventModalById(${e.id})">View Details</button></div>
      </div>
    </div>
  `).join('');

  // Attach hover play for videos
  document.querySelectorAll('#eventsContainer .card-image-container video').forEach(v => {
    v.addEventListener('mouseenter', () => { try { v.play(); } catch (e) { } });
    v.addEventListener('mouseleave', () => { try { v.pause(); v.currentTime = 0; } catch (e) { } });
  });
}

async function openEventModalById(id) {
  try {
    const result = await EventsAPI.getById(id);
    if (result.success) {
      const ev = result.data;
      openEventModal(ev.title, ev.video_path || '', ev.description || '', new Date(ev.event_date).toLocaleDateString());
      // Track this event view and fetch alerts
      trackPlace(ev.title, 'view');
      const alerts = await trackEventAlerts(id);
      if (alerts.length > 0) {
        setTimeout(() => displayEventAlerts(alerts), 500);
      }
    }
  } catch (error) {
    console.error('Error opening event:', error);
  }
}

// Load events on page load
if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', function() {
    if (typeof renderEventsOnSite === 'function') renderEventsOnSite();
  });
} else {
  if (typeof renderEventsOnSite === 'function') renderEventsOnSite();
}

// Listen for storage changes from other tabs
window.addEventListener('storage', function(e) {
  if (e.key === 'events' && typeof renderEventsOnSite === 'function') {
    renderEventsOnSite();
  }
});

// ===== SHOP DETAIL MODAL =====

// Global cache for shops and products
let shopsCache = {};
let productsCache = {};

// Load shops from backend API
async function loadShopsFromBackend() {
  try {
    const response = await fetch(apiUrl('api.php?action=list_shops'));
    const data = await response.json();
    if (Array.isArray(data.shops)) {
      shopsCache = {};
      data.shops.forEach(shop => {
        shopsCache[shop.id] = {
          id: shop.id,
          name: shop.name || shop.NAME || 'Unknown Shop',
          description: shop.description,
          address: shop.address,
          contact: shop.contact || 'N/A',
          owner: shop.owner || 'Local Artisan',
          image: shop.image || 'https://via.placeholder.com/400?text=' + encodeURIComponent(shop.name || shop.NAME || 'Shop')
        };
      });
      return shopsCache;
    }
  } catch (error) {
    console.error('Error loading shops:', error);
  }
  return shopsCache;
}

// Load products from backend API
async function loadProductsFromBackend() {
  try {
    const response = await fetch(apiUrl('api.php?action=list_products'));
    const data = await response.json();
    if (Array.isArray(data.products)) {
      productsCache = {};
      data.products.forEach(product => {
        const shopId = product.shop_id;
        if (!productsCache[shopId]) {
          productsCache[shopId] = [];
        }
        productsCache[shopId].push({
          id: product.id,
          name: product.name,
          description: product.description,
          price: '₱' + parseFloat(product.price).toFixed(2),
          image: product.image || 'https://via.placeholder.com/400?text=' + encodeURIComponent(product.name),
          shop_name: product.shop_name || '',
          owner_name: product.owner_name || '',
          // Prefer category_name returned by backend, fallback to product.category or derive from name
          category: product.category_name || product.category || product.name.split(' ')[0] || 'Product'
        });
      });
      return productsCache;
    }
  } catch (error) {
    console.error('Error loading products:', error);
  }
  return productsCache;
}

// Render shops dynamically on the page
async function renderShopsOnPage() {
  const container = document.getElementById('shopsContainer');
  if (!container) return;

  // Load shops and products from backend
  await loadShopsFromBackend();
  await loadProductsFromBackend();

  // Clear existing content
  container.innerHTML = '';

  // ONLY display admin-created shops from backend (no fallback to demo database)
  if (Object.keys(shopsCache).length === 0) {
    container.innerHTML = '<div style="padding: 40px; text-align: center; color: #999;">No shops available yet. Admin is building the shop catalog.</div>';
    return;
  }

  // Render each shop with simplified card layout
  Object.entries(shopsCache).forEach(([shopId, shop]) => {
    const card = document.createElement('div');
    card.className = 'card shop-card';
    card.setAttribute('data-shop-id', shopId);
    card.onclick = () => openShopModal(shopId);
    card.style.width = '100%';
    card.style.cursor = 'pointer';

    card.innerHTML = `
      <div class="card-image-container">
        <img src="${shop.image}" alt="${shop.name}" onerror="this.src='https://via.placeholder.com/600x400?text=${encodeURIComponent(shop.name)}'" />
        <div class="shop-badge">Local</div>
      </div>
      <div class="card-content">
        <div class="shop-header"><h3>${shop.name}</h3></div>
        <div class="shop-actions"><button class="btn btn-primary" onclick="event.stopPropagation(); openShopModal(${shopId})">View Shop</button></div>
      </div>
    `;

    container.appendChild(card);
  });
}

// Initialize shops on page load
document.addEventListener('DOMContentLoaded', () => {
  renderShopsOnPage();
});

const shopsDatabase = {
  1: {
    name: 'Angelis Est 1993',
    image: 'https://www.vigattintourism.com/assets/article_main_photos/optimize/1352189242eS4o7LLo.jpg',
    address: '123 Rizal Avenue, Downtown Legazpi City',
    contact: '09094552734 | angelisest1993@email.com',
    owner: 'Maria Santos',
    description: 'Eco-friendly abaca bags handcrafted by local artisans using sustainable materials. Perfect souvenirs showcasing Bicolano craftsmanship and environmental consciousness. Each piece tells a story of tradition and quality.',
    products: [
      { name: 'Woven Tote Bag', category: 'Bags', price: '₱850', description: 'Large everyday tote made from pure abaca fiber with sturdy handles', image: 'https://images.unsplash.com/photo-1548036328-c9fa89d128fa?w=400&h=400&fit=crop' },
      { name: 'Market Basket', category: 'Bags', price: '₱650', description: 'Traditional woven basket for shopping and storage with natural finish', image: 'https://images.unsplash.com/photo-1599599810694-b3b4efaf1838?w=400&h=400&fit=crop' },
      { name: 'Clutch Purse', category: 'Accessories', price: '₱450', description: 'Compact abaca clutch with magnetic closure for evening outings', image: 'https://images.unsplash.com/photo-1548036328-c9fa89d128fa?w=400&h=400&fit=crop' },
      { name: 'Shoulder Bag', category: 'Bags', price: '₱950', description: 'Spacious shoulder bag with adjustable strap and zipper pocket', image: 'https://images.unsplash.com/photo-1594938298603-c8148c4dae35?w=400&h=400&fit=crop' },
      { name: 'Woven Belt', category: 'Accessories', price: '₱350', description: 'Traditional woven belt with decorative metal buckle', image: 'https://images.unsplash.com/photo-1548036328-c9fa89d128fa?w=400&h=400&fit=crop' }
    ]
  },
  2: {
    name: 'Mayon Keychains',
    image: 'https://blogger.googleusercontent.com/img/b/R29vZ2xl/AVvXsEgrciS164aO9CdQXkD4y8pzVQJunuAmNDFHru88P-oEGSPVUNHScaViXrI0r6HU0t13GbY69mnJ-74RJkUKPue44D0_491zHZs2ML2pzku7FJaMbdVnk8L7IEArh6cVN8Qv0GUbkDsOnw0/s800/20130728-BICOL-D80-0038.jpg',
    address: '456 Peñaranda Street, Legazpi City',
    contact: '(052) 481-XXXX | keychain.shop@email.com',
    owner: 'Juan Dela Cruz',
    description: 'Handmade souvenir keychains inspired by Mayon Volcano and local landmarks. Perfect gifts for tourists and collectors. Each keychain is uniquely crafted with attention to detail.',
    products: [
      { name: 'Mayon Volcano Keychain', category: 'Souvenirs', price: '₱120', description: 'Miniature Mayon Volcano with metal ring and detailed carving', image: 'https://images.unsplash.com/photo-1572635196237-14b3f281503f?w=400&h=400&fit=crop' },
      { name: 'Wildlife Park Souvenir Set', category: 'Souvenirs', price: '₱150', description: 'Exclusive souvenir collection featuring local wildlife and nature reserve themes', image: 'https://images.unsplash.com/photo-1599643478518-a784e5dc4c8f?w=400&h=400&fit=crop' },
      { name: 'Wooden Charm', category: 'Accessories', price: '₱100', description: 'Hand-carved wooden keychain charm with natural wood finish', image: 'https://images.unsplash.com/photo-1535585209827-a7611917a7c0?w=400&h=400&fit=crop' },
      { name: 'Beaded Keychain', category: 'Accessories', price: '₱150', description: 'Colorful beaded design with tassel and durable string', image: 'https://images.unsplash.com/photo-1515627556474-7fd3a84f7338?w=400&h=400&fit=crop' },
      { name: 'Metal Badge', category: 'Souvenirs', price: '₱180', description: 'Enamel metal badge of Legazpi City with key ring attachment', image: 'https://images.unsplash.com/photo-1599643478518-a784e5dc4c8f?w=400&h=400&fit=crop' },
      { name: 'Rubber Charm Pack', category: 'Souvenirs', price: '₱80', description: 'Set of 3 cute rubber keycharms with fun designs', image: 'https://images.unsplash.com/photo-1572635196237-14b3f281503f?w=400&h=400&fit=crop' }
    ]
  },
  3: {
    name: 'Bicol Spices & Products',
    image: 'https://blogger.googleusercontent.com/img/b/R29vZ2xl/AVvXsEg8-jO0LB9riUt9zFI1XNxSDcJdepzxDH6ep8JPOcKVNfHlLc7kPGVq3gX31C4QV7JBFmXW1KWpnkj1VMZW4TaqeZNZ51k9roRlt-8BcKJL7o9HTdJ0sAGzyfnopbA6zECp10v0wUQeqng/s800/20130728-BICOL-D80-0041.jpg',
    address: '789 Caedo Street, Commercial District, Legazpi',
    contact: '(052) 482-XXXX | bicol.spices@email.com',
    owner: 'Rosa Fernandez',
    description: 'Authentic Bicolano spices and traditional products that capture the essence of Bicol cuisine. All products are locally sourced, naturally prepared, and packed with flavor.',
    products: [
      { name: 'Bicol Express Paste', category: 'Spices', price: '₱280', description: 'Ready-to-cook paste for famous Bicol Express with authentic flavor', image: 'https://images.unsplash.com/photo-1596040306464-137b7aea60be?w=400&h=400&fit=crop' },
      { name: 'Red Hot Chili Powder', category: 'Spices', price: '₱150', description: 'Ground local red chili pepper with intense heat and flavor', image: 'https://images.unsplash.com/photo-1596040306464-137b7aea60be?w=400&h=400&fit=crop' },
      { name: 'Gabi Chips', category: 'Snacks', price: '₱120', description: 'Crispy taro root chips perfectly seasoned with herbs and salt', image: 'https://images.unsplash.com/photo-1569718776027-bcf26d29c2f3?w=400&h=400&fit=crop' },
      { name: 'Dried Fish (Tuyo)', category: 'Seafood', price: '₱380', description: 'Traditional dried fish from Albay Gulf with premium quality', image: 'https://images.unsplash.com/photo-1627068797174-a5b0fa1e96b3?w=400&h=400&fit=crop' },
      { name: 'Coconut Jam (Ube)', category: 'Food', price: '₱200', description: 'Sweet purple yam spread made from fresh local ube', image: 'https://images.unsplash.com/photo-1599599810694-b3b4efaf1838?w=400&h=400&fit=crop' },
      { name: 'Pili Nuts', category: 'Snacks', price: '₱320', description: 'Roasted and salted local pili nuts with crunchy texture', image: 'https://images.unsplash.com/photo-1599599810694-b3b4efaf1838?w=400&h=400&fit=crop' },
      { name: 'Spice Mix Bundle', category: 'Spices', price: '₱450', description: 'Pack of 3 different specialty spices for cooking enthusiasts', image: 'https://images.unsplash.com/photo-1596040306464-137b7aea60be?w=400&h=400&fit=crop' }
    ]
  }
};

function openShopModal(shopId) {
  // Try to get shop from cache first, then fallback to database
  let shop = shopsCache[shopId] || shopsDatabase[shopId];
  if (!shop) return;

  document.getElementById('shopModalName').textContent = shop.name;
  document.getElementById('shopModalImage').src = shop.image || 'https://via.placeholder.com/800x400?text=' + encodeURIComponent(shop.name);
  document.getElementById('shopModalAddress').textContent = shop.address || '📍 Address not available';
  document.getElementById('shopModalContact').textContent = shop.contact || '📞 Contact not available';
  document.getElementById('shopModalOwner').textContent = shop.owner || shop.owner_name || 'Unknown Owner';
  document.getElementById('shopModalDescription').textContent = shop.description;

  // Get products for this shop from cache ONLY (no hardcoded fallback)
  let shopProducts = productsCache[shopId] || [];
  
  // Get unique categories from products
  const categories = shopProducts.length > 0 ? [...new Set(shopProducts.map(p => p.category))] : [];
  
  // Create category filter buttons
  const filterDiv = document.getElementById('shopCategoryFilter');
  filterDiv.innerHTML = '';
  filterDiv.style.cssText = 'display:flex; gap:10px; flex-wrap:wrap; justify-content:center; align-items:center;';
  
  // Add "All Products" button
  const allBtn = document.createElement('button');
  allBtn.textContent = 'All Products';
  allBtn.style.cssText = 'padding:10px 18px; border:2px solid #667eea; background:#667eea; color:white; border-radius:6px; cursor:pointer; font-weight:600; font-size:0.95rem; transition:all 0.3s;';
  allBtn.onmouseover = () => {
    allBtn.style.background = '#764ba2';
    allBtn.style.borderColor = '#764ba2';
    allBtn.style.transform = 'translateY(-2px)';
    allBtn.style.boxShadow = '0 4px 12px rgba(102,126,234,0.3)';
  };
  allBtn.onmouseout = () => {
    allBtn.style.background = '#667eea';
    allBtn.style.borderColor = '#667eea';
    allBtn.style.transform = 'translateY(0)';
    allBtn.style.boxShadow = 'none';
  };
  allBtn.onclick = () => displayShopProducts(shopId, null);
  filterDiv.appendChild(allBtn);
  
  categories.forEach(cat => {
    const btn = document.createElement('button');
    btn.textContent = cat;
    btn.style.cssText = 'padding:10px 18px; border:2px solid #ddd; background:white; color:#666; border-radius:6px; cursor:pointer; font-weight:600; transition:all 0.3s; font-size:0.95rem;';
    btn.onmouseover = () => {
      btn.style.borderColor = '#667eea';
      btn.style.color = '#667eea';
      btn.style.transform = 'translateY(-2px)';
      btn.style.boxShadow = '0 4px 12px rgba(102,126,234,0.2)';
    };
    btn.onmouseout = () => {
      btn.style.borderColor = '#ddd';
      btn.style.color = '#666';
      btn.style.transform = 'translateY(0)';
      btn.style.boxShadow = 'none';
    };
    btn.onclick = () => {
      document.querySelectorAll('#shopCategoryFilter button').forEach(b => {
        b.style.background = 'white';
        b.style.color = '#666';
        b.style.borderColor = '#ddd';
      });
      btn.style.background = '#667eea';
      btn.style.color = 'white';
      btn.style.borderColor = '#667eea';
      displayShopProducts(shopId, cat);
    };
    filterDiv.appendChild(btn);
  });
  
  // Display all products by default
  displayShopProducts(shopId, null);
  
  // Show modal with proper flex centering
  const modal = document.getElementById('shopDetailModal');
  modal.style.display = 'flex';
  document.body.style.overflow = 'hidden';
}

function displayShopProducts(shopId, category) {
  // Get products from cache ONLY (no hardcoded fallback)
  let products = productsCache[shopId] || [];
  
  if (category) {
    products = products.filter(p => p.category === category);
  }
  
  const listDiv = document.getElementById('shopProductsList');
  listDiv.innerHTML = '';
  listDiv.style.cssText = 'display:grid; grid-template-columns:repeat(auto-fill, minmax(280px, 1fr)); gap:20px; width:100%;';
  
  if (products.length === 0) {
    const emptyDiv = document.createElement('div');
    emptyDiv.style.cssText = 'grid-column: 1/-1; text-align:center; padding:40px 20px; color:#999;';
    emptyDiv.innerHTML = '<p style="font-size:1rem; margin:0;">No products available yet</p>';
    listDiv.appendChild(emptyDiv);
    return;
  }
  
  products.forEach(prod => {
    const item = document.createElement('div');
    item.style.cssText = 'background:white; border-radius:10px; overflow:hidden; border:1px solid #e0e0e0; cursor:pointer; transition:all 0.3s ease; box-shadow:0 2px 8px rgba(0,0,0,0.1);';
    
    item.innerHTML = `
      <div style="width:100%; height:200px; overflow:hidden; background:#f0f0f0; position:relative;">
        <img src="${prod.image}" alt="${prod.name}" style="width:100%; height:100%; object-fit:cover; transition:transform 0.3s ease;" onerror="this.src='https://via.placeholder.com/400?text=${encodeURIComponent(prod.name)}'" />
        <div style="position:absolute; top:0; left:0; width:100%; height:100%; background:rgba(102,126,234,0.1); opacity:0; transition:opacity 0.3s ease;" class="product-overlay"></div>
      </div>
      <div style="padding:15px;">
        <h4 style="margin:0 0 5px 0; color:#333; font-size:1rem;">${prod.name}</h4>
        <span style="display:inline-block; background:#667eea; color:white; padding:4px 8px; border-radius:4px; font-size:0.8rem; font-weight:600; margin-bottom:10px;">${prod.category}</span>
        <p style="margin:8px 0; color:#666; font-size:0.9rem; line-height:1.4;">${prod.description}</p>
        <div style="display:flex; justify-content:space-between; align-items:center; margin-top:12px;">
          <p style="margin:0; color:#27ae60; font-size:1.3rem; font-weight:bold;">${prod.price}</p>
          <button onclick="viewProductDetail(event)" style="background:#667eea; color:white; border:none; padding:6px 12px; border-radius:4px; cursor:pointer; font-weight:600; font-size:0.85rem; transition:all 0.2s;">View</button>
        </div>
      </div>
    `;
    
    const button = item.querySelector('button');
    button.onmouseover = () => {
      button.style.background = '#764ba2';
      button.style.transform = 'scale(1.05)';
    };
    button.onmouseout = () => {
      button.style.background = '#667eea';
      button.style.transform = 'scale(1)';
    };
    
    const img = item.querySelector('img');
    const overlay = item.querySelector('.product-overlay');
    
    item.onmouseover = () => {
      item.style.transform = 'translateY(-8px) scale(1.02)';
      item.style.boxShadow = '0 12px 30px rgba(102,126,234,0.35)';
      item.style.borderColor = '#667eea';
      img.style.transform = 'scale(1.1)';
      overlay.style.opacity = '1';
    };
    
    item.onmouseout = () => {
      item.style.transform = 'translateY(0) scale(1)';
      item.style.boxShadow = '0 2px 8px rgba(0,0,0,0.1)';
      item.style.borderColor = '#e0e0e0';
      img.style.transform = 'scale(1)';
      overlay.style.opacity = '0';
    };
    
    item.addEventListener('click', (e) => {
      if(e.target.textContent !== 'View') {
        openProductModal(prod);
      }
    });
    
    listDiv.appendChild(item);
  });
}

function viewProductDetail(event) {
  event.stopPropagation();
  // Get product info from parent elements
  const button = event.target;
  const card = button.closest('div').parentElement.parentElement;
  const productName = card.querySelector('h4').textContent;
  const productImage = card.querySelector('img').src;
  const productPrice = card.querySelector('p:last-of-type').previousElementSibling.textContent;
  const productCategory = card.querySelector('span').textContent;
  const productDesc = card.querySelector('p').textContent;
  
  openProductModal({name: productName, image: productImage, price: productPrice, category: productCategory, description: productDesc});
}

function openProductModal(product) {
  const modal = document.createElement('div');
  modal.style.cssText = 'position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.85); display:flex; align-items:center; justify-content:center; z-index:9999;';
  
  const content = document.createElement('div');
  content.style.cssText = 'background:white; border-radius:12px; width:90%; max-width:700px; max-height:90vh; overflow-y:auto; padding:0;';
  
  content.innerHTML = `
    <div style="position:sticky; top:0; background:white; padding:15px; border-bottom:1px solid #e0e0e0; display:flex; justify-content:space-between; align-items:center; z-index:1;">
      <h2 style="margin:0; color:#333; font-size:1.3rem;">${product.name}</h2>
      <button onclick="this.closest('div').parentElement.parentElement.remove()" style="background:none; border:none; font-size:24px; cursor:pointer; color:#999;">✕</button>
    </div>
    
    <div style="padding:25px;">
      <div style="width:100%; height:350px; overflow:hidden; border-radius:10px; margin-bottom:20px; background:#f0f0f0;">
        <img src="${product.image}" style="width:100%; height:100%; object-fit:cover;" />
      </div>
      
      <div style="background:#f8f9fa; padding:20px; border-radius:10px; margin-bottom:20px;">
        <p style="margin:0 0 15px 0; font-size:0.95rem; color:#666; line-height:1.6;">${product.description}</p>
        
        <div style="display:grid; grid-template-columns:1fr 1fr; gap:15px; margin-top:15px;">
          <div style="background:white; padding:12px; border-radius:8px; border-left:4px solid #667eea;">
            <p style="margin:0 0 5px 0; color:#999; font-size:0.85rem;">Category</p>
            <p style="margin:0; font-weight:600; color:#333;">${product.category}</p>
          </div>
          <div style="background:white; padding:12px; border-radius:8px; border-left:4px solid #27ae60;">
            <p style="margin:0 0 5px 0; color:#999; font-size:0.85rem;">Price</p>
            <p style="margin:0; font-weight:600; color:#27ae60; font-size:1.3rem;">${product.price}</p>
          </div>
        </div>
      </div>
      
      <div style="background:#e3f2fd; padding:15px; border-radius:8px; border-left:4px solid #667eea; margin-bottom:20px;">
        <p style="margin:0; color:#1976d2; font-size:0.9rem;"><strong>ℹ️ Tip:</strong> Visit the shop to see this product in person and inquire about bulk orders or customization options!</p>
      </div>
      
      <button onclick="this.parentElement.parentElement.parentElement.remove()" style="width:100%; padding:12px; background:#667eea; color:white; border:none; border-radius:6px; cursor:pointer; font-weight:600; font-size:0.95rem;">Close</button>
    </div>
  `;
  
  modal.appendChild(content);
  document.body.appendChild(modal);
  
  modal.addEventListener('click', function(e) {
    if(e.target === modal) modal.remove();
  });
}

function closeShopModal() {
  document.getElementById('shopDetailModal').style.display = 'none';
  document.body.style.overflow = 'auto';
}

function closeShopModalFunc() {
  closeShopModal();
}

// Close modal when clicking outside
document.addEventListener('click', function(e) {
  const modal = document.getElementById('shopDetailModal');
  if (modal && e.target === modal) {
    closeShopModal();
  }
});





// Feedback marquee removed - keeping only feedback form functionality


// Session ID management for anonymous tracking
function getSessionId() {
  let sid = localStorage.getItem('analyticSessionId');
  if (!sid) {
    sid = 'sess_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
    localStorage.setItem('analyticSessionId', sid);
  }
  return sid;
}

// Track anonymous visitor session
async function trackAnonymousSession() {
  try {
    const navigator_ua = navigator.userAgent;
    const device_type = /mobile|android|iphone|ipad|phone/i.test(navigator_ua) ? 'Mobile' : 'Desktop';
    const data = {
      session_id: getSessionId(),
      ip_address: 'client',
      city: 'Unknown',
      country: 'Unknown',
      device_type: device_type,
      page_views: 1,
      total_time_minutes: 0
    };
    const res = await fetch(apiUrl('api.php?action=track_anonymous'), {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(data)
    });
    return res.json();
  } catch (e) {
    console.log('Analytics tracking not available');
  }
}

// Track place view or click
async function trackPlace(placeName, trackType = 'view') {
  try {
    const data = { place_name: placeName, type: trackType };
    const res = await fetch(apiUrl('api.php?action=track_place'), {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(data)
    });
    return res.json();
  } catch (e) {
    console.log('Place tracking not available');
  }
}

// Track shop interaction
async function trackShop(shopId, shopName, trackType = 'view') {
  try {
    const data = { shop_id: shopId, shop_name: shopName, type: trackType };
    const res = await fetch(apiUrl('api.php?action=track_shop'), {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(data)
    });
    return res.json();
  } catch (e) {
    console.log('Shop tracking not available');
  }
}

// Track event alerts when event is viewed
async function trackEventAlerts(eventId) {
  try {
    const res = await fetch(apiUrl('api.php?action=get_event_alerts&event_id=' + eventId));
    const data = await res.json();
    return data.alerts || [];
  } catch (e) {
    console.log('Event alerts not available');
    return [];
  }
}

// Display event alerts in a modal if any exist
function displayEventAlerts(alerts) {
  if (!alerts || alerts.length === 0) return;
  
  const alertMap = {
    'do': { emoji: '✅', label: 'DO', color: '#27ae60' },
    'dont': { emoji: '❌', label: 'DO NOT', color: '#e74c3c' },
    'bring': { emoji: '🎒', label: 'BRING', color: '#f39c12' },
    'dont_bring': { emoji: '🚫', label: 'DO NOT BRING', color: '#c0392b' }
  };
  
  let html = '<div style="background:white; padding:20px; border-radius:8px;">';
  html += '<h3>Safety Alerts for this Event</h3>';
  alerts.forEach(a => {
    const info = alertMap[a.alert_type] || { emoji: '📌', label: a.alert_type, color: '#667eea' };
    html += `<div style="margin:10px 0; padding:10px; background:#f0f0f0; border-left:4px solid ${info.color};">
      <strong>${info.emoji} ${info.label}</strong><br/>
      ${a.content}
    </div>`;
  });
  html += '</div>';
  
  showSnackbar(html, 'info'); // Simple alert; could be enhanced to modal
}

// ===== GUEST-SIDE LOADERS FOR DESTINATIONS, EXPERIENCES, FESTIVALS =====

// Store destinations globally for filtering
window.destinationsCache = [];

async function loadDestinationsFromBackend(){
  try {
    console.log('🔄 Loading destinations and categories...');
    const [destRes, catRes] = await Promise.all([
      fetch(apiUrl('api.php?action=list_destinations')),
      fetch(apiUrl('api.php?action=list_destination_categories'))
    ]);
    const destinations = (await destRes.json()).destinations || [];
    const categories = (await catRes.json()).categories || [];
    console.log('📊 Destinations count:', destinations.length, 'Categories count:', categories.length);
    window.destinationsCache = destinations;
    
    const container = document.getElementById('adminDestinationsContainer');
    if (!container) return;
    
    if (destinations.length === 0) {
      container.innerHTML = '';
      return;
    }
    
    // Build category tabs
    let html = '<div class="destination-filters" style="display:flex; gap:10px; margin-bottom:20px; overflow-x:auto; padding-bottom:8px; flex-wrap:wrap; justify-content:center; align-items:center;">';
    html += '<button class="dest-category-tab active" onclick="filterDestinationsByCategory(null, this)" style="padding:8px 16px; border:2px solid #667eea; background:#667eea; color:white; border-radius:20px; cursor:pointer; font-weight:600; white-space:nowrap;">All Destinations</button>';
    categories.forEach(cat => {
      html += `<button class="dest-category-tab" onclick="filterDestinationsByCategory(${cat.id}, this)" style="padding:8px 16px; border:2px solid #ddd; background:transparent; color:#333; border-radius:20px; cursor:pointer; font-weight:600; white-space:nowrap;">${cat.name}</button>`;
    });
    html += '</div>';
    html += '<div id="destinationsCardContainer" class="card-grid"></div>';
    
    container.innerHTML = html;
    
    // Display all destinations by default
    displayDestinationsByFilter(destinations, null);
  } catch (e) {
    console.error('loadDestinationsFromBackend error:', e);
  }
}

function displayDestinationsByFilter(destinations, categoryId) {
  const cardContainer = document.getElementById('destinationsCardContainer');
  let filtered = destinations;
  
  if (categoryId) {
    filtered = destinations.filter(d => d.category_id == categoryId);
  }
  
  if (filtered.length === 0) {
    cardContainer.innerHTML = '<div style="grid-column:1/-1; text-align:center; padding:40px; color:#999;">No destinations in this category</div>';
    return;
  }
  
  let html = '';
  filtered.forEach((d, idx) => {
    const img = d.image ? d.image : 'https://via.placeholder.com/400x300?text=' + encodeURIComponent(d.name);
    html += `
      <div class="card destination-card" data-card-index="${idx}" data-title="${d.name}" data-image="${img}" data-description="${d.description ? d.description : (d.location || '')}" style="width:100%;">
        <div class="card-image-container">
          <img src="${img}" alt="${d.name}" onerror="this.src='https://via.placeholder.com/400x300'">
        </div>
        <div class="card-content">
          <h3 style="word-wrap:break-word; overflow-wrap:break-word; white-space:normal;">${d.name}</h3>
          <p style="margin:0; color:#666; word-wrap:break-word; overflow-wrap:break-word;">${d.location ? '📍 ' + d.location : ''}</p>
        </div>
      </div>
    `;
  });
  cardContainer.innerHTML = html;
  
  // Add click listeners to all destination cards
  document.querySelectorAll('.destination-card').forEach(card => {
    card.addEventListener('click', function() {
      const title = this.dataset.title;
      const image = this.dataset.image;
      const description = this.dataset.description;
      openCardModal(title, image, description);
    });
  });
}

function filterDestinationsByCategory(categoryId, btn) {
  const tabs = document.querySelectorAll('.dest-category-tab');
  tabs.forEach(tab => {
    tab.style.background = 'transparent';
    tab.style.color = '#333';
    tab.style.borderColor = '#ddd';
  });
  
  if (btn) {
    btn.style.background = '#667eea';
    btn.style.color = 'white';
    btn.style.borderColor = '#667eea';
  }
  
  displayDestinationsByFilter(window.destinationsCache, categoryId);
}

async function loadExperiencesFromBackend(){
  try {
    const endpoint = apiUrl('api.php?action=list_experiences');
    console.log('🔄 Loading experiences from:', endpoint);
    const res = await fetch(endpoint);
    const data = await res.json();
    console.log('📊 Experiences data:', data);
    let experiences = data.experiences || [];
    // if destinations have been loaded, avoid showing any experience that has the same title
    if (window.destinationsCache && window.destinationsCache.length) {
      const destNames = window.destinationsCache.map(d=>d.name.toLowerCase());
      experiences = experiences.filter(e=> !destNames.includes((e.title||'').toLowerCase()));
    }
    const container = document.getElementById('adminExperiencesContainer');
    if (!container) return;
    
    if (experiences.length === 0) {
      container.innerHTML = '';
      return;
    }
    
    let html = '';
    
    experiences.forEach((x, idx) => {
      const img = x.image ? x.image : 'https://via.placeholder.com/400x300?text=' + encodeURIComponent(x.title);
      html += `
        <div class="experience-card" data-card-index="${idx}" data-title="${x.title}" data-image="${img}" data-description="${x.description || ''}" style="cursor: pointer; transition: all 0.3s ease; border-radius: 16px; overflow: hidden; background: linear-gradient(135deg, rgba(102,126,234,0.08), rgba(118,75,162,0.05)); border: 2px solid rgba(102,126,234,0.2); display: flex; flex-direction: column; height: 100%; box-shadow: 0 10px 30px rgba(0,0,0,0.2);" onmouseover="this.style.transform='translateY(-8px)'; this.style.borderColor='rgba(255,122,24,0.5)'; this.style.boxShadow='0 20px 50px rgba(255,122,24,0.2)'" onmouseout="this.style.transform='translateY(0)'; this.style.borderColor='rgba(102,126,234,0.2)'; this.style.boxShadow='0 10px 30px rgba(0,0,0,0.2)'">
          <div style="position: relative; height: 240px; overflow: hidden; background: linear-gradient(135deg, #667eea, #764fa2); flex-shrink: 0;">
            <img src="${img}" alt="${x.title}" style="width: 100%; height: 100%; object-fit: cover; transition: transform 0.4s ease;" onmouseover="this.style.transform='scale(1.08)'" onmouseout="this.style.transform='scale(1)'" onerror="this.src='https://via.placeholder.com/400x300'">
            <div style="position: absolute; inset: 0; background: linear-gradient(135deg, rgba(255,122,24,0.15), transparent); pointer-events: none;"></div>
          </div>
          <div style="flex: 1; padding: 20px; display: flex; flex-direction: column; justify-content: space-between;">
            <div>
              <h3 class="text-heading" style="color: white; font-weight: 700; font-size: 1.1rem; margin: 0 0 8px 0; word-wrap: break-word; overflow-wrap: break-word; animation-delay: 0.2s; opacity: 0;">${x.title}</h3>
              <p style="color: #a0aec0; font-size: 0.85rem; margin: 0; line-height: 1.5; word-wrap: break-word; overflow-wrap: break-word;">${x.type ? '📍 ' + x.type : '✨ Local Activity'}</p>
            </div>
            <p class="text-paragraph" style="color: #cbd5e1; font-size: 0.9rem; margin: 12px 0 0 0; line-height: 1.6; word-wrap: break-word; overflow-wrap: break-word; white-space: normal; animation-delay: 0.3s; opacity: 0;">${x.description ? x.description : 'Experience the local charm'}</p>
          </div>
        </div>
      `;
    });
    
    container.innerHTML = html;
    
    // Add click listeners to all experience cards
    document.querySelectorAll('.experience-card').forEach(card => {
      card.addEventListener('click', function() {
        const title = this.dataset.title;
        const image = this.dataset.image;
        const description = this.dataset.description;
        openCardModal(title, image, description);
      });
    });
  } catch (e) {
    console.error('loadExperiencesFromBackend error:', e);
  }
}

async function loadFestivalsFromBackend(){
  try {
    const res = await fetch(apiUrl('api.php?action=list_festivals'));
    const data = await res.json();
    const festivals = data.festivals || [];
    const container = document.getElementById('adminFestivalsContainer');
    if (!container) return;
    
    if (festivals.length === 0) {
      container.innerHTML = '';
      return;
    }
    
    let html = '';
    
    festivals.forEach((f, idx) => {
      const isVideo = f.image && f.image.endsWith('.mp4');
      const mediaUrl = f.image ? f.image : 'https://via.placeholder.com/400x300?text=' + encodeURIComponent(f.name);
      
      let mediaHtml = '';
      if (isVideo) {
        mediaHtml = `
          <video style="width:100%; height:100%; object-fit:cover;" muted autoplay loop playsinline>
            <source src="${mediaUrl}" type="video/mp4">
          </video>
        `;
      } else {
        mediaHtml = `<img src="${mediaUrl}" alt="${f.name}" style="width: 100%; height: 100%; object-fit: cover; transition: transform 0.4s ease;" onmouseover="this.style.transform='scale(1.08)'" onmouseout="this.style.transform='scale(1)'" onerror="this.src='https://via.placeholder.com/400x300'">`;
      }
      
      html += `
        <div class="festival-card" data-card-index="${idx}" data-title="${f.name}" data-image="${mediaUrl}" data-description="${f.description || ''}" data-date_start="${f.date_start || ''}" data-date_end="${f.date_end || ''}" style="cursor: pointer; transition: all 0.3s ease; border-radius: 16px; overflow: hidden; background: linear-gradient(135deg, rgba(255,122,24,0.08), rgba(255,106,136,0.05)); border: 2px solid rgba(255,122,24,0.25); display: flex; flex-direction: column; height: 100%; box-shadow: 0 10px 30px rgba(0,0,0,0.2);" onmouseover="this.style.transform='translateY(-8px)'; this.style.borderColor='rgba(255,122,24,0.6)'; this.style.boxShadow='0 20px 50px rgba(255,122,24,0.25)'" onmouseout="this.style.transform='translateY(0)'; this.style.borderColor='rgba(255,122,24,0.25)'; this.style.boxShadow='0 10px 30px rgba(0,0,0,0.2)'">
          <div style="position: relative; height: 240px; overflow: hidden; background: linear-gradient(135deg, #ff7a18, #ff6a88); flex-shrink: 0;">
            ${mediaHtml}
            <div style="position: absolute; inset: 0; background: linear-gradient(135deg, rgba(255,122,24,0.2), transparent); pointer-events: none;"></div>
          </div>
          <div style="flex: 1; padding: 20px; display: flex; flex-direction: column; justify-content: center; align-items: center;">
            <h3 class="text-heading" style="color: white; font-weight: 700; font-size: 1.1rem; margin: 0; word-wrap: break-word; overflow-wrap: break-word; animation-delay: 0.2s; opacity: 0; text-align: center;">${f.name}</h3>
          </div>
        </div>
      `;
    });
    
    container.innerHTML = html;
    
    // Add click listeners to all festival cards
    document.querySelectorAll('.festival-card').forEach(card => {
      card.addEventListener('click', function() {
        const title = this.dataset.title;
        const image = this.dataset.image;
        const description = this.dataset.description;
        const date_start = this.dataset.date_start;
        const date_end = this.dataset.date_end;
        openCardModal(title, image, description, date_start, date_end);
      });
    });
  } catch (e) {
    console.error('loadFestivalsFromBackend error:', e);
  }
}

// Show hotel details from map popup and highlight on map
function showHotelDetailsFromMap(hotelDataStr) {
  try {
    const hotelData = JSON.parse(hotelDataStr);
    
    // Pan and zoom map to hotel if map instance exists
    if (window.hotelMapInstance) {
      window.hotelMapInstance.setView([hotelData.lat, hotelData.lon], 16);
    }
    
    const msg = `
🏨 ${hotelData.name}
⭐ Rating: ${hotelData.rating}/5
💰 Price: ₱${hotelData.ratePerNight}/night
📞 ${hotelData.phone}
📍 ${hotelData.address}
🏷️ ${hotelData.landmark}
📁 Category: ${hotelData.category}
↪ Distance to destination: ${hotelData.dist.toFixed(2)} km
    `;
    showSnackbar(msg, 'info', 5000);
  } catch (e) {
    console.error('Error showing hotel details:', e);
    showSnackbar('Hotel details unavailable', 'info');
  }
}

// Initialize Google Maps-based hotel map (replaces Leaflet implementation)
function initHotelMap(hotels, userLocation, destinations) {
  try {
    const container = document.getElementById('hotelMapContainer');
    if (!container) {
      console.warn('hotelMapContainer not found');
      return;
    }
    container.style.display = 'block';
    if (window.hotelMapInstance && window.hotelMapInstance.setMap) {
      window.hotelMapInstance.setMap(null);
    }
    if (!window.google || !google.maps) {
      console.error('Google Maps not available');
      return;
    }
    const map = new google.maps.Map(container, {
      center: { lat: userLocation.lat, lng: userLocation.lon },
      zoom: 14,
      mapTypeId: 'satellite'
    });
    const bounds = new google.maps.LatLngBounds();
    bounds.extend({ lat: userLocation.lat, lng: userLocation.lon });
    new google.maps.Marker({position:{lat:userLocation.lat,lng:userLocation.lon},map,title:'Your Location'});
    // add destinations
    if (destinations && Array.isArray(destinations)) {
      destinations.forEach(destName => {
        const destCoords = destinationCoords[destName];
        if (destCoords && destCoords.lat && destCoords.lon) {
          new google.maps.Marker({position:{lat:destCoords.lat,lng:destCoords.lon},map,title:destName,icon:'http://maps.google.com/mapfiles/ms/icons/green-dot.png'});
          bounds.extend({lat:destCoords.lat,lng:destCoords.lon});
        }
      });
    }
    // add hotels
    if (hotels && Array.isArray(hotels)) {
      hotels.forEach(hotel => {
        const lat = hotel.lat || hotel.latitude;
        const lon = hotel.lon || hotel.longitude;
        if (lat && lon) {
          new google.maps.Marker({position:{lat,lng:lon},map,title:hotel.name,icon:'http://maps.google.com/mapfiles/ms/icons/blue-dot.png'});
          bounds.extend({lat,lng:lon});
        }
      });
    }
    map.fitBounds(bounds);
    window.hotelMapInstance = map;
    console.log('Hotel map initialized (Google Maps)');
  } catch(error){
    console.error('Error initializing hotel map:', error);
  }
}

/* original Leaflet implementation commented out for reference
// Initialize Leaflet-based hotel map with markers, details, and routes
function initHotelMap(hotels, userLocation, destinations) {
  try {
    const container = document.getElementById('hotelMapContainer');
    if (!container) {
      console.warn('hotelMapContainer not found');
      return;
    }

    // Show the container
    container.style.display = 'block';

    // Remove existing map instance if it exists
    if (window.hotelMapInstance) {
      window.hotelMapInstance.remove();
    }

    // Initialize Leaflet map
    const map = L.map('hotelMapContainer').setView(
      [userLocation.lat, userLocation.lon],
      14
    );

    // Add OpenStreetMap tile layer
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
      attribution: '© OpenStreetMap contributors',
      maxZoom: 19
    }).addTo(map);

    // Add user location marker
    const userMarker = L.circleMarker(
      [userLocation.lat, userLocation.lon],
      {
        radius: 8,
        fillColor: '#3498db',
        color: '#2980b9',
        weight: 3,
        opacity: 1,
        fillOpacity: 0.8
      }
    ).addTo(map);

    userMarker.bindPopup(
      '<div style="text-align: center;"><strong>📍 Your Location</strong><br>(' +
      userLocation.lat.toFixed(4) + ', ' + userLocation.lon.toFixed(4) + ')</div>'
    );

    // Create bounds to fit all markers
    const bounds = L.latLngBounds([[userLocation.lat, userLocation.lon]]);

    // Track nearest hotels to each destination
    const hotelsByDestination = {};

    // Add destination markers
    if (destinations && Array.isArray(destinations)) {
      destinations.forEach((destName) => {
        const destCoords = destinationCoords[destName];
        const destData = destinationDatabase[destName];
        
        if (destCoords && destCoords.lat && destCoords.lon) {
          // Calculate distance from user to destination
          const dist = haversine(
            userLocation.lat,
            userLocation.lon,
            destCoords.lat,
            destCoords.lon
          );

          // Find hotels within 10km radius of this destination
          let nearestHotels = [];
          if (hotels && Array.isArray(hotels)) {
            nearestHotels = hotels.map(hotel => {
              const hotelLat = hotel.lat || hotel.latitude;
              const hotelLon = hotel.lon || hotel.longitude;
              if (hotelLat && hotelLon) {
                return {
                  ...hotel,
                  distToDest: haversine(hotelLat, hotelLon, destCoords.lat, destCoords.lon)
                };
              }
              return null;
            }).filter(h => h !== null && h.distToDest <= 10)
              .sort((a, b) => a.distToDest - b.distToDest);
          }

          hotelsByDestination[destName] = nearestHotels;

          // Create destination marker with green color
          const marker = L.marker([destCoords.lat, destCoords.lon], {
            title: destName,
            icon: L.icon({
              iconUrl: 'data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSIzMiIgaGVpZ2h0PSIzMiIgdmlld0JveD0iMCAwIDMyIDMyIj48cmVjdCB3aWR0aD0iMzIiIGhlaWdodD0iMzIiIGZpbGw9IiMyN2FlNjAiIHJ4PSI0Ii8+PHRleHQgeD0iNTAlIiB5PSI1MCUiIGR5PSIuM2VtIiB0ZXh0LWFuY2hvcj0ibWlkZGxlIiBmaWxsPSJ3aGl0ZSIgZm9udC1zaXplPSIyMCIgZm9udC13ZWlnaHQ9ImJvbGQiPnA=',
              iconSize: [32, 32],
              iconAnchor: [16, 32],
              popupAnchor: [0, -32]
            })
          }).addTo(map);

          // Create destination popup with image and nearest hotels
          let hotelListHtml = '';
          if (nearestHotels.length > 0) {
            hotelListHtml = '<div style="margin-top: 10px; border-top: 1px solid #ddd; padding-top: 8px;"><strong style="font-size: 11px; color: #333;">🏨 Nearby Hotels (<10km):</strong><ul style="margin: 6px 0; padding-left: 18px; font-size: 11px;">';
            nearestHotels.forEach(hotel => {
              const hotelLat = hotel.lat || hotel.latitude;
              const hotelLon = hotel.lon || hotel.longitude;
              const hotelData = JSON.stringify({
                id: hotel.id, name: hotel.name, rating: hotel.rating, 
                ratePerNight: hotel.ratePerNight, phone: hotel.phone, 
                address: hotel.address, landmark: hotel.landmark, 
                category: hotel.category, image: hotel.image,
                lat: hotelLat, lon: hotelLon, dist: hotel.distToDest
              }).replace(/"/g, '&quot;');
              hotelListHtml += `<li style="margin: 3px 0;"><span style="cursor: pointer; color: #0066cc; text-decoration: underline;" onclick="showHotelDetailsFromMap('${hotelData.replace(/'/g, "\\'")}')"><strong>${hotel.name}</strong></span><br><span style="color: #666;">↪ ${hotel.distToDest.toFixed(2)} km away | ⭐${hotel.rating}</span></li>`;
            });
            hotelListHtml += '</ul></div>';
          }

          const popupContent = `
            <div style="width: 250px; font-family: Arial, sans-serif;">
              <img src="${destData ? destData.image : 'https://via.placeholder.com/250x120'}" alt="${destName}" style="width: 100%; height: 100px; object-fit: cover; border-radius: 4px; margin-bottom: 6px;">
              <h4 style="margin: 4px 0; color: #27ae60; font-size: 14px;">${destName}</h4>
              <p style="margin: 3px 0; font-size: 10px; color: #666;">${destData ? destData.description : ''}</p>
              <p style="margin: 4px 0; font-size: 10px;"><strong>📍 From you:</strong> ${dist.toFixed(2)} km</p>
              <p style="margin: 3px 0; font-size: 9px; color: #999;">${destData ? destData.category : ''}</p>
              ${hotelListHtml}
            </div>
          `;

          marker.bindPopup(popupContent, { maxWidth: 300 });

          // Add route polyline from user to destination
          L.polyline(
            [
              [userLocation.lat, userLocation.lon],
              [destCoords.lat, destCoords.lon]
            ],
            {
              color: '#27ae60',
              weight: 2,
              opacity: 0.5,
              dashArray: '8, 4'
            }
          ).addTo(map);

          // Add routes from destination to nearby hotels
          if (nearestHotels.length > 0) {
            nearestHotels.forEach((hotel, idx) => {
              const hotelLat = hotel.lat || hotel.latitude;
              const hotelLon = hotel.lon || hotel.longitude;
              if (hotelLat && hotelLon) {
                // Draw route from destination to hotel
                L.polyline(
                  [
                    [destCoords.lat, destCoords.lon],
                    [hotelLat, hotelLon]
                  ],
                  {
                    color: '#f39187',
                    weight: 1.5,
                    opacity: 0.4,
                    dashArray: '3, 3'
                  }
                ).addTo(map).bindPopup(`<strong>${hotel.name}</strong><br>${hotel.distToDest.toFixed(2)} km to this destination`);
              }
            });
          }

          bounds.addLatLng([destCoords.lat, destCoords.lon]);
        }
      });
    }

    // Add hotel markers
    if (hotels && Array.isArray(hotels)) {
      let hotelCount = 0;
      hotels.forEach((hotel, index) => {
        // Use lat/lon properties (the hotel database uses these)
        const lat = hotel.lat || hotel.latitude;
        const lon = hotel.lon || hotel.longitude;
        
        if (lat && lon) {
          hotelCount++;
          // Calculate distance from user to hotel
          const dist = haversine(
            userLocation.lat,
            userLocation.lon,
            lat,
            lon
          );

          // Find which destinations have this hotel as a nearest option
          let associatedDestinations = [];
          Object.entries(hotelsByDestination).forEach(([destName, nearestHotels]) => {
            if (nearestHotels.some(h => h.id === hotel.id)) {
              associatedDestinations.push(destName);
            }
          });

          // Create hotel marker
          const marker = L.marker([lat, lon], {
            title: hotel.name,
            icon: L.icon({
              iconUrl: 'data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSIzMiIgaGVpZ2h0PSIzMiIgdmlld0JveD0iMCAwIDMyIDMyIj48cmVjdCB3aWR0aD0iMzIiIGhlaWdodD0iMzIiIGZpbGw9IiNmMzkxODciIHJ4PSI0Ii8+PHRleHQgeD0iNTAlIiB5PSI1MCUiIGR5PSIuM2VtIiB0ZXh0LWFuY2hvcj0ibWlkZGxlIiBmaWxsPSJ3aGl0ZSIgZm9udC1zaXplPSIyMCIgZm9udC13ZWlnaHQ9ImJvbGQiPvCfkofwn5CHPC90ZXh0Pjwvc3ZnPg==',
              iconSize: [32, 32],
              iconAnchor: [16, 32],
              popupAnchor: [0, -32]
            })
          }).addTo(map);

          // Add numbered badge to hotel marker
          L.circleMarker([lat, lon], {
            radius: 14,
            fillColor: '#fff',
            color: '#f39187',
            weight: 2,
            opacity: 1,
            fillOpacity: 0.9,
            className: 'hotel-badge'
          }).addTo(map).bindPopup(`<strong style="font-size: 12px;">Hotel #${hotelCount}</strong>`);

          // Add tooltip with hotel name
          L.tooltip({
            permanent: false,
            direction: 'top'
          }).setContent(`${hotel.name} (${hotelCount})`).setLatLng([lat, lon]).addTo(map);

          // Create detailed popup with associated destinations
          let destListHtml = '';
          if (associatedDestinations.length > 0) {
            destListHtml = `<div style="margin-top: 10px; border-top: 1px solid #f0f0f0; padding-top: 8px;"><strong style="font-size: 11px; color: #27ae60;">📍 Great for visiting:</strong><div style="font-size: 11px; color: #666; margin-top: 4px;">${associatedDestinations.join(', ')}</div></div>`;
          }

          const popupContent = `
            <div style="width: 250px; font-family: Arial, sans-serif;">
              <div style="background: #f39187; color: white; padding: 6px; border-radius: 4px; margin-bottom: 8px; text-align: center; font-weight: bold;">Hotel #${hotelCount}</div>
              <img src="${hotel.image}" alt="${hotel.name}" style="width: 100%; height: 120px; object-fit: cover; border-radius: 4px;">
              <div style="padding: 10px 0;">
                <h4 style="margin: 8px 0; color: #333;">${hotel.name}</h4>
                <p style="margin: 4px 0; font-size: 12px; color: #666;">${hotel.address}</p>
                <p style="margin: 4px 0;"><strong>⭐ ${hotel.rating}/5</strong> | <strong>₱${hotel.ratePerNight}/night</strong></p>
                <p style="margin: 4px 0; font-size: 12px;"><strong>📍 Distance from you:</strong> ${dist.toFixed(2)} km</p>
                <p style="margin: 4px 0; font-size: 12px;"><strong>📞 ${hotel.phone}</strong></p>
                <p style="margin: 8px 0 0 0; font-size: 11px; color: #27ae60; background: #f0f9f0; padding: 6px; border-radius: 3px;">
                  ✓ ${hotel.category} | ${hotel.landmark}
                </p>
                ${destListHtml}
              </div>
            </div>
          `;

          marker.bindPopup(popupContent, { maxWidth: 300 });

          // Add route polyline from user to hotel
          L.polyline(
            [
              [userLocation.lat, userLocation.lon],
              [lat, lon]
            ],
            {
              color: '#667eea',
              weight: 2,
              opacity: 0.4,
              dashArray: '5, 5'
            }
          ).addTo(map);

          bounds.addLatLng([lat, lon]);
        }
      });
    }

    // Fit map to show all markers with padding
    if (bounds.isValid()) {
      map.fitBounds(bounds, { padding: [50, 50] });
    }

    // Store the map instance globally so we can remove it later
    window.hotelMapInstance = map;

    console.log('Hotel map initialized with', hotels.length, 'hotels and', (destinations || []).length, 'destinations');
  } catch (error) {
    console.error('Error initializing hotel map:', error);
  }
}
*/

// ===== ROUTE VISUALIZATION WITH TRAFFIC COLORING =====
function displayRouteMap(eventId, routes) {
  try {
    const container = document.getElementById('routeMapContainer-' + eventId);
    if (!container || !window.google || !google.maps) return;

    // Clean up any existing map instances
    if (window['routeMap_' + eventId] && window['routeMap_' + eventId].setMap) {
      window['routeMap_' + eventId].setMap(null);
    }

    const routeMap = new google.maps.Map(container, {
      center: { lat: 13.1410, lng: 123.7470 },
      zoom: 12,
      mapTypeId: 'satellite'
    });

    const bounds = new google.maps.LatLngBounds();
    const colors = ['#4caf50', '#ff9800', '#e74c3c'];

    routes.forEach((route, idx) => {
      if (!route.coordinates || route.coordinates.length === 0) return;
      const trafficLevel = route.traffic_level || 0;
      const lineColor = colors[trafficLevel];

      const path = route.coordinates.map(coord => {
        if (Array.isArray(coord)) return { lat: coord[0], lng: coord[1] };
        if (typeof coord === 'object') return { lat: coord.lat || coord[0], lng: coord.lng || coord[1] };
        return coord;
      });
      path.forEach(p => bounds.extend(p));

      const poly = new google.maps.Polyline({
        path,
        strokeColor: lineColor,
        strokeOpacity: 0.8,
        strokeWeight: 4,
        map: routeMap
      });

      if (route.name) {
        const info = new google.maps.InfoWindow({
          content: `<strong>${route.name}</strong><br/>Distance: ${route.distance} km<br/>Duration: ${route.duration} mins`
        });
        poly.addListener('click', () => info.open(routeMap));
      }

      if (path.length > 0) {
        const startCoord = path[0];
        const endCoord = path[path.length - 1];

        new google.maps.Circle({
          center: startCoord,
          radius: 6,
          fillColor: '#667eea',
          fillOpacity: 1,
          strokeColor: '#fff',
          strokeWeight: 2,
          map: routeMap
        }).addListener('click', () => new google.maps.InfoWindow({ content: '<strong>Start</strong>' }).open(routeMap, new google.maps.Marker({ position: startCoord, map: routeMap })));

        new google.maps.Circle({
          center: endCoord,
          radius: 6,
          fillColor: '#e74c3c',
          fillOpacity: 1,
          strokeColor: '#fff',
          strokeWeight: 2,
          map: routeMap
        }).addListener('click', () => new google.maps.InfoWindow({ content: '<strong>End</strong>' }).open(routeMap, new google.maps.Marker({ position: endCoord, map: routeMap })));
      }
    });

    if (!bounds.isEmpty()) routeMap.fitBounds(bounds, { padding: 50 });
    window['routeMap_' + eventId] = routeMap;
  } catch (error) {
    console.error('Error displaying route map:', error);
  }
}
console.log('🟢 DOMContentLoaded fired - Loading admin content...');
  
// Initialize Google Sign-In when page loads (renders button if GSI script present)
document.addEventListener('DOMContentLoaded', function(){
  // Run migration on first load
  fetch(apiUrl('run_migration.php')).catch(()=>{});
  
  // Load admin-created content
  loadDestinationsFromBackend();
  loadExperiencesFromBackend();
  loadFestivalsFromBackend();
  
  // Track anonymous session
  trackAnonymousSession();
  
  // Add click tracking to destination cards
  document.querySelectorAll('[data-destination]').forEach(el => {
    el.addEventListener('click', function() {
      const place = this.getAttribute('data-destination');
      if (place) trackPlace(place, 'click');
    });
  });
  
  // Add click tracking to shop elements  
  document.querySelectorAll('[data-shop-id]').forEach(el => {
    el.addEventListener('click', function() {
      const shopId = this.getAttribute('data-shop-id');
      const shopName = this.getAttribute('data-shop-name') || 'Shop ' + shopId;
      if (shopId) trackShop(shopId, shopName, 'click');
    });
  });
  
  // Load event alerts when event modal opens
  window.addEventListener('openEventModal', function(e) {
    if (e.detail && e.detail.eventId) {
      trackEventAlerts(e.detail.eventId).then(alerts => {
        if (alerts.length > 0) displayEventAlerts(alerts);
      });
    }
  });
  
  // Google Sign-in removed - feedback now uses simple name/email inputs
  
  // ========== SMOOTH ANIMATIONS & PARALLAX EFFECTS ==========
  
  // Initialize scroll animation observer
  const observerOptions = {
    threshold: 0.1,
    rootMargin: '0px 0px -50px 0px'
  };
  
  const observer = new IntersectionObserver(function(entries) {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        entry.target.classList.add('visible');
        observer.unobserve(entry.target);
      }
    });
  }, observerOptions);
  
  // Apply observer to scroll-animate elements
  document.querySelectorAll('.scroll-animate').forEach(el => {
    observer.observe(el);
  });
  
  // Add stagger animation class to children
  document.querySelectorAll('[data-stagger]').forEach(parent => {
    parent.querySelectorAll('[data-stagger-child]').forEach((child, idx) => {
      child.classList.add('stagger-child');
    });
  });
  
  // Parallax scroll effect
  window.addEventListener('scroll', () => {
    const scrolled = window.pageYOffset;
    document.querySelectorAll('.parallax-bg').forEach(el => {
      const scrollSpeed = el.dataset.scrollSpeed || 0.5;
      el.style.backgroundPosition = `center ${scrolled * scrollSpeed}px`;
    });
    
    // Floating elements
    document.querySelectorAll('.floating').forEach(el => {
      const rect = el.getBoundingClientRect();
      if (rect.top < window.innerHeight && rect.bottom > 0) {
        el.style.transform = `translateY(${scrolled * 0.3}px) rotate(var(--rotate, 0deg))`;
      }
    });
  }, { passive: true });
  
  // Add animation classes on sections
  const sections = document.querySelectorAll('section');
  sections.forEach((section, index) => {
    if (index > 0) {
      section.style.animation = `slideInUp 0.8s ease-out ${index * 0.2}s forwards`;
      section.style.opacity = '0';
    }
  });
});

# AgriIntel - Smart Agriculture Platform

## Project Overview
AgriIntel is an AI-powered agriculture platform that helps farmers and vendors make better farming and business decisions through live data, AI predictions, and market analytics.

## Tech Stack
- **Backend**: PHP 8.2 with PDO
- **Database**: PostgreSQL
- **Frontend**: HTML, CSS, JavaScript
- **Maps**: Leaflet.js (OpenStreetMap)
- **Charts**: Chart.js

## Project Structure
```
AgriIntel/
├── public/                 # Web-accessible files
│   ├── index.php          # Front controller
│   ├── home.php           # Landing page
│   ├── login.php          # Login page
│   ├── register.php       # Registration page
│   ├── farmer-dashboard.php    # Farmer panel
│   ├── vendor-dashboard.php    # Vendor panel
│   ├── admin-dashboard.php     # Admin panel
│   └── assets/
│       └── css/
│           └── style.css  # Main stylesheet
├── includes/              # PHP libraries
│   ├── config.php        # Database configuration
│   └── functions.php     # Helper functions
└── database/             # Database schema
    └── schema.sql        # PostgreSQL schema
```

## Database Schema
- **users**: Stores farmer, vendor, and admin accounts
- **farms**: Farm information with GPS coordinates
- **crops**: Crop details with calculations
- **inquiries**: Vendor-farmer communication (legacy)
- **messages**: Bidirectional chat messages between farmers and vendors
- **weather_data**: Cached weather forecasts
- **analytics**: System metrics
- **complaints**: User feedback and complaints

## Features

### 1. Farmer Panel
- Register and login with role-based access
- Add farms with GPS location detection
- Interactive map for farm area selection
- Add crops with automatic calculations:
  - Seeds required
  - Estimated yield
  - Market price
  - Profit/loss estimation
- View all farms and crops in dashboard
- Real-time statistics
- **AI-Powered Features:**
  - AI crop recommendations based on weather and location
  - Personalized farming tips and best practices
  - Market insights and price predictions
  - Intelligent crop planning assistance
- **Bidirectional Chat System**:
  - Real-time messaging with vendors
  - View all conversations in one place
  - Unread message badges
  - Respond to vendor inquiries instantly

### 2. Vendor Panel
- Browse available crops from all farmers
- Filter by crop type and location
- View crop details: yield, price, harvest date
- **Bidirectional Chat System**:
  - Real-time messaging with farmers
  - View all conversations in one place
  - Unread message badges
  - Start new conversations with any farmer
- Legacy inquiry system (deprecated)

### 3. Admin Panel
- Monitor total farmers, vendors, crops, and inquiries
- View recent registrations
- Analytics charts:
  - Crops distribution (doughnut chart)
  - Profit trends over 6 months (line chart)
- Manage complaints with response system
- Real-time data visualization

## Key Calculations

### Seeds Required
Based on crop type and area (hectares)
- Wheat: 120 kg/ha
- Rice: 40 kg/ha
- Corn: 20 kg/ha
- Cotton: 15 kg/ha
- And more...

### Yield Estimation
- Wheat: 3000 kg/ha
- Rice: 4000 kg/ha
- Potato: 20000 kg/ha
- Tomato: 35000 kg/ha

### Profit Calculation
`Profit = (Yield × Market Price) - Production Cost`

## Environment Variables
- `DATABASE_URL`: PostgreSQL connection string
- `PGHOST`, `PGPORT`, `PGDATABASE`, `PGUSER`, `PGPASSWORD`: Database credentials
- `OPENAI_API_KEY`: OpenAI API key for AI-powered insights and recommendations
- `WEATHER_API_KEY`: OpenWeatherMap API key (optional - uses deterministic model if not set)
- `TRANSLATE_API_KEY`: Translation API key (optional - uses built-in catalog)

## Default Admin Credentials
- Email: admin@agriintel.com
- Password: password

## Weather Forecast Methodology

The 6-month weather forecast feature uses a **deterministic seasonal model** based on Indian agricultural climate patterns:

### Approach
- **Fully Deterministic**: Identical inputs (location, month) always produce identical forecasts
- **Seasonal Templates**: Uses predefined monthly temperature and rainfall patterns typical for Indian agriculture
- **Month-by-Month Projections**: Generates forecasts for the next 6 months from current date
- **Fixed Variation**: Deterministic monthly variation applied to seasonal baseline
- **Condition Classification**: Categorizes weather as "Sunny", "Partly Cloudy", "Rainy", or "Heavy Rainfall"

### Data Sources
- Seasonal averages based on Indian Meteorological Department patterns
- Deterministic monthly variation patterns
- Humidity calculated from rainfall correlation (60 + rainfall/5)

### Limitations
- Not suitable for real-time meteorological accuracy
- Provides general seasonal guidance for crop planning
- Should be validated with local agricultural extension services

## Translation System

Multi-language support implemented using **built-in translation catalog**:
- Languages: English, Hindi (हिंदी), Marathi (मराठी)
- Method: Pre-defined translations in `includes/translations.php`
- Function: `t('key')` returns translated string based on session language
- Coverage: Home page fully translated; other pages use English with extensible framework

To add translations to additional pages, wrap text in `t()` calls and add keys to the translation catalog.

## AI Features (Implemented)
- **AI Crop Recommendations**: Get personalized crop suggestions based on farm location, size, and weather patterns
- **Farming Insights**: Receive AI-generated tips on irrigation, pest management, and harvest timing
- **Market Intelligence**: AI-powered market analysis and price forecasting
- **Smart Decision Support**: Contextual advice tailored to each crop and farm condition

## Communication Features
- **Bidirectional Chat System**: Real-time messaging between farmers and vendors
  - Conversation list with unread badges
  - Message history for each contact
  - Automatic read status tracking
  - Clean, modern messaging UI
  - Works for both farmers and vendors
  - PostgreSQL-backed with optimized CTE queries

## Future Enhancements
- Real mandi price API integration (AGMARKNET)
- Expand multi-language translation to all pages
- Mobile app version
- SMS/Email notifications for inquiries
- Advanced analytics and reporting
- Soil quality analysis
- Disease detection using image recognition
- Integration with historical weather data for improved forecasting
- Voice-based AI assistant for farmers

## Development Notes
- PHP 8.2 installed
- PostgreSQL database initialized with schema
- Session-based authentication
- PDO for secure database queries
- Responsive design for mobile devices
- Interactive maps with Leaflet.js
- Data visualization with Chart.js

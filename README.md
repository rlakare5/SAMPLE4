# AgriIntel - Smart Agriculture Platform

## Overview
AgriIntel is an AI-powered agriculture platform that helps farmers and vendors make better farming and business decisions through live data, AI predictions, and market analytics.

## Features

### 🌱 Farmer Panel
- Register and login with role-based access
- Add farms with GPS location detection
- Interactive map for farm area selection using Leaflet.js
- Add crops with automatic calculations:
  - Seeds required based on crop type
  - Estimated yield predictions
  - Current market prices
  - Profit/loss estimation
- View all farms and crops in personalized dashboard
- Real-time statistics and analytics
- **6-Month Weather Forecasts** with seasonal patterns
- **AI-Powered Features:**
  - AI crop recommendations based on weather and location
  - Personalized farming tips and best practices
  - Market insights and price predictions
  - Intelligent crop planning assistance

### 💼 Vendor Panel
- Browse available crops from all farmers
- Filter crops by type and location
- View detailed crop information:
  - Crop type, quantity, and area
  - Harvest dates and location
  - Market prices and profit estimates
- Contact farmers through inquiry system
- Track sent inquiries

### 🧠 Admin Panel
- Monitor system metrics:
  - Total farmers and vendors
  - Total crops and inquiries
- View recent user registrations
- Analytics charts powered by Chart.js:
  - Crops distribution (doughnut chart)
  - Profit trends over 6 months (line chart)
- Complaint management system
- Real-time data visualization

## Tech Stack
- **Backend**: PHP 8.2 with PDO
- **Database**: PostgreSQL
- **Frontend**: HTML, CSS, JavaScript
- **Maps**: Leaflet.js (OpenStreetMap)
- **Charts**: Chart.js
- **AI**: OpenAI GPT-4o-mini for insights and recommendations

## Setup Instructions

### Prerequisites
- PHP 8.2 or higher
- PostgreSQL database
- OpenAI API key (for AI features)

### Environment Variables
Required environment variables (automatically set on Replit):
```
DATABASE_URL=postgresql://...
PGHOST=...
PGPORT=5432
PGDATABASE=...
PGUSER=...
PGPASSWORD=...
OPENAI_API_KEY=sk-...  # Get from https://platform.openai.com/api-keys
```

Optional:
```
WEATHER_API_KEY=...  # OpenWeatherMap API (uses deterministic model if not set)
TRANSLATE_API_KEY=...  # Translation API (uses built-in catalog if not set)
```

### Installation
1. Clone the repository
2. Set up environment variables
3. Initialize database with schema:
   ```bash
   psql $DATABASE_URL -f database/schema.sql
   ```
4. Start the PHP server:
   ```bash
   php -S 0.0.0.0:5000 -t public
   ```
5. Access the application at http://localhost:5000

## Default Credentials

### Admin Login
- **Email**: admin@agriintel.com
- **Password**: password

## Project Structure
```
AgriIntel/
├── public/                 # Web-accessible files
│   ├── index.php          # Front controller with routing
│   ├── home.php           # Landing page
│   ├── login.php          # Login page
│   ├── register.php       # Registration page
│   ├── farmer-dashboard.php    # Farmer panel
│   ├── weather.php        # Weather forecast page
│   ├── ai-insights.php    # AI recommendations page
│   ├── vendor-dashboard.php    # Vendor panel
│   ├── admin-dashboard.php     # Admin panel
│   ├── components/        # Reusable components
│   └── assets/
│       └── css/
│           └── style.css  # Main stylesheet
├── includes/              # PHP libraries
│   ├── config.php        # Database configuration
│   ├── functions.php     # Helper functions & AI integration
│   └── translations.php  # Multi-language support
├── database/             # Database schema
│   └── schema.sql        # PostgreSQL schema
└── README.md
```

## Database Schema
- **users**: Farmer, vendor, and admin accounts
- **farms**: Farm information with GPS coordinates
- **crops**: Crop details with automatic calculations
- **inquiries**: Vendor-farmer communication
- **weather_data**: Cached weather forecasts
- **analytics**: System metrics
- **complaints**: User feedback and complaint management

## Key Features Explained

### Automatic Crop Calculations
Based on scientific agricultural data for Indian farming:
- **Seeds Required**: Calculated per hectare (e.g., Wheat: 120 kg/ha, Rice: 40 kg/ha)
- **Yield Estimation**: Crop-specific yields (e.g., Potato: 20,000 kg/ha, Tomato: 35,000 kg/ha)
- **Profit Calculation**: `(Yield × Market Price) - Production Cost`

### Weather Forecasting
- Deterministic 6-month seasonal forecast model
- Based on Indian agricultural climate patterns
- Month-by-month temperature, rainfall, and humidity
- Weather conditions classification (Sunny, Rainy, Heavy Rainfall)

### AI Integration
Powered by OpenAI GPT-4o-mini:
- **Crop Recommendations**: Analyzes weather, location, and farm size
- **Farming Tips**: Personalized advice on irrigation, pest management, harvest timing
- **Market Intelligence**: Price trends and demand forecasts
- **Error Handling**: Graceful fallbacks with clear user messaging
- **Timeouts**: 15-second request timeout, 10-second connection timeout

### Multi-Language Support
- Languages: English, Hindi (हिंदी), Marathi (मराठी)
- Built-in translation catalog in `includes/translations.php`
- Function: `t('key')` for translated strings
- Extensible framework for adding more languages

## Security Features
- Password hashing with bcrypt
- SQL injection protection via PDO prepared statements
- Input sanitization for all user data
- Session-based authentication
- Role-based access control (farmer/vendor/admin)
- Environment-based API key management

## Future Enhancements
- Real mandi price API integration (AGMARKNET)
- Expand multi-language support to all pages
- Mobile app version
- SMS/Email notifications for inquiries
- Advanced analytics and reporting
- Soil quality analysis
- Disease detection using image recognition
- Historical weather data integration
- Voice-based AI assistant for farmers
- Caching for AI requests to reduce costs

## Contributing
Contributions are welcome! Please feel free to submit a Pull Request.

## License
This project is open source and available under the MIT License.

## Support
For issues or questions, please open an issue on GitHub or contact the development team.

---
**Built with ❤️ for farmers in India**

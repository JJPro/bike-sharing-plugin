GETTING DATA:
- ✅Cron to download new zipfile data at end of next month
	- zip URL: https://s3.amazonaws.com/hubway-data/{year}{month-1}-bluebikes-tripdata.zip
	- batch insert into db
- Lazy loading of daily weather data and save a copy locally
	- Boston Coord: -71.0589, 42.3601
	- API Endpoint: https://rapidapi.com/darkskyapis/api/dark-sky/endpoints
	- Data Attributes:
		- precipType: e.g. rain
		- temperatureMax
		- temperatureMin
		- humidity
		- pressure
		- windSpeed
		- windGust
		- cloudCover
		- uvIndex
		- visibility
		- ozone
- WP REST Endpoint for Frontend

Deployment Notes:
1. `SET GLOBAL local_infile = ON;` with root
2. enable mysqli.allow_local_infile from php ini file
# Amazon URL Support Setup

## Free Third-Party Service Integration

I've integrated **ScraperAPI** which offers **2,000 free requests per month** to handle Amazon and other blocked sites.

### Setup Steps:

1. **Sign up for ScraperAPI** (Free):
   - Go to https://www.scraperapi.com/
   - Create a free account
   - Get your API key from the dashboard

2. **Configure the API key**:
   
   **Option A: Environment Variable (Recommended)**
   ```bash
   # Add to your .env file
   SCRAPERAPI_KEY=your_api_key_here
   ```
   
   **Option B: Direct Configuration**
   ```php
   // Edit config/scraperapi.php
   return [
       'api_key' => 'your_api_key_here', // Set directly here
       'enabled' => true,
       'free_tier_limit' => 2000,
   ];
   ```

3. **Test the integration**:
   ```php
   // Test with Amazon URL
   $service = new UrlMetadataService();
   $result = $service->fetchMetadata('https://www.amazon.com/dp/B08N5WRWNW');
   ```

### How It Works:

1. **First attempt**: Direct fetch (works for most sites)
2. **If blocked**: ScraperAPI handles Amazon and other blocked sites
3. **Fallback**: Clear error message if all methods fail

### Free Tier Limits:

- **2,000 requests per month** (free)
- **Perfect for personal use** and small applications
- **No credit card required** for free tier

### Alternative Free Services:

If ScraperAPI doesn't work well, you can easily switch to:

1. **ScrapingBee** - Free credits for testing
2. **Apify** - Pay-per-event pricing
3. **QuickScraper** - Free trial

Just update the `fetchWithScraperAPI` method to use a different service.

### Benefits:

✅ **Amazon support** - Works with Amazon URLs  
✅ **Free tier** - 2,000 requests/month at no cost  
✅ **JavaScript rendering** - Handles dynamic content  
✅ **IP rotation** - Avoids blocking  
✅ **Easy setup** - Just add API key  

The service will automatically use ScraperAPI when available, and fall back to the direct method for other sites.

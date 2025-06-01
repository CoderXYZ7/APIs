# News and Events API Documentation

## Database Setup

1. Run the `init.sql` file to create the database schema and sample data
2. Update the database credentials in `api.php` (host, username, password)
3. Place `api.php` on your web server

## API Endpoints

### Base URL
```
http://your-domain.com/api.php
```

### News Endpoints

#### Get Latest News
```
GET /api.php?action=get&type=news&limit=10
```

#### Get News by ID
```
GET /api.php?action=get&type=news&id=1
```

#### Get News by Name/Title
```
GET /api.php?action=get&type=news&name=technology
```

#### Get News in Time Window
```
GET /api.php?action=get&type=news&from_date=2025-01-01&to_date=2025-12-31
```

#### Get News with Connected Events
```
GET /api.php?action=get&type=news&with_events=true
```

### Events Endpoints

#### Get Latest Events
```
GET /api.php?action=get&type=events&limit=10
```

#### Get Events by ID
```
GET /api.php?action=get&type=events&id=1
```

#### Get Events by Name/Title
```
GET /api.php?action=get&type=events&name=conference
```

#### Get Future Events (Top N)
```
GET /api.php?action=get&type=events&future_only=true&limit=5
```

#### Get Past Events
```
GET /api.php?action=get&type=events&past_only=true
```

#### Get Events in Date Range
```
GET /api.php?action=get&type=events&from_date=2025-06-01&to_date=2025-07-31
```

#### Get Events with Connected News
```
GET /api.php?action=get&type=events&with_news=true
```

## Parameters

### Common Parameters
- `action`: Always "get"
- `type`: "news" or "events"
- `id`: Specific item ID
- `name`: Search by title (partial match)
- `limit`: Maximum number of results
- `from_date`: Start date (YYYY-MM-DD or YYYY-MM-DD HH:MM:SS)
- `to_date`: End date (YYYY-MM-DD or YYYY-MM-DD HH:MM:SS)

### News Specific
- `with_events`: Set to "true" to include connected event IDs

### Events Specific
- `future_only`: Set to "true" to get only future events
- `past_only`: Set to "true" to get only past events
- `with_news`: Set to "true" to include connected news IDs

## Response Format

### Success Response
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "title": "News Title",
      "content": "News content...",
      "images": ["image1.jpg", "image2.jpg"],
      "created_at": "2025-06-01 10:00:00",
      "updated_at": "2025-06-01 10:00:00",
      "connected_event_ids": [1, 2]
    }
  ],
  "count": 1
}
```

### Error Response
```json
{
  "error": "Error message"
}
```

## Example Usage

### Get Latest 5 News Articles
```bash
curl "http://your-domain.com/api.php?action=get&type=news&limit=5"
```

### Get News with Connected Events
```bash
curl "http://your-domain.com/api.php?action=get&type=news&with_events=true"
```

### Get Next 3 Future Events
```bash
curl "http://your-domain.com/api.php?action=get&type=events&future_only=true&limit=3"
```

### Search Events by Name
```bash
curl "http://your-domain.com/api.php?action=get&type=events&name=conference"
```

### Get Events in June 2025
```bash
curl "http://your-domain.com/api.php?action=get&type=events&from_date=2025-06-01&to_date=2025-06-30"
```

## Database Schema

### Tables
- **news**: id, title, content, images (JSON), created_at, updated_at
- **events**: id, title, content, images (JSON), event_date, created_at, updated_at  
- **news_events**: Junction table for many-to-many relationships

### Relationships
- News can be connected to multiple events
- Events can be connected to multiple news articles
- Junction table maintains these relationships
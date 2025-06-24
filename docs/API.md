# EduLinks API Documentation

Bu sənəd EduLinks sisteminin REST API endpointlərini təsvir edir.

## Base URL
```
https://yourdomain.com/api
```

## Authentication
Bütün API endpointləri istifadəçi authentikasiyası tələb edir. Session-based authentication istifadə olunur.

## Response Format
Bütün API cavabları JSON formatındadır:

### Success Response:
```json
{
    "success": true,
    "message": "Success message",
    "data": {
        // Response data
    }
}
```

### Error Response:
```json
{
    "success": false,
    "error": "Error message",
    "code": 400
}
```

## Endpoints

### Link Management

#### Record Link Click
```
POST /api/links/{id}/click
```
Link kliklərini qeydə alır.

**Response:**
```json
{
    "success": true,
    "message": "Click recorded",
    "data": {
        "click_count": 15
    }
}
```

#### Get Link Details
```
GET /api/links/{id}
```
Link haqqında ətraflı məlumat əldə edir.

**Response:**
```json
{
    "success": true,
    "message": "Link details",
    "data": {
        "link": {
            "id": 1,
            "title": "Sample Document",
            "description": "Description",
            "type": "file",
            "url": null,
            "file_name": "document.pdf",
            "file_size_formatted": "2.5 MB",
            "click_count": 15,
            "is_featured": true,
            "is_active": true,
            "page_title": "Mathematics"
        }
    }
}
```

#### Toggle Link Featured Status (Admin only)
```
PUT /api/links/{id}/featured
```
Linkin featured statusunu dəyişir.

**Response:**
```json
{
    "success": true,
    "message": "Featured status updated",
    "data": {
        "is_featured": true
    }
}
```

### User Management

#### Get User's Accessible Pages
```
GET /api/user/pages
```
İstifadəçinin giriş icazəsi olan səhifələri qaytarır.

**Response:**
```json
{
    "success": true,
    "message": "User pages",
    "data": {
        "pages": [
            {
                "id": 1,
                "title": "Mathematics",
                "slug": "mathematics",
                "description": "Math resources",
                "color": "#007bff",
                "icon": "fas fa-calculator",
                "total_links": 5
            }
        ],
        "total": 1
    }
}
```

#### Get User Statistics
```
GET /api/user/stats
```
İstifadəçi statistikalarını qaytarır.

**Response:**
```json
{
    "success": true,
    "message": "User statistics",
    "data": {
        "accessible_pages": 3,
        "total_links": 25,
        "recent_links": [
            // Recent link objects
        ]
    }
}
```

### Page Management

#### Get Page Links
```
GET /api/pages/{id}/links
```
Səhifənin linklərini qaytarır.

**Response:**
```json
{
    "success": true,
    "message": "Page links",
    "data": {
        "page": {
            "id": 1,
            "title": "Mathematics",
            "slug": "mathematics"
        },
        "links": [
            // Link objects
        ],
        "total": 5
    }
}
```

#### Reorder Page Links (Admin only)
```
PUT /api/pages/{id}/links/reorder
```
Səhifədəki linklərin sırasını dəyişir.

**Request Body:**
```json
{
    "link_ids": [3, 1, 2, 5, 4]
}
```

**Response:**
```json
{
    "success": true,
    "message": "Link order updated"
}
```

### Search

#### Search Links
```
GET /api/search?q={query}&limit={limit}
```
Linkləri axtarır.

**Parameters:**
- `q` (required): Axtarış sorğusu (minimum 2 simvol)
- `limit` (optional): Nəticə sayı (default: 20, max: 100)

**Response:**
```json
{
    "success": true,
    "message": "Search results",
    "data": {
        "query": "mathematics",
        "links": [
            // Link objects
        ],
        "total": 3
    }
}
```

### File Management

#### Upload File
```
POST /api/files/upload
```
Fayl yükləyir.

**Request:** Multipart form data with file field

**Response:**
```json
{
    "success": true,
    "message": "File uploaded successfully",
    "data": {
        "file_path": "/uploads/2024/01/document.pdf",
        "file_name": "document.pdf",
        "file_size": 2621440,
        "file_type": "application/pdf",
        "file_url": "/uploads/2024/01/document.pdf"
    }
}
```

#### Get File Information
```
GET /api/files/{id}/info
```
Fayl haqqında məlumat əldə edir.

**Response:**
```json
{
    "success": true,
    "message": "File information",
    "data": {
        "link": {
            // Link object
        },
        "file_info": {
            "exists": true,
            "size": 2621440,
            "last_modified": "2024-01-15 10:30:00"
        }
    }
}
```

#### Delete File (Admin only)
```
DELETE /api/files/{id}
```
Fayl və bağlı linki silir.

**Response:**
```json
{
    "success": true,
    "message": "File and link deleted"
}
```

#### Get File Statistics (Admin only)
```
GET /api/files/stats
```
Fayl statistikalarını qaytarır.

#### Cleanup Temporary Files (Admin only)
```
POST /api/files/cleanup
```
Müvəqqəti faylları təmizləyir.

### System Management (Admin only)

#### Get System Health
```
GET /api/system/health
```
Sistemin sağlamlığını yoxlayır.

**Response:**
```json
{
    "success": true,
    "message": "System health",
    "data": {
        "status": "healthy",
        "timestamp": "2024-01-15 10:30:00",
        "database": {
            "status": "ok",
            "message": "Database connection is healthy"
        },
        "storage": {
            "status": "ok",
            "free_space": "50.2 GB",
            "total_space": "100 GB",
            "used_percent": 49.8
        },
        "uploads": {
            "status": "ok",
            "total_files": 125,
            "total_size": "2.1 GB",
            "average_size": "17.2 MB"
        }
    }
}
```

#### Get System Statistics (Admin only)
```
GET /api/system/stats
```
Sistem statistikalarını qaytarır.

**Response:**
```json
{
    "success": true,
    "message": "System statistics",
    "data": {
        "users": {
            "total": 25,
            "active": 20,
            "admins": 2
        },
        "pages": {
            "total": 8,
            "active": 7
        },
        "links": {
            "total": 156,
            "active": 145,
            "file_links": 98,
            "url_links": 47,
            "total_clicks": 2847
        },
        "storage": {
            "used_space": "2.1 GB",
            "free_space": "50.2 GB",
            "total_space": "100 GB",
            "used_percent": 4.18
        }
    }
}
```

## Error Codes

- `400` - Bad Request
- `401` - Unauthorized
- `403` - Forbidden
- `404` - Not Found
- `405` - Method Not Allowed
- `500` - Internal Server Error

## Rate Limiting

API-də rate limiting tətbiq edilməyib, lakin production mühitində tövsiyə olunur.

## CORS

CORS dəstəyi mövcud deyil. Əgər lazımdırsa, web server konfiqurasiyasında əlavə edilməlidir.

## Examples

### JavaScript/Fetch API
```javascript
// Record link click
fetch('/api/links/1/click', {
    method: 'POST',
    headers: {
        'Content-Type': 'application/json',
        'X-CSRF-Token': window.csrfToken
    }
})
.then(response => response.json())
.then(data => console.log(data));

// Search links
fetch('/api/search?q=mathematics&limit=10')
.then(response => response.json())
.then(data => console.log(data.data.links));
```

### cURL
```bash
# Get user pages
curl -X GET "https://yourdomain.com/api/user/pages" \
     -H "Cookie: edulinks_session=your_session_cookie"

# Upload file
curl -X POST "https://yourdomain.com/api/files/upload" \
     -H "Cookie: edulinks_session=your_session_cookie" \
     -F "file=@document.pdf"
```
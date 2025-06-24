<?php
/**
 * EduLinks API Routes
 * 
 * All API routes for the EduLinks system
 */

use App\Core\Router;

// API Link Management
Router::post('/api/links/{id}/click', 'ApiController@recordLinkClick');
Router::get('/api/links/{id}', 'ApiController@getLinkDetails');
Router::put('/api/links/{id}/featured', 'ApiController@toggleLinkFeatured');

// API User Management
Router::get('/api/user/pages', 'ApiController@getUserPages');
Router::get('/api/user/stats', 'ApiController@getUserStats');

// API Page Management
Router::get('/api/pages/{id}/links', 'ApiController@getPageLinks');
Router::put('/api/pages/{id}/links/reorder', 'ApiController@reorderPageLinks');

// API Search
Router::get('/api/search', 'ApiController@searchLinks');

// API System Management (Admin only)
Router::get('/api/system/health', 'ApiController@getSystemHealth');
Router::get('/api/system/stats', 'ApiController@getSystemStats');

// API File Management
Router::post('/api/files/upload', 'FileController@upload');
Router::get('/api/files/{id}/info', 'FileController@info');
Router::delete('/api/files/{id}', 'FileController@deleteFile');
Router::get('/api/files/stats', 'FileController@stats');
Router::post('/api/files/cleanup', 'FileController@cleanup');
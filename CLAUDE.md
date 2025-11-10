# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

HTTP Log Analyzer - A PHP web application that parses Apache/Nginx HTTP access logs to generate histograms and statistics for identifying problematic URLs and traffic patterns. The application runs in a Docker container with Nginx and PHP-FPM.

## Architecture

### Core Components

- **index.php**: Single-page application entry point with HTML form and results display
- **lib/lib.inc.php**: Contains `LogAnalyzer` class - the main parsing engine
  - Parses log files line-by-line using configurable regex patterns
  - Aggregates data into histograms (time-series) and detail tables
  - Tracks: requests, bytes transferred, 404s, IPs, User Agents
- **lib/_log_results.php**: Results display template with Google Charts integration
- **config.inc.php**: Log format configuration (regex patterns, field indices)

### Data Flow

1. User submits form with log path and filters → index.php
2. `LogAnalyzer` constructor reads entire log file into memory
3. Each log line matched against regex, filtered, and aggregated
4. Results stored in arrays (histograms by timestamp, detail lists by URL/IP/UA)
5. Display template renders Google Charts histograms and Bootstrap tables

### Log Parsing Configuration

The log format is defined via regex in `config.inc.php`:
- `$config['log_format']['regexp']`: Main parsing regex
- Field indices map regex capture groups to data fields (ip, url, date, time, bytes, status, method, user agent)
- Currently configured for Apache Combined Log Format with User Agent
- To support different log formats, adjust the regex and corresponding indices

## Docker Environment

### Starting the Application

```bash
docker-compose up
```

The application will be available at http://localhost

### Container Details

- Container name: `apachehisto_nginx`
- Base image: Ubuntu 24.04
- Stack: Nginx + PHP 8.1-FPM
- Working directory in container: `/var/www/apache-histo`
- User: `docker` (UID/GID from `.env`)

### Environment Configuration

Edit `.env` before building:
```bash
UID=1000  # Your user ID (run `id` to check)
GID=1000  # Your group ID
```

**Important**: After changing `.env`, rebuild with `docker-compose build`

### Accessing Logs

Log files must be accessible from within the container. Two approaches:
1. Place logs in `logs/` directory (mounted as volume)
2. Add volume mount in `docker-compose.yml`

Example log path in the UI: `/var/www/apache-histo/logs/access.log`

## XDebug Configuration

### Enable XDebug
```bash
./xdebug-enable.sh
```

This script:
- Detects host IP (Mac vs Linux)
- Configures `xdebug.ini` with correct `xdebug.client_host`
- Restarts PHP-FPM

### Disable XDebug
```bash
./xdebug-disable.sh
```

### PHPStorm Setup

Set server name to `apachehisto` in PHPStorm (matches `PHP_IDE_CONFIG` env var)

## Development Notes

### Performance Considerations

- **Memory**: Entire log file loaded into memory - large files (>1GB) may cause issues
- **Parsing Speed**: Regex matching done per line - very large files may timeout
- **Histogram Limit**: `$config['nb_bars_max']` (default 1000) prevents excessive data points

### Security Notes

- No authentication - designed for local development only
- Log path input is not sanitized - ensure proper file permissions
- `open_basedir` PHP directive may restrict file access

### Filtering Capabilities

The application supports filtering by:
- **URL**: Regex pattern matching
- **User Agent**: Substring matching
- **IP Address**: Substring matching
- **Date Range**: Start/end datetime (format: `22/11/2013 08:00`)
- **Log Line**: Regex applied to entire log line
- **Exclude Dependencies**: Checkbox to filter out static assets (.jpg, .css, .js, etc.)

### Drill-Down Feature

Tables include clickable URLs that apply filters to drill into specific traffic:
- Clicking a URL applies exact URL filter
- Clicking an IP applies IP filter
- Clicking a User Agent applies UA filter
- Filters are cumulative with existing query parameters

## File Structure

```
/
├── index.php              # Main entry point
├── config.inc.php         # Log format configuration
├── lib/
│   ├── lib.inc.php        # LogAnalyzer class
│   └── _log_results.php   # Results display template
├── docker/
│   ├── Dockerfile         # Container definition
│   ├── entrypoint.sh      # Container startup script
│   ├── nginx/             # Nginx configuration
│   └── php/               # PHP configuration
├── css/                   # Custom styles
├── js/                    # JavaScript files
├── bootstrap/             # Bootstrap framework
└── logs/                  # Log files (gitignored)
```

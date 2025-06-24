# EduLinks - Educational Document Management System

EduLinks, təhsil müəssisələri üçün hazırlanmış Linktree tipli sənəd və link paylaşım sistemidir. Sistem, müəllimlər və şagirdlər arasında təhsil materiallarının təşkilə edilmiş şəkildə paylaşılmasını təmin edir.

## 🌟 Xüsusiyyətlər

### Admin Panel
- **İstifadəçi İdarəetməsi**: Tam CRUD əməliyyatları ilə istifadəçi idarəetməsi
- **Səhifə İdarəetməsi**: Fənn və ya mövzu əsasında səhifələr yaradın
- **Link İdarəetməsi**: Fayl yükləmə və xarici linklərin idarə edilməsi
- **İcazə Sistemi**: Səhifə əsasında istifadəçi icazələri
- **Statistika Dashboard**: Sistem istifadəsi haqqında ətraflı hesabatlar

### İstifadəçi İnterfeysi
- **Responsive Design**: Bütün cihazlarla uyğun
- **Axtarış Sistemi**: Güclü axtarış və filtrleme
- **Klik Statistikaları**: Link kliklərinin izlənməsi
- **Fayl Önizləmə**: PDF və şəkillərin önizləməsi
- **Kategoriyalar**: Fənn və mövzu əsasında təşkil

### Güvenlik
- **Session İdarəetməsi**: Güvənli session idarəetməsi
- **CSRF Qoruma**: Cross-site request forgery qoruma
- **Fayl Təhlükəsizliyi**: Güvənli fayl yükləmə və validasiya
- **İcazə Kontrolü**: Səviyyə əsasında giriş kontrolü

## 🚀 Quraşdırma

### Tələblər
- PHP 8.1+
- PostgreSQL 13+
- Nginx/Apache
- Composer

### Docker ilə Quraşdırma (Tövsiyə olunur)

1. **Repository-ni klonlayın:**
```bash
git clone https://github.com/scorpionabid/edulinks-app.git
cd edulinks-app
```

2. **Environment faylını konfiqurasiya edin:**
```bash
cp .env.example .env
# .env faylını redaktə edin
```

3. **Docker konteynerləri işə salın:**
```bash
docker-compose up -d
```

4. **Database məlumatlarını yükləyin:**
```bash
docker-compose exec postgres psql -U edulinks_user -d edulinks -f /docker-entrypoint-initdb.d/install.sql
```

5. **Brauzerdə açın:**
```
http://localhost:8080
```

### Manual Quraşdırma

1. **PHP dependencies quraşdırın:**
```bash
composer install
```

2. **Database yaradın və konfiqurasiya edin:**
```bash
createdb edulinks
psql -d edulinks -f database/install.sql
```

3. **Web server konfiqurasiya edin** (Nginx konfiqurasiyası üçün bax: `docker/nginx/default.conf`)

4. **Permissions təyin edin:**
```bash
chmod -R 775 storage/
chmod -R 775 public/uploads/
```

## 🧪 Test Etmə

### Bütün testləri işə salın:
```bash
php tests/run_tests.php
```

### Ayrı-ayrı test suiteləri:
```bash
php tests/DatabaseTest.php
php tests/AuthTest.php
php tests/ApiTest.php
```

### Sistem tələblərini yoxlayın:
```bash
php tests/run_tests.php --requirements
```

## 📊 İstifadə

### Default Login Məlumatları
```
Admin:
Email: admin@edulinks.az
Parol: admin123

User:
Email: user@edulinks.az
Parol: user123
```

### API Documentation
API endpointləri haqqında ətraflı məlumat üçün bax: [`docs/API.md`](docs/API.md)

## 🏗️ Arxitektura

### MVC Pattern
```
app/
├── controllers/     # İstək idarə edənlər
├── models/         # Məlumat bazası modeli
├── views/          # UI şablonları
└── core/           # Core sistem klassları
```

### Database Schema
```
users              # İstifadəçilər
pages              # Səhifələr (fənnlər)
links              # Linklər və fayllar
page_permissions   # İstifadəçi icazələri
```

## 🔧 Konfigurasiya

### Environment Variables (.env)
```env
# Database
DB_HOST=localhost
DB_PORT=5432
DB_NAME=edulinks
DB_USER=edulinks_user
DB_PASSWORD=your_password

# Application
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.com

# Security
SESSION_LIFETIME=7200
CSRF_TOKEN_LIFETIME=3600

# File Upload
UPLOAD_MAX_SIZE=104857600
ALLOWED_FILE_TYPES=pdf,doc,docx,xls,xlsx,ppt,pptx,txt,jpg,jpeg,png,gif
```

## 📁 Katalog Strukturu

```
edulinks-app/
├── app/
│   ├── controllers/
│   ├── core/
│   ├── includes/
│   ├── models/
│   └── views/
├── database/
├── docs/
├── docker/
├── public/
├── routes/
├── storage/
├── tests/
└── vendor/
```

## 🛠️ Development

### Local Development Environment
```bash
# Docker ilə development
docker-compose up -d

# Log-ları izləyin
docker-compose logs -f app

# Konteynerə daxil olun
docker-compose exec app bash
```

### Code Style
- PSR-4 autoloading
- PSR-12 coding standards
- DocBlock comments

## 🚀 Deployment

### Production Deployment
1. Server-də tələbləri yoxlayın
2. SSL sertifikatı quraşdırın
3. Environment variables təyin edin
4. Database backup planı hazırlayın
5. Log rotation konfiqurasiya edin

### Nginx Konfigurasiyası
Nginx konfigurasiya nümunəsi: [`docker/nginx/default.conf`](docker/nginx/default.conf)

## 🔒 Güvenlik

### Təhlükəsizlik Tədbiri
- HTTPS istifadə edin
- Düzenli backup alın
- Log faylları izləyin
- Dependency-ləri yeniləyin
- Database icazələrini məhdudlaşdırın

### Fayl Güvenliği
- MIME type yoxlama
- Fayl ölçüsü məhdudiyyəti
- Virus skanı (tövsiyə olunur)
- Upload kataloqunun izolasiyası

## 📝 API

### Mövcud Endpointlər
- `GET /api/user/pages` - İstifadəçinin səhifələri
- `GET /api/user/stats` - İstifadəçi statistikaları
- `POST /api/links/{id}/click` - Link klik qeydiyyatı
- `GET /api/search` - Link axtarışı

Tam API documentation: [`docs/API.md`](docs/API.md)

## 🤝 Təkmilləşdirmə

### Contribution Guidelines
1. Fork repository
2. Feature branch yaradın
3. Test yazın
4. Code review istəyin
5. Merge edin

### Bug Report
GitHub Issues vasitəsilə bug report edin:
- Bug təsviri
- Reproduce addımları
- Gözlənilən və faktiki nəticə
- Environment məlumatları

## 📄 License

Bu layihə MIT lisenzi altında paylaşılır. Ətraflı məlumat üçün LICENSE faylına baxın.

## 👥 Müəlliflər

- **Abid Məmmədov** - Lead Developer - [@scorpionabid](https://github.com/scorpionabid)

## 🙏 Təşəkkürlər

- Bootstrap UI framework
- Font Awesome iconlar
- PostgreSQL database
- PHP community

## 📞 Support

Suallar və dəstək üçün:
- GitHub Issues: [Issues page](https://github.com/scorpionabid/edulinks-app/issues)
- Email: [support@edulinks.az](mailto:support@edulinks.az)

---

**EduLinks** - Təhsildə fərq yaradan texnologiya! 🚀
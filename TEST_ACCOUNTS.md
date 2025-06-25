# EduLinks Test Hesabları

Bu sənəd EduLinks sistemində test etmək üçün mövcud hesabları ehtiva edir.

## 🔑 Test Hesabları

### Admin Hesabı
```
Email: admin@edulinks.az
Parol: password
Rol: Admin
Status: Aktiv
```
✅ **TEST EDİLDİ - İŞLƏYİR**

**Admin İcazələri:**
- Bütün səhifələrə giriş
- İstifadəçi idarəetməsi (CRUD)
- Səhifə idarəetməsi (CRUD)
- Link idarəetməsi (CRUD)
- İcazə sistemi idarəetməsi
- Sistem statistikaları
- Fayl yükləmə və silmə

### İstifadəçi Hesabı
```
Email: user@edulinks.az
Parol: password
Rol: User
Status: Aktiv
```
✅ **TEST EDİLDİ - İŞLƏYİR**

**İstifadəçi İcazələri:**
- Yalnız icazəli səhifələrə giriş
- Link görüntüləmə və endirmə
- Axtarış funksiyası
- Profil redaktəsi
- Klik statistikalarının görülməsi

## 🌐 Sistem URL-ləri

### Ana URL
```
http://localhost:8080
```

### Giriş Səhifələri
```
Login: http://localhost:8080/login
Admin Panel: http://localhost:8080/admin
İstifadəçi Panel: http://localhost:8080/user
```

### API Endpointləri
```
Health Check: http://localhost:8080/health
API Base: http://localhost:8080/api/
```

## 📋 Default Səhifələr

Sistemdə aşağıdakı default səhifələr mövcuddur:

1. **Riyaziyyat** (slug: `riyaziyyat`)
   - Rəng: #007bff (mavi)
   - İkon: fas fa-calculator

2. **Fizika** (slug: `fizika`)
   - Rəng: #28a745 (yaşıl)
   - İkon: fas fa-atom

3. **Kimya** (slug: `kimya`)
   - Rəng: #dc3545 (qırmızı)
   - İkon: fas fa-flask

4. **Biologiya** (slug: `biologiya`)
   - Rəng: #17a2b8 (teal)
   - İkon: fas fa-dna

5. **Tarix** (slug: `tarix`)
   - Rəng: #ffc107 (sarı)
   - İkon: fas fa-monument

## 🧪 Test Ssenariləri

### Admin Test Ssenariləri:

1. **Giriş Testi**
   - `admin@edulinks.az` / `password` ilə daxil olun
   - Admin panelə yönləndirilməli
   - Dashboard statistikaları görünməli

2. **İstifadəçi İdarəetməsi**
   - Yeni istifadəçi yaradın
   - İstifadəçi məlumatlarını redaktə edin
   - İcazələr təyin edin

3. **Səhifə İdarəetməsi**
   - Yeni səhifə yaradın
   - Səhifə rəngi və ikonunu dəyişin
   - Səhifəni deaktiv edin

4. **Link İdarəetməsi**
   - URL link əlavə edin
   - Fayl yükləyin
   - Link-i featured edin

### İstifadəçi Test Ssenariləri:

1. **Giriş Testi**
   - `user@edulinks.az` / `password` ilə daxil olun
   - İstifadəçi panelə yönləndirilməli
   - Yalnız icazəli səhifələr görünməli

2. **Səhifə Görüntüləmə**
   - Default səhifələri açın
   - Linkləri test edin
   - Axtarış funksiyasını istifadə edin

3. **Profil İdarəetməsi**
   - Profil məlumatlarını dəyişin
   - Parol dəyişin

## 🔒 Təhlükəsizlik Testləri

### CSRF Testi
- Formları CSRF token olmadan göndərməyə çalışın
- Fərqli sessiondan CSRF token istifadə edin

### Session Testi
- Browser tab-ları arasında session paylaşımını test edin
- Logout sonrası səhifələrə giriş cəhdini test edin

### İcazə Testi
- İstifadəçi rolunda admin səhifələrinə giriş cəhdi
- Başqa istifadəçinin səhifələrinə giriş cəhdi

## 📊 Database Bağlantı Məlumatları

### PostgreSQL
```
Host: localhost
Port: 5432
Database: edulinks
Username: edulinks_user
Password: edulinks_password
```

### pgAdmin (Development)
```
URL: http://localhost:8081
Email: admin@edulinks.local
Password: admin123
```

### Redis (Cache)
```
Host: localhost
Port: 6379
Password: (yoxdur)
```

## 🐛 Məlum Problemlər

1. **PHP OPcache Warning**
   - Log-larda "Cannot load Zend OPcache" xəbərdarlığı
   - Funksionallığa təsir etmir

2. **Nginx Health Check**
   - Docker Compose-da unhealthy status
   - `/health` endpoint işləyir

## 📝 Test Nəticələrini Qeyd Etmək

Test zamanı aşkarladığınız problemləri GitHub Issues-a əlavə edin:
https://github.com/scorpionabid/edulinks-app/issues

### Problem Report Formatı:
```
**Başlıq:** Qısa problem təsviri
**Addımlar:** Reproduce etmək üçün addımlar
**Gözlənilən:** Nə olmalı idi
**Faktiki:** Nə oldu
**Browser:** Chrome/Firefox/Safari versiyası
**Screenshot:** Əgər varsa
```

## 🚀 Performans Testləri

### Load Testing
```bash
# Apache Bench ilə test (əgər qurulubsa)
ab -n 1000 -c 10 http://localhost:8080/login

# Curl ilə multiple requests
for i in {1..100}; do curl -s http://localhost:8080/health; done
```

### Memory Usage
```bash
# Container memory usage
docker stats edulinks_app --no-stream

# PHP memory usage
docker exec edulinks_app php -r "echo memory_get_peak_usage(true) / 1024 / 1024 . ' MB';"
```

---

**Son Yenilənmə:** 25 Iyun 2025  
**Test Edilən Versiya:** v1.0.0-alpha  
**Docker Compose Versiyası:** Latest